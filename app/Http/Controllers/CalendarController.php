<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Action;
use App\Models\Prepla_schedules;
use App\Models\Formation;
use App\Models\Sessiondate;

use Illuminate\Http\Request;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use App\Models\AfSchedulegroup;
use App\Models\Contact;
use App\Models\Enrollment;
use App\Models\Entitie;
use App\Models\Group;
use App\Models\Groupment;
use App\Models\Groupmentgroup;
use App\Models\Member;
use App\Models\Prepla_scheduledate_groups;
use App\Models\Prepla_scheduledate_intervenants;
use App\Models\Preplanning;
use App\Models\Price;
use App\Models\Schedule;
use App\Models\Schedulecontact;
use App\Models\Session;
use App\Models\Timestructure;
use App\Models\Timestructurecategory;
use DateInterval;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CalendarController extends Controller
{
    public function formPreplanifications($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Preplanning::findOrFail($row_id);
        } else {
            $row = new Preplanning(); // create a new Preplanning object with default values
        }
        return view('pages.af.planifications.form.formPreplanifications', compact('row', 'row_id'));
    }
    //selection Produit formation
    public function selectFormation()
    {
        $result = [];
        $rows = Formation::select('id', 'title')->where('autorize_af', 1)->get();
        if (count($rows) > 0) {
            foreach ($rows as $pf) {
                $result[] = ['id' => $pf['id'], 'name' => Str::limit($pf['title'], 80)];
            }
        }
        return response()->json($result);
    }
    // add new Preplanifications
    public function storeFormPreplanifications(Request $request, $produitFormation)
    {
        $success = false;
        $msg = 'Oops !';
        if ($produitFormation > 0) {
            $Preplanning = Preplanning::find($produitFormation);
            $dateabsc = Carbon::createFromFormat('d/m/Y', $request->start_date)->format('d-m-Y');
            $Preplanning->update([
                'title' => $request->title,
                'Start_date' => $dateabsc,
                'PF_id' => $request->pf_id_title,
            ]);
            if ($Preplanning) {
                $success = true;
                $msg = 'Données mises à jour avec succès';
            }
        } else {
            if ($request->isMethod('post')) {
                //dd($request->all());
                $request->validate([
                    'pf_id_title' => 'required',
                    'start_date' => 'required',
                    [
                        'pf_id_title.required' => 'Veuillez sélectionner Le Produit de référence',
                        'start_date.required' => 'Veuillez sélectionner la date du début',
                    ]
                ]);

                $data = $request->all();
                // $title = Formation::where('id',$request->pf_id_title)->first('title');
                // $data['title']= $title->title;
                $data['title'] = $request->title;
                $data['state'] = 'Créé';
                $data['Nb_Sessions'] = null;
                $dateabsc = Carbon::createFromFormat('d/m/Y', $request->start_date)->format('d-m-Y');
                //dd($dateabsc);
                $data['Start_date'] = $dateabsc;

                $data['PF_id'] = $request->pf_id_title;
                $data['AF_target_id'] = null;
                $status = Preplanning::create($data);
                if ($status) {
                    Prepla_schedules::create([
                        'Pp_id' => $status->id,
                        'Pf_session' => $status->PF_id,
                    ]);
                    $success = true;
                    $msg = 'Le données ont étés enregistrées avec succès';
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    // produit Formation list dt_produit_formation
    public function produitFormation()
    {
        $page_title = 'Liste des produit de formation';
        $page_description = '';
        return view('pages.af.planifications.list', compact('page_title', 'page_description'));
    }
    // produit Formation update
    public function updatePlanification(Request $request, $id)
    {
        $success = false;
        $Preplanning = Preplanning::find($id);
        if ($Preplanning) {
            $dateStart = Carbon::createFromFormat('d/m/Y', $request->start_date)->format('d-m-Y');
            $Preplanning->update([
                'Start_date' => $dateStart,
                'PF_id' => $request->pf_id_title,
            ]);
            $success = true;
        }
        return response()->json(['success' => $success]);
    }
    public function sdtPlanifications(Request $request)
    {
        //dd($af_id);
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];

        //$datas = Action::select('title','state','formation_id','started_at','nb_days','code')->where('id', $af_id)->first();
        $datas = Preplanning::latest();

        $recordsTotal = count($datas->get());
        if ($request->length > 0) {
            $start = (int) $request->start;
            $length = (int) $request->length;
            $datas->skip($start)->take($length);
        }
        $udatas = $datas->orderByDesc('id')->get();

        foreach ($udatas as $d) {
            $row = array();

            // $row[] = $d->id;
            $row[] = $d->title;
            $row[] = $d->state;

            $row[] = '<span>Titre : </span><span style="color:#8950FC;"> ' . $d->formation->title . '</span> <br>
                  <span class="font-size-xs">Code : </span><span class="font-size-xs" style="color:#0073e9;">' . $d->formation->code . '</span>';
            $row[] = $d->Start_date->format('d-m-Y');
            if ($d->Nb_Sessions == null) {
                $row[] = '<span class="fs-6" style="color:red;">Pas de Séances</span>';
            } else {
                $row[] = '<span  class="fs-6" style="color:#0073e9;">Total : </span><span class="fs-4" style="color:black;"> ' . $d->Nb_Sessions . ' </span>';
            }
            $row[] = ($d->AF_target_id == null) ? '<span class="fs-6" style="color:red;">Pas encore défini</span>' : $d->action->title;

            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formPreplanification(' . $d->id . ')" title="Édition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_view = '<button class="btn btn-sm btn-clean btn-icon"  onclick="_viewPlanification(' . $d->id . ')" title="Voir le calendrier"><i class="' . $tools->getIconeByAction('EYE') . '"></i></button>';
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deletePlanification(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';

            //ACTION
            $row[] = $btn_view . $btn_edit . $btn_delete;

            $data[] = $row;
        }
        $sort = !empty($dtRequests['sort']['sort']) ? $dtRequests['sort']['sort'] : 'asc';
        $field = !empty($dtRequests['sort']['field']) ? $dtRequests['sort']['field'] : 'ID';
        $page = !empty($dtRequests['pagination']['page']) ? (int)$dtRequests['pagination']['page'] : 1;
        $perpage = !empty($dtRequests['pagination']['perpage']) ? (int)$dtRequests['pagination']['perpage'] : -1;
        $pages = 1;
        $total = count($data); // total items in array
        $meta = [
            'page' => $page,
            'pages' => $pages,
            'perpage' => $perpage,
            'total' => $total,
            'sort' => $sort,
            'field' => $field,
        ];
        $result = [
            'meta' => $meta,
            'data' => $data,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
        ];
        return response()->json($result);
    }
    // update af cible Preplanification in calendar
    public function updatePreplanification(Request $request, $planification_id)
    {

        $success = false;
        //dd($planification_id);
        if ($planification_id) {
            $Preplanning = Preplanning::find($planification_id);
            //dd($Preplanning);
            $Preplanning->update([
                'AF_target_id' => $request->afcibleid,
            ]);
            $success = true;
        }
        return response()->json(['success' => $success]);
    }
    // suppression
    public function deletePlanification($planification_id)
    {
        $success = false;
        if ($planification_id) {
            $Preplanning = Preplanning::find($planification_id);

            // Check if there are any related rows in the Prepla_schedules table
            $Prepla_schedule = Prepla_schedules::where('Pp_id', $Preplanning->id)->get();
            if ($Prepla_schedule) {
                // Delete any related rows in the Prepla_schedules table
                $Prepla_schedule->each(function ($schedule) {
                    $schedule->delete();
                });
            }

            // Delete the Preplanning row
            $Preplanning->delete();
            $success = true;
        }
        return response()->json(['success' => $success]);
    }
    // view planification calendar
    public function index($planification_id)
    {
        $preplanning = Preplanning::find($planification_id);
        //$events = array();
        $Prepla_schedules = Prepla_schedules::where('Pp_id', $planification_id)
            ->orderBy('updated_at', 'asc')
            ->get()
            ->all();

        //$Prepla_schedules = Prepla_schedules::all();
        //dd($Prepla_schedules);
        if ($Prepla_schedules) {
            // $sequenceCounter = 0;    
            foreach ($Prepla_schedules as $schedule) {
                $editable = true;
                if ($schedule->color == '#2986CC') {
                    $editable = false;
                }
                $schedules[] = [
                    'Pp_id' => $schedule->Pp_id,
                    'id'   => $schedule->id,
                    'Pf_session' => $schedule->Pf_session,
                    'end_hour' => $schedule->end_hour,
                    'start_hour' => $schedule->start_hour,
                    'end' => $schedule->end_hour,
                    'start' => $schedule->start_hour,
                    'title' => $schedule->title,
                    'color' => $schedule->color,
                    'remarks' => $schedule->remarks,
                    'sequence_number' => $schedule->sequence_number,
                    'sequence_total' => $schedule->sequence_total,
                    'editable' => $editable,
                ];
            }
        } else {
            $schedules[] = [];
        }
        $page_title = "Titre : " . $preplanning->title;
        return view('pages.af.planifications.calendar', compact('page_title', 'schedules', 'preplanning'));
    }
    //selection AF ciblé
    public function AFcible()
    {
        $result = [];
        $rows = Action::select('id', 'title')->get();
        if (count($rows) > 0) {
            foreach ($rows as $pf) {
                $result[] = ['id' => $pf['id'], 'name' => Str::limit($pf['title'], 80)];
            }
        }
        return response()->json($result);
    }
    // load Modal
    public function loadModal($pp_schedule_id)
    {
        $group_id_db = Prepla_scheduledate_groups::select('Groupe', 'Regroupement')->where(['Pp_schedule_id' => $pp_schedule_id])->first();
        $interv_id_db = Prepla_scheduledate_intervenants::select('Contact_id')->where(['Pp_schedule_id' => $pp_schedule_id])->first();

        $data = [];
        if (!empty($group_id_db->Groupe)) {
            $data = ['group_id' => $group_id_db->Groupe];
        } else {
            $data = ['group_id' => ''];
        }
        if (!empty($group_id_db->Regroupement)) {
            $data += ['Regroupement_id' => $group_id_db->Regroupement];
        } else {
            $data += ['Regroupement_id' => ''];
        }
        if (!empty($interv_id_db->Contact_id)) {
            $data += ['interv_id' => $interv_id_db->Contact_id];
        } else {
            $data += ['interv_id' => ''];
        }
        //dd($data);
        return response()->json($data);
    }
    public function GroupeSelect($af_id)
    {
        $result = [];
        $rows = Group::select('id', 'title')->where(['af_id' => $af_id])->get();
        if (count($rows) > 0) {
            foreach ($rows as $pf) {
                $result[] = ['id' => $pf['id'], 'name' => Str::limit($pf['title'], 80)];
            }
        }
        return response()->json($result);
    }

    public function updateGroupeSelect(Request $request, $pp_schedule_id)
    {
        $scheduledate_groups = Prepla_scheduledate_groups::select('id')->where(['Pp_schedule_id' => $pp_schedule_id])->first();
        if (!empty($scheduledate_groups->id)) {
            $scheduledate_groups->update([
                'Groupe' => $request->groupid,
            ]);
            return response()->json('Group mise à jour');
        } else {
            Prepla_scheduledate_groups::create([
                'Pp_schedule_id' => $pp_schedule_id,
                'Groupe' => $request->groupid,
            ]);
            return response()->json('Group Ajouté avec success');
        }
    }




    //Regroupement
    public function RegroupementSelect($af_id)
    {
        $result = [];
        $rows = Groupment::select('id', 'name')->where(['af_id' => $af_id])->get();
        if (count($rows) > 0) {
            foreach ($rows as $pf) {
                $result[] = ['id' => $pf['id'], 'name' => Str::limit($pf['name'], 80)];
            }
        }
        return response()->json($result);
    }
    public function updateRegroupementSelect(Request $request, $pp_schedule_id)
    {
        $scheduledate_groups = Prepla_scheduledate_groups::select('id')->where(['Pp_schedule_id' => $pp_schedule_id])->first();
        if (!empty($scheduledate_groups->id)) {
            $scheduledate_groups->update([
                'Regroupement' => $request->Regroupementid,
            ]);
            return response()->json('Group mise à jour');
        } else {
            Prepla_scheduledate_groups::create([
                'Pp_schedule_id' => $pp_schedule_id,
                'Regroupement' => $request->Regroupementid,
            ]);
            return response()->json('Group Ajouté avec success');
        }
    }
    //End Regroupement
    public function updateIntervenant(Request $request, $pp_schedule_id)
    {
        // search if intervenant exist in other plannings
        $intervenant = Prepla_scheduledate_intervenants::where(['Contact_id' => $request->intervid])->get();
        if ($intervenant) {
            $from = $request->start;
            $to = $request->end;

            $datas = collect(); // Create a new collection to store the results

            foreach ($intervenant as $intv) {
                $scheduleId = $intv->Pp_schedule_id;

                $results = Prepla_schedules::where('id', '<>', $pp_schedule_id)
                    ->where(function ($query) use ($from, $to) {
                        $query->whereBetween('start_hour', [$from, $to])
                            ->orWhereBetween('end_hour', [$from, $to])
                            ->orWhere(function ($query2) use ($from, $to) {
                                $query2->where('start_hour', '<', $from)
                                    ->where('end_hour', '>', $to);
                            });
                    })
                    ->whereHas('intervenants', function ($query) use ($request) {
                        $query->where('Contact_id', $request->intervid);
                    })
                    ->get();

                foreach ($results as $result) {
                    $planning = Preplanning::where('id', $result->Pp_id)->first();
                    $uniqueKey = $result->id . '-' . $planning->id;

                    $datas->put($uniqueKey, [
                        'title' => $result->title,
                        'start_hour' => $result->start_hour,
                        'end_hour' => $result->end_hour,
                        'planning' => $planning
                    ]);
                }
            }

            if ($datas->count() > 0) {
                return response()->json(['datas' => $datas]);
            }
        }
        // after the search 
        $scheduledate_interv = Prepla_scheduledate_intervenants::where(['Contact_id' => $request->intervid, 'Pp_schedule_id' => $pp_schedule_id])->first();
        $contact = Contact::find($request->intervid);

        if (!$contact) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        $interv_type = $contact->type_former_intervention ?? 'Interne';
        $price = ($interv_type === 'Interne') ? 0 : null;

        if (!$scheduledate_interv) {
            $scheduledate_interv = new Prepla_scheduledate_intervenants([
                'Pp_schedule_id' => $pp_schedule_id,
                'Contact_id' => $request->intervid,
                'type' => $interv_type,
                'price' => $price,
            ]);

            $scheduledate_interv->save();
        } else {
            $scheduledate_interv->update([
                'type' => $interv_type,
                'price' => $price,
            ]);
        }
        return response()->json($scheduledate_interv);
    }
    public function SelectListIntervenant()
    {
        $result = [];
        $rows = Contact::select('id', 'firstname', 'lastname')->where('is_former', 1)->get();
        // dd($rows);
        if (count($rows) > 0) {
            foreach ($rows as $pf) {
                $result[] = ['id' => $pf['id'], 'fullname' => $pf['firstname'] . " " . $pf['lastname']];
            }
        }
        return response()->json($result);
    }
    public function SelectListIntervenantPrice()
    {
        $result = [];
        $rows = Price::select('id', 'price')->where('is_former_price', 1)->orderBy('price')->get();
        if (count($rows) > 0) {
            foreach ($rows as $pf) {
                $result[] = ['id' => $pf['id'], 'price' => $pf['price']];
            }
        }
        return response()->json($result);
    }
    public function ShowListIntervenant($pp_schedule_id)
    {
        $result = [];
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        if ($pp_schedule_id > 0) {
            $rows = Prepla_scheduledate_intervenants::where('pp_schedule_id', $pp_schedule_id)->get();
            //dd($rows);
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $interv_name = Contact::where('id', $row->Contact_id)->first();
                    //dd($interv_name);
                    $fullname =  $interv_name->firstname . " " . $interv_name->lastname;
                    $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteIntervenant(' . $row->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';

                    if ($row->type == 'Sur facture') {
                        $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_reloadInputPrice(' . $row->id . ')" title="Édition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                        $btn_save = '<button class="btn btn-sm btn-clean btn-icon" onclick="_editIntervenantFacture(' . $row->id . ')" title="Sauvegarder"><i class="' . $tools->getIconeByAction('SAVE') . '"></i></button>';
                        $action = $btn_edit . $btn_save . $btn_delete;
                    }
                    if ($row->type == 'Sur contrat') {
                        $btn_save = '<button class="btn btn-sm btn-clean btn-icon" onclick="_editIntervenantContrat(' . $row->id . ')" title="Sauvegarder"><i class="' . $tools->getIconeByAction('SAVE') . '"></i></button>';
                        $action = $btn_save . $btn_delete;
                    }
                    if ($row->type != 'Sur contrat' && $row->type != 'Sur facture') {
                        $action = $btn_delete;
                    }
                    $result[] = ['id' => $row['id'], 'Contact_id' => $row['Contact_id'], 'fullname' => $fullname, 'price' => $row['price'], 'type' => $row['type'], 'action' => $action];
                }
                return response()->json($result);
            }
        }
        if (!request()->input('interv_id')) {
            $result[] = '';
            return response()->json($result);
        }
    }
    public function deleteIntervenant($id)
    {
        // $Prepla_schedule = Prepla_schedules::find($id);
        $scheduledate_intervenants = Prepla_scheduledate_intervenants::where(['id' => $id])->first();
        if (!empty($scheduledate_intervenants)) {
            $scheduledate_intervenants->delete();
            return $id;
        }
    }
    public function editIntervenant(Request $request, $id)
    {
        // $Prepla_schedule = Prepla_schedules::find($id);
        $scheduledate_intervenants = Prepla_scheduledate_intervenants::where('id', $id)->first();
        // dd($scheduledate_intervenants);
        if (!empty($scheduledate_intervenants)) {
            $scheduledate_intervenants->update([
                'price' => $request->price,
            ]);
            return $id;
        }
    }
    public function duplidateEvent(Request $request)
    {
        $scheduledate_groups = Prepla_scheduledate_groups::where(['Pp_schedule_id' => $request->pp_schedule_id])->first();
        $scheduledate_intervenants = Prepla_scheduledate_intervenants::where(['Pp_schedule_id' => $request->pp_schedule_id])->get();

        $from = $request->start_hour;
        $to = $request->end_hour;

        $datas = collect(); // Create a new collection to store the results

        // Check if any intervenants have conflicting events
        foreach ($scheduledate_intervenants as $intrv) {
            if (!empty($intrv->Contact_id)) {
                $intervenantId = $intrv->Contact_id;

                // Check if the intervenant has any conflicting events
                $results = Prepla_schedules::where('id', '<>', $request->pp_schedule_id)
                    ->where('start_hour', '<', $to)
                    ->where('end_hour', '>', $from)
                    ->whereHas('intervenants', function ($query) use ($intervenantId) {
                        $query->where('Contact_id', '=', $intervenantId);
                    })
                    ->get();

                foreach ($results as $result) {
                    $planning = Preplanning::where('id', $result->Pp_id)->first();
                    $datas->push([
                        'title' => $result->title,
                        'start_hour' => $result->start_hour,
                        'end_hour' => $result->end_hour,
                        'planning' => $planning
                    ]);
                }

                if ($results->count() > 0) {
                    return response()->json(['datas' => $datas]);
                }
            }
        }

        // No conflicting events found, so create the new event
        $Prepla_schedules = Prepla_schedules::create([
            'id'   => $request->id,
            'Pp_id' => $request->Pp_id,
            'title' => $request->title,
            'date_start' => $request->date_start,
            'start_hour' => $request->start_hour,
            'end_hour' => $request->end_hour,
            'sequence_number' => $request->sequence_number,
            'sequence_total' => $request->sequence_total,
            'color' => $request->color,
            'remarks' => $request->remarks,
            'Pf_session' => $request->Pf_session,
        ]);
        $latestPreplaSchedules = Prepla_schedules::latest()->take(1)->first();
        $preplanning = Preplanning::find($request->Pp_id);
        $preplanning->Nb_Sessions++;
        $preplanning->save();

        if (!empty($scheduledate_groups->Groupe) || !empty($scheduledate_groups->Regroupement)) {
            Prepla_scheduledate_groups::create([
                'Pp_schedule_id' => $latestPreplaSchedules->id,
                'Groupe' => $scheduledate_groups->Groupe,
                'Regroupement' => $scheduledate_groups->Regroupement,
            ]);
        }



        // Add the intervenants for the new event
        foreach ($scheduledate_intervenants as $intrv) {
            if (!empty($intrv->Contact_id)) {
                Prepla_scheduledate_intervenants::create([
                    'Pp_schedule_id' => $latestPreplaSchedules->id,
                    'Contact_id' => $intrv->Contact_id,
                    'price' => $intrv->price,
                    'type' => $intrv->type
                ]);
            }
        }
        return response()->json([
            'id' => $Prepla_schedules->id,
            'Pp_id' => $Prepla_schedules->Pp_id,
            'title' => $Prepla_schedules->title,
            'date_start' => $Prepla_schedules->date_start,
            'start_hour' => $Prepla_schedules->start_hour,
            'end_hour' => $Prepla_schedules->end_hour,
            'sequence_number' => $Prepla_schedules->sequence_number,
            'sequence_total' => $Prepla_schedules->sequence_total,
            'color' => $Prepla_schedules->color,
            'remarks' => $Prepla_schedules->remarks,
            'Pf_session' => $Prepla_schedules->Pf_session,
        ]);
    }
    public function store(Request $request)
    {
        // $request->validate([
        //     'title' => 'required|string'
        // ]);

        $Prepla_schedules = Prepla_schedules::create([
            'id'   => $request->id,
            'Pp_id' => $request->Pp_id,
            'title' => $request->title,
            'date_start' => $request->date_start,
            'start_hour' => $request->start_hour,
            'end_hour' => $request->end_hour,
            'sequence_number' => $request->sequence_number,
            'sequence_total' => $request->sequence_total,
            'color' => $request->color,
            'remarks' => $request->remarks,
            'Pf_session' => $request->Pf_session,
        ]);
        if ($Prepla_schedules) {
            // incrementer le Nb_Sessions
            $Preplanning = Preplanning::find($request->Pp_id);
            $Preplanning->Nb_Sessions++;
            $Preplanning->save();

            return response()->json([
                'id' => $Prepla_schedules->id,
                'Pp_id' => $Prepla_schedules->Pp_id,
                'title' => $Prepla_schedules->title,
                'date_start' => $Prepla_schedules->date_start,
                'start_hour' => $Prepla_schedules->start_hour,
                'end_hour' => $Prepla_schedules->end_hour,
                'sequence_number' => $Prepla_schedules->sequence_number,
                'sequence_total' => $Prepla_schedules->sequence_total,
                'color' => $Prepla_schedules->color,
                'remarks' => $Prepla_schedules->remarks,
                'Pf_session' => $Prepla_schedules->Pf_session,
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        $Prepla_schedule = Prepla_schedules::find($id);
        if (!$Prepla_schedule) {
            return response()->json([
                'error' => 'Impossible de localiser les horaires de préplanification'
            ], 404);
        }

        // $millisecondsToAdd = 30;
        // $startTime= strtotime($request->heure_debut);
        // $newStartTime = $startTime + ($millisecondsToAdd / 1000);
        // $from = date('Y-m-d H:i:s', $newStartTime);

        $carbonDate_start = Carbon::createFromFormat('Y-m-d H:i', $request->heure_debut);
        $formattedDate_start = $carbonDate_start->format('Y-m-d H:i:s');

        $carbonDate_end = Carbon::createFromFormat('Y-m-d H:i', $request->heure_fin);
        $formattedDate_end = $carbonDate_end->format('Y-m-d H:i:s');

        // dd($formattedDate_start,$formattedDate_end);

        $schedules_end_hour = Prepla_schedules::where('end_hour',$formattedDate_start)->count();

        if($schedules_end_hour > 0)
        {
            $carbonDateTime = Carbon::parse($formattedDate_start);

            $seconds = $carbonDateTime->second;

            $seconds = $seconds + 1;

            $inputDatetime_start = Carbon::parse($formattedDate_start);
            $inputDatetime_start->addSeconds($seconds);

            $formattedDate_start = $inputDatetime_start->format('Y-m-d H:i:s');

            $inputDatetime_end = Carbon::parse($formattedDate_end);
            $inputDatetime_end->addSeconds($seconds);

            $formattedDate_end = $inputDatetime_end->format('Y-m-d H:i:s');
        }


        // $from = $request->start_hour;
        // $to = $request->end_hour;

        $scheduledate_intervenants = Prepla_scheduledate_intervenants::where(['Pp_schedule_id' => $Prepla_schedule->id])->get();
        $datas = collect(); // Create a new collection to store the results

        // Check if any intervenants have conflicting events
        foreach ($scheduledate_intervenants as $intrv) {
            if (!empty($intrv->Contact_id)) {
                $intervenantId = $intrv->Contact_id;

                // Check if the intervenant has any conflicting events
                $results = Prepla_schedules::where('id', '<>', $Prepla_schedule->id)
                    ->where('start_hour', '<', $formattedDate_start)
                    ->where('end_hour', '>', $formattedDate_end)
                    ->whereHas('intervenants', function ($query) use ($intervenantId) {
                        $query->where('Contact_id', '=', $intervenantId);
                    })
                    ->get();

                foreach ($results as $result) {
                    $planning = Preplanning::where('id', $result->Pp_id)->first();
                    $datas->push([
                        'title' => $result->title,
                        'start_hour' => $result->start_hour,
                        'end_hour' => $result->end_hour,
                        'planning' => $planning
                    ]);
                }

                if ($results->count() > 0) {
                    return response()->json(['datas' => $datas]);
                }
            }
        }
        $Prepla_schedule->update([
            'title' => $request->title,
            'sequence_number' => $request->sequence_number,
            'sequence_total' => $request->sequence_total,
            'date_start' => $request->date_start,
            'start_hour' => $formattedDate_start,
            'end_hour' => $formattedDate_end,
            'remarks' => $request->remarks,
        ]);


        $scheduledate_groups = Prepla_scheduledate_groups::where(['Pp_schedule_id' => $id])->first();
        $scheduledate_intervenants = Prepla_scheduledate_intervenants::where(['Pp_schedule_id' => $id])->get()->all();

        if ($scheduledate_groups) {
            if (!empty($scheduledate_groups->Groupe) || !empty($scheduledate_groups->Regroupement)) {
                $update = false;
                foreach ($scheduledate_intervenants as $intervenant) {
                    if (!empty($intervenant->price) && ($intervenant->type == 'Sur facture' || $intervenant->type == 'Sur contrat')) {
                        $update = true;
                        break;
                    }
                    if (!empty($intervenant->price) && $intervenant->type == 'Interne') {
                        $update = true;
                        break;
                    }
                }
                if ($update) {
                    $Prepla_schedule->update([
                        'color' => "#67A93B",
                    ]);
                } else {
                    $Prepla_schedule->update([
                        'color' => "#FFD966",
                    ]);
                }
            }
        }
        return response()->json('Event mise à jour');
    }



    public function dropUpdate(Request $request, $id)
    {
        $Prepla_schedule = Prepla_schedules::find($id);
        if (!$Prepla_schedule) {
            return response()->json([
                'error' => 'Impossible de localiser les horaires de préplanification'
            ], 404);
        }

        $from = $request->start_hour;
        $to = $request->end_hour;

        $scheduledate_intervenants = Prepla_scheduledate_intervenants::where(['Pp_schedule_id' => $Prepla_schedule->id])->get();
        $datas = collect(); // Create a new collection to store the results

        // Check if any intervenants have conflicting events
        foreach ($scheduledate_intervenants as $intrv) {
            if (!empty($intrv->Contact_id)) {
                $intervenantId = $intrv->Contact_id;

                // Check if the intervenant has any conflicting events
                $results = Prepla_schedules::where('id', '<>', $Prepla_schedule->id)
                    ->where('start_hour', '<', $to)
                    ->where('end_hour', '>', $from)
                    ->whereHas('intervenants', function ($query) use ($intervenantId) {
                        $query->where('Contact_id', '=', $intervenantId);
                    })
                    ->get();

                foreach ($results as $result) {
                    $planning = Preplanning::where('id', $result->Pp_id)->first();
                    $datas->push([
                        'title' => $result->title,
                        'start_hour' => $result->start_hour,
                        'end_hour' => $result->end_hour,
                        'planning' => $planning
                    ]);
                }

                if ($results->count() > 0) {
                    return response()->json(['datas' => $datas]);
                }
            }
        }

        $Prepla_schedule->update([
            'date_start' => $request->date_start,
            'start_hour' => $request->start_hour,
            'end_hour' => $request->end_hour,
        ]);

        return response()->json('Event updated');
    }

    public function destroy($id)
    {
        $Prepla_schedule = Prepla_schedules::find($id);
        //dd($Prepla_schedule->id);
        if (!$Prepla_schedule) {
            return response()->json([
                'error' => 'Impossible de localiser les horaires de préplanification'
            ], 404);
        }
        $scheduledate_groups = Prepla_scheduledate_groups::where(['Pp_schedule_id' => $Prepla_schedule->id])->first();
        $scheduledate_intervenants = Prepla_scheduledate_intervenants::where(['Pp_schedule_id' => $Prepla_schedule->id])->get();
        if (!empty($scheduledate_groups)) {
            $scheduledate_groups->delete();
        }
        foreach ($scheduledate_intervenants as $intrv) {
            if (!empty($intrv)) {
                $intrv->delete();
            }
        }
        $Prepla_schedule->delete();
        // decrement  le Nb_Sessions
        $Preplanning = Preplanning::find($Prepla_schedule->Pp_id);
        $Preplanning->Nb_Sessions--;
        $Preplanning->save();
        return $id;
    }
    // session affichage
    public function getJsonTimeStructure($pf_id)
    {

        $tools = new PublicTools();
        $categories = Timestructurecategory::all();
        $datas = [];
        $i = 0;

        foreach ($categories as $c) {

            $structures = Timestructure::where('category_id', $c->id)->orderBy('sort')->get()->all();
            foreach ($structures as $a) {
                $datas[] = array(
                    "id" => 'C' . $a->id,
                    "text" => $a->name,
                    "state" => array('opened' => true),
                    "icon" => 'fa fa-folder',
                    "parent" => '#'
                );
                break;
            }
            if (count($structures) > 0) {
                foreach ($structures as $t) {

                    $fa = 'folder';
                    $classCss = 'success';
                    $disabled_select = true;
                    if ($t->parent_id > 0) {
                        $fa = 'file';
                        $classCss = 'info';
                        $disabled_select = false;
                    }

                    $icon = "fa fa-" . $fa . " text-" . $classCss;

                    // if ($param == 1 && $t->deleted_at) {
                    //     $disabled_select = true;
                    // }

                    $selected = ($t->id) ? true : false;

                    $datas[] = array(
                        "id" => 'S' . $t->id,
                        "text" => $t->name,
                        "state" => array('opened' => false, 'disabled' => $disabled_select, 'selected' => $selected),
                        "icon" => $icon,
                        "parent" => ($t->parent_id > 0) ? 'S' . $t->parent_id : 'C' . $t->id
                    );

                    //Pfs
                    $pfs = Formation::where('timestructure_id', $t->id)->orderBy('timestructure_sort')->get();
                    $pfs_array = $pfs->toArray();
                    if (count($pfs) > 0) {
                        foreach ($pfs as $pf) {
                            $item_parent = array_filter($pfs_array, function ($p) use ($pf) {
                                return $p['id'] == $pf->parent_id;
                            });
                            $parent_exits = !empty($item_parent);

                            $datas[] = array(
                                "id" => $pf->id,
                                //"text" => $pf->title.(!$parent_exits?$btnEdit:''),
                                "text" => $pf->title,
                                "state" => array('opened' => false),
                                "icon" => 'fa fa-file text-info',
                                "parent" => $parent_exits ? $pf->parent_id : 'S' . $t->id
                            );
                            // break;
                        }
                    }
                }
            }
        }
        //dd($datas);
        return response()->json($datas);
    }

    //transferer Pplanifications 
    public function transfererPplanifications(Request $request)
    {
        $Ppreplanning_id = $request->Ppreplanning_id;
        $start = $request->preplannings_start_date;
        $end = $request->preplannings_end_date;

        $from = Carbon::createFromFormat('d-m-Y', $start)->format('Y-m-d');
        $to = Carbon::createFromFormat('d-m-Y', $end)->format('Y-m-d');

        $datas = Prepla_schedules::where('Pp_id', $Ppreplanning_id)
            ->whereDate('date_start', '>=', $from)
            ->whereDate('date_start', '<=', $to)
            ->where('color', '!=', '#2986CC')
            ->orderBy('date_start', 'asc')
            ->get();
        $preplanning = Preplanning::find($Ppreplanning_id);

        foreach ($datas as $data) {
            $id = $data->id;
            $scheduledate_groups = Prepla_scheduledate_groups::where(['Pp_schedule_id' => $id])->first();

            if (!empty($scheduledate_groups)) {
                $group = null;
                if (!empty($scheduledate_groups->Groupe)) {
                    $group = Group::find($scheduledate_groups->Groupe);
                    if (!empty($group)) {
                        $data->group = $group->title;
                    } else {
                        $data->group = "";
                    }
                } else {
                    $data->group = "";
                }

                $regroupment = null;
                if (!empty($scheduledate_groups->Regroupement)) {
                    $regroupment = Groupment::find($scheduledate_groups->Regroupement);
                    if (!empty($regroupment)) {
                        $data->group .= " / " . $regroupment->name;
                    } else {
                        $data->group .= " / ";
                    }
                } else {
                    $data->group .= " / ";
                }
            } else {
                $data->group = "";
            }

            $scheduledate_intervenants = Prepla_scheduledate_intervenants::where(['Pp_schedule_id' => $id])->orderBy('price', 'asc')->orderBy('type', 'desc')->first();
            //dd($scheduledate_intervenants);
            if (!empty($scheduledate_intervenants->Contact_id)) {
                $interv_name = Contact::find($scheduledate_intervenants->Contact_id);
                $data->formateur =  !empty($interv_name->firstname) && !empty($interv_name->lastname) ? $interv_name->firstname . " " . $interv_name->lastname : "";
                $data->formateur_type = !empty($scheduledate_intervenants->type) ? $scheduledate_intervenants->type : "";
                $data->price = !empty($scheduledate_intervenants->price) ? $scheduledate_intervenants->price : "";
            } else {
                $data->formateur = "";
                $data->formateur_type = "";
                $data->price = "";
            }
        }
        //dd($datas);
        return response()->json($datas);
    }

    // Nouvel fonction de transfert planning fait le 04/08/2023
    public function transferPreplanning(Request $request)
    {
        // dd($request);


        $preplannings = $request->input('preplannings');

        foreach ($preplannings as $preplanning) {
            // dd($preplanning);
            // Define Variables
            $preplanning_whole_preplaning_Id = $preplanning['id'];
            $preplanningId = $preplanning['Pp_id'];
            $date_start = new DateTime($preplanning['date_start']);
            $start_hour = new DateTime($preplanning['start_hour']);
            $end_hour = new DateTime($preplanning['end_hour']);
            $formateur = $preplanning['formateur'];
            $groupe_id = null;
            $regroupe_id = null;

            $interval = $start_hour->diff($end_hour);

            $hours = $interval->h + ($interval->days * 24);
            $minutes = $interval->i;

            $duration = $hours + ($minutes / 60);

            $prepla_schedule_group = Prepla_scheduledate_groups::where('Pp_schedule_id', $preplanning_whole_preplaning_Id)->first();

            // dd($prepla_schedule_group);

            $preplanning = Preplanning::find($preplanningId);

            if(isset($prepla_schedule_group->Groupe))
            {
                $groups = Group::find($prepla_schedule_group->Groupe);
            }

            if(isset($prepla_schedule_group->Regroupement))
            {
                $regroups = Groupment::find($prepla_schedule_group->Regroupement);

            }

            $intervenants = Prepla_scheduledate_intervenants::where('Pp_schedule_id', $preplanning_whole_preplaning_Id)->get();

            $preplanning_schedule = Prepla_schedules::find($preplanning_whole_preplaning_Id);

            $af_id = $preplanning->AF_target_id;

            // Create new Session 
            $session = new Session();
            $session->code = '';
            $session->title = $preplanning_schedule->title;
            $session->nb_days = 0;
            $session->nb_hours = 0;
            $session->is_uknown_date = 0;
            $session->session_type = 'AF_SESSION_TYPE_SCSS';
            $session->state = 'AF_STATES_OPEN';
            $session->is_active = 1;
            $session->is_main_session = 1;
            $session->started_at = $date_start;
            $session->ended_at = $date_start;
            $session->session_mode = 'SESSION';
            $session->timestructure_id = 0;
            $session->af_id = $af_id;
            $session->is_internship_period = 0;
            $session->is_evaluation = 0;
            $session->save();

            $lastInsertedSession = $session->id;

            $foreign_key = str_pad($af_id, 4, '0', STR_PAD_LEFT);
            $custom_code = 'S_AF' . $foreign_key . '_' . $lastInsertedSession;

            $session->code = $custom_code;
            $session->save();

            // Create new Session Date
            $sessiondate = new Sessiondate();
            $sessiondate->session_id = $lastInsertedSession;
            $sessiondate->planning_date = $date_start->format('Y-m-d');
            $sessiondate->duration = $duration;
            $sessiondate->save();

            $lastInsertedSessionDate = $sessiondate->id;

            // Create new Schedule

            $schedule = new Schedule();
            $schedule->start_hour = $start_hour;
            $schedule->end_hour = $end_hour;
            $schedule->duration = $duration;
            $schedule->sessiondate_id = $lastInsertedSessionDate;
            $schedule->type = 'M';
            $schedule->save();

            $lastInsertedID = $schedule->id;

            // Getting Entity
            foreach ($intervenants as $intervenant) {
                $contact = Contact::find($intervenant->Contact_id);
                // $entity = Entitie::where('name', 'LIKE', $contact->lastname)->orWhere('email', $contact->email)->orWhere('pro_phone', $contact->pro_phone)->orWhere('is_active',1)->first();
                $entity = Entitie::where('name', 'LIKE', $contact->lastname)->orWhere('email', $contact->email)->orWhere('is_active',1)->first();

                // Create Enrollement 
                $enrollement = new Enrollment();
                $enrollement->entitie_id = $entity->id;
                $enrollement->af_id = $af_id;
                $enrollement->enrollment_type = 'F';
                $enrollement->save();
                $lastInsertedEnrollement = $enrollement->id;

                // Create Mmber for Intervenant
                $member = new Member();
                $member->contact_id = $intervenant->Contact_id;
                $member->enrollment_id = $lastInsertedEnrollement;
                $member->save();

                // Create Schedule Contact for Intervenant
                $schedulecontact = new Schedulecontact();
                $schedulecontact->is_absent = 0;
                $schedulecontact->pointing = 'not_pointed';
                $schedulecontact->price = $intervenant->price;
                $schedulecontact->total_cost = $intervenant->price;
                $schedulecontact->type_of_intervention = 'TI_FORMATION';
                $schedulecontact->schedule_id = $lastInsertedID;
                $schedulecontact->member_id = $member->id;
                $schedulecontact->is_sent_sage_paie = 0;
                $schedulecontact->is_former = 1;
                $schedulecontact->save();
            }

            if (isset($prepla_schedule_group->Groupe)) {

                $members = Member::where('group_id', $groups->id)->get();

                foreach ($members as $member) {
                    $schedulecontact = new Schedulecontact();
                    $schedulecontact->is_absent = 0;
                    $schedulecontact->pointing = 'not_pointed';
                    $schedulecontact->schedule_id = $lastInsertedID;
                    $schedulecontact->member_id = $member->id;
                    $schedulecontact->is_sent_sage_paie = 0;
                    $schedulecontact->save();
                }
            }

            // Store Regroupment into Schedule Contact
            if (isset($prepla_schedule_group->Regroupement)) {

                $group_groupments = Groupmentgroup::where('groupment_id', $regroups->id)->get();

                foreach ($group_groupments as $groupment) {
                    if (isset($prepla_schedule_group->Groupe)) {
                        if ($groupment->group_id != $groups->id) {
                            $members = Member::where('group_id', $groupment->group->id)->get();
                            foreach ($members as $member) {
                                $schedulecontact = new Schedulecontact();
                                $schedulecontact->is_absent = 0;
                                $schedulecontact->pointing = 'not_pointed';
                                $schedulecontact->schedule_id = $lastInsertedID;
                                $schedulecontact->member_id = $member->id;
                                $schedulecontact->is_sent_sage_paie = 0;
                                $schedulecontact->save();
                            }
                        
                        } else {
                            $members = Member::where('group_id', $groupment->group->id)->get();
                            foreach ($members as $member) {
                                $schedulecontact = new Schedulecontact();
                                $schedulecontact->is_absent = 0;
                                $schedulecontact->pointing = 'not_pointed';
                                $schedulecontact->schedule_id = $lastInsertedID;
                                $schedulecontact->member_id = $member->id;
                                $schedulecontact->is_sent_sage_paie = 0;
                                $schedulecontact->save();
                            }
                        }
                    }else{
                            $members = Member::where('group_id', $groupment->group->id)->get();
                            foreach ($members as $member) {
                                $schedulecontact = new Schedulecontact();
                                $schedulecontact->is_absent = 0;
                                $schedulecontact->pointing = 'not_pointed';
                                $schedulecontact->schedule_id = $lastInsertedID;
                                $schedulecontact->member_id = $member->id;
                                $schedulecontact->is_sent_sage_paie = 0;
                                $schedulecontact->save();
                            }
                    }
                }
    
                
            }
            // Store Groupment into Schedule Contact

            if(isset($prepla_schedule_group->Groupe) || isset($prepla_schedule_group->Regroupement))
            {
                    if(isset($prepla_schedule_group->Groupe))
                    {
                        $groupe_id = $prepla_schedule_group->Groupe;
                    }
        
                    if(isset($prepla_schedule_group->Regroupement))
                    {
                        $regroupe_id = $prepla_schedule_group->Regroupement;
                    }

        
                    // Create Schedule Group
                    $schedulegroupe = new AfSchedulegroup();
                    $schedulegroupe->schedule_id = $lastInsertedID;
                    $schedulegroupe->group_id = $groupe_id;
                    $schedulegroupe->regroup_id = $regroupe_id;
                    $schedulegroupe->save();
                    
                    // Update Color of Preplanning Scheduled Groups
                    $preplanning_scheduled = Prepla_schedules::find($preplanning_whole_preplaning_Id);
                    $preplanning_scheduled->color = '#2986CC';
                    $preplanning_scheduled->save();
      


            }else{
                return response()->json(['error' => 'Something went wrong'], 400);
            }

            

            
        }


        return response()->json(['message' => 'Success']);

    }

    // public function transferPreplanning(Request $request)
    // {

    //     $preplannings = $request->input('preplannings');

    //     DB::beginTransaction();

    //     try {
    //         foreach ($preplannings as $preplanning) {
    //             $preplanningId = $preplanning['Pp_id'];
    //             $start = $preplanning['start_hour'];
    //             $end = $preplanning['end_hour'];
    //             DB::select("CALL TRANSFERT_PREPLA('$preplanningId', DATE_FORMAT('$start', '%Y-%m-%d'), DATE_FORMAT('$end', '%Y-%m-%d'), @res)");
    //         }
    //         DB::commit();
    //         $message = 'Les préplannings sélectionnés ont été transférés avec succès.';
    //         $icon = 'success';
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         $message = 'Une erreur s\'est produite lors du transfert des préplannings sélectionnés.';
    //         $icon = 'error';
    //     }
    //     // return json response
    //     return response()->json(['message' => $message, 'icon' => $icon]);
    //     // get the result from the procedure
    //     // $result = DB::select("SELECT @res as result");


    // }

}
