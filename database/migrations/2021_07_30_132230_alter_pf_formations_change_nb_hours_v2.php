<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPfFormationsChangeNbHoursV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pf_formations', function (Blueprint $table) {
            $table->decimal('nb_hours')->nullable()->change();
            $table->decimal('nb_pratical_hours')->nullable()->change();
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
