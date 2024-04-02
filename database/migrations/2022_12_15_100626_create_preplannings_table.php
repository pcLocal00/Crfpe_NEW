<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreplanningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_preplannings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('state')->nullable();//Etat
            $table->timestamp('Start_date')->nullable();
            $table->string('Nb_Sessions')->nullable();
            $table->unsignedBigInteger('PF_id');
            $table->foreign('PF_id')->references('id')->on('pf_formations');
            $table->unsignedBigInteger('AF_target_id')->nullable();
            $table->foreign('AF_target_id')->references('id')->on('af_actions');
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
        Schema::dropIfExists('preplannings');
    }
}
