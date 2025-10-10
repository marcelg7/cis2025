<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BellPricing extends Model
{

    	
    protected $table = 'bell_pricing';
    
    protected $fillable = [
        'bell_device_id',
        'tier',
        'retail_price',
        'upfront_payment',
        'agreement_credit',
        'plan_cost',
        'monthly_device_cost_pre_tax',
        'monthly_device_cost_with_hst',
        'plan_plus_device_pre_tax',
        'plan_with_10_hay_credit',
        'hay_credit_plus_device_pre_tax',
        'plan_with_15_aal',
        'aal_15_plan_plus_device_pre_tax',
        'plan_with_30_aal',
        'aal_30_plan_plus_device_pre_tax',
        'plan_with_40_aal',
        'aal_40_plan_plus_device_pre_tax',
        'effective_date',
        'is_current',
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'upfront_payment' => 'decimal:2',
        'agreement_credit' => 'decimal:2',
        'plan_cost' => 'decimal:2',
        'monthly_device_cost_pre_tax' => 'decimal:2',
        'monthly_device_cost_with_hst' => 'decimal:2',
        'plan_plus_device_pre_tax' => 'decimal:2',
        'plan_with_10_hay_credit' => 'decimal:2',
        'hay_credit_plus_device_pre_tax' => 'decimal:2',
        'plan_with_15_aal' => 'decimal:2',
        'aal_15_plan_plus_device_pre_tax' => 'decimal:2',
        'plan_with_30_aal' => 'decimal:2',
        'aal_30_plan_plus_device_pre_tax' => 'decimal:2',
        'plan_with_40_aal' => 'decimal:2',
        'aal_40_plan_plus_device_pre_tax' => 'decimal:2',
        'effective_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(BellDevice::class, 'bell_device_id');
    }

    /**
     * Get pricing by device and tier
     */
    public static function getPricing(int $deviceId, string $tier, bool $currentOnly = true)
    {
        $query = static::where('bell_device_id', $deviceId)
            ->where('tier', $tier);
            
        if ($currentOnly) {
            $query->where('is_current', true);
        }
        
        return $query->first();
    }
}