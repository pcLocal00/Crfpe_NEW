<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterContactsAddStudentStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('en_contacts', function (Blueprint $table) {
            $table->enum('student_status', ['student', 'apprentices','employees','jobseeker'])->after('function')->nullable();
            $table->date('student_status_date')->after('student_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('en_contacts', function (Blueprint $table) {
            //
        });
    }
}
