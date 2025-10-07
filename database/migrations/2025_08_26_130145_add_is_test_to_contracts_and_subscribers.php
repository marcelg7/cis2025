<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsTestToContractsAndSubscribers extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->boolean('is_test')->default(false)->after('signature_path');
        });
        Schema::table('subscribers', function (Blueprint $table) {
            $table->boolean('is_test')->default(false)->after('status');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_test')->default(false)->after('last_fetched_at');
        });
        Schema::table('plans', function (Blueprint $table) { // Add this block
            $table->boolean('is_test')->default(false)->after('is_active');
        });

        // Set existing records as test data
        DB::table('contracts')->update(['is_test' => 1]);
        DB::table('subscribers')->update(['is_test' => 1]);
        DB::table('customers')->update(['is_test' => 1]);
        DB::table('plans')->update(['is_test' => 1]); // Add this line
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });
        Schema::table('plans', function (Blueprint $table) { // Add this block
            $table->dropColumn('is_test');
        });
    }
}