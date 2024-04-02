<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterGedAttachmentsUpdateCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE ged_attachments MODIFY category enum('AUTRE', 'PARCOURSUP', 'PROSPECTS') NOT NULL DEFAULT 'AUTRE';");
        // Schema::table('ged_attachments', function (Blueprint $table) {
        //     $table->enum('category', ['AUTRE', 'PARCOURSUP', 'PROSPECTS'])->default('AUTRE')->change();
        // });
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
