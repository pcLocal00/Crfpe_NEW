<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAfSchedulecontactsAddInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_schedulecontacts', function (Blueprint $table) {
            $table->enum('pointing', ['not_pointed','absent', 'present'])->after('total_cost')->default('not_pointed');
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
