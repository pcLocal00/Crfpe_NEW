<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertIntoParParamsImportTypes extends Migration
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
                    'param_code' => 'TASK_TYPE',
                    'param_name' => 'Type tâche',
                    'code' => 'REPORT_REQUEST',
                    'name' => 'Demande de rapport',
                    'css_class' => 'info',
                    'order_show' => '6',
                ],
                [
                    'param_code' => 'TASK_TYPE',
                    'param_name' => 'Type tâche',
                    'code' => 'INFO_REQUEST',
                    'name' => 'Demande d\'info',
                    'css_class' => 'info',
                    'order_show' => '7',
                ],
                [
                    'param_code' => 'TASK_SOURCE',
                    'param_name' => 'Source tâche',
                    'code' => 'REPORT',
                    'name' => 'Rapport',
                    'css_class' => 'info',
                    'order_show' => '1',
                ],
                [
                    'param_code' => 'TASK_RESPONSE_MODE',
                    'param_name' => 'Mode de réponse tâche',
                    'code' => 'RESPONSE_EMAIL',
                    'name' => 'Email',
                    'css_class' => 'info',
                    'order_show' => '8',
                ],
                [
                    'param_code' => 'TASK_RESPONSE_MODE',
                    'param_name' => 'Mode de réponse tâche',
                    'code' => 'RESPONSE_PHONE',
                    'name' => 'Téléphone',
                    'css_class' => 'info',
                    'order_show' => '9',
                ],
                [
                    'param_code' => 'TASK_CALLBACK_MODE',
                    'param_name' => 'Mode de rappel tâche',
                    'code' => 'CALLBACK_EMAIL',
                    'name' => 'Email',
                    'css_class' => 'info',
                    'order_show' => '7',
                ],
                [
                    'param_code' => 'TASK_CALLBACK_MODE',
                    'param_name' => 'Mode de rappel tâche',
                    'code' => 'CALLBACK_PHONE',
                    'name' => 'Téléphone',
                    'css_class' => 'info',
                    'order_show' => '8',
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
