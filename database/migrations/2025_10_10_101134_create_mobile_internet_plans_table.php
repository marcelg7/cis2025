<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_internet_plans', function (Blueprint $table) {
            $table->id();
            $table->string('soc_code', 50)->comment('Plan SOC code from Bell');
            $table->string('plan_name')->comment('Human readable plan name');
            $table->decimal('monthly_rate', 8, 2)->comment('Monthly rate');
            $table->string('category', 100)->nullable()->comment('ABC category');
            $table->string('promo_group', 100)->nullable()->comment('Promo group description');
            $table->text('description')->nullable()->comment('Additional details');
            $table->date('effective_date')->comment('Date this pricing becomes effective');
            $table->boolean('is_current')->default(true)->comment('Is this the current pricing?');
            $table->boolean('is_active')->default(true)->comment('Is plan available for sale?');
            $table->boolean('is_test')->default(false)->comment('Test data flag');
            $table->timestamps();
            
            // Indexes
            $table->unique(['soc_code', 'effective_date']);
            $table->index(['is_current', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_internet_plans');
    }
};