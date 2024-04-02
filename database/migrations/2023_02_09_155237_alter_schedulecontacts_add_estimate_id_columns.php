<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSchedulecontactsAddEstimateIdColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_schedulecontacts', function (Blueprint $table) {
            $table->unsignedBigInteger('estimate_id')->nullable();
            $table->foreign('estimate_id')->references('id')->on('dev_estimates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_schedulecontacts', function (Blueprint $table) {
            //
        });
    }
}
