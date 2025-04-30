<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model {
    protected $guarded = [];

    public function mobilityAccount() {
        return $this->belongsTo(MobilityAccount::class);
    }

    public function contracts() {
        return $this->hasMany(Contract::class);
    }
}
