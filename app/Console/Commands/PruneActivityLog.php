<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;
use App\Helpers\SettingsHelper;

class PruneActivityLog extends Command
{
    protected $signature = 'activitylog:prune {--days=365 : Number of days to keep logs}';

    protected $description = 'Prune old activity logs';

	public function handle()
	{
		$days = SettingsHelper::get('log_prune_days', 365);
		$threshold = now()->subDays($days);

		$deleted = Activity::where('created_at', '<', $threshold)->delete();

		$this->info("Deleted {$deleted} old activity logs older than {$days} days.");
	}
}