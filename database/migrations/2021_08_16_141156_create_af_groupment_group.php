<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfGroupmentGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_groupment_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('groupment_id');
            $table->unsignedBigInteger('group_id');
            $table->foreign('groupment_id')->references('id')->on('af_groupments');
            $table->foreign('group_id')->references('id')->on('af_groups');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('af_groupment_group');
    }
}
