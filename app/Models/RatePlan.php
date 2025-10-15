<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RatePlan extends Model
{
    protected $fillable = [
		'soc_code',
		'plan_name',
		'plan_type',
		'tier',
		'base_price',
		'promo_price',
		'promo_description',
		'data_amount',
		'is_international',
		'is_us_mexico',
		'features',
		'effective_date',
		'is_current',
		'is_active',
		'is_test',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'effective_date' => 'date',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
        'is_test' => 'boolean',
        'is_international' => 'boolean',
        'is_us_mexico' => 'boolean',
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
     * Scope to filter by plan type (byod or smartpay)
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('plan_type', $type);
    }

    /**
     * Scope to filter by tier
     */
    public function scopeOfTier($query, $tier)
    {
        return $query->where('tier', $tier);
    }

    /**
     * Get the effective price (promo if available, otherwise base)
     */
    public function getEffectivePriceAttribute()
    {
        return $this->promo_price ?? $this->base_price;
    }

    /**
     * Check if plan has a promotion
     */
    public function getHasPromoAttribute()
    {
        return !is_null($this->promo_price);
    }

    /**
     * Get formatted display name with price
     */
    public function getDisplayNameAttribute()
    {
        $price = $this->has_promo 
            ? '$' . number_format($this->promo_price, 2) . ' (was $' . number_format($this->base_price, 2) . ')'
            : '$' . number_format($this->base_price, 2);
        
        return "{$this->plan_name} - {$price}";
    }

    /**
     * Get contracts using this rate plan (we'll add this relationship later)
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get pricing for a specific SOC code and tier
     */
    public static function getPricing($socCode, $tier = null)
    {
        $query = static::where('soc_code', $socCode)
            ->where('is_current', true)
            ->where('is_active', true);
        
        if ($tier) {
            $query->where('tier', $tier);
        }
        
        return $query->first();
    }

    /**
     * Get all current BYOD plans
     */
    public static function getCurrentByodPlans()
    {
        return static::current()
            ->active()
            ->ofType('byod')
            ->orderBy('tier')
            ->orderBy('base_price')
            ->get();
    }

    /**
     * Get all current SmartPay plans
     */
    public static function getCurrentSmartPayPlans()
    {
        return static::current()
            ->active()
            ->ofType('smartpay')
            ->orderBy('tier')
            ->orderBy('base_price')
            ->get();
    }
}