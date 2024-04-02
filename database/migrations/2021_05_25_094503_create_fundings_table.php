<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_fundings', function (Blueprint $table) {
            $table->id();
            $table->enum('amount_type', ['percentage', 'fixed_amount'])->nullable();
            $table->double('amount', 8, 2)->nullable();
            $table->enum('status', ['created','partial_paid','paid','cancelled','invoiced'])->default('created');
            $table->unsignedBigInteger('entitie_id');
            $table->foreign('entitie_id')->references('id')->on('en_entities');
            $table->unsignedBigInteger('agreement_id');
            $table->foreign('agreement_id')->references('id')->on('af_agreements');
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
        Schema::dropIfExists('af_fundings');
    }
}
