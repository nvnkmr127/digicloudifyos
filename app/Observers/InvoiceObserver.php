<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Jobs\ProcessWorkflowAutomation;

class InvoiceObserver
{
    public function updated(Invoice $invoice)
    {
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
}
