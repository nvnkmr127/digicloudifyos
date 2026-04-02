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
        $insights = AdInsight::where('organization_id', $orgId)
            ->where('date', '>=', $startDate)
            ->where('level', 'account')
            ->selectRaw('SUM(spend) as total_spend, SUM(conversions) as total_conversions, AVG(roas) as avg_roas')
            ->first();

        $totalLeads = FacebookLead::where('organization_id', $orgId)
            ->where('created_at', '>=', $startDate)
            ->count();

        $overview = [
            'total_spend' => $insights->total_spend ?? 0,
            'total_leads' => $totalLeads,
            'avg_cpl' => $totalLeads > 0 ? ($insights->total_spend / $totalLeads) : 0,
            'avg_roas' => $insights->avg_roas ?? 0,
        ];

        // 2. Campaigns
        $campaigns = Campaign::where('organization_id', $orgId)
            ->with(['adInsights' => function ($q) use ($startDate) {
                $q->where('date', '>=', $startDate)->where('level', 'campaign');
            }])
            ->get()
            ->map(function ($campaign) {
                $spend = $campaign->adInsights->sum('spend');
                $leads = FacebookLead::where('campaign_id', $campaign->id)->count();
                $impressions = $campaign->adInsights->sum('impressions');
                $clicks = $campaign->adInsights->sum('clicks');

                return [
                    'name' => $campaign->name,
                    'spend' => $spend,
                    'leads' => $leads,
                    'cpl' => $leads > 0 ? $spend / $leads : 0,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                ];
            })->sortByDesc('spend')->values()->toArray();

        // 3. Creatives
        $creatives = Creative::where('organization_id', $orgId)
            ->with(['ad.adInsights' => function ($q) use ($startDate) {
                $q->where('date', '>=', $startDate)->where('level', 'ad');
            }])
            ->get()
            ->map(function ($creative) {
                $ad = $creative->ad;
                if (! $ad) {
                    return null;
                }
                $insights = $ad->adInsights;
                $spend = $insights->sum('spend');
                $leads = FacebookLead::where('ad_id', $ad->id)->count();
                $impressions = $insights->sum('impressions');
                $clicks = $insights->sum('clicks');

                return [
                    'asset_name' => $creative->headline ?: $creative->creative_id,
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
                0, // impressions dummy if not in map
                0, // clicks dummy
                $camp['leads'],
                round($camp['ctr'], 2),
                round($camp['cpl'], 2),
                0, // roas dummy
            ];
        }

        return $rows;
    }
}
