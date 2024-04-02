<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('source_id')->nullable()->constrained();
            $table->foreign('source_id')->references('id')->on('par_params');
            $table->unsignedBigInteger('type_id')->nullable()->constrained();
            $table->foreign('type_id')->references('id')->on('par_params');
            $table->unsignedBigInteger('etat_id')->nullable()->constrained();
            $table->foreign('etat_id')->references('id')->on('par_params');
            $table->string('priority')->nullable();
            $table->unsignedBigInteger('responsable_id')->nullable()->constrained();
            $table->foreign('responsable_id')->references('id')->on('en_contacts');
            $table->unsignedBigInteger('apporteur_id')->nullable()->constrained();
            $table->foreign('apporteur_id')->references('id')->on('en_contacts');
            $table->timestamp('start_date')->nullable();	
            $table->timestamp('ended_date')->nullable();	
            $table->timestamp('callback_date')->nullable();	
            $table->string('callback_mode')->nullable();;
            $table->unsignedBigInteger('reponse_mode_id')->nullable()->constrained();
            $table->foreign('reponse_mode_id')->references('id')->on('par_params');
            $table->unsignedBigInteger('entite_id')->nullable()->constrained();
            $table->foreign('entite_id')->references('id')->on('en_entities');
            $table->unsignedBigInteger('contact_id')->nullable()->constrained();
            $table->foreign('contact_id')->references('id')->on('en_contacts');
            $table->unsignedBigInteger('af_id')->nullable()->constrained();
            $table->foreign('af_id')->references('id')->on('af_actions');
            $table->unsignedBigInteger('pf_id')->nullable()->constrained();
            $table->foreign('pf_id')->references('id')->on('pf_formations');
            $table->unsignedBigInteger('task_parent_id')->nullable()->constrained();
            $table->foreign('task_parent_id')->references('id')->on('tasks');
            $table->boolean('sub_task')->default(false);
            $table->boolean('is_sent')->default(false);
            $table->string('file')->nullable();
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
        Schema::dropIfExists('tasks');
    }
}
