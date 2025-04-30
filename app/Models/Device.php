<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $guarded = [];

    public function pricings() {
        return $this->hasMany(DevicePricing::class);
    }
}
