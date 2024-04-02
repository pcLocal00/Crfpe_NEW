<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertIntoParParamsTaskProps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('par_params', function (Blueprint $table) {
            DB::table('par_params')->insert(
                [
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_INGO_GENERALE',
                        'name' => 'Demande d’information générale',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_INFO_FORMATION',
                        'name' => 'Demande d’information sur une formation',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_INFO_APPRENTISSAGE',
                        'name' => 'Demande d’information sur l’apprentissage',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_ENVOI_DEVIS',
                        'name' => 'Demande d’envoi d’un devis',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_ENVOI_CONVENTION',
                        'name' => 'Demande d’envoi d’une convention/contrat de formation - client',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_ENVOI_FACTURE',
                        'name' => 'Demande d’envoi d’une facture',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_PLACE_FORMATION',
                        'name' => 'Demande de place sur une formation inter',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_RAPPEL_SALARIE',
                        'name' => 'Demande de rappel d’un salarié CRFPE',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_INFO_EVENEMENT',
                        'name' => 'Demande d’information sur un évènement CRFPE',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'DDE_INFO_CONTRAT_INTERV',
                        'name' => 'Demande d’information sur un contrat intervenant',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_TYPE',
                        'param_name' => 'Type tâche',
                        'code' => 'TASK_AUTRE',
                        'name' => 'Autre',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_SOURCE',
                        'param_name' => 'Source tâche',
                        'code' => 'APPEL_ENTRANT',
                        'name' => 'Appel entrant',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_SOURCE',
                        'param_name' => 'Source tâche',
                        'code' => 'VENUE_CRFPE',
                        'name' => 'Venue au CRFPE',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_SOURCE',
                        'param_name' => 'Source tâche',
                        'code' => 'EMAIL',
                        'name' => 'Email',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_SOURCE',
                        'param_name' => 'Source tâche',
                        'code' => 'APPEL_SORTANT',
                        'name' => 'Appel sortant',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_SOURCE',
                        'param_name' => 'Source tâche',
                        'code' => 'RDV_CLI',
                        'name' => 'Rendez-vous clientèle',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_SOURCE',
                        'param_name' => 'Source tâche',
                        'code' => 'SALON',
                        'name' => 'Salon',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_SOURCE',
                        'param_name' => 'Source tâche',
                        'code' => 'EVENEMENTS_CRFPE',
                        'name' => 'Evènements CRFPE',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_RESPONSE_MODE',
                        'param_name' => 'Mode de réponse tâche',
                        'code' => 'RDV',
                        'name' => 'RDV',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                    [
                        'param_code' => 'TASK_RESPONSE_MODE',
                        'param_name' => 'Mode de réponse tâche',
                        'code' => 'COURRIER',
                        'name' => 'Courrier',
                        'css_class' => 'info',
                        'order_show' => '0',
                    ],
                ]
            );
        });
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
