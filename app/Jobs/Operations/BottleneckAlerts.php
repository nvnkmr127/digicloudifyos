<?php

namespace App\Jobs\Operations;

use App\Models\Organization;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BottleneckAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        foreach (Organization::all() as $org) {
            $this->staleTasks($org->id);
            $this->wipOverload($org->id);
        }
    }

    protected function staleTasks(string $orgId): void
    {
        $stale = Task::where('organization_id', $orgId)
            ->whereIn('status', ['in_progress'])
            ->where('updated_at', '<', now()->subDays(7))
            ->count();

        if ($stale <= 0) {
            return;
        }

        $title = 'Ops: review stale tasks';

        $exists = Task::where('organization_id', $orgId)
            ->whereNull('client_id')
            ->where('title', $title)
            ->where('status', '!=', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();

        if ($exists) {
            return;
        }

        Task::create([
            'organization_id' => $orgId,
            'client_id' => null,
            'title' => $title,
            'description' => 'There are '.$stale.' tasks in_progress with no updates in 7+ days. Review blockers and reassign.',
            'status' => 'pending',
            'priority' => 'high',
            'deadline' => now()->addDays(2),
        ]);
    }

    protected function wipOverload(string $orgId): void
    {
        $users = User::where('organization_id', $orgId)->get(['id']);

        foreach ($users as $user) {
            $wip = Task::where('organization_id', $orgId)
                ->where('assigned_to', $user->id)
                ->whereIn('status', ['in_progress'])
                ->count();

            if ($wip < 8) {
                continue;
            }

            $title = 'Ops: reduce WIP';

            $exists = Task::where('organization_id', $orgId)
                ->where('assigned_to', $user->id)
                ->where('title', $title)
                ->where('status', '!=', 'completed')
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if ($exists) {
                continue;
            }

            Task::create([
                'organization_id' => $orgId,
                'client_id' => null,
                'assigned_to' => $user->id,
                'title' => $title,
                'description' => 'You have '.$wip.' tasks in_progress. Limit WIP and close or reassign items to improve throughput.',
                'status' => 'pending',
                'priority' => 'medium',
                'deadline' => now()->addDays(2),
            ]);
        }
    }
}
