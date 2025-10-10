<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Bell device and pricing references
            $table->foreignId('bell_device_id')->nullable()->after('shortcode_id')->constrained('bell_devices')->onDelete('set null');
            $table->string('bell_pricing_type')->nullable()->after('bell_device_id'); // 'smartpay' or 'dro'
            $table->string('bell_tier')->nullable()->after('bell_pricing_type'); // 'Ultra', 'Max', 'Select', 'Lite'
            
            // Snapshot of pricing at time of contract creation
            $table->decimal('bell_retail_price', 10, 2)->nullable()->after('bell_tier');
            $table->decimal('bell_monthly_device_cost', 10, 2)->nullable()->after('bell_retail_price');
            $table->decimal('bell_plan_cost', 10, 2)->nullable()->after('bell_monthly_device_cost');
            $table->decimal('bell_dro_amount', 10, 2)->nullable()->after('bell_plan_cost');
            $table->decimal('bell_plan_plus_device', 10, 2)->nullable()->after('bell_dro_amount');
            
            // Add index for faster queries
            $table->index(['bell_device_id', 'bell_pricing_type', 'bell_tier']);
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['bell_device_id']);
            $table->dropIndex(['bell_device_id', 'bell_pricing_type', 'bell_tier']);
            $table->dropColumn([
                'bell_device_id',
                'bell_pricing_type',
                'bell_tier',
                'bell_retail_price',
                'bell_monthly_device_cost',
                'bell_plan_cost',
                'bell_dro_amount',
                'bell_plan_plus_device',
            ]);
        });
    }
};