<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvInvoicesAddFundingOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inv_invoices', function (Blueprint $table) {
            $table->enum('funding_option', ['contact_itself','entity_contact','other_funders'])->default('other_funders')->after('agreement_id');
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
