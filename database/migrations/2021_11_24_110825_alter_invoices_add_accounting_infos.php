<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvoicesAddAccountingInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inv_invoices', function (Blueprint $table) {
            $table->string('accounting_code')->after('funding_option')->nullable();
            $table->string('analytical_code')->after('accounting_code')->nullable();///
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
