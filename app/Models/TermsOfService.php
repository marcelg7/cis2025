<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermsOfService extends Model
{
    use HasFactory;

    protected $table = 'terms_of_service';

    protected $fillable = [
        'filename',
        'path',
        'version',
        'is_active',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who uploaded this ToS
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the currently active Terms of Service
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Get all versions ordered by most recent
     */
    public static function getAllVersions()
    {
        return self::with('uploader')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Activate this version and deactivate all others
     */
    public function activate()
    {
        // Deactivate all other versions
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this one
        $this->update(['is_active' => true]);
    }
}