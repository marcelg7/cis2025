<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model {
    protected $guarded = [];
	
	use HasFactory;

    protected $fillable = [
        'name',
		'is_test',
    ];
	
	protected $casts = [
        'is_test' => 'boolean', // Add this
    ];	
	
}
