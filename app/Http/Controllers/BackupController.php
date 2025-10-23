<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;
use App\Models\Setting;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        // Get backup status
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitor_backups'));

        $backups = [];
        foreach ($statuses as $status) {
            $backups[] = [
                'name' => $status->backupDestination()->backupName(),
                'disk' => $status->backupDestination()->diskName(),
                'reachable' => $status->backupDestination()->isReachable(),
                'healthy' => $status->isHealthy(),
                'amount' => $status->backupDestination()->backups()->count(),
                'newest' => $status->backupDestination()->newestBackup()
                    ? $status->backupDestination()->newestBackup()->date()->diffForHumans()
                    : 'No backups yet',
                'usedStorage' => $status->backupDestination()->usedStorage(),
            ];
        }

        // Get list of backup files
        $backupFiles = [];
        $backupName = config('backup.backup.name');
        $disk = Storage::disk('local');
        $path = "private/{$backupName}";

        if ($disk->exists($path)) {
            $files = $disk->files($path);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $backupFiles[] = [
                        'path' => $file,
                        'name' => basename($file),
                        'size' => $disk->size($file),
                        'date' => $disk->lastModified($file),
                    ];
                }
            }
            // Sort by date descending
            usort($backupFiles, function($a, $b) {
                return $b['date'] - $a['date'];
            });
        }

        return view('admin.backups.index', compact('backups', 'backupFiles'));
    }

    public function run(Request $request)
    {
        try {
            $onlyDb = $request->boolean('only_db');

            if ($onlyDb) {
                Artisan::call('backup:run', ['--only-db' => true]);
            } else {
                Artisan::call('backup:run');
            }

            $output = Artisan::output();

            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup created successfully! ' . (strlen($output) > 200 ? substr($output, 0, 200) . '...' : $output));
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function settings()
    {
        $settings = [
            'backup_enabled' => Setting::get('backup_enabled') === 'true',
            'backup_schedule_time' => Setting::get('backup_schedule_time') ?: '02:00',
            'backup_keep_daily' => Setting::get('backup_keep_daily') ?: 7,
            'backup_keep_weekly' => Setting::get('backup_keep_weekly') ?: 4,
            'backup_keep_monthly' => Setting::get('backup_keep_monthly') ?: 6,
            'backup_vault_ftp_enabled' => Setting::get('backup_vault_ftp_enabled') === 'true',
            'backup_notification_email' => Setting::get('backup_notification_email') ?: '',
            'backup_notification_slack_webhook' => Setting::get('backup_notification_slack_webhook') ?: '',
            'backup_notification_on_success' => Setting::get('backup_notification_on_success') === 'true',
            'backup_notification_on_failure' => Setting::get('backup_notification_on_failure') === 'true',
        ];

        return view('admin.backups.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'backup_enabled' => 'boolean',
            'backup_schedule_time' => 'required|date_format:H:i',
            'backup_keep_daily' => 'required|integer|min:1|max:365',
            'backup_keep_weekly' => 'required|integer|min:1|max:52',
            'backup_keep_monthly' => 'required|integer|min:1|max:12',
            'backup_vault_ftp_enabled' => 'boolean',
            'backup_notification_email' => 'nullable|email',
            'backup_notification_slack_webhook' => 'nullable|url',
            'backup_notification_on_success' => 'boolean',
            'backup_notification_on_failure' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, is_bool($value) ? ($value ? 'true' : 'false') : $value);
        }

        // Clear config cache so new settings take effect
        Artisan::call('config:clear');

        return redirect()->route('admin.backups.settings')
            ->with('success', 'Backup settings updated successfully!');
    }

    public function download($filename)
    {
        $backupName = config('backup.backup.name');
        $path = "private/{$backupName}/{$filename}";

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Backup file not found');
        }

        return Storage::disk('local')->download($path);
    }

    public function delete($filename)
    {
        $backupName = config('backup.backup.name');
        $path = "private/{$backupName}/{$filename}";

        if (!Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Backup file not found');
        }

        Storage::disk('local')->delete($path);

        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup deleted successfully!');
    }
}
