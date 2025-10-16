<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('financing_status')->default('not_required')->after('status'); // not_required, pending, signed, finalized
            $table->string('financing_signature_path')->nullable()->after('signature_path');
            $table->string('financing_pdf_path')->nullable()->after('pdf_path');
            $table->timestamp('financing_signed_at')->nullable()->after('financing_signature_path');
        });
    }

    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['financing_status', 'financing_signature_path', 'financing_pdf_path', 'financing_signed_at']);
        });
    }
};