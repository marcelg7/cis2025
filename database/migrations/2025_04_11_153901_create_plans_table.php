<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration {
    public function up(): void {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->enum('service_level', ['consumer', 'business']);
            $table->enum('plan_type', ['byod', 'smartpay']);
            $table->string('name', 100);
            $table->decimal('price', 8, 2); // e.g., 49.99
            $table->text('details')->nullable(); // Plan specifics (e.g., "5GB data, unlimited calls")
            $table->boolean('is_active')->default(true); // Active or Inactive
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('plans');
    }
}
