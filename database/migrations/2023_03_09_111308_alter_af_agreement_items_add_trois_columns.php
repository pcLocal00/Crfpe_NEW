<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAfAgreementItemsAddTroisColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_agreement_items', function (Blueprint $table) {
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
        Schema::table('af_agreement_items', function (Blueprint $table) {
            //
        });
    }
}
