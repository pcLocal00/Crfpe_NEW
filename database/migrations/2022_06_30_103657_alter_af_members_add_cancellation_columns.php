<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAfMembersAddCancellationColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_members', function (Blueprint $table) {
            $table->enum('stop_reason', ['suspend', 'stop', 'cancel'])->after('enrollment_id')->nullable();
            $table->date('effective_date')->nullable()->after('stop_reason');
            $table->date('resumption_date')->nullable()->after('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_members', function (Blueprint $table) {
            //
        });
    }
}
