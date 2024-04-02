<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDevEstimatesAddAttachementIdColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dev_estimates', function (Blueprint $table) {
            $table->unsignedBigInteger('attachement_id')->nullable();
            $table->foreign('attachement_id')->references('id')->on('ged_attachments');
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
