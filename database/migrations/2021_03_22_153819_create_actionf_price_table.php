<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionfPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_rel_price', function (Blueprint $table) {
            $table->unsignedBigInteger('af_id');
            $table->unsignedBigInteger('price_id');
            $table->foreign('af_id')->references('id')->on('af_actions')->onCascade('delete');
            $table->foreign('price_id')->references('id')->on('pf_prices')->onCascade('delete');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('af_rel_price');
    }
}
