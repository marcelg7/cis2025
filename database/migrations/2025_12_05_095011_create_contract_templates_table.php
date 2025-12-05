<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();

            // Template metadata
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null = team template
            $table->string('name'); // e.g., "Standard iPhone Activation"
            $table->text('description')->nullable();
            $table->boolean('is_team_template')->default(false); // admin-created team templates
            $table->integer('use_count')->default(0); // track usage for sorting

            // Contract configuration fields
            $table->foreignId('activity_type_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bell_device_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('rate_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('mobile_internet_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('commitment_period_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');

            // Add-ons and fees (stored as JSON arrays of IDs)
            $table->json('selected_add_ons')->nullable(); // array of plan_add_on IDs
            $table->json('selected_one_time_fees')->nullable(); // array of one_time_fee IDs

            // Optional configuration
            $table->boolean('hay_credit_applied')->default(false);
            $table->boolean('is_byod')->default(false);
            $table->decimal('connection_fee_override', 10, 2)->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('is_team_template');
            $table->index(['user_id', 'use_count']); // for sorting personal templates by usage
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
