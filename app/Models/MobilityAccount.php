<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobilityAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobility_account',
        'status',
        'ivue_account_id',
    ];

    public function ivueAccount() {
        return $this->belongsTo(IvueAccount::class);
    }

    public function subscribers() {
        return $this->hasMany(Subscriber::class);
    }
}
