<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('nb_days')->nullable();
            $table->integer('nb_hours')->nullable();
            $table->boolean('is_uknown_date')->default(false);
            $table->integer('nb_dates_to_program')->nullable();//Nombre de dates connues à programmer
            $table->integer('nb_total_dates_to_program')->nullable();//Nombre de dates totales à programmer
            $table->integer('max_nb_trainees')->nullable();//Nombre de stagiaires max
            $table->string('session_type')->nullable();//Session continue sans/avec samedi ou discontinue
            $table->string('state');//Etat
            $table->string('training_site')->nullable();//lieu de formation
            $table->string('other_training_site')->nullable();//lieu de formation
            $table->boolean('is_active')->default(true);
            $table->boolean('is_main_session')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedBigInteger('planning_template_id')->nullable();
            $table->unsignedBigInteger('af_id');
            $table->foreign('planning_template_id')->references('id')->on('par_planning_templates');
            $table->foreign('af_id')->references('id')->on('af_actions');
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
        Schema::dropIfExists('af_session');
    }
}
