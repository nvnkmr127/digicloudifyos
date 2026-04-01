<?php

namespace App\Jobs\Intelligence;

use App\Models\DailyBriefing;
use App\Models\BriefingActionItem;
use App\Models\AiInsight;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateDailyBriefing implements ShouldQueue
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
        Log::info("GenerateDailyBriefing job started.");

        $organizations = Organization::all();
        $date = today()->toDateString();

        foreach ($organizations as $org) {
            $briefing = DailyBriefing::updateOrCreate(
                ['organization_id' => $org->id, 'briefing_date' => $date],
                ['status' => 'generating']
            );

            // Get all insights for today
            $insights = AiInsight::where('organization_id', $org->id)
                ->whereDate('insight_date', $date)
                ->where('is_dismissed', false)
                ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low', 'opportunity')")
                ->get();

            $briefing->actionItems()->delete(); // Clear if re-generating

            $itemsCount = 0;
            $urgentCount = 0;
            $opportunityCount = 0;

            foreach ($insights as $index => $insight) {
                $priorityLevel = match($insight->priority) {
                    'critical', 'high' => 'urgent',
                    'opportunity' => 'opportunity',
                    default => 'important',
                };

                BriefingActionItem::create([
                    'briefing_id' => $briefing->id,
                    'client_id' => $insight->client_id,
                    'ai_insight_id' => $insight->id,
                    'sort_order' => $index,
                    'priority_level' => $priorityLevel,
                    'title' => $insight->title,
                    'description' => $insight->issue_description,
                    'action' => $insight->recommended_action,
                    'expected_impact' => $insight->expected_impact,
                    'effort' => $insight->effort_level,
                ]);

                $itemsCount++;
                if ($priorityLevel === 'urgent') $urgentCount++;
                if ($priorityLevel === 'opportunity') $opportunityCount++;
            }

            $briefing->update([
                'status' => 'ready',
                'total_clients_analyzed' => $insights->unique('client_id')->count(),
                'critical_alerts_count' => $urgentCount,
                'opportunities_count' => $opportunityCount,
                'generated_at' => now(),
            ]);
        }

        Log::info("GenerateDailyBriefing job completed.");
    }
}
