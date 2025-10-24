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
        Schema::create('plan_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name')->nullable(); // User-given name for the comparison
            $table->text('notes')->nullable(); // Optional notes about this comparison

            // Store the comparison data as JSON
            $table->json('comparison_data'); // Will contain: plans, device, financing, add-ons, etc.

            // Quick access fields (denormalized for searching/filtering)
            $table->decimal('lowest_monthly_cost', 10, 2)->nullable();
            $table->decimal('lowest_total_cost', 10, 2)->nullable();
            $table->integer('plan_count')->default(0);

            $table->timestamps();

            // Indexes
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_comparisons');
    }
};
