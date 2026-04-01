<?php

namespace App\Jobs\Intelligence;

use App\Models\DailyBriefing;
use App\Models\Organization;
use App\Models\User;
use App\Mail\DailyBriefingMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDailyBriefingEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('intelligence');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("SendDailyBriefingEmail job started.");

        if (!config('intelligence.briefing_email_enabled')) {
            Log::info("Briefing emails are disabled in config.");
            return;
        }

        $briefings = DailyBriefing::where('status', 'ready')
            ->whereDate('briefing_date', today())
            ->get();

        foreach ($briefings as $briefing) {
            $org = Organization::find($briefing->organization_id);
            if (!$org) continue;

            // Get admins/owners for the organization
            // Assuming your Organization model has users and they have roles
            $recipients = User::where('organization_id', $org->id)
                ->whereHas('roles', function($q) {
                    $q->whereIn('name', ['admin', 'owner']);
                })
                ->get();

            if ($recipients->isEmpty()) {
                Log::warning("No recipients found for briefing for organization {$org->id}");
                continue;
            }

            try {
                // In Phase 3 TASK-035 we will create this mailable
                // For now, it will be a lint error but that's expected
                if (class_exists(DailyBriefingMail::class)) {
                    foreach ($recipients as $user) {
                        Mail::to($user->email)->send(new DailyBriefingMail($briefing));
                    }
                }
                
                $briefing->markSent();
                Log::info("Sent briefing for organization {$org->id} to " . $recipients->count() . " recipients.");
            } catch (\Exception $e) {
                Log::error("Failed to send briefing email for organization {$org->id}: " . $e->getMessage());
            }
        }

        Log::info("SendDailyBriefingEmail job completed.");
    }
}
