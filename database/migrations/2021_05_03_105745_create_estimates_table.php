<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dev_estimates', function (Blueprint $table) {
            $table->id();
            $table->string('estimate_number');
            $table->date('estimate_date');
            $table->date('valid_until');
            $table->text('note')->nullable();
            $table->timestamp('last_email_sent_date')->nullable();
            $table->string('state')->nullable();//etat
            $table->string('status')->nullable();//'draft', 'sent', 'accepted', 'declined'
            $table->double('tax_percentage', 8, 2)->nullable();
            $table->enum('discount_type', ['before_tax', 'after_tax'])->default('before_tax');
            $table->double('discount_amount', 8, 2)->nullable();
            $table->enum('discount_amount_type', ['percentage', 'fixed_amount'])->nullable();
            $table->unsignedBigInteger('entitie_id');
            $table->foreign('entitie_id')->references('id')->on('en_entities');
            $table->unsignedBigInteger('contact_id');
            $table->foreign('contact_id')->references('id')->on('en_contacts'); 
            $table->unsignedBigInteger('af_id');
            $table->foreign('af_id')->references('id')->on('af_actions'); 
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
        Schema::dropIfExists('dev_estimates');
    }
}
