<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BellDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'manufacturer',
        'model',
        'storage',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['has_smartpay', 'has_dro'];

    public function getHasSmartpayAttribute()
    {
        return $this->currentPricing()->exists(); // Checks if any current SmartPay pricing exists
    }

    public function getHasDroAttribute()
    {
        return $this->currentDroPricing()->exists(); // Checks if any current DRO pricing exists
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(BellPricing::class);
    }

    public function droPricing(): HasMany
    {
        return $this->hasMany(BellDroPricing::class);
    }

    public function currentPricing(): HasMany
    {
        return $this->hasMany(BellPricing::class)->where('is_current', true);
    }

    public function currentDroPricing(): HasMany
    {
        return $this->hasMany(BellDroPricing::class)->where('is_current', true);
    }

    /**
     * Parse device name into components
     */
    public static function parseDeviceName(string $name): array
    {
        $parts = explode(' ', $name);
        
        return [
            'manufacturer' => $parts[0] ?? null,
            'model' => implode(' ', array_slice($parts, 1, -1)),
            'storage' => end($parts),
        ];
    }
}