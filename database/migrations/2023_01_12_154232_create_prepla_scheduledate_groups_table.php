<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreplaScheduledateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_prepla_scheduledate_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Pp_schedule_id');
            $table->foreign('Pp_schedule_id')->references('id')->on('af_prepla_schedules');
            $table->unsignedBigInteger('Regroupement')->nullable();
            $table->foreign('Regroupement')->references('id')->on('af_groupments');
            $table->unsignedBigInteger('Groupe')->nullable();
            $table->foreign('Groupe')->references('id')->on('af_groups');
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
        Schema::dropIfExists('af_prepla_scheduledate_groups');
    }
}
