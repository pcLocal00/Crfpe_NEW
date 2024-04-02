<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('en_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->decimal('price',8,2)->default(0);
            $table->string('accounting_code')->nullable();
            $table->string('state')->nullable();//etat
            $table->string('status')->nullable();//status
            $table->timestamp('signed_at')->nullable();//signé le
            $table->unsignedBigInteger('contact_id');
            $table->foreign('contact_id')->references('id')->on('en_contacts'); 
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
        Schema::dropIfExists('en_contracts');
    }
}
