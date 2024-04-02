<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRessourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('res_ressources', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_dispo')->default(false);
            $table->boolean('is_internal')->default(false);
            $table->unsignedBigInteger('ressource_id')->nullable();
            $table->foreign('ressource_id')->references('id')->on('res_ressources');
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
        Schema::dropIfExists('res_ressources');
    }
}
