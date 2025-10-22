<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Helpers\SettingsHelper;

class MonitorSecurityEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:monitor {--email : Send email alert if issues found}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor security logs for suspicious patterns and alert if necessary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Analyzing security logs...');

        $logPath = storage_path('logs/security.log');

        if (!File::exists($logPath)) {
            $this->info('No security log file found. No events to monitor.');
            return 0;
        }

        // Get logs from the last hour
        $recentLogs = $this->getRecentLogs($logPath, 60);

        if (empty($recentLogs)) {
            $this->info('No recent security events in the last hour.');
            return 0;
        }

        // Analyze for suspicious patterns
        $issues = $this->analyzeSecurityPatterns($recentLogs);

        if (empty($issues)) {
            $this->info('✓ No security issues detected.');
            return 0;
        }

        // Display issues
        $this->warn('⚠ Security issues detected:');
        foreach ($issues as $issue) {
            $this->line("  - {$issue}");
        }

        // Send email alert if requested
        if ($this->option('email')) {
            $this->sendSecurityAlert($issues);
        }

        // Log to application log
        Log::warning('Security monitoring detected issues', ['issues' => $issues]);

        return 1; // Return non-zero to indicate issues found
    }

    /**
     * Get recent log entries
     */
    private function getRecentLogs(string $logPath, int $minutes): array
    {
        $cutoffTime = now()->subMinutes($minutes);
        $recentLogs = [];

        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Parse timestamp from log line
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                $logTime = \Carbon\Carbon::parse($matches[1]);

                if ($logTime->gte($cutoffTime)) {
                    $recentLogs[] = $line;
                }
            }
        }

        return $recentLogs;
    }

    /**
     * Analyze security patterns for suspicious activity
     */
    private function analyzeSecurityPatterns(array $logs): array
    {
        $issues = [];

        // Count different types of events
        $failedLogins = 0;
        $authFailures = 0;
        $rateLimitExceeded = 0;
        $suspiciousActivity = 0;
        $privilegeEscalation = 0;

        $failedLoginIPs = [];
        $authFailureUsers = [];

        foreach ($logs as $line) {
            if (str_contains($line, 'Failed login attempt')) {
                $failedLogins++;

                // Extract IP
                if (preg_match('/"ip":"([^"]+)"/', $line, $matches)) {
                    $ip = $matches[1];
                    $failedLoginIPs[$ip] = ($failedLoginIPs[$ip] ?? 0) + 1;
                }
            }

            if (str_contains($line, 'Authorization failure')) {
                $authFailures++;

                // Extract user ID
                if (preg_match('/"user_id":(\d+)/', $line, $matches)) {
                    $userId = $matches[1];
                    $authFailureUsers[$userId] = ($authFailureUsers[$userId] ?? 0) + 1;
                }
            }

            if (str_contains($line, 'Rate limit exceeded')) {
                $rateLimitExceeded++;
            }

            if (str_contains($line, 'Suspicious activity')) {
                $suspiciousActivity++;
            }

            if (str_contains($line, 'Privilege escalation')) {
                $privilegeEscalation++;
            }
        }

        // Detect patterns
        if ($failedLogins >= 10) {
            $issues[] = "High number of failed login attempts: {$failedLogins} in the last hour";
        }

        foreach ($failedLoginIPs as $ip => $count) {
            if ($count >= 5) {
                $issues[] = "Repeated failed logins from IP {$ip}: {$count} attempts";
            }
        }

        if ($authFailures >= 20) {
            $issues[] = "High number of authorization failures: {$authFailures} in the last hour";
        }

        foreach ($authFailureUsers as $userId => $count) {
            if ($count >= 10) {
                $issues[] = "User #{$userId} has {$count} authorization failures - possible privilege escalation attempt";
            }
        }

        if ($rateLimitExceeded >= 10) {
            $issues[] = "Rate limits exceeded {$rateLimitExceeded} times - possible DoS attempt";
        }

        if ($suspiciousActivity > 0) {
            $issues[] = "{$suspiciousActivity} suspicious activity event(s) detected";
        }

        if ($privilegeEscalation > 0) {
            $issues[] = "⚠ CRITICAL: {$privilegeEscalation} privilege escalation attempt(s) detected";
        }

        return $issues;
    }

    /**
     * Send security alert email
     */
    private function sendSecurityAlert(array $issues): void
    {
        $supervisorEmail = SettingsHelper::get('cellular_supervisor_email', 'supervisor@hay.net');

        try {
            Mail::raw(
                "Security monitoring has detected the following issues:\n\n" .
                implode("\n", array_map(fn($issue) => "• {$issue}", $issues)) .
                "\n\nPlease review the security logs at: storage/logs/security.log" .
                "\n\nTimestamp: " . now()->toDateTimeString(),
                function ($message) use ($supervisorEmail) {
                    $message->to($supervisorEmail)
                        ->subject('[SECURITY ALERT] ' . config('app.name') . ' - Security Issues Detected');
                }
            );

            $this->info("✓ Security alert email sent to {$supervisorEmail}");
        } catch (\Exception $e) {
            $this->error("Failed to send security alert email: {$e->getMessage()}");
        }
    }
}
