<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAfFundings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_fundings', function (Blueprint $table) {
            $table->unsignedBigInteger('agreement_id')->nullable()->change();
            $table->unsignedBigInteger('invoice_id')->nullable()->after('agreement_id');
            $table->foreign('invoice_id')->references('id')->on('inv_invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_fundings', function (Blueprint $table) {
            //
        });
    }
}
