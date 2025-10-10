<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bell_dro_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bell_device_id')->constrained()->onDelete('cascade');
            $table->enum('tier', ['Ultra', 'Max', 'Select', 'Lite']);
            $table->decimal('retail_price', 10, 2);
            $table->decimal('upfront_payment', 10, 2)->default(0);
            $table->decimal('agreement_credit', 10, 2)->default(0);
            $table->decimal('dro_amount', 10, 2); // Device Return Option
            $table->decimal('plan_cost', 10, 2);
            $table->decimal('monthly_device_cost_pre_tax', 10, 2);
            $table->decimal('monthly_device_cost_with_hst', 10, 2);
            $table->decimal('plan_plus_device_pre_tax', 10, 2);
            $table->decimal('plan_with_10_hay_credit', 10, 2);
            $table->decimal('hay_credit_plus_device_pre_tax', 10, 2);
            $table->decimal('plan_with_15_aal', 10, 2);
            $table->decimal('aal_15_plan_plus_device_pre_tax', 10, 2);
            $table->decimal('plan_with_30_aal', 10, 2);
            $table->decimal('aal_30_plan_plus_device_pre_tax', 10, 2);
            $table->decimal('plan_with_40_aal', 10, 2);
            $table->decimal('aal_40_plan_plus_device_pre_tax', 10, 2);
            $table->date('effective_date');
            $table->boolean('is_current')->default(true);
            $table->timestamps();
            
            $table->index(['bell_device_id', 'tier', 'is_current']);
            $table->unique(['bell_device_id', 'tier', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bell_dro_pricing');
    }
};