<?php

namespace App\Observers;

use App\Jobs\ProcessWorkflowAutomation;
use App\Models\FacebookLead;
use App\Models\Lead;

class LeadObserver
{
    /**
     * Handle the Lead "created" event.
     */
    public function created(Lead $lead): void
    {
        ProcessWorkflowAutomation::dispatch('lead_created', [
            'organization_id' => $lead->organization_id,
            'entity_type' => 'lead',
            'entity_id' => $lead->id,
            'email' => $lead->email,
            'full_name' => $lead->name,
            'phone' => $lead->phone,
            'source' => $lead->source,
            'status' => $lead->status,
        ]);
    }

    /**
     * Handle the Lead "deleted" event.
     */
    public function deleted(Lead $lead): void
    {
        if ($lead->email) {
            FacebookLead::where('email', $lead->email)
                ->where('organization_id', $lead->organization_id)
                ->delete();
        }
    }
}
