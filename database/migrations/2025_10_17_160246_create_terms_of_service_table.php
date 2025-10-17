<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('terms_of_service', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->string('version')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('terms_of_service');
    }
};