<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterParParamsAddParamsCodeComptable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('par_params', function (Blueprint $table) {
            $table->string('accounting_code')->nullable();
            $table->double('amount', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('par_params', function (Blueprint $table) {
            //
        });
    }
}
