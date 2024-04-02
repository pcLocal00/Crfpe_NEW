<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgreementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('af_agreements', function (Blueprint $table) {
            $table->id();
            $table->enum('agreement_type', ['convention', 'contract']);
            $table->string('number');
            $table->timestamp('last_email_sent_date')->nullable();
            $table->enum('status', ['draft', 'sent','signed','canceled'])->default('draft');
            $table->double('tax_percentage', 8, 2)->nullable();
            $table->enum('discount_type', ['before_tax', 'after_tax'])->default('before_tax');
            $table->double('discount_amount', 8, 2)->nullable();
            $table->enum('discount_amount_type', ['percentage', 'fixed_amount'])->nullable();
            $table->unsignedBigInteger('entitie_id');
            $table->foreign('entitie_id')->references('id')->on('en_entities');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('en_contacts');
            $table->unsignedBigInteger('af_id');
            $table->foreign('af_id')->references('id')->on('af_actions');
            $table->unsignedBigInteger('estimate_id')->nullable();
            $table->foreign('estimate_id')->references('id')->on('dev_estimates');
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
        Schema::dropIfExists('af_agreements');
    }
}
