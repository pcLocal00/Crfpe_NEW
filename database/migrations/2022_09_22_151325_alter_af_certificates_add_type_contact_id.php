<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAfCertificatesAddTypeContactId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_certificates', function (Blueprint $table) {
            $table->enum('type', ['student','employer'])->default('employer')->after('number');
            $table->unsignedBigInteger('contact_id')->nullable()->after('enrollment_id');
            $table->foreign('contact_id')->references('id')->on('en_contacts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('af_certificates', function (Blueprint $table) {
            //
        });
    }
}
