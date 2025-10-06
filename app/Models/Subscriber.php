<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model {
    protected $guarded = [];


    use HasFactory;

    protected $fillable = [
        'mobile_number',
        'first_name',
        'last_name',
        'status',
        'mobility_account_id',
		'is_test',
    ];
	
	protected $casts = [
		'is_test' => 'boolean', 
	];	

    public function mobilityAccount() {
        return $this->belongsTo(MobilityAccount::class);
    }

    public function contracts() {
        return $this->hasMany(Contract::class);
    }
}
