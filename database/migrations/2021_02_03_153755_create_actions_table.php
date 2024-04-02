<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_actions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('nb_days')->nullable();
            $table->integer('nb_hours')->nullable();
            $table->boolean('is_uknown_date')->default(false);
            $table->string('bpf_main_objective')->nullable();
            $table->string('bpf_training_specialty')->nullable();
            $table->string('device_type');//type dispositif
            $table->string('state');//Etat
            $table->string('status');//Status
            $table->integer('max_nb_trainees')->nullable();//Nombre de stagiaires max
            $table->string('training_site')->nullable();//lieu de formation
            $table->string('other_training_site')->nullable();//lieu de formation
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('formation_id');
            $table->foreign('formation_id')->references('id')->on('pf_formations');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
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
        Schema::dropIfExists('af_actions');
    }
}
