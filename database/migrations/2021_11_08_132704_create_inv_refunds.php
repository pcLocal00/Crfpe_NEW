<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('number',45);
            //reason
            $table->text('reason')->nullable();
            //refund_date
            $table->date('refund_date')->nullable();			
            //invoice_id
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
        Schema::dropIfExists('inv_refunds');
    }
}
