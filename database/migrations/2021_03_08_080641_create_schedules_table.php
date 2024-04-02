<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('type', 2)->nullable();//M or A
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->timestamp('start_hour');
            $table->timestamp('end_hour')->nullable();
            $table->decimal('duration',8,2)->default(0);
            $table->unsignedBigInteger('sessiondate_id');
            $table->foreign('sessiondate_id')->references('id')->on('af_sessiondates');
            $table->softDeletes();
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
        Schema::dropIfExists('af_schedules');
    }
}
