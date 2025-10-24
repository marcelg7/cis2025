<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'name',
        'notes',
        'comparison_data',
        'lowest_monthly_cost',
        'lowest_total_cost',
        'plan_count',
    ];

    protected $casts = [
        'comparison_data' => 'array',
        'lowest_monthly_cost' => 'decimal:2',
        'lowest_total_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this comparison
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
