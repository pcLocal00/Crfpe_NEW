<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertIntoParParamsImportSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('par_params')->insert(
            [
                [
                    'param_code' => 'TASK_SOURCE',
                    'param_name' => 'Source tâche',
                    'code' => 'IMPORT_PROSPECT',
                    'name' => 'Prospect',
                    'css_class' => 'info',
                    'order_show' => '2',
                ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('par_params', function (Blueprint $table) {
            //
        });
    }
}
