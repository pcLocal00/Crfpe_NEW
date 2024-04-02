<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('en_adresses', function (Blueprint $table) {
            $table->id();
            $table->text('line_1')->nullable();
            $table->text('line_2')->nullable();
            $table->text('line_3')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city');
            $table->string('country');
            $table->boolean('is_billing')->default(false);//facturation
            $table->boolean('is_formation_site')->default(false);//lieu de formation
            $table->boolean('is_stage_site')->default(false);//terrain de stage
            $table->boolean('is_main_contact_address')->default(false);//Adresse principale du contact
            $table->boolean('is_main_entity_address')->default(false);//Adresse principale entity
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('entitie_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('en_contacts');
            $table->foreign('entitie_id')->references('id')->on('en_entities');
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
        Schema::dropIfExists('en_adresses');
    }
}
