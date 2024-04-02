<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pf_formations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('max_availability')->nullable();
            //Nb Jours Théo / nb Heures Théo
            $table->integer('nb_days')->nullable();
            $table->integer('nb_hours')->nullable();
            //Nb Jours Pratiques / nb Heures Pratiques
            $table->integer('nb_pratical_days')->nullable();
            $table->integer('nb_pratical_hours')->nullable();
            $table->string('bpf_main_objective')->nullable();
            $table->string('bpf_training_specialty')->nullable();
            //le code comptable de rattachement
            $table->string('accounting_code')->nullable();
            //les codes analytiques
            $table->string('analytical_codes')->nullable();
            //autorize_af
            $table->boolean('autorize_af')->default(true);
            $table->unsignedBigInteger('categorie_id');
            $table->foreign('categorie_id')->references('id')->on('par_pf_categories');
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
        Schema::dropIfExists('pf_formations');
    }
}
