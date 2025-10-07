<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];
	
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_fetched_at' => 'datetime',
		'is_test' => 'boolean',
    ];	

    use HasFactory;
	
    protected $fillable = [
        'ivue_customer_number',
        'first_name',
        'last_name',
        'email',
        'address',
        'city',
        'state',
        'zip_code',
        'display_name',
        'is_individual',
        'customer_json',
        'last_fetched_at',
		'is_test',
    ];	

    public function ivueAccounts() {
        return $this->hasMany(IvueAccount::class);
    }
}
