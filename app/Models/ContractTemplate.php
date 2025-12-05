<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_team_template',
        'use_count',
        'activity_type_id',
        'bell_device_id',
        'rate_plan_id',
        'mobile_internet_plan_id',
        'commitment_period_id',
        'location_id',
        'selected_add_ons',
        'selected_one_time_fees',
        'hay_credit_applied',
        'is_byod',
        'connection_fee_override',
    ];

    protected $casts = [
        'is_team_template' => 'boolean',
        'hay_credit_applied' => 'boolean',
        'is_byod' => 'boolean',
        'use_count' => 'integer',
        'connection_fee_override' => 'decimal:2',
        'selected_add_ons' => 'array',
        'selected_one_time_fees' => 'array',
    ];

    /**
     * Get the user who created this template
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the activity type
     */
    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    /**
     * Get the Bell device
     */
    public function bellDevice(): BelongsTo
    {
        return $this->belongsTo(BellDevice::class);
    }

    /**
     * Get the rate plan
     */
    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    /**
     * Get the mobile internet plan
     */
    public function mobileInternetPlan(): BelongsTo
    {
        return $this->belongsTo(MobileInternetPlan::class);
    }

    /**
     * Get the commitment period
     */
    public function commitmentPeriod(): BelongsTo
    {
        return $this->belongsTo(CommitmentPeriod::class);
    }

    /**
     * Get the location
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Increment the use count when template is applied
     */
    public function incrementUseCount(): void
    {
        $this->increment('use_count');
    }

    /**
     * Scope to get personal templates for a user
     */
    public function scopePersonal($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->where('is_team_template', false);
    }

    /**
     * Scope to get team templates
     */
    public function scopeTeam($query)
    {
        return $query->where('is_team_template', true);
    }

    /**
     * Scope to order by usage
     */
    public function scopePopular($query)
    {
        return $query->orderBy('use_count', 'desc');
    }
}
