<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('shortcodes');
    }

    public function down(): void
    {
        // Optional: Recreate if needed for rollback
        Schema::create('shortcodes', function (Blueprint $table) {
            $table->id();
            // Add original columns here if you want rollback support
            $table->timestamps();
        });
    }
};