<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PdfExportController extends Controller
{
    public function __invoke(Request $request)
    {
        $orgId = Auth::user()->organization_id;

        [$start, $end] = $this->resolveDateRange((string) $request->query('dateRange', 'this_month'));

        $reportData = [
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
                'new_clients' => Client::where('organization_id', $orgId)
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ],
            'meta' => [
                'date_range' => $this->labelDateRange($start, $end),
                'generated_at' => now(),
            ],
        ];

        $pdf = Pdf::loadView('reports.pdf', [
            'reportData' => $reportData,
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="report.pdf"',
        ]);
    }

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

    private function labelDateRange(Carbon $start, Carbon $end): string
    {
        return $start->format('M d, Y').' – '.$end->format('M d, Y');
    }
}
