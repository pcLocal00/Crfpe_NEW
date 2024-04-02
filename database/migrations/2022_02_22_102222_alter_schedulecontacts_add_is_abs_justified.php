<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSchedulecontactsAddIsAbsJustified extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_schedulecontacts', function (Blueprint $table) {
            $table->boolean('is_abs_justified')->after('pointing')->default(false);
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
