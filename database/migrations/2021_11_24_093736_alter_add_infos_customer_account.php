<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddInfosCustomerAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('en_entities', function (Blueprint $table) {
            $table->string('collective_customer_account')->after('bic')->nullable();
            $table->string('auxiliary_customer_account')->after('collective_customer_account')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('en_entities', function (Blueprint $table) {
            //
        });
    }
}
