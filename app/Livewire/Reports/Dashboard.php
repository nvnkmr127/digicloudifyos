<?php

namespace App\Livewire\Reports;

use App\Models\Client;
use App\Models\Report;
use App\Services\ReportGeneratorService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $showCreateModal = false;

    public $reportName;

    public $reportType = 'campaign';

    public $reportFormat = 'pdf';

    public $clientId;

    public $dateRange = 30;

    public function generateReport(ReportGeneratorService $service)
    {
        $this->validate([
            'reportName' => 'required|string|max:255',
            'reportType' => 'required',
            'reportFormat' => 'required',
            'clientId' => 'nullable|exists:clients,id',
        ]);

        $report = Report::create([
            'organization_id' => Auth::user()->organization_id,
            'client_id' => $this->clientId,
            'name' => $this->reportName,
            'type' => $this->reportType,
            'format' => $this->reportFormat,
            'parameters' => [
                'days' => $this->dateRange,
            ],
            'status' => 'PENDING',
        ]);

        try {
            $service->generate($report);
            $this->showCreateModal = false;
            $this->reset(['reportName', 'reportType', 'reportFormat', 'clientId']);
            session()->flash('success', 'Report generated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate report: '.$e->getMessage());
        }
    }

    public function download($reportId)
    {
        $report = Report::findOrFail($reportId);
        if ($report->file_path && \Storage::disk('public')->exists($report->file_path)) {
            $path = \Storage::disk('public')->path($report->file_path);

            return response()->download($path);
        }
        session()->flash('error', 'File not found.');
    }

    public function deleteReport($id)
    {
        $report = Report::findOrFail($id);
        if ($report->file_path) {
            \Storage::disk('public')->delete($report->file_path);
        }
        $report->delete();
        session()->flash('success', 'Report deleted.');
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        $reports = Report::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $clients = Client::where('organization_id', $organizationId)->get();

        return view('livewire.reports.dashboard', [
            'reports' => $reports,
            'clients' => $clients,
        ])->layout('layouts.app');
    }
}
