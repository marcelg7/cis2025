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
        Schema::create('mobility_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ivue_account_id')->constrained()->onDelete('cascade')->unique();
            $table->string('mobility_account', 127)->unique();
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobility_accounts');
    }
};
