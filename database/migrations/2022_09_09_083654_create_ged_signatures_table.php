<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGedSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ged_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('contract');
            $table->unsignedBigInteger('contract_id')->nullable()->constrained();
            $table->unsignedBigInteger('agreement_id')->nullable()->constrained();
            $table->text('ged_doc_id')->nullable();
            $table->string('ged_doc_state')->nullable();
            $table->unsignedBigInteger('task_id')->nullable()->constrained();

            $table->foreign('contract_id')->references('id')->on('en_contracts');
            $table->foreign('agreement_id')->references('id')->on('af_agreements');
            $table->foreign('task_id')->references('id')->on('tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ged_signatures', function (Blueprint $table) {
            //
        });
    }
}
