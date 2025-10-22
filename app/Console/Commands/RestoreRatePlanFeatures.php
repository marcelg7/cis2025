<?php

namespace App\Console\Commands;

use App\Models\RatePlan;
use Illuminate\Console\Command;

class RestoreRatePlanFeatures extends Command
{
    protected $signature = 'rate-plans:restore-features {--from-date= : Source effective date (Y-m-d)} {--to-date= : Target effective date (Y-m-d)} {--dry-run : Show what would be updated without making changes}';

    protected $description = 'Restore rate plan features from one effective date to another by matching SOC codes';

    public function handle()
    {
        $fromDate = $this->option('from-date');
        $toDate = $this->option('to-date');
        $dryRun = $this->option('dry-run');

        if (!$fromDate || !$toDate) {
            $this->error('Both --from-date and --to-date are required.');
            $this->info('Example: php artisan rate-plans:restore-features --from-date=2025-10-15 --to-date=2025-10-21');
            return 1;
        }

        $this->info("Restoring rate plan features from {$fromDate} to {$toDate}...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get all source plans with features
        $sourcePlans = RatePlan::where('effective_date', $fromDate)
            ->whereNotNull('features')
            ->where('features', '!=', '')
            ->get();

        if ($sourcePlans->isEmpty()) {
            $this->warn("No plans with features found for date {$fromDate}");
            return 1;
        }

        $this->info("Found {$sourcePlans->count()} plans with features on {$fromDate}");

        $updated = 0;
        $notFound = 0;
        $skipped = 0;

        foreach ($sourcePlans as $sourcePlan) {
            // Find matching plan in target date by SOC code
            $targetPlan = RatePlan::where('soc_code', $sourcePlan->soc_code)
                ->where('effective_date', $toDate)
                ->first();

            if (!$targetPlan) {
                $notFound++;
                $this->warn("  ✗ {$sourcePlan->soc_code} - No matching plan found on {$toDate}");
                continue;
            }

            // Check if target already has features
            if (!empty($targetPlan->features)) {
                $skipped++;
                $this->info("  ⊙ {$sourcePlan->soc_code} - Already has features, skipping");
                continue;
            }

            if ($dryRun) {
                $this->info("  ✓ {$sourcePlan->soc_code} - Would restore features (" . strlen($sourcePlan->features) . " chars)");
                $updated++;
            } else {
                $targetPlan->features = $sourcePlan->features;
                $targetPlan->save();
                $updated++;
                $this->info("  ✓ {$sourcePlan->soc_code} - Features restored (" . strlen($sourcePlan->features) . " chars)");
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Updated: {$updated}");
        $this->info("  Not found in target: {$notFound}");
        $this->info("  Skipped (already has features): {$skipped}");

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN MODE - Run without --dry-run to actually restore features');
        } else {
            $this->newLine();
            $this->info('✅ Rate plan features restored successfully!');
        }

        return 0;
    }
}
