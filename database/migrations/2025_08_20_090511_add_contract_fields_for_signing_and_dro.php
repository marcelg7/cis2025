<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('status')->default('draft'); // 'draft', 'signed', 'finalized'
            $table->decimal('dro_amount', 8, 2)->nullable();
            $table->string('signature_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['status', 'dro_amount', 'signature_path']);
        });
    }
};
