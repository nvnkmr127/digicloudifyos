<?php

namespace App\Jobs;

use App\Models\AdAccount;
use App\Models\LeadSyncLog;
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

    public function handle(MetaAdsService $service): void
    {
        Log::info('Processing Facebook Webhook Lead', ['leadgen_id' => $this->leadgenId]);

        // Find the ad account associated with this page
        $adAccount = AdAccount::where('facebook_page_id', $this->pageId)
            ->whereNotNull('facebook_page_token')
            ->first();

        if (! $adAccount) {
            Log::warning('No connected Ad Account found for Facebook Page ID', ['page_id' => $this->pageId]);

            return;
        }

        $syncLog = LeadSyncLog::create([
            'organization_id' => $adAccount->organization_id,
            'ad_account_id' => $adAccount->id,
            'source' => 'webhook',
            'status' => 'processing',
            'details' => ['leadgen_id' => $this->leadgenId, 'form_id' => $this->formId],
        ]);

        try {
            $lead = $service->syncLead($adAccount, $this->leadgenId, $this->formId);

            if ($lead) {
                $syncLog->update([
                    'status' => 'success',
                    'leads_processed' => 1,
                ]);

                Log::info('Successfully processed webhook lead', [
                    'facebook_lead_id' => $lead->facebook_lead_id,
                    'email' => $lead->email,
                ]);

                // Notify sales / admins for this organization
                $usersToNotify = User::where('organization_id', $adAccount->organization_id)
                    ->whereIn('role', ['OWNER', 'ADMIN'])
                    ->get();

                if ($usersToNotify->isNotEmpty()) {
                    Notification::send($usersToNotify, new NewFacebookLeadNotification($lead));
                }

                // REMOVED redundant ProcessWorkflowAutomation dispatch.
                // The LeadObserver already dispatches 'lead_created' when MetaAdsService calls Lead::firstOrCreate.

            } else {
                $syncLog->update([
                    'status' => 'failed',
                    'error_message' => 'Individual lead sync returned null.',
                ]);
                Log::error('Individual lead sync failed during webhook processing', ['leadgen_id' => $this->leadgenId]);
            }
        } catch (\Exception $e) {
            $syncLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'details' => ['leadgen_id' => $this->leadgenId, 'trace' => $e->getTraceAsString()],
            ]);
            throw $e;
        }
    }
}
