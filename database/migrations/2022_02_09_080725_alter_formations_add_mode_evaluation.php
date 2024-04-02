<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFormationsAddModeEvaluation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pf_formations', function (Blueprint $table) {
            $table->enum('evaluation_mode', ['PRESENTIEL', 'EP_ECRIT', 'EP_ORAL', 'EXPOSE', 'DOSSIER', 'FORMATIF'])
            ->after('is_evaluation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pf_formations', function (Blueprint $table) {
            //
        });
    }
}
