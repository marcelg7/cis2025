<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractOneTimeFee extends Model {
    protected $fillable = [
        'contract_id',
        'name',
        'cost',
    ];

    public function contract() {
        return $this->belongsTo(Contract::class);
    }
}
