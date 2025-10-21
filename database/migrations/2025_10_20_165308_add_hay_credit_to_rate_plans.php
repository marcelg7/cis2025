<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rate_plans', function (Blueprint $table) {
            $table->boolean('hay_credit_eligible')->default(false)->after('promo_description');
            $table->decimal('hay_credit_amount', 8, 2)->nullable()->after('hay_credit_eligible');
        });
    }

    public function down()
    {
        Schema::table('rate_plans', function (Blueprint $table) {
            $table->dropColumn(['hay_credit_eligible', 'hay_credit_amount']);
        });
    }
};