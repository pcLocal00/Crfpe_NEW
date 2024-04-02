<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrollmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entitie_id');
            $table->integer('nb_participants')->nullable();
            $table->decimal('price',8,2)->default(0)->nullable();
            $table->string('price_type')->nullable();
            $table->string('enrollment_type')->nullable();//Formateur or Stagiaire (F or S)
            $table->unsignedBigInteger('af_id');
            $table->foreign('af_id')->references('id')->on('af_actions');
            $table->softDeletes();
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
        Schema::dropIfExists('af_enrollments');
    }
}
