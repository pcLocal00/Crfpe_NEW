<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreplaSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_prepla_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Pp_id');
            $table->foreign('Pp_id')->references('id')->on('af_preplannings');
            $table->string('title')->nullable();
            $table->dateTime('date_start')->nullable();
            $table->dateTime('start_hour')->nullable();
            $table->dateTime('end_hour')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->integer('sequence_total')->nullable();
            $table->string('color')->nullable();
            $table->string('remarks', 500)->nullable();
            $table->unsignedBigInteger('Pf_session')->nullable();
            $table->foreign('Pf_session')->references('id')->on('pf_formations');
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
        Schema::dropIfExists('prepla_schedules');
    }
}
