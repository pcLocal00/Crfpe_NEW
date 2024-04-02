<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreplaScheduledateIntervenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_prepla_scheduledate_intervenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Pp_schedule_id');
            $table->foreign('Pp_schedule_id')->references('id')->on('af_prepla_schedules');
            $table->unsignedBigInteger('Contact_id');
            $table->foreign('Contact_id')->references('id')->on('en_contacts');
            $table->decimal('price')->nullable();
            $table->string('type')->nullable();
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
        Schema::dropIfExists('af_prepla_scheduledate_intervenants');
    }
}
