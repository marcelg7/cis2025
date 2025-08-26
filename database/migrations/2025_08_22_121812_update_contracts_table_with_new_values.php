<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateContractsTableWithNewValues extends Migration
{
    public function up()
    {
        DB::table('contracts')->update([
            'device_price' => 1027.08,
            'required_upfront_payment' => 200.00,
            'optional_down_payment' => 150.00,
            'deferred_payment_amount' => 130.00,
            'dro_amount' => 330.00,
            'manufacturer' => 'apple',
            'model' => 'iphone',
            'version' => '15',
            'device_storage' => '128gb',
            'extra_info' => 'retail',
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        // Optionally reset fields to null or previous values
        DB::table('contracts')->update([
            'device_price' => null,
            'required_upfront_payment' => null,
            'optional_down_payment' => null,
            'deferred_payment_amount' => null,
            'dro_amount' => null,
            'manufacturer' => null,
            'model' => null,
            'version' => null,
            'device_storage' => null,
            'extra_info' => null,
            'updated_at' => now(),
        ]);
    }
}