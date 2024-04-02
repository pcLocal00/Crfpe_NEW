<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAfSchedulecontactsAddPointageInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_schedulecontacts', function (Blueprint $table) {
            $table->dateTime('pointed_at')->after('pointing')->nullable();
            $table->unsignedBigInteger('pointed_by')->after('pointed_at')->nullable();
            $table->dateTime('validated_at')->nullable();
            $table->unsignedBigInteger('validated_by')->after('validated_at')->nullable();
            $table->boolean('is_sent_sage_paie')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_schedulecontacts', function (Blueprint $table) {
            //
        });
    }
}
