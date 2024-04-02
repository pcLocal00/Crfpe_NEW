<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvInvoicesAddInvoiceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inv_invoices', function (Blueprint $table) {
            $table->enum('invoice_type',['cvts_ctrs','students'])->default('cvts_ctrs')->after('note');
            $table->unsignedBigInteger('af_id')->after('fundingpayment_id')->nullable();
            $table->foreign('af_id')->references('id')->on('af_actions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inv_invoices', function (Blueprint $table) {
            //
        });
    }
}
