<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractAddOn extends Model {
    protected $fillable = [
        'contract_id',
        'name',
        'code',
        'cost',
    ];

    public function contract() {
        return $this->belongsTo(Contract::class);
    }
}
