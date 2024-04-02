<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundingPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_funding_payments', function (Blueprint $table) {
            $table->id();
            $table->enum('amount_type', ['percentage', 'fixed_amount'])->nullable();
             $table->double('amount',8,2);
             $table->date('due_date');	
             $table->timestamp('payment_date')->nullable();	
             $table->unsignedBigInteger('funding_id');
             $table->foreign('funding_id')->references('id')->on('af_fundings');
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
        Schema::dropIfExists('af_funding_payments');
    }
}
