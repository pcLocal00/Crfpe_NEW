<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDevAgreementsAddDiscountLabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_agreements', function (Blueprint $table) {
            $table->string('discount_label')->after('tax_percentage')->default('Remise')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_agreements', function (Blueprint $table) {
            //
        });
    }
}
