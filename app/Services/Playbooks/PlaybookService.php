<?php

namespace App\Services\Playbooks;

use App\Models\Client;
use App\Models\ClientPlaybookRun;
use App\Models\PlaybookRunTask;
use App\Models\PlaybookTemplate;
use App\Models\Task;
use Carbon\Carbon;

class PlaybookService
{
    public function ensureDefaults(string $orgId): void
    {
        $existing = PlaybookTemplate::where('organization_id', $orgId)->count();
        if ($existing > 0) {
            return;
        }

        PlaybookTemplate::create([
            'organization_id' => $orgId,
            'name' => 'Lead Gen Revenue Sprint (14-day onboarding)',
            'category' => 'onboarding',
            'is_active' => true,
            'steps' => $this->leadGenOnboardingSteps(),
        ]);

        PlaybookTemplate::create([
            'organization_id' => $orgId,
            'name' => 'Local Services Lead Gen Sprint (14-day onboarding)',
            'category' => 'onboarding',
            'is_active' => true,
            'steps' => array_merge($this->leadGenOnboardingSteps(), [
                ['key' => 'gbp', 'title' => 'Google Business Profile optimization checklist', 'priority' => 'high', 'due_days' => 3],
                ['key' => 'service-areas', 'title' => 'Service areas + location pages plan', 'priority' => 'medium', 'due_days' => 5],
            ]),
        ]);

        PlaybookTemplate::create([
            'organization_id' => $orgId,
            'name' => 'B2B Lead Gen Sprint (14-day onboarding)',
            'category' => 'onboarding',
            'is_active' => true,
            'steps' => array_merge($this->leadGenOnboardingSteps(), [
                ['key' => 'qualify', 'title' => 'Define sales qualification rubric + lead scoring', 'priority' => 'high', 'due_days' => 2],
                ['key' => 'followup', 'title' => 'Follow-up sequence plan (email/call) + SLA', 'priority' => 'medium', 'due_days' => 6],
            ]),
        ]);

        PlaybookTemplate::create([
            'organization_id' => $orgId,
            'name' => 'E-commerce Growth Sprint (14-day onboarding)',
            'category' => 'onboarding',
            'is_active' => true,
            'steps' => array_merge($this->leadGenOnboardingSteps(), [
                ['key' => 'product-feed', 'title' => 'Merchant Center feed + disapproval health check', 'priority' => 'high', 'due_days' => 3],
                ['key' => 'aov', 'title' => 'AOV levers plan (bundles/upsells/shipping)', 'priority' => 'medium', 'due_days' => 6],
            ]),
        ]);

        PlaybookTemplate::create([
            'organization_id' => $orgId,
            'name' => 'Brand Kit & Creative System (setup)',
            'category' => 'branding',
            'is_active' => true,
            'steps' => $this->brandingSteps(),
        ]);

        PlaybookTemplate::create([
            'organization_id' => $orgId,
            'name' => 'SEO 30/60/90 Foundation',
            'category' => 'seo',
            'is_active' => true,
            'steps' => $this->seoSteps(),
        ]);
    }

    public function runTemplateForClient(string $orgId, Client $client, PlaybookTemplate $template, string $date): ?ClientPlaybookRun
    {
        $exists = ClientPlaybookRun::where('organization_id', $orgId)
            ->where('client_id', $client->id)
            ->where('playbook_template_id', $template->id)
            ->whereDate('run_date', $date)
            ->exists();

        if ($exists) {
            return null;
        }

        $run = ClientPlaybookRun::create([
            'organization_id' => $orgId,
            'client_id' => $client->id,
            'playbook_template_id' => $template->id,
            'run_date' => $date,
            'status' => 'running',
        ]);

        $steps = is_array($template->steps) ? $template->steps : [];
        foreach ($steps as $step) {
            if (! is_array($step)) {
                continue;
            }
            $title = $step['title'] ?? null;
            if (! is_string($title) || $title === '') {
                continue;
            }

            $task = Task::create([
                'organization_id' => $orgId,
                'client_id' => $client->id,
                'title' => $title,
                'description' => isset($step['description']) ? (string) $step['description'] : null,
                'task_type' => isset($step['task_type']) ? (string) $step['task_type'] : null,
                'priority' => isset($step['priority']) ? (string) $step['priority'] : 'medium',
                'status' => 'pending',
                'deadline' => isset($step['due_days']) ? Carbon::parse($date)->addDays((int) $step['due_days']) : Carbon::parse($date)->addDays(3),
            ]);

            PlaybookRunTask::create([
                'organization_id' => $orgId,
                'client_playbook_run_id' => $run->id,
                'task_id' => $task->id,
                'step_key' => isset($step['key']) ? (string) $step['key'] : null,
            ]);
        }

        $run->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $run;
    }

    protected function leadGenOnboardingSteps(): array
    {
        return [
            ['key' => 'tracking-map', 'title' => 'Define tracking map (events, UTMs, conversion actions)', 'priority' => 'high', 'due_days' => 1],
            ['key' => 'offer', 'title' => 'Finalize offer + lead qualification questions', 'priority' => 'high', 'due_days' => 2],
            ['key' => 'lp', 'title' => 'Landing page brief + wireframe', 'priority' => 'high', 'due_days' => 3],
            ['key' => 'forms', 'title' => 'Form + thank-you conversion setup', 'priority' => 'high', 'due_days' => 4],
            ['key' => 'ads-structure', 'title' => 'Campaign structure plan (Meta + Google)', 'priority' => 'medium', 'due_days' => 5],
            ['key' => 'creative-plan', 'title' => 'Creative testing plan (hooks/offers/angles)', 'priority' => 'medium', 'due_days' => 6],
            ['key' => 'launch-checklist', 'title' => 'Launch checklist (tracking validation + QA)', 'priority' => 'high', 'due_days' => 7],
        ];
    }

    protected function brandingSteps(): array
    {
        return [
            ['key' => 'brand-kit', 'title' => 'Create Brand Kit (logos, colors, fonts, tone)', 'priority' => 'high', 'due_days' => 2],
            ['key' => 'claims', 'title' => 'Approved/restricted claims list (compliance)', 'priority' => 'high', 'due_days' => 2],
            ['key' => 'creative-brief', 'title' => 'Creative brief template setup (meta/google/seo)', 'priority' => 'medium', 'due_days' => 3],
            ['key' => 'creative-library', 'title' => 'Creative naming + tagging convention', 'priority' => 'low', 'due_days' => 5],
        ];
    }

    protected function seoSteps(): array
    {
        return [
            ['key' => 'audit', 'title' => 'Run technical + on-page audit (crawl-lite)', 'priority' => 'high', 'due_days' => 1],
            ['key' => 'sc-map', 'title' => 'Query-to-page map (top queries and landing pages)', 'priority' => 'medium', 'due_days' => 3],
            ['key' => 'page2', 'title' => 'Page-2 opportunities plan (positions 11–20)', 'priority' => 'high', 'due_days' => 4],
            ['key' => 'decay', 'title' => 'Content refresh plan for decaying pages', 'priority' => 'high', 'due_days' => 7],
        ];
    }
}
