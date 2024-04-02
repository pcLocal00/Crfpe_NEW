<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddAccountingInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_actions', function (Blueprint $table) {
            $table->string('accounting_code')->after('other_training_site')->nullable();
            $table->string('analytical_code')->after('accounting_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_actions', function (Blueprint $table) {
            //
        });
    }
}
