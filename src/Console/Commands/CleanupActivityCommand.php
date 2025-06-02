<?php

namespace thbappy7706\ActivityTracker\Console\Commands;

use Illuminate\Console\Command;
use thbappy7706\ActivityTracker\ActivityTracker;

class CleanupActivityCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'activity:cleanup 
                            {--days= : Number of days to keep (overrides config)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Cleanup old activity records based on configuration';

    /**
     * Execute the console command.
     */
    public function handle(ActivityTracker $tracker): int
    {
        $days = $this->option('days') ?: config('activity-tracker.cleanup_after_days');
        $isDryRun = $this->option('dry-run');

        if (!$days) {
            $this->error('Cleanup is disabled. Set cleanup_after_days in config or use --days option.');
            return self::FAILURE;
        }

        $this->info("Cleaning up activity records older than {$days} days...");

        if ($isDryRun) {
            $count = $this->getDryRunCount($days);
            $this->info("Dry run: Would delete {$count} activity records.");
            return self::SUCCESS;
        }

        $deleted = $tracker->cleanup();
        
        if ($deleted > 0) {
            $this->info("Successfully cleaned up {$deleted} old activity records.");
        } else {
            $this->info("No old activity records found to cleanup.");
        }

        return self::SUCCESS;
    }

    /**
     * Get the count of records that would be deleted in a dry run.
     */
    protected function getDryRunCount(int $days): int
    {
        $threshold = now()->subDays($days);
        
        return \thbappy7706\ActivityTracker\Models\UserActivity::where('last_activity', '<', $threshold)->count();
    }
}
