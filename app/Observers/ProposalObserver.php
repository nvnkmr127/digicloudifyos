<?php

namespace App\Observers;

use App\Models\Proposal;
use App\Jobs\ProcessWorkflowAutomation;

class ProposalObserver
{
    public function updated(Proposal $proposal)
    {
        if ($proposal->isDirty('status')) {
            $event = null;
            if ($proposal->status === 'sent') {
                $event = 'proposal_sent';
            } elseif ($proposal->status === 'accepted') {
                $event = 'proposal_accepted';
            } elseif ($proposal->status === 'declined') {
                $event = 'proposal_declined';
            }

            if ($event) {
                ProcessWorkflowAutomation::dispatch($event, [
                    'organization_id' => $proposal->organization_id,
                    'proposal_id' => $proposal->id,
                    'client_id' => $proposal->client_id,
                    'total_amount' => $proposal->total_amount,
                ]);
            }
        }
    }
}
