<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPfFormationsAddTimestructureSortV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pf_formations', function (Blueprint $table) {
            $table->integer('timestructure_sort')->after('timestructure_id')->default(1);
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
