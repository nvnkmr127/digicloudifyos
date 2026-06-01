<?php

namespace App\Observers;

use App\Jobs\ProcessWorkflowAutomation;
use App\Models\Invoice;
use App\Services\AgencyManagementService;

class InvoiceObserver
{
    public function updated(Invoice $invoice)
    {
        // Security: Clear agency dashboard cache when financial data changes (B002)
        app(AgencyManagementService::class)->clearCache($invoice->organization_id);

        if ($invoice->isDirty('status')) {
            $event = null;
            if ($invoice->status === 'paid') {
                $event = 'invoice_paid';
            } elseif ($invoice->status === 'sent') {
                $event = 'invoice_sent';
            } elseif ($invoice->status === 'overdue') {
                $event = 'invoice_overdue';
            }

            if ($event) {
                ProcessWorkflowAutomation::dispatch($event, [
                    'organization_id' => $invoice->organization_id,
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                    'total_amount' => $invoice->total_amount,
                ]);
            }
        }
    }

    public function created(Invoice $invoice)
    {
        app(AgencyManagementService::class)->clearCache($invoice->organization_id);
    }

    public function deleted(Invoice $invoice)
    {
        app(AgencyManagementService::class)->clearCache($invoice->organization_id);
    }
}
