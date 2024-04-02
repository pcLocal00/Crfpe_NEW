<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSessionsAddModeEvaluation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('af_sessions', function (Blueprint $table) {
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
        Schema::table('af_sessions', function (Blueprint $table) {
            //
        });
    }
}
