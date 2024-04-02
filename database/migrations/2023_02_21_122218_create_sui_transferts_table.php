<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuiTransfertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sui_transfert', function (Blueprint $table) {
            $table->id();
            $table->integer('af')->nullable();
            $table->integer('prepla')->nullable();
            $table->integer('pps_id')->nullable();
            $table->text('titre')->nullable();
            $table->date('date_s')->nullable();
            $table->datetime('heure_d')->nullable();
            $table->datetime('heure_f')->nullable();
            $table->string('couleur')->nullable();
            $table->integer('pf_session')->nullable();
            $table->integer('time_str')->nullable();
            $table->integer('parent')->nullable();
            $table->integer('contact')->nullable();
            $table->decimal('tarif')->nullable();
            $table->string('type_int')->nullable();
            $table->integer('regrp')->nullable();
            $table->integer('grp')->nullable();
            $table->text('result')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sui_transfert');
    }
}
