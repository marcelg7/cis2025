<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractOneTimeFeesTable extends Migration {
    public function up(): void {
        Schema::create('contract_one_time_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->decimal('cost', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('contract_one_time_fees');
    }
}
