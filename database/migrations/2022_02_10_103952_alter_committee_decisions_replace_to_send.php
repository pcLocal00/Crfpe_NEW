<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCommitteeDecisionsReplaceToSend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_committee_decisions', function (Blueprint $table) {
            $table->dropColumn('to_send');
            $table->boolean('send_transcript')->after('next_todo_comment')->default(false);
            $table->boolean('send_comment_mail')->after('send_transcript')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_committee_decisions', function (Blueprint $table) {
            //
        });
    }
}
