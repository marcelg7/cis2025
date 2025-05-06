<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShortcodesTable extends Migration
{
    public function up()
    {
        Schema::create('shortcodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wp_id')->unique(); // Maps to SH_CD_SHORTCODES.id
            $table->string('slug');
            $table->text('data')->nullable();
            $table->boolean('disabled')->default(false);
            $table->string('previous_slug')->nullable();
            $table->boolean('multisite')->default(false);
            $table->timestamps(); // Optional, for Laravel tracking
        });
    }

    public function down()
    {
        Schema::dropIfExists('shortcodes');
    }
}