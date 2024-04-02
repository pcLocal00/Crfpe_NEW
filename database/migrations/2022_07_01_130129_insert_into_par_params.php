<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertIntoParParams extends Migration
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
                    'code' => 'COMMITTEE',
                    'name' => 'Comité',
                    'css_class' => 'info',
                    'order_show' => '1',
                ],
                [
                    'param_code' => 'TASK_TYPE',
                    'param_name' => 'Type tâche',
                    'code' => 'STD_SUSPEND',
                    'name' => 'Suspension étudiant',
                    'css_class' => 'info',
                    'order_show' => '2',
                ],
                [
                    'param_code' => 'TASK_TYPE',
                    'param_name' => 'Type tâche',
                    'code' => 'STD_STOP',
                    'name' => 'Exclusion étudiant',
                    'css_class' => 'info',
                    'order_show' => '3',
                ],
                [
                    'param_code' => 'TASK_TYPE',
                    'param_name' => 'Type tâche',
                    'code' => 'STD_RESUMPTION',
                    'name' => 'Reprise de suspension',
                    'css_class' => 'info',
                    'order_show' => '4',
                ],
                [
                    'param_code' => 'TASK_TYPE',
                    'param_name' => 'Type tâche',
                    'code' => 'STD_CANCEL',
                    'name' => 'Abandance étudiant',
                    'css_class' => 'info',
                    'order_show' => '5',
                ],
                [
                    'param_code' => 'TASK_CALLBACK_MODE',
                    'param_name' => 'Mode de rappel tâche',
                    'code' => 'CALLBACK_SOLARIS',
                    'name' => 'Solaris',
                    'css_class' => 'info',
                    'order_show' => '6',
                ],
                [
                    'param_code' => 'TASK_RESPONSE_MODE',
                    'param_name' => 'Mode de réponse tâche',
                    'code' => 'RESPONSE_SOLARIS',
                    'name' => 'Solaris',
                    'css_class' => 'info',
                    'order_show' => '7',
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
