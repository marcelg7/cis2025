<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobileInternetPlan extends Model
{
    protected $fillable = [
		'soc_code',
		'plan_name',
		'monthly_rate',
		'category',
		'promo_group',
		'description',
		'effective_date',
		'is_current',
		'is_active',
		'is_test',
    ];

    protected $casts = [
        'monthly_rate' => 'decimal:2',
        'effective_date' => 'date',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
        'is_test' => 'boolean',
    ];

    /**
     * Scope to get only current pricing
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope to get only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get formatted display name with price
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->plan_name} - $" . number_format($this->monthly_rate, 2) . "/mo";
    }

    /**
     * Get contracts using this mobile internet plan (we'll add this relationship later)
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get pricing for a specific SOC code
     */
    public static function getPricing($socCode)
    {
        return static::where('soc_code', $socCode)
            ->where('is_current', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all current mobile internet plans
     */
    public static function getCurrentPlans()
    {
        return static::current()
            ->active()
            ->orderBy('monthly_rate')
            ->get();
    }
}