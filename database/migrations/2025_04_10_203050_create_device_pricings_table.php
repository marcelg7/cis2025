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
        Schema::create('device_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['smartpay', 'byod']); // SmartPay or BYOD
            $table->decimal('price', 8, 2); // e.g., 29.99 per month or one-time fee
            $table->integer('term')->nullable(); // Months for SmartPay, null for BYOD
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_pricings');
    }
};
