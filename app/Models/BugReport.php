<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugReport extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'severity',
        'feedback_type',
        'status',
        'category',
        'url',
        'browser_info',
        'screenshot',
        'slack_thread_ts',
        'slack_channel_id',
        'admin_notes',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Feedback types
     */
    public const FEEDBACK_TYPES = [
        'bug' => 'Bug Report',
        'feature' => 'Feature Request',
        'change' => 'Change Request',
        'general' => 'General Feedback',
    ];

    /**
     * Bug report categories
     */
    public const CATEGORIES = [
        'ui' => 'User Interface',
        'functionality' => 'Functionality',
        'performance' => 'Performance',
        'data' => 'Data Issue',
        'security' => 'Security',
        'other' => 'Other',
    ];

    /**
     * Severity levels with colors
     */
    public const SEVERITIES = [
        'low' => ['label' => 'Low', 'color' => 'gray'],
        'medium' => ['label' => 'Medium', 'color' => 'yellow'],
        'high' => ['label' => 'High', 'color' => 'orange'],
        'critical' => ['label' => 'Critical', 'color' => 'red'],
    ];

    /**
     * Status options with colors
     */
    public const STATUSES = [
        'open' => ['label' => 'Open', 'color' => 'red'],
        'in_progress' => ['label' => 'In Progress', 'color' => 'blue'],
        'resolved' => ['label' => 'Resolved', 'color' => 'green'],
        'closed' => ['label' => 'Closed', 'color' => 'gray'],
    ];

    /**
     * Get the user who reported the bug
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user assigned to fix the bug
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all comments for this feedback
     */
    public function comments()
    {
        return $this->hasMany(FeedbackComment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get severity display info
     */
    public function getSeverityInfoAttribute(): array
    {
        return self::SEVERITIES[$this->severity] ?? self::SEVERITIES['medium'];
    }

    /**
     * Get status display info
     */
    public function getStatusInfoAttribute(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['open'];
    }
}
