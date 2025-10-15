<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Rate Plan (for voice/data plans)
            $table->foreignId('rate_plan_id')->nullable()->after('bell_device_id')->constrained('rate_plans')->nullOnDelete();
            
            // Mobile Internet Plan (for data-only devices like tablets)
            $table->foreignId('mobile_internet_plan_id')->nullable()->after('rate_plan_id')->constrained('mobile_internet_plans')->nullOnDelete();
            
            // Track the pricing at time of contract creation (for historical accuracy)
            $table->decimal('rate_plan_price', 8, 2)->nullable()->after('mobile_internet_plan_id');
            $table->decimal('mobile_internet_price', 8, 2)->nullable()->after('rate_plan_price');
            
            // Optional: Store which tier was selected (for Bell devices with tier pricing)
            $table->string('selected_tier', 50)->nullable()->after('mobile_internet_price');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['rate_plan_id']);
            $table->dropForeign(['mobile_internet_plan_id']);
            $table->dropColumn([
                'rate_plan_id',
                'mobile_internet_plan_id',
                'rate_plan_price',
                'mobile_internet_price',
                'selected_tier',
            ]);
        });
    }
};