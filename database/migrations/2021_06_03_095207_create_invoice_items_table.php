<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->double('quantity',8,2)->default(1);
            $table->string('unit_type')->nullable();
            $table->double('rate',8,2);//taux
            $table->double('total',8,2);
            $table->integer('sort')->default(1);
            $table->unsignedBigInteger('fundingpayment_id')->nullable();
            $table->foreign('fundingpayment_id')->references('id')->on('af_funding_payments');
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('inv_invoices');
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
        Schema::dropIfExists('inv_invoice_items');
    }
}
