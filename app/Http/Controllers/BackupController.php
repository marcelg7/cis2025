<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Helpers\SettingsHelper as Setting;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        // Get list of backup files
        $backupFiles = [];
        $backupName = config('backup.backup.name');
        $disk = Storage::disk('local');
        // Local disk root is already storage/app/private, so just use backup name
        $path = $backupName;

        // Build backup statistics
        $backupStats = [
            'name' => $backupName,
            'disk' => 'local',
            'reachable' => true,
            'healthy' => true,
            'amount' => 0,
            'newest' => 'No backups yet',
            'usedStorage' => 0,
        ];

        if ($disk->exists($path)) {
            $files = $disk->files($path);
            $totalSize = 0;
            $latestTimestamp = 0;

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $fileSize = $disk->size($file);
                    $fileDate = $disk->lastModified($file);

                    $totalSize += $fileSize;

                    if ($fileDate > $latestTimestamp) {
                        $latestTimestamp = $fileDate;
                    }

                    $backupFiles[] = [
                        'path' => $file,
                        'name' => basename($file),
                        'size' => $fileSize,
                        'date' => $fileDate,
                    ];
                }
            }

            // Sort by date descending
            usort($backupFiles, function($a, $b) {
                return $b['date'] - $a['date'];
            });

            $backupStats['amount'] = count($backupFiles);
            $backupStats['usedStorage'] = $totalSize;

            if ($latestTimestamp > 0) {
                $backupStats['newest'] = \Carbon\Carbon::createFromTimestamp($latestTimestamp)->diffForHumans();
            }
        }

        $backups = [$backupStats];

        return view('admin.backups.index', compact('backups', 'backupFiles'));
    }

    public function run(Request $request)
    {
        try {
            $onlyDb = $request->boolean('only_db');

            // Run backup in background to avoid timeout
            if ($onlyDb) {
                Artisan::queue('backup:run', ['--only-db' => true]);
                $message = 'Database-only backup started in background. You will receive an email when complete.';
            } else {
                Artisan::queue('backup:run');
                $message = 'Full backup started in background. You will receive an email when complete.';
            }

            return redirect()->route('admin.backups.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Failed to start backup: ' . $e->getMessage());
        }
    }

    public function settings()
    {
        // Provide defaults in case migration hasn't been run yet
        $settings = [
            'backup_enabled' => Setting::get('backup_enabled', 'true') === 'true',
            'backup_schedule_time' => Setting::get('backup_schedule_time', '02:00'),
            'backup_keep_daily' => Setting::get('backup_keep_daily', '7'),
            'backup_keep_weekly' => Setting::get('backup_keep_weekly', '4'),
            'backup_keep_monthly' => Setting::get('backup_keep_monthly', '6'),
            'backup_vault_ftp_enabled' => Setting::get('backup_vault_ftp_enabled', 'false') === 'true',
            'backup_notification_email' => Setting::get('backup_notification_email', ''),
            'backup_notification_slack_webhook' => Setting::get('backup_notification_slack_webhook', ''),
            'backup_notification_on_success' => Setting::get('backup_notification_on_success', 'false') === 'true',
            'backup_notification_on_failure' => Setting::get('backup_notification_on_failure', 'true') === 'true',
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

        // Handle boolean checkboxes (unchecked = not present in request)
        $booleanFields = [
            'backup_enabled',
            'backup_vault_ftp_enabled',
            'backup_notification_on_success',
            'backup_notification_on_failure',
        ];

        foreach ($booleanFields as $field) {
            $value = $request->boolean($field) ? 'true' : 'false';
            Setting::set($field, $value);
        }

        // Handle other fields
        Setting::set('backup_schedule_time', $validated['backup_schedule_time']);
        Setting::set('backup_keep_daily', $validated['backup_keep_daily']);
        Setting::set('backup_keep_weekly', $validated['backup_keep_weekly']);
        Setting::set('backup_keep_monthly', $validated['backup_keep_monthly']);
        Setting::set('backup_notification_email', $validated['backup_notification_email'] ?? '');
        Setting::set('backup_notification_slack_webhook', $validated['backup_notification_slack_webhook'] ?? '');

        // Update .env file with notification settings
        $this->updateEnvFile([
            'BACKUP_NOTIFICATION_EMAIL' => $validated['backup_notification_email'] ?? '',
            'BACKUP_NOTIFICATION_SLACK_WEBHOOK' => $validated['backup_notification_slack_webhook'] ?? '',
            'BACKUP_NOTIFICATION_ON_SUCCESS' => $request->boolean('backup_notification_on_success') ? 'true' : 'false',
            'BACKUP_NOTIFICATION_ON_FAILURE' => $request->boolean('backup_notification_on_failure') ? 'true' : 'false',
            'BACKUP_VAULT_FTP_ENABLED' => $request->boolean('backup_vault_ftp_enabled') ? 'true' : 'false',
            'BACKUP_KEEP_DAILY' => $validated['backup_keep_daily'],
            'BACKUP_KEEP_WEEKLY' => $validated['backup_keep_weekly'],
            'BACKUP_KEEP_MONTHLY' => $validated['backup_keep_monthly'],
        ]);

        // Clear config cache so new settings take effect
        Artisan::call('config:clear');

        return redirect()->route('admin.backups.settings')
            ->with('success', 'Backup settings updated successfully!');
    }

    public function download($filename)
    {
        $backupName = config('backup.backup.name');
        // Local disk root is already storage/app/private
        $path = "{$backupName}/{$filename}";

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Backup file not found');
        }

        return Storage::disk('local')->download($path);
    }

    public function delete($filename)
    {
        $backupName = config('backup.backup.name');
        // Local disk root is already storage/app/private
        $path = "{$backupName}/{$filename}";

        if (!Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Backup file not found');
        }

        Storage::disk('local')->delete($path);

        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup deleted successfully!');
    }

    /**
     * Update .env file with new values
     */
    protected function updateEnvFile(array $data)
    {
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            return;
        }

        $env = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            // Escape special characters in value
            $value = str_replace('"', '\"', $value);

            // Check if key exists in .env
            if (preg_match("/^{$key}=/m", $env)) {
                // Update existing key
                $env = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}=\"{$value}\"",
                    $env
                );
            } else {
                // Add new key at the end
                $env .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($envFile, $env);
    }
}
