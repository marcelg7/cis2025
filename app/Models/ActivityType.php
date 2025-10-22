<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityType extends Model {
    protected $fillable = [
        'name',
        'is_active',
    ];
}
