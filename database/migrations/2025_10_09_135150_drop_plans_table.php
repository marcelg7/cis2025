<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('plans');
    }

    public function down(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->enum('service_level', ['consumer', 'business']);
            $table->enum('plan_type', ['byod', 'smartpay']);
            $table->string('name', 100);
            $table->decimal('price', 8, 2);
            $table->text('details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_test')->default(false);
            $table->timestamps();
        });
    }
};