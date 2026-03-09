<?php

namespace App\Console\Commands;

use App\Models\AutomationLog;
use App\Models\Notification;
use App\Models\WorkflowEvent;
use Illuminate\Console\Command;

class CleanupOldData extends Command
{
    protected $signature = 'cleanup:old-data
                            {--days=90 : Number of days to keep data}
                            {--dry-run : Preview what would be deleted without actually deleting}';

    protected $description = 'Cleanup old automation logs, notifications, and events';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoffDate = now()->subDays($days);

        $this->info("Cleaning up data older than {$cutoffDate->format('Y-m-d')}...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
        }

        $this->cleanupAutomationLogs($cutoffDate, $dryRun);
        $this->cleanupWorkflowEvents($cutoffDate, $dryRun);
        $this->cleanupReadNotifications($cutoffDate, $dryRun);

        $this->info('Cleanup complete!');

        return self::SUCCESS;
    }

    protected function cleanupAutomationLogs($cutoffDate, bool $dryRun): void
    {
        $query = AutomationLog::where('executed_at', '<', $cutoffDate);
        $count = $query->count();

        if ($count > 0) {
            $this->line("Automation logs to delete: {$count}");

            if (! $dryRun) {
                $deleted = $query->delete();
                $this->info("Deleted {$deleted} automation logs");
            }
        }
    }

    protected function cleanupWorkflowEvents($cutoffDate, bool $dryRun): void
    {
        $query = WorkflowEvent::where('created_at', '<', $cutoffDate)
            ->whereNotNull('processed_at');
        $count = $query->count();

        if ($count > 0) {
            $this->line("Workflow events to delete: {$count}");

            if (! $dryRun) {
                $deleted = $query->delete();
                $this->info("Deleted {$deleted} workflow events");
            }
        }
    }

    protected function cleanupReadNotifications($cutoffDate, bool $dryRun): void
    {
        $query = Notification::where('created_at', '<', $cutoffDate)
            ->whereNotNull('read_at');
        $count = $query->count();

        if ($count > 0) {
            $this->line("Read notifications to delete: {$count}");

            if (! $dryRun) {
                $deleted = $query->delete();
                $this->info("Deleted {$deleted} read notifications");
            }
        }
    }
}
