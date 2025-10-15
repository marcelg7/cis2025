<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->string('soc_code', 50)->comment('Plan SOC code from Bell');
            $table->string('plan_name')->comment('Human readable plan name');
            $table->enum('plan_type', ['byod', 'smartpay'])->comment('BYOD or SmartPay');
            $table->enum('tier', ['Lite', 'Select', 'Max', 'Ultra'])->nullable()->comment('Plan tier');
            $table->decimal('base_price', 8, 2)->comment('Base monthly price');
            $table->decimal('promo_price', 8, 2)->nullable()->comment('Promotional price if applicable');
            $table->string('promo_description')->nullable()->comment('Promo details (e.g., $10 Hay Credit)');
            $table->text('data_amount')->nullable()->comment('Data included (e.g., 100GB, 175GB US)');
            $table->boolean('is_international')->default(false)->comment('Includes international features');
            $table->boolean('is_us_mexico')->default(false)->comment('Includes US/Mexico coverage');
            $table->text('features')->nullable()->comment('Additional plan features');
            $table->date('effective_date')->comment('Date this pricing becomes effective');
            $table->boolean('is_current')->default(true)->comment('Is this the current pricing?');
            $table->boolean('is_active')->default(true)->comment('Is plan available for sale?');
            $table->boolean('is_test')->default(false)->comment('Test data flag');
            $table->timestamps();
            
            // Indexes
            $table->unique(['soc_code', 'effective_date']);
            $table->index(['plan_type', 'tier', 'is_current', 'is_active']);
            $table->index(['is_current', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_plans');
    }
};