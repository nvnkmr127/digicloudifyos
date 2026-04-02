<?php

namespace App\Jobs\Playbooks;

use App\Models\Client;
use App\Models\ClientServicePackage;
use App\Models\Organization;
use App\Models\PlaybookTemplate;
use App\Models\ServicePackage;
use App\Services\Playbooks\PlaybookService;
use App\Services\Playbooks\ServicePackageService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunRecurringServicePackages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(PlaybookService $playbooks, ServicePackageService $packages): void
    {
        $date = $this->date ?? now()->toDateString();
        $day = Carbon::parse($date);

        foreach (Organization::all() as $org) {
            $playbooks->ensureDefaults($org->id);
            $packages->ensureDefaults($org->id);

            $assignments = ClientServicePackage::where('organization_id', $org->id)
                ->where('is_active', true)
                ->with('package')
                ->get()
                ->groupBy('client_id');

            foreach ($assignments as $clientId => $rows) {
                $client = Client::where('organization_id', $org->id)->find($clientId);
                if (! $client) {
                    continue;
                }

                foreach ($rows as $assignment) {
                    $pkg = $assignment->package;
                    if (! $pkg instanceof ServicePackage) {
                        continue;
                    }
                    if (! $pkg->is_active) {
                        continue;
                    }

                    if (! $this->shouldRun($pkg, $day)) {
                        continue;
                    }

                    $templateIds = (array) ($pkg->config['playbook_template_ids'] ?? []);
                    $templateIds = collect($templateIds)->map(fn ($v) => (string) $v)->filter()->values()->all();
                    if (empty($templateIds)) {
                        continue;
                    }

                    $templates = PlaybookTemplate::where('organization_id', $org->id)
                        ->whereIn('id', $templateIds)
                        ->where('is_active', true)
                        ->get();

                    foreach ($templates as $template) {
                        $playbooks->runTemplateForClient($org->id, $client, $template, $date);
                    }
                }
            }
        }
    }

    protected function shouldRun(ServicePackage $pkg, Carbon $day): bool
    {
        if ($pkg->cadence === 'weekly') {
            $dow = (int) ($pkg->day_of_week ?? 1);

            return $day->dayOfWeekIso === $dow;
        }

        if ($pkg->cadence === 'monthly') {
            $dom = (int) ($pkg->day_of_month ?? 1);

            return $day->day === max(1, min(28, $dom));
        }

        if ($pkg->cadence === 'quarterly') {
            $dom = (int) ($pkg->day_of_month ?? 1);
            if ($day->day !== max(1, min(28, $dom))) {
                return false;
            }

            return in_array($day->month, [1, 4, 7, 10], true);
        }

        return false;
    }
}
