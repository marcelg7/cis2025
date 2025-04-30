<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommitmentPeriodsTable extends Migration {
    public function up(): void {
        Schema::create('commitment_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // e.g., "24-Month SmartPay"
            $table->text('cancellation_policy')->nullable(); // Text with variables like {balance}
            $table->boolean('is_active')->default(true); // Active or Inactive
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('commitment_periods');
    }
}
