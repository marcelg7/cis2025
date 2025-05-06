<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shortcode extends Model
{
    protected $fillable = ['wp_id', 'slug', 'data', 'disabled', 'previous_slug', 'multisite'];

    protected $casts = [
        'disabled' => 'boolean',
        'multisite' => 'boolean',
    ];
}