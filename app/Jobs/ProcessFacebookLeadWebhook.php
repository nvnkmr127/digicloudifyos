<?php

namespace App\Jobs;

use App\Models\AdAccount;
use App\Models\FacebookLead;
use App\Models\User;
use App\Notifications\NewFacebookLeadNotification;
use App\Services\MetaAdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProcessFacebookLeadWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public string $leadgenId,
        public ?string $pageId = null,
        public ?string $formId = null
    ) {
        $this->onQueue('leads');
    }

    public function handle(): void
    {
        Log::info('Processing Facebook Webhook Lead', ['leadgen_id' => $this->leadgenId]);

        // Find the ad account associated with this page
        $adAccount = AdAccount::where('facebook_page_id', $this->pageId)
            ->whereNotNull('facebook_page_token')
            ->first();

        if (!$adAccount) {
            Log::warning('No connected Ad Account found for Facebook Page ID', ['page_id' => $this->pageId]);
            return;
        }

        $service = new MetaAdsService();
        $lead = $service->syncLead($adAccount, $this->leadgenId, $this->formId);

        if ($lead) {
            Log::info('Successfully processed webhook lead', [
                'facebook_lead_id' => $lead->facebook_lead_id,
                'email' => $lead->email
            ]);

            // Notify sales / admins for this organization
            $usersToNotify = User::where('organization_id', $adAccount->organization_id)
                ->whereIn('role', ['OWNER', 'ADMIN'])
                ->get();

            if ($usersToNotify->isNotEmpty()) {
                Notification::send($usersToNotify, new NewFacebookLeadNotification($lead));
            }

            // Trigger Automation Workflow
            ProcessWorkflowAutomation::dispatch('lead_captured', [
                'organization_id' => $adAccount->organization_id,
                'entity_type' => 'lead',
                'entity_id' => $lead->id,
                'email' => $lead->email,
                'full_name' => $lead->full_name,
                'form_name' => $lead->form_name,
            ]);

        } else {
            Log::error('Individual lead sync failed during webhook processing', ['leadgen_id' => $this->leadgenId]);
        }
    }
}
