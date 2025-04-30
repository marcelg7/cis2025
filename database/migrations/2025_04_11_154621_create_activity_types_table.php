<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityTypesTable extends Migration {
    public function up(): void {
        Schema::create('activity_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // e.g., "Activation", "Upgrade"
            $table->boolean('is_active')->default(true); // Active or Inactive
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('activity_types');
    }
}
