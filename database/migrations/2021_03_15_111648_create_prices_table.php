<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pf_prices', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type')->nullable();//S or P
            $table->string('device_type')->nullable();//Intra or Inter
            $table->decimal('price',8,2)->default(0);
            $table->string('price_type');//Groupe or personne
            $table->string('accounting_code')->nullable();//Code comptable
            $table->boolean('is_broadcast')->default(false);//diffusé site
            $table->boolean('is_forbidden')->default(false);//Non applicable
            $table->boolean('is_ondemande')->default(false);//sur devis
            $table->boolean('is_former_price')->default(false);//sur un tarif formateur
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
        Schema::dropIfExists('af_prices');
    }
}
