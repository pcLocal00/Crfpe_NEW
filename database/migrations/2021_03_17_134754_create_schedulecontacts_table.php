<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulecontactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_schedulecontacts', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_former')->default(false);
            $table->decimal('price',8,2)->default(0)->nullable();
            $table->string('price_type')->nullable();
            $table->decimal('total_cost',8,2)->default(0)->nullable();
            $table->boolean('is_absent')->default(false);
            $table->text('type_absent')->nullable();
            $table->string('type_of_intervention')->nullable();//formation, correction, copie
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->foreign('schedule_id')->references('id')->on('af_schedules');
            $table->foreign('member_id')->references('id')->on('af_members');
            $table->foreign('contract_id')->references('id')->on('en_contracts');
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
        Schema::dropIfExists('af_schedulecontacts');
    }
}
