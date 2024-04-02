<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEnContactsPayInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('en_contacts', function (Blueprint $table) {
            $table->string('birth_department')->nullable();
            $table->string('birth_city')->nullable();
            $table->string('social_security_number')->nullable();
            $table->string('nationality')->nullable();
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
