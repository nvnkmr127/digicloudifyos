<?php

namespace App\Livewire\Ads;

use App\Jobs\SyncFacebookLeads;
use App\Models\AdAccount;
use App\Models\FacebookLead;
use App\Models\LeadSyncLog;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use Livewire\WithPagination;

class Leads extends Component
{
    use WithPagination;

    public $search = '';

    public $formFilter = '';

    public $statusFilter = '';

    public $showLogsModal = false;

    public function mount()
    {
        if (auth()->user()->role !== 'ADMIN' && auth()->user()->role !== 'OWNER') {
            abort(403, 'Unauthorized access to leads.');
        }
    }

    public function syncLeads()
    {
        $organizationId = auth()->user()->organization_id;
        $adAccounts = AdAccount::where('organization_id', $organizationId)
            ->where('platform', 'META_ADS')
            ->whereNotNull('facebook_page_id')
            ->get();

        if ($adAccounts->isEmpty()) {
            session()->flash('error', 'No active Meta Ad Accounts found with a connected Facebook Page.');

            return;
        }

        foreach ($adAccounts as $account) {
            SyncFacebookLeads::dispatch($account, 'manual');
        }

        session()->flash('message', 'Lead synchronization started in the background.');
    }

    public function viewSyncLogs()
    {
        $this->showLogsModal = true;
    }

    public function exportLeads()
    {
        $organizationId = auth()->user()->organization_id;

        $leads = FacebookLead::with(['campaign', 'crmLead'])
            ->where('organization_id', $organizationId)
            ->when($this->search, function ($query) {
                $query->where('full_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->when($this->formFilter, function ($query) {
                $query->where('form_name', $this->formFilter);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $csvHeader = ['Date', 'Name', 'Email', 'Phone', 'Form Name', 'Campaign', 'Status'];
        $csvData = [];
        $csvData[] = implode(',', $csvHeader);

        foreach ($leads as $lead) {
            $row = [
                $lead->created_at->format('Y-m-d H:i:s'),
                '"'.str_replace('"', '""', $lead->full_name).'"',
                $lead->email,
                $lead->phone_number,
                '"'.str_replace('"', '""', $lead->form_name).'"',
                '"'.str_replace('"', '""', $lead->campaign?->name ?? 'Unknown').'"',
                $lead->crmLead?->status ?? 'Unknown',
            ];
            $csvData[] = implode(',', $row);
        }

        $csvString = implode("\n", $csvData);

        return Response::streamDownload(function () use ($csvString) {
            echo $csvString;
        }, 'leads_export_'.now()->format('Ymd_His').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        $organizationId = auth()->user()->organization_id;

        $query = FacebookLead::with(['campaign', 'crmLead'])
            ->where('organization_id', $organizationId)
            ->when($this->search, function ($query) {
                $query->where('full_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->when($this->formFilter, function ($query) {
                $query->where('form_name', $this->formFilter);
            });

        if ($this->statusFilter) {
            $query->whereHas('crmLead', function ($q) {
                $q->where('status', $this->statusFilter);
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(15);

        $forms = FacebookLead::where('organization_id', $organizationId)
            ->whereNotNull('form_name')
            ->distinct()
            ->pluck('form_name');

        $syncLogs = collect();
        if ($this->showLogsModal) {
            $syncLogs = LeadSyncLog::where('organization_id', $organizationId)
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get();
        }

        return view('livewire.ads.leads', [
            'leads' => $leads,
            'forms' => $forms,
            'statuses' => ['New', 'Contacted', 'Interested', 'Offer Sent', 'Won', 'Lost'],
            'syncLogs' => $syncLogs,
        ])->layout('layouts.app');
    }
}
