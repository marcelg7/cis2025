<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractAddOnsTable extends Migration {
    public function up(): void {
        Schema::create('contract_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('code', 50);
            $table->decimal('cost', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('contract_add_ons');
    }
}
