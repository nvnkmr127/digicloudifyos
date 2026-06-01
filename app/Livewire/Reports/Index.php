<?php

namespace App\Livewire\Reports;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $reportType = 'financial';

    public $dateRange = 'this_month';

    private function resolveDateRange(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'this_quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        [$start, $end] = $this->resolveDateRange((string) $this->dateRange);

        $data = [
            'financial' => [
                'total_invoiced' => Invoice::where('organization_id', $orgId)
                    ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
                    ->sum('total_amount'),
                'total_paid' => Invoice::where('organization_id', $orgId)
                    ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
                    ->where('status', 'paid')
                    ->sum('total_amount'),
                'pending_amount' => Invoice::where('organization_id', $orgId)
                    ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
                    ->where('status', '!=', 'paid')
                    ->sum('total_amount'),
            ],
            'performance' => [
                'active_projects' => Project::where('organization_id', $orgId)->where('status', 'active')->count(),
                'completed_projects' => Project::where('organization_id', $orgId)->where('status', 'completed')->count(),
                'total_hours' => TimeEntry::where('organization_id', $orgId)
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->sum('hours'),
            ],
            'clients' => [
                'total_clients' => Client::where('organization_id', $orgId)->count(),
                'new_clients_in_range' => Client::where('organization_id', $orgId)
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ],
        ];

        $chartStart = $end->copy()->subDays(13)->startOfDay();
        if ($chartStart->lt($start)) {
            $chartStart = $start->copy()->startOfDay();
        }

        $invoiceSeries = Invoice::where('organization_id', $orgId)
            ->whereBetween('issue_date', [$chartStart->toDateString(), $end->toDateString()])
            ->selectRaw('issue_date as d, SUM(total_amount) as v')
            ->groupBy('issue_date')
            ->pluck('v', 'd');

        $hoursSeries = TimeEntry::where('organization_id', $orgId)
            ->whereBetween('date', [$chartStart->toDateString(), $end->toDateString()])
            ->selectRaw('date as d, SUM(hours) as v')
            ->groupBy('date')
            ->pluck('v', 'd');

        $days = [];
        $cursor = $chartStart->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $days[] = [
                'label' => $cursor->format('M d'),
                'invoiced' => (float) ($invoiceSeries[$key] ?? 0),
                'hours' => (float) ($hoursSeries[$key] ?? 0),
            ];
            $cursor->addDay();
        }

        return view('livewire.reports.index', [
            'reportData' => $data,
            'trendDays' => $days,
        ])->layout('layouts.app');
    }
}
