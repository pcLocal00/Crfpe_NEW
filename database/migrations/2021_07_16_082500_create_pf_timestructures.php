<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePfTimestructures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pf_timestructures', function (Blueprint $table) {
            $table->id();
            $table->string('sort')->default(1);
            $table->string('name');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('pf_timestructures');
            $table->foreign('category_id')->references('id')->on('pf_timestructurescategories');
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
        Schema::dropIfExists('pf_timestructures');
    }
}
