<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPfFormationsAddV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pf_formations', function (Blueprint $table) {
            $table->string('product_type')->after('autorize_af')->nullable();
            $table->integer('sort')->after('product_type')->default(1);
            $table->unsignedBigInteger('timestructure_id')->after('sort')->nullable();
            $table->unsignedBigInteger('parent_id')->after('timestructure_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('pf_formations');
            $table->integer('nb_session_duplication')->after('parent_id')->default(1);
            $table->integer('nb_sessiondates')->after('nb_session_duplication')->default(1);
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
