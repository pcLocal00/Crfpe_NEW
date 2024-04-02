<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfInternshiproposals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_internshiproposals', function (Blueprint $table) {
            $table->id();
            $table->enum('state',['draft','approuved','invalid','validated','imposed']);
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->unsignedBigInteger('representing_contact_id')->nullable();//Représenté par (nom du signataire de la convention) :
            $table->unsignedBigInteger('internship_referent_contact_id')->nullable();//Fonction du représentant
            $table->unsignedBigInteger('trainer_referent_contact_id')->nullable();//Formateur référent
            $table->string('service')->nullable();
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('af_id');
            $table->unsignedBigInteger('adresse_id')->nullable();//Adresse lieu de stage
            $table->foreign('representing_contact_id')->references('id')->on('en_contacts');
            $table->foreign('internship_referent_contact_id')->references('id')->on('en_contacts');
            $table->foreign('trainer_referent_contact_id')->references('id')->on('en_contacts');
            $table->foreign('entity_id')->references('id')->on('en_entities');
            $table->foreign('member_id')->references('id')->on('af_members');
            $table->foreign('session_id')->references('id')->on('af_sessions');
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
        Schema::dropIfExists('af_internshiproposals');
    }
}
