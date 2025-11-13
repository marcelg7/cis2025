<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackComment extends Model
{
    protected $fillable = [
        'bug_report_id',
        'user_id',
        'comment',
    ];

    /**
     * Get the bug report (feedback) this comment belongs to
     */
    public function bugReport(): BelongsTo
    {
        return $this->belongsTo(BugReport::class);
    }

    /**
     * Get the user who wrote this comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
