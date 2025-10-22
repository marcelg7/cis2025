<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class SecurityLogger
{
    /**
     * Log a failed login attempt
     */
    public static function logFailedLogin(string $email, string $ip): void
    {
        Log::channel('security')->warning('Failed login attempt', [
            'email' => $email,
            'ip' => $ip,
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log a successful login
     */
    public static function logSuccessfulLogin(int $userId, string $email, string $ip): void
    {
        Log::channel('security')->info('Successful login', [
            'user_id' => $userId,
            'email' => $email,
            'ip' => $ip,
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log an authorization failure (403)
     */
    public static function logAuthorizationFailure(int $userId, string $action, string $resource): void
    {
        Log::channel('security')->warning('Authorization failure', [
            'user_id' => $userId,
            'action' => $action,
            'resource' => $resource,
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log a suspicious activity pattern
     */
    public static function logSuspiciousActivity(string $type, array $details): void
    {
        Log::channel('security')->warning('Suspicious activity detected', [
            'type' => $type,
            'details' => $details,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log a file upload attempt
     */
    public static function logFileUpload(string $filename, string $mimeType, int $size, bool $success): void
    {
        $level = $success ? 'info' : 'warning';

        Log::channel('security')->{$level}('File upload attempt', [
            'filename' => $filename,
            'mime_type' => $mimeType,
            'size' => $size,
            'success' => $success,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log rate limit exceeded
     */
    public static function logRateLimitExceeded(string $route): void
    {
        Log::channel('security')->warning('Rate limit exceeded', [
            'route' => $route,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log password change
     */
    public static function logPasswordChange(int $userId): void
    {
        Log::channel('security')->info('Password changed', [
            'user_id' => $userId,
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log account locked
     */
    public static function logAccountLocked(int $userId, string $reason): void
    {
        Log::channel('security')->warning('Account locked', [
            'user_id' => $userId,
            'reason' => $reason,
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log privilege escalation attempt
     */
    public static function logPrivilegeEscalation(int $userId, string $attemptedAction): void
    {
        Log::channel('security')->error('Privilege escalation attempt', [
            'user_id' => $userId,
            'attempted_action' => $attemptedAction,
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
