<?php

namespace App\Livewire\WorkflowMonitoring;

use App\Models\AutomationLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Logs extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $selectedLog = null;

    public function viewDetails($id)
    {
        $organizationId = Auth::user()->organization_id;

        $log = AutomationLog::with(['rule'])
            ->whereHas('rule', fn ($query) => $query->where('organization_id', $organizationId))
            ->find($id);

        if ($log) {
            // S031: Redact PII on-the-fly for inspector view
            $redact = function (&$data) use (&$redact) {
                if (! is_array($data)) {
                    return;
                }
                $sensitiveKeys = ['password', 'token', 'secret', 'key', 'auth', 'cvv'];
                foreach ($data as $k => &$v) {
                    if (in_array(strtolower($k), $sensitiveKeys)) {
                        $v = '[REDACTED]';
                    } elseif (is_array($v)) {
                        $redact($v);
                    }
                }
            };

            $details = $log->details;
            $result = $log->result;

            $redact($details);
            $redact($result);

            $log->details = $details;
            $log->result = $result;

            $this->selectedLog = $log;
        }
    }

    public function closeInspector()
    {
        $this->selectedLog = null;
    }

    public function render()
    {
        $organizationId = Auth::user()->organization_id;

        $logs = AutomationLog::with(['rule'])
            ->whereHas('rule', fn ($query) => $query->where('organization_id', $organizationId))
            ->when($this->search, function ($query) {
                $query->whereHas('rule', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.workflow-monitoring.logs', [
            'logs' => $logs,
        ])->layout('layouts.app');
    }
}
