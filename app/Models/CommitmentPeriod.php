<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitmentPeriod extends Model {
    protected $fillable = [
        'name',
        'cancellation_policy',
        'is_active',
    ];
}
