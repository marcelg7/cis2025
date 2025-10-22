<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // Set existing records as test data (only if tables have data)
        if (DB::table('contracts')->exists()) {
            DB::table('contracts')->update(['is_test' => 1]);
        }
        if (DB::table('subscribers')->exists()) {
            DB::table('subscribers')->update(['is_test' => 1]);
        }
        if (DB::table('customers')->exists()) {
            DB::table('customers')->update(['is_test' => 1]);
        }
        if (DB::table('plans')->exists()) {
            DB::table('plans')->update(['is_test' => 1]);
        }
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