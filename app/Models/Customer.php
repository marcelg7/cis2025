<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];
	
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_fetched_at' => 'datetime',
    ];	

    public function ivueAccounts() {
        return $this->hasMany(IvueAccount::class);
    }
}
