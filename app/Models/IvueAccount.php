<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IvueAccount extends Model
{
    protected $guarded = [];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function mobilityAccount() {
        return $this->hasOne(MobilityAccount::class);
    }
}
