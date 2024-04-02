<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('en_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('gender')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable();
            $table->string('pro_phone')->nullable();
            $table->string('pro_mobile')->nullable();
            $table->string('function')->nullable();
            $table->boolean('is_main_contact')->default(false);//contact principale
            $table->boolean('is_billing_contact')->default(false);//contact de facturation
            $table->string('is_order_contact')->default(false);
            $table->boolean('is_trainee_contact')->default(false);//stagiaire
            $table->boolean('is_former')->default(false);//Si formateur
            $table->string('type_former_intervention')->nullable();//Si formateur : type d'intervention
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('entitie_id');
            $table->foreign('entitie_id')->references('id')->on('en_entities');
            $table->date('birth_date')->nullable();
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
        Schema::dropIfExists('en_contacts');
    }
}
