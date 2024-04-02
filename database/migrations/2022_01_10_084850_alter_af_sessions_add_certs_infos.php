<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAfSessionsAddCertsInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_sessions', function (Blueprint $table) {
            $table->enum('session_mode', ['SESSION', 'HIERARCHIE'])->after('planning_template_id')->default('SESSION');
            $table->unsignedBigInteger('timestructure_id')->after('session_mode')->nullable();
            $table->unsignedBigInteger('session_parent_id')->after('timestructure_id')->nullable();
            $table->foreign('session_parent_id')->references('id')->on('af_sessions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_sessions', function (Blueprint $table) {
            //
        });
    }
}
