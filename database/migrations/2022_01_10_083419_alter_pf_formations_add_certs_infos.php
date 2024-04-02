<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPfFormationsAddCertsInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pf_formations', function (Blueprint $table) {
            $table->integer('ects')->after('nb_sessiondates')->nullable();
            $table->integer('coefficient')->after('ects')->nullable();
            $table->boolean('is_evaluation')->after('coefficient')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pf_formations', function (Blueprint $table) {
            //
        });
    }
}
