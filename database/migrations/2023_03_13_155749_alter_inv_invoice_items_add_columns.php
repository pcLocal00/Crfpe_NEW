<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvInvoiceItemsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inv_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('attachement_id')->nullable();
            $table->foreign('attachement_id')->references('id')->on('ged_attachments');
            $table->string('statut')->nullable();
            $table->boolean('is_validation')->default(0);
            $table->text('motif')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inv_invoice_items', function (Blueprint $table) {
            //
        });
    }
}
