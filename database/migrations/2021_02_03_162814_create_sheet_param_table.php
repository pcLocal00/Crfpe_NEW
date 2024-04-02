<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetParamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pf_sheet_param', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->integer('order_show')->default(1);
            $table->unsignedBigInteger('sheet_id');
            $table->unsignedBigInteger('param_id');
            $table->foreign('sheet_id')->references('id')->on('pf_sheets');
            $table->foreign('param_id')->references('id')->on('par_params');
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
        Schema::dropIfExists('pf_sheet_param');
    }
}
