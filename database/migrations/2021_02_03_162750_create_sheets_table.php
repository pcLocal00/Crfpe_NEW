<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pf_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('ft_code')->unique();
            $table->integer('version');
            $table->longText('description')->nullable();
            $table->boolean('is_default');
            $table->unsignedBigInteger('param_id');
            $table->unsignedBigInteger('formation_id')->nullable();
            $table->unsignedBigInteger('action_id')->nullable();
            $table->foreign('param_id')->references('id')->on('par_params');
            $table->foreign('formation_id')->references('id')->on('pf_formations');
            $table->foreign('action_id')->references('id')->on('af_actions');
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
        Schema::dropIfExists('pf_sheets');
    }
}
