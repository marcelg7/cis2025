<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IvueAccount extends Model
{
    protected $guarded = [];

    use HasFactory;

	
    protected $fillable = [
        'ivue_account',
        'status',
        'customer_id',
    ];	

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function mobilityAccount() {
        return $this->hasOne(MobilityAccount::class);
    }
}
