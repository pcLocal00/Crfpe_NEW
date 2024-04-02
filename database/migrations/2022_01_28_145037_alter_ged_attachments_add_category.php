<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGedAttachmentsAddCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ged_attachments', function (Blueprint $table) {
            $table->enum('category', ['AUTRE', 'PARCOURSUP'])->default('AUTRE')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ged_attachments', function (Blueprint $table) {
            //
        });
    }
}
