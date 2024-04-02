<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number',45);
            //bill_date
            $table->date('bill_date');
            //due_date
            $table->date('due_date');
            //note
            $table->text('note')->nullable();
            //tax_percentage
            $table->double('tax_percentage', 8, 2)->nullable();
            //discount_amount
            $table->double('discount_amount',8,2)->nullable();
            //discount_amount_type
            $table->enum('discount_amount_type', ['percentage', 'fixed_amount'])->nullable();
            //discount_type
            $table->enum('discount_type', ['before_tax', 'after_tax'])->default('before_tax');
            //status
            $table->enum('status', ['draft', 'not_paid','partial_paid','paid','cancelled'])->default('draft');//enum('draft', 'not_paid','partial_paid', 'paid','cancelled')
            //cancelled_at
            $table->timestamp('cancelled_at')->nullable();
            //cancelled_by
            $table->unsignedBigInteger('cancelled_by')->nullable();//users
            $table->unsignedBigInteger('created_by')->nullable();//users
            $table->unsignedBigInteger('entitie_id');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('agreement_id')->nullable();
            $table->unsignedBigInteger('entitie_funder_id')->nullable();
            $table->unsignedBigInteger('contact_funder_id')->nullable();
            $table->unsignedBigInteger('fundingpayment_id')->nullable();//deadline
            $table->foreign('entitie_id')->references('id')->on('en_entities');
            $table->foreign('contact_id')->references('id')->on('en_contacts'); 
            $table->foreign('agreement_id')->references('id')->on('af_agreements');
            $table->foreign('entitie_funder_id')->references('id')->on('en_entities');
            $table->foreign('contact_funder_id')->references('id')->on('en_contacts');
            $table->foreign('fundingpayment_id')->references('id')->on('af_funding_payments');
            $table->foreign('created_by')->references('id')->on('users');
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
        Schema::dropIfExists('inv_invoices');
    }
}
