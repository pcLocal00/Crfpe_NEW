<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDevEstimateDropAttachementIdColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dev_estimates', function (Blueprint $table) {
            $table->dropForeign('dev_estimates_attachement_id_foreign');
            $table->dropColumn('attachement_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dev_estimates', function (Blueprint $table) {
            //
        });
    }
}
