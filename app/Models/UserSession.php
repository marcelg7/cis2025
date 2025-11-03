<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'duration_seconds',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    /**
     * Get the user that owns the session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate and set duration when logout occurs
     */
    public function endSession(): void
    {
        $this->logout_at = now();
        $this->duration_seconds = $this->login_at->diffInSeconds($this->logout_at);
        $this->save();
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d hours %d min', $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf('%d min %d sec', $minutes, $seconds);
        } else {
            return sprintf('%d sec', $seconds);
        }
    }

    /**
     * Scope to get active sessions (no logout time)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('logout_at');
    }

    /**
     * Scope to get sessions within a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('login_at', [$startDate, $endDate]);
    }
}
