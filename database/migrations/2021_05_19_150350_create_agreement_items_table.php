<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgreementItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_agreement_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->double('quantity',8,2)->default(1);
            $table->string('unit_type')->nullable();
            $table->double('rate',8,2);//taux
            $table->double('total',8,2);
            $table->boolean('is_main_item')->default(false);
            $table->unsignedBigInteger('agreement_id');
            $table->foreign('agreement_id')->references('id')->on('af_agreements');
            $table->unsignedBigInteger('pf_id')->nullable();
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
        Schema::dropIfExists('af_agreement_items');
    }
}
