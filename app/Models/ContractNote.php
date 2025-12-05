<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractNote extends Model
{
    protected $fillable = [
        'contract_id',
        'user_id',
        'note',
        'is_important',
    ];

    protected $casts = [
        'is_important' => 'boolean',
    ];

    /**
     * Get the contract that owns the note
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get the user who created the note
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
