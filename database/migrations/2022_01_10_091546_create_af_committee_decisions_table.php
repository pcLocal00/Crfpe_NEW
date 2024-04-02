<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfCommitteeDecisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_committee_decisions', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->text('next_todo_comment')->nullable();
            $table->enum('to_send', ['PERIOD_TRANSCRIPT', 'COMMENT_MAIL'])->default('PERIOD_TRANSCRIPT');
            $table->unsignedBigInteger('timestructure_id')->nullable();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('af_members');
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
        Schema::dropIfExists('af_committee_decisions');
    }
}
