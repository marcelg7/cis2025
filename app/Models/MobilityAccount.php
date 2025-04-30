<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobilityAccount extends Model
{
    protected $guarded = [];

    public function ivueAccount() {
        return $this->belongsTo(IvueAccount::class);
    }

    public function subscribers() {
        return $this->hasMany(Subscriber::class);
    }
}
