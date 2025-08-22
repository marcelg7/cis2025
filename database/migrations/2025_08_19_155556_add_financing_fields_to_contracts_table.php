<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('required_upfront_payment', 8, 2)->nullable();
            $table->decimal('optional_down_payment', 8, 2)->nullable();
            $table->decimal('deferred_payment_amount', 8, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['required_upfront_payment', 'optional_down_payment', 'deferred_payment_amount']);
        });
    }
};
