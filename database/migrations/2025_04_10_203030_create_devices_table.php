<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('manufacturer', 100);
            $table->string('model', 100);
            $table->decimal('srp', 8, 2); // e.g., 999.99
            $table->string('image')->nullable(); // Path to stored image
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
