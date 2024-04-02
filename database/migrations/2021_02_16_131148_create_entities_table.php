<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('en_entities', function (Blueprint $table) {
            $table->id();
            $table->string('ref');
            $table->enum('entity_type', ['P', 'S']);//P or S
            $table->string('name');
            $table->string('type')->nullable();//SARL, SAS ...
            $table->string('type_establishment')->nullable();
            $table->string('siren')->nullable();
            $table->string('siret')->nullable();
            $table->string('acronym')->nullable();//Sigle
            $table->string('naf_code')->nullable();
            $table->string('tva')->nullable();
            $table->string('pro_phone')->nullable();
            $table->string('pro_mobile')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('prospecting_area')->nullable();
            $table->boolean('is_client')->default(false);
            $table->boolean('is_funder')->default(false);
            $table->boolean('is_former')->default(false);
            $table->boolean('is_stage_site')->default(false);//terrain de stage
            $table->boolean('is_prospect')->default(false);
            $table->string('matricule_code')->nullable();
            $table->string('personal_thirdparty_code')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('rep_id')->nullable();//chargé d'affaires
            $table->unsignedBigInteger('entitie_id')->nullable();
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
        Schema::dropIfExists('en_entities');
    }
}
