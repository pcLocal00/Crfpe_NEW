<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('par_params', function (Blueprint $table) {
            $table->id();
            $table->string('param_code');
            $table->string('param_name')->nullable();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('css_class')->default('info')->nullable();
            $table->integer('order_show');
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('par_params');
    }
}
