<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanAddOn extends Model
{
    protected $fillable = [
        'soc_code',
        'add_on_name',
        'monthly_rate',
        'category',
        'group_soc',
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
     * Scope to get only active add-ons
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
        return "{$this->add_on_name} - $" . number_format($this->monthly_rate, 2) . "/mo";
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
     * Get all current add-ons grouped by category
     */
    public static function getCurrentAddOnsByCategory()
    {
        return static::current()
            ->active()
            ->orderBy('category')
            ->orderBy('monthly_rate')
            ->get()
            ->groupBy('category');
    }

    /**
     * Get all current add-ons
     */
    public static function getCurrentAddOns()
    {
        return static::current()
            ->active()
            ->orderBy('category')
            ->orderBy('add_on_name')
            ->get();
    }
}