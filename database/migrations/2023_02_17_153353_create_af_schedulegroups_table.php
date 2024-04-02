<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfSchedulegroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_schedulegroups', function (Blueprint $table) {
                $table->id();
                $table->integer('schedule_id')->notnull();
                $table->integer('group_id')->nullable();
                $table->integer('regroup_id')->nullable();
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
        Schema::dropIfExists('af_schedulegroups');
    }
}
