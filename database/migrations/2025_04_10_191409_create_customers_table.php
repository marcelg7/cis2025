<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('ivue_customer_number', 24)->unique();
            $table->string('first_name', 60);
            $table->string('last_name', 60);
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code', 7)->nullable();
            $table->string('display_name');
            $table->boolean('is_individual')->default(true);
            $table->text('customer_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
