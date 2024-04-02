<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks_comment', function (Blueprint $table) {
            $table->id();
            $table->longText('description')->nullable();
            $table->timestamp('date_comment')->nullable();	
            $table->unsignedBigInteger('task_id')->nullable()->constrained();
            $table->foreign('task_id')->references('id')->on('tasks');
            $table->unsignedBigInteger('contact_id')->nullable()->constrained();
            $table->foreign('contact_id')->references('id')->on('en_contacts');
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
        Schema::dropIfExists('tasks_comment');
    }
}
