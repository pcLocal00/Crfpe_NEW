<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAfActionsAddPraticalInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_actions', function (Blueprint $table) {
            $table->integer('nb_pratical_days')->after('nb_hours')->nullable();
            $table->decimal('nb_pratical_hours')->after('nb_pratical_days')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_actions', function (Blueprint $table) {
            //
        });
    }
}
