<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert default backup settings
        $settings = [
            ['key' => 'backup_enabled', 'value' => 'true'],
            ['key' => 'backup_schedule_time', 'value' => '02:00'],
            ['key' => 'backup_keep_daily', 'value' => '7'],
            ['key' => 'backup_keep_weekly', 'value' => '4'],
            ['key' => 'backup_keep_monthly', 'value' => '6'],
            ['key' => 'backup_vault_ftp_enabled', 'value' => 'false'],
            ['key' => 'backup_notification_email', 'value' => ''],
            ['key' => 'backup_notification_slack_webhook', 'value' => ''],
            ['key' => 'backup_notification_on_success', 'value' => 'false'],
            ['key' => 'backup_notification_on_failure', 'value' => 'true'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'backup_enabled',
            'backup_schedule_time',
            'backup_keep_daily',
            'backup_keep_weekly',
            'backup_keep_monthly',
            'backup_vault_ftp_enabled',
            'backup_notification_email',
            'backup_notification_slack_webhook',
            'backup_notification_on_success',
            'backup_notification_on_failure',
        ];

        DB::table('settings')->whereIn('key', $keys)->delete();
    }
};
