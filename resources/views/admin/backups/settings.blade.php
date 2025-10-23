@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Backup Settings</h1>
            <p class="text-blue-100 text-sm mt-1">Configure backup schedule, retention, and notifications</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mx-6 mt-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('admin.backups.update-settings') }}" class="p-6 space-y-8">
            @csrf

            <!-- General Settings Section -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    General Settings
                </h2>

                <!-- Backup Enabled -->
                <div class="mb-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                type="checkbox"
                                id="backup_enabled"
                                name="backup_enabled"
                                value="1"
                                {{ $settings['backup_enabled'] ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="backup_enabled" class="font-medium text-gray-700">Enable Automated Backups</label>
                            <p class="text-gray-500">Automatically create backups according to the schedule below</p>
                        </div>
                    </div>
                </div>

                <!-- Schedule Time -->
                <div>
                    <label for="backup_schedule_time" class="block text-sm font-semibold text-gray-700 mb-2">
                        Backup Schedule Time
                    </label>
                    <input
                        type="time"
                        id="backup_schedule_time"
                        name="backup_schedule_time"
                        value="{{ old('backup_schedule_time', $settings['backup_schedule_time']) }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('backup_schedule_time') border-red-500 @enderror"
                    >
                    <p class="mt-2 text-sm text-gray-500">
                        <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Time when daily backups will run (server time)
                    </p>
                    @error('backup_schedule_time')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Retention Settings Section -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Retention Policy
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Daily Backups -->
                    <div>
                        <label for="backup_keep_daily" class="block text-sm font-semibold text-gray-700 mb-2">
                            Daily Backups
                        </label>
                        <input
                            type="number"
                            id="backup_keep_daily"
                            name="backup_keep_daily"
                            value="{{ old('backup_keep_daily', $settings['backup_keep_daily']) }}"
                            min="1"
                            max="365"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('backup_keep_daily') border-red-500 @enderror"
                        >
                        <p class="mt-2 text-sm text-gray-500">Days to keep</p>
                        @error('backup_keep_daily')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Weekly Backups -->
                    <div>
                        <label for="backup_keep_weekly" class="block text-sm font-semibold text-gray-700 mb-2">
                            Weekly Backups
                        </label>
                        <input
                            type="number"
                            id="backup_keep_weekly"
                            name="backup_keep_weekly"
                            value="{{ old('backup_keep_weekly', $settings['backup_keep_weekly']) }}"
                            min="1"
                            max="52"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('backup_keep_weekly') border-red-500 @enderror"
                        >
                        <p class="mt-2 text-sm text-gray-500">Weeks to keep</p>
                        @error('backup_keep_weekly')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Monthly Backups -->
                    <div>
                        <label for="backup_keep_monthly" class="block text-sm font-semibold text-gray-700 mb-2">
                            Monthly Backups
                        </label>
                        <input
                            type="number"
                            id="backup_keep_monthly"
                            name="backup_keep_monthly"
                            value="{{ old('backup_keep_monthly', $settings['backup_keep_monthly']) }}"
                            min="1"
                            max="12"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('backup_keep_monthly') border-red-500 @enderror"
                        >
                        <p class="mt-2 text-sm text-gray-500">Months to keep</p>
                        @error('backup_keep_monthly')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Retention policy keeps all backups for the daily period, then transitions to weekly, then monthly. The newest backup is never deleted.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Settings Section -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Storage Destinations
                </h2>

                <!-- Vault FTP -->
                <div class="mb-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                type="checkbox"
                                id="backup_vault_ftp_enabled"
                                name="backup_vault_ftp_enabled"
                                value="1"
                                {{ $settings['backup_vault_ftp_enabled'] ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="backup_vault_ftp_enabled" class="font-medium text-gray-700">Enable Vault FTP Backup</label>
                            <p class="text-gray-500">Upload backups to Vault FTP server for off-site storage</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Note:</strong> Vault FTP credentials must be configured in your .env file. Backups are always stored locally regardless of this setting.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings Section -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Notifications
                </h2>

                <!-- Email Notification -->
                <div class="mb-6">
                    <label for="backup_notification_email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Notification Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input
                            type="email"
                            id="backup_notification_email"
                            name="backup_notification_email"
                            value="{{ old('backup_notification_email', $settings['backup_notification_email']) }}"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('backup_notification_email') border-red-500 @enderror"
                            placeholder="backups@example.com"
                        >
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Leave blank to disable email notifications
                    </p>
                    @error('backup_notification_email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slack Webhook -->
                <div class="mb-6">
                    <label for="backup_notification_slack_webhook" class="block text-sm font-semibold text-gray-700 mb-2">
                        Slack Webhook URL
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                        </div>
                        <input
                            type="url"
                            id="backup_notification_slack_webhook"
                            name="backup_notification_slack_webhook"
                            value="{{ old('backup_notification_slack_webhook', $settings['backup_notification_slack_webhook']) }}"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('backup_notification_slack_webhook') border-red-500 @enderror"
                            placeholder="https://hooks.slack.com/services/..."
                        >
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Leave blank to disable Slack notifications
                    </p>
                    @error('backup_notification_slack_webhook')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notification Preferences -->
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                type="checkbox"
                                id="backup_notification_on_success"
                                name="backup_notification_on_success"
                                value="1"
                                {{ $settings['backup_notification_on_success'] ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="backup_notification_on_success" class="font-medium text-gray-700">Notify on Success</label>
                            <p class="text-gray-500">Send notification when backup completes successfully</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                type="checkbox"
                                id="backup_notification_on_failure"
                                name="backup_notification_on_failure"
                                value="1"
                                {{ $settings['backup_notification_on_failure'] ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="backup_notification_on_failure" class="font-medium text-gray-700">Notify on Failure</label>
                            <p class="text-gray-500">Send notification when backup fails (recommended)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('admin.backups.index') }}" class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                    ← Back to Backups
                </a>
                <button
                    type="submit"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
