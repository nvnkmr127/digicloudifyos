<?php

namespace App\Jobs\Deliverables;

use App\Models\Client;
use App\Models\ClientDeliverable;
use App\Models\DeliverableTemplate;
use App\Models\Organization;
use App\Models\PerformanceSnapshot;
use App\Models\SeoOpportunity;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDailyDeliverables implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();
        $day = Carbon::parse($date);

        foreach (Organization::all() as $org) {
            $templates = DeliverableTemplate::where('organization_id', $org->id)->where('is_active', true)->get();
            if ($templates->isEmpty()) {
                DeliverableTemplate::create([
                    'organization_id' => $org->id,
                    'name' => 'Weekly Growth Summary',
                    'frequency' => 'weekly',
                    'is_active' => true,
                ]);

                DeliverableTemplate::create([
                    'organization_id' => $org->id,
                    'name' => 'Monthly Performance Review',
                    'frequency' => 'monthly',
                    'is_active' => true,
                ]);

                $templates = DeliverableTemplate::where('organization_id', $org->id)->where('is_active', true)->get();
            }

            $clients = Client::where('organization_id', $org->id)->active()->get(['id', 'name']);

            foreach ($clients as $client) {
                foreach ($templates as $template) {
                    if (! $this->shouldRunTemplate($template->frequency, $day)) {
                        continue;
                    }
                    $this->generateForClient($org->id, $client, $template, $date);
                }
            }
        }
    }

    protected function shouldRunTemplate(string $frequency, Carbon $day): bool
    {
        if ($frequency === 'weekly') {
            return $day->isMonday();
        }
        if ($frequency === 'monthly') {
            return $day->day === 1;
        }

        return false;
    }

    protected function generateForClient(string $orgId, Client $client, DeliverableTemplate $template, string $date): void
    {
        $exists = ClientDeliverable::where('organization_id', $orgId)
            ->where('client_id', $client->id)
            ->where('deliverable_template_id', $template->id)
            ->whereDate('deliverable_date', $date)
            ->exists();

        if ($exists) {
            return;
        }

        $deliverable = ClientDeliverable::create([
            'organization_id' => $orgId,
            'client_id' => $client->id,
            'deliverable_template_id' => $template->id,
            'deliverable_date' => $date,
            'title' => $template->name.' — '.$client->name,
            'status' => 'scheduled',
        ]);

        try {
            $snapshots = PerformanceSnapshot::where('organization_id', $orgId)
                ->where('client_id', $client->id)
                ->whereDate('snapshot_date', $date)
                ->orderBy('channel_type')
                ->get();

            $seo = SeoOpportunity::where('organization_id', $orgId)
                ->where('client_id', $client->id)
                ->whereDate('opportunity_date', $date)
                ->orderByRaw("FIELD(severity, 'critical','high','medium','low')")
                ->get();

            $html = view('deliverables.templates.summary', [
                'client' => $client,
                'date' => $date,
                'template' => $template,
                'snapshots' => $snapshots,
                'seo' => $seo,
            ])->render();

            $deliverable->update([
                'status' => 'generated',
                'generated_at' => now(),
                'body_html' => $html,
                'payload' => [
                    'snapshot_channels' => $snapshots->pluck('channel_type')->all(),
                    'seo_opportunities' => $seo->count(),
                ],
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            $deliverable->update([
                'status' => 'failed',
                'generated_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
