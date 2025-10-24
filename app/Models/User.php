<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Keeping as-is
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification; // For override
use App\Notifications\CustomResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'location_id',
        'password',
		'component_styles',
		'session_lifetime',
		'theme',
		'show_development_info',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
		'component_styles' => 'array',
		'show_development_info' => 'boolean',
    ];

    // Customize password reset email to distinguish new user setup
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token, $this->wasRecentlyCreated));
    }

    /**
     * Get the user's notification preferences
     */
    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /**
     * Get the location assigned to this user
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}