<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAfSchedulecontactsAddCertsInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_schedulecontacts', function (Blueprint $table) {
            $table->double('score', 8, 2)->after('type_of_intervention')->nullable();
            $table->integer('ects')->after('score')->nullable();
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
