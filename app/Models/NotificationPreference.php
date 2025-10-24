<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'enabled',
        'settings',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Available notification types
     */
    public const TYPES = [
        'contract_pending_signature' => 'Contracts Pending Signature',
        'ftp_upload_failed' => 'Failed FTP Uploads',
        'contract_renewal' => 'Upcoming Contract Renewals',
        'device_pricing_uploaded' => 'New Device Pricing Uploaded',
    ];

    /**
     * Get the user that owns the preference
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a user has a specific notification enabled
     */
    public static function isEnabled(int $userId, string $notificationType): bool
    {
        $preference = static::where('user_id', $userId)
            ->where('notification_type', $notificationType)
            ->first();

        // Default to enabled if no preference exists
        return $preference ? $preference->enabled : true;
    }
}
