<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfConvocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_convocations', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->timestamp('last_email_sent_date')->nullable();
            $table->enum('status', ['draft', 'sent','signed','canceled'])->default('draft');
            $table->unsignedBigInteger('entitie_id');
            $table->foreign('entitie_id')->references('id')->on('en_entities');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('en_contacts');
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
        Schema::dropIfExists('af_convocations');
    }
}
