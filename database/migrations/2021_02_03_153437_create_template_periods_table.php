<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatePeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('par_template_periods', function (Blueprint $table) {
            $table->id();
            $table->string('type', 2)->nullable();//M or A
            $table->timestamp('start_hour');
            $table->timestamp('end_hour')->nullable();
            $table->decimal('duration',8,2)->default(0);
            $table->unsignedBigInteger('planning_template_id')->nullable();
            $table->foreign('planning_template_id')->references('id')->on('par_planning_templates');
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
        Schema::dropIfExists('par_template_periods');
    }
}
