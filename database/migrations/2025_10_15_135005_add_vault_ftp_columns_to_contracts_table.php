<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('contracts', function (Blueprint $table) {
        $table->boolean('ftp_to_vault')->default(false)->after('pdf_path');
        $table->timestamp('ftp_at')->nullable()->after('ftp_to_vault');
        $table->string('vault_path')->nullable()->after('ftp_at');
        $table->text('ftp_error')->nullable()->after('vault_path');
    });
}

public function down()
{
    Schema::table('contracts', function (Blueprint $table) {
        $table->dropColumn(['ftp_to_vault', 'ftp_at', 'vault_path', 'ftp_error']);
    });
}
};
