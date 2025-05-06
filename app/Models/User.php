<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use Notifiable;
	
	protected $fillable = [
		'name',
		'email',
		'password',
		'role',
		'session_lifetime',
        'component_styles',		
	];	

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'component_styles',
        'role' => 'string',
    ];

    public function isAdmin(): bool {
        return $this->role === 'admin';
    }
}