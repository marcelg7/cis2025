<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bell_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('storage')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['manufacturer', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bell_devices');
    }
};