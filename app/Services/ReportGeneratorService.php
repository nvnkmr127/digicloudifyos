<?php

namespace App\Services;

use App\Exports\AdsPerformanceExport;
use App\Models\AdInsight;
use App\Models\Campaign;
use App\Models\Creative;
use App\Models\FacebookLead;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportGeneratorService
{
    public function generate(Report $report)
    {
        $report->update(['status' => 'PROCESSING']);

        try {
            $data = $this->prepareData($report);
            $fileName = 'reports/'.Str::slug($report->name).'-'.time();
            $path = '';

            if ($report->format === 'pdf') {
                $path = $fileName.'.pdf';
                $pdf = Pdf::loadView('reports.performance', $data);
                Storage::disk('public')->put($path, $pdf->output());
            } else {
                $path = $fileName.'.xlsx';
                $exportData = $this->mapForExcel($data);
                Excel::store(new AdsPerformanceExport($exportData, $report->name), $path, 'public');
            }

            $report->update([
                'status' => 'COMPLETED',
                'file_path' => $path,
                'generated_at' => now(),
            ]);

            return $report;
        } catch (\Exception $e) {
            $report->update(['status' => 'FAILED', 'parameters' => array_merge($report->parameters ?? [], ['error' => $e->getMessage()])]);
            throw $e;
        }
    }

    protected function prepareData(Report $report)
    {
        $orgId = $report->organization_id;
        $params = $report->parameters;
        $days = $params['days'] ?? 30;
        $startDate = now()->subDays($days)->toDateString();

        // 1. Overview
        $insightsOverview = AdInsight::where('organization_id', $orgId)
            ->where('date', '>=', $startDate)
            ->where('level', 'account')
            ->selectRaw('SUM(spend) as total_spend, SUM(revenue) as total_revenue, SUM(conversions) as total_conversions')
            ->first();

        $totalLeads = FacebookLead::where('organization_id', $orgId)
            ->where('created_at', '>=', $startDate)
            ->count();

        $overview = [
            'total_spend' => (float) ($insightsOverview->total_spend ?? 0),
            'total_leads' => $totalLeads,
            'avg_cpl' => $totalLeads > 0 ? ($insightsOverview->total_spend / $totalLeads) : 0,
            'avg_roas' => ($insightsOverview->total_spend > 0) ? ($insightsOverview->total_revenue / $insightsOverview->total_spend) : 0,
        ];

        // Performance: Bulk aggregate leads to avoid N+1 (D018)
        $campaignLeads = FacebookLead::where('organization_id', $orgId)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('campaign_id')
            ->selectRaw('campaign_id, COUNT(*) as lead_count')
            ->groupBy('campaign_id')
            ->pluck('lead_count', 'campaign_id');

        $adLeads = FacebookLead::where('organization_id', $orgId)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('ad_id')
            ->selectRaw('ad_id, COUNT(*) as lead_count')
            ->groupBy('ad_id')
            ->pluck('lead_count', 'ad_id');

        // 2. Campaigns
        $campaigns = Campaign::with(['adInsights' => function ($q) use ($startDate) {
            $q->where('date', '>=', $startDate)->where('level', 'campaign');
        }])
            ->where('organization_id', $orgId)
            ->get()
            ->map(function ($campaign) use ($campaignLeads) {
                $spend = $campaign->adInsights->sum('spend');
                $impressions = $campaign->adInsights->sum('impressions');
                $clicks = $campaign->adInsights->sum('clicks');
                $revenue = $campaign->adInsights->sum('revenue');
                $leads = $campaignLeads->get($campaign->id, 0);

                return [
                    'name' => $campaign->name,
                    'spend' => $spend,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'leads' => $leads,
                    'cpl' => $leads > 0 ? $spend / $leads : 0,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                    'roas' => $spend > 0 ? $revenue / $spend : 0,
                ];
            })->sortByDesc('spend')->values()->toArray();

        // 3. Creatives
        $creatives = Creative::with(['ad.adInsights' => function ($q) use ($startDate) {
            $q->where('date', '>=', $startDate)->where('level', 'ad');
        }])
            ->where('organization_id', $orgId)
            ->get()
            ->map(function ($creative) use ($adLeads) {
                $ad = $creative->ad;
                if (! $ad) {
                    return null;
                }

                $insights = $ad->adInsights;
                $impressions = $insights->sum('impressions');
                $clicks = $insights->sum('clicks');
                $leads = $adLeads->get($ad->id, 0);

                return [
                    'asset_name' => $creative->headline ?: ($creative->name ?? $creative->creative_id),
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                    'leads' => $leads,
                    'engagement_rate' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                ];
            })->filter()->sortByDesc('leads')->values()->toArray();

        return [
            'reportName' => $report->name,
            'clientName' => $report->client?->name ?? 'Default Account',
            'dateRange' => "Last $days Days",
            'overview' => $overview,
            'campaigns' => $campaigns,
            'creatives' => $creatives,
        ];
    }

    protected function mapForExcel($data)
    {
        $rows = [];
        foreach ($data['campaigns'] as $camp) {
            $rows[] = [
                $camp['name'],
                $camp['spend'],
                (int) ($camp['impressions'] ?? 0),
                (int) ($camp['clicks'] ?? 0),
                $camp['leads'],
                round($camp['ctr'], 2),
                round($camp['cpl'], 2),
                round((float) ($camp['roas'] ?? 0), 2),
            ];
        }

        return $rows;
    }
}
