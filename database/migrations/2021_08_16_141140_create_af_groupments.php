<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfGroupments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_groupments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('af_id');
            $table->foreign('af_id')->references('id')->on('af_actions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('af_groupments');
    }
}
