<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfCertificates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            //$table->enum('type', ['student','employer'])->default('student');
            $table->enum('status', ['draft', 'signed','cancelled'])->default('draft');
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->foreign('session_id')->references('id')->on('af_sessions');
            $table->unsignedBigInteger('af_id');
            $table->unsignedBigInteger('enrollment_id')->nullable();
            $table->foreign('af_id')->references('id')->on('af_actions');
            $table->foreign('enrollment_id')->references('id')->on('af_enrollments');
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
        Schema::dropIfExists('af_certificates');
    }
}
