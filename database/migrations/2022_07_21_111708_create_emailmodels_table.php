<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailmodelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emailmodels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 45);
            $table->string('name');
            $table->text('default_content');
            $table->text('custom_content');
            $table->text('default_header');
            $table->text('custom_header');
            $table->text('default_footer');
            $table->text('custom_footer');
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
        Schema::dropIfExists('emailmodels');
    }
}
