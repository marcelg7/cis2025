<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('device_pricings');
    }

    public function down(): void
    {
        // Optional: Recreate table if needed
        Schema::create('device_pricings', function (Blueprint $table) {
            $table->id();
            // Add original columns, e.g.
            $table->string('manufacturer');
            $table->string('model');
            $table->timestamps();
        });
    } 
}; 