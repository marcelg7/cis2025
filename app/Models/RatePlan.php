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
		'credit_eligible',
		'credit_amount',
		'credit_type',
        'credit_duration',
        'credit_when_applicable',
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
		'credit_amount' => 'decimal:2',  
		'effective_date' => 'date',
		'is_current' => 'boolean',
		'is_active' => 'boolean',
		'is_test' => 'boolean',
		'is_international' => 'boolean',
		'is_us_mexico' => 'boolean',
		'credit_eligible' => 'boolean',  
	];

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('plan_type', $type);
    }

    public function scopeOfTier($query, $tier)
    {
        return $query->where('tier', $tier);
    }

    /**
     * Get the effective price (promo if available, otherwise base)
     * NOTE: Does NOT apply Hay Credit - that's manual
     */
    public function getEffectivePriceAttribute()
    {
        return $this->promo_price ?? $this->base_price;
    }

    /**
     * Check if plan has a promotion (excluding Hay Credit)
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

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

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

    public static function getCurrentByodPlans()
    {
        return static::current()
            ->active()
            ->ofType('byod')
            ->orderBy('tier')
            ->orderBy('base_price')
            ->get();
    }

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