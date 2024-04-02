<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_invoice_payments', function (Blueprint $table) {
            $table->id();
            //amount
            $table->double('amount',8,2);	
            //payment_date
            $table->timestamp('payment_date')->nullable();	
            //payment_method
            $table->string('payment_method',45);
            //reference	
            $table->string('reference')->nullable();	
            //note
            $table->text('note')->nullable();	
            //invoice_id
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('inv_invoices');
            $table->unsignedBigInteger('funding_payment_id')->nullable();
            $table->foreign('funding_payment_id')->references('id')->on('af_funding_payments');
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
        Schema::dropIfExists('inv_invoice_payments');
    }
}
