<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Contact;
use App\Models\Entitie;
use App\Models\Invoice;
use App\Models\Session;
use App\Models\Estimate;
use App\Models\Agreement;
use App\Models\Formation;
use Illuminate\Http\Request;
use App\Library\Services\DbHelperTools;
use App\Models\Param;
use App\Models\Task;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function getStatisticsPf($pf_id)
    {
        $nb_versions=0;
        if ($pf_id>0) {
            $DbHelperTools=new DbHelperTools();
            $arrayStats = $DbHelperTools->getStatisticsPf($pf_id);
            $nb_versions=$arrayStats['nb_versions'];
        }
        return response()->json([
            'nb_versions' => $nb_versions,
        ]);
    }
    public function getStatisticsAf($af_id)
    {
        $nb_sessions=$nb_enrollments_stagiaires=$nb_enrollments_intervenants=$nb_devis=$nb_conventions=$nb_contrats=$nb_stage_periods=0;
        if ($af_id>0) {
            $DbHelperTools=new DbHelperTools();
            $arrayStats = $DbHelperTools->getStatisticsAf($af_id);
            $nb_sessions=$arrayStats['nb_sessions'];
            $nb_enrollments_stagiaires=$arrayStats['nb_enrollments_stagiaires'];
            $nb_enrollments_intervenants=$arrayStats['nb_enrollments_intervenants'];
            $nb_devis=$arrayStats['nb_devis'];
            $nb_conventions=$arrayStats['nb_conventions'];
            $nb_contrats=$arrayStats['nb_contrats'];
            $nb_stage_periods=$arrayStats['nb_stage_periods'];
        }
        return response()->json([
            'nb_sessions' => $nb_sessions,
            'nb_enrollments_stagiaires' => $nb_enrollments_stagiaires,
            'nb_enrollments_intervenants' => $nb_enrollments_intervenants,
            'nb_devis' => $nb_devis,
            'nb_conventions' => $nb_conventions,
            'nb_contrats' => $nb_contrats,
            'nb_stage_periods' => $nb_stage_periods,
        ]);
    }
    public function getStatisticsDashboardWidgets()
    {
        $contact_id = auth()->user()->contact_id;

        $nb_pf=$nb_clients=$nb_contacts=$nb_afs=$nb_sessions=$nb_devis=$nb_agreements=$nb_invoices=0;
        $nb_pf=Formation::select('id')->count();
        $nb_afs=Action::select('id')->count();
        $nb_clients=Entitie::select('id')->count();
        $nb_contacts=Contact::select('id')->count();
        $nb_sessions=Session::select('id')->count();
        $nb_devis=Estimate::select('id')->count();
        $nb_agreements=Agreement::select('id')->count();
        $nb_invoices=Invoice::select('id')->count();

        $tastk_states = Param::where('param_name', 'Etat')->where('code', ['Créé'])->pluck('id');
        $nb_tasks=Task::where('etat_id', $tastk_states)->count();

        $tastk_progress = Param::where('param_name', 'Etat')
                        ->whereIn('code', ['Créé','En cours','En attente'])
                        ->pluck('id');

        $tastk_unread = Param::where('param_name', 'Etat')
                    ->whereIn('code', ['Créé','En cours','En attente'])
                    ->pluck('id');

        $nb_task_in_progress=Task::whereIn('etat_id', $tastk_progress)
                            ->where('responsable_id',$contact_id)
                            ->count();

        $nb_task_unread=Task::whereIn('etat_id', $tastk_unread)
                        ->where('responsable_id',$contact_id)
                        ->count();
                        
        $nb_task_callback_depassed = Task::whereIn('etat_id', $tastk_progress)
                       ->where('responsable_id', $contact_id)
                       ->where('callback_date', '<', date('Y-m-d H:i:s'))
                       ->count();

        $nb_task_ended_depassed = Task::whereIn('etat_id', $tastk_progress)
                       ->where('responsable_id', $contact_id)
                       ->where('ended_date', '<', date('Y-m-d H:i:s'))
                       ->count();


        return response()->json([
            'nb_pf' => $nb_pf,
            'nb_clients' => $nb_clients,
            'nb_contacts' => $nb_contacts,
            'nb_afs' => $nb_afs,
            'nb_sessions' => $nb_sessions,
            'nb_devis' => $nb_devis,
            'nb_agreements' => $nb_agreements,
            'nb_invoices' => $nb_invoices,
            'nb_tasks' => $nb_tasks,
            'nb_task_in_progress' => $nb_task_in_progress,
            'nb_task_unread' => $nb_task_unread,
            'nb_task_depassed' => $nb_task_callback_depassed,
            'nb_task_ended' => $nb_task_ended_depassed,
        ]);
    }
}
