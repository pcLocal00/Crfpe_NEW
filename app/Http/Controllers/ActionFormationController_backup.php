<?php

namespace App\Http\Controllers;

use DateTime;
use PDF;
use stdClass;
use Exception;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Param;
use App\Models\Price;
use App\Models\Sheet;
use App\Models\Action;
use App\Models\Member;
use App\Models\User;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Session;
use App\Models\Contract;
use App\Models\Helpindex;
use App\Models\Schedule;
use App\Models\Formation;
use App\Models\Emailmodel;
use App\Models\Groupment;
use App\Models\Ressource;
use App\Models\Enrollment;
use App\Models\Sheetparam;
use App\Models\Certificate;
use App\Models\FileContact;
use App\Models\Sessiondate;
use Illuminate\Http\Request;
use App\Models\Documentmodel;
use App\Models\Studentstatus;
use App\Models\Timestructure;
use App\Models\Groupmentgroup;
use App\Models\Templateperiod;
use App\Library\Helpers\Helper;
use App\Models\Schedulecontact;
use App\Models\Planningtemplate;
use App\Models\CommitteeDecision;
use App\Models\Scheduleressource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use App\Library\Services\PublicTools;
use App\Models\Timestructurecategory;
use App\Library\Services\DbHelperTools;
use App\Models\Task;
use App\Models\Estimate;
use App\Models\Entitie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;

class ActionFormationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list()
    {
        $page_title = 'Actions de formations';
        $page_description = '';
        return view('pages.af.list', compact('page_title', 'page_description'));
    }

    public function sdtAfs(Request $request)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];

        $userid = auth()->user()->id;
        $roles = auth()->user()->roles;

        if ($roles[0]->code == 'FORMATEUR') {
            $contactid = DB::table('users')
                ->where('id', $userid)
                ->pluck('contact_id');

            $enrollment_id = DB::table('af_members')->whereIn('contact_id', $contactid)->pluck('enrollment_id');
            $af_id = DB::table('af_enrollments')->whereIn('id', $enrollment_id)->pluck('af_id');
            $datas = Action::latest();

            if ($request->isMethod('post')) {
                if ($request->has('filter')) {
                    if ($request->has('filter_text') && !empty($request->filter_text)) {
                        $datas->where('code', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('title', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('description', 'like', '%' . $request->filter_text . '%');
                    }
                    if ($request->has('filter_start') && $request->has('filter_end')) {
                        if (!empty($request->filter_start) && !empty($request->filter_end)) {
                            $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                            $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                            $datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                        }
                    }
                    if ($request->has('filter_state') && !empty($request->filter_state)) {
                        $datas->where('state', $request->filter_state);
                    }
                    if ($request->has('filter_status') && !empty($request->filter_status)) {
                        $datas->where('status', $request->filter_status);
                    }
                    if ($request->has('filter_device_type') && !empty($request->filter_device_type)) {
                        $datas->where('device_type', $request->filter_device_type);
                    }
                    if ($request->has('filter_activation') && !empty($request->filter_activation)) {
                        $is_active = ($request->filter_activation == 'a') ? 1 : 0;
                        $datas->where('is_active', $is_active);
                    }
                    if ($request->has('filter_formation_id') && !empty($request->filter_formation_id)) {
                        $datas->where('formation_id', $request->filter_formation_id);
                    }
                } else {
                    $datas = $datas->whereIn('id', $af_id)->orderByDesc('id');
                    // $datas = Action::orderByDesc('id');
                    // dd($datas->get());
                }
            }

            $recordsTotal = count($datas->get());

            if ($request->length > 0) {
                $start = (int) $request->start;
                $length = (int) $request->length;
                $datas->skip($start)->take($length);
            }
            $udatas = $datas->orderByDesc('id')->get();
            foreach ($udatas as $d) {
                $href = '/view/af/' . $d->id;
                $row = array();
                //ID
                $row[] = $d->id;
                //<th>Formation</th>
                $spanUknownDate = ($d->is_uknown_date === 1) ? '<p class="text-warning font-size-xs"><i class="fas fa-exclamation-triangle text-warning"></i> Pas de dates connues actuellement</p>' : '';
                $row[] = $spanUknownDate . '<p class="text-primary"><a href="' . $href . '">' . $d->code . '</a></p><p><a href="' . $href . '">' . $d->title . '</a></p><p class="text-info font-size-xs">' . $d->formation->categorie->name . '</p><p class="text-info font-size-xs">PF : ' . $d->formation->title . ' (' . $d->formation->code . ')</p>';
                //<th>Type / Etat / Status</th>
                $labelActive = 'Désactivé';
                $cssClassActive = 'danger';
                if ($d->is_active == 1) {
                    $labelActive = 'Activé';
                    $cssClassActive = 'success';
                }
                $spanActive = $tools->constructParagraphLabelDot('xs', $cssClassActive, $labelActive);
                $spanTypeDispositif = $tools->constructParagraphLabelDot('xs', 'info', $d->device_type);
                $spanState = $tools->constructParagraphLabelDot('xs', 'primary', 'Etat : ' . $DbHelperTools->getNameParamByCode($d->state));
                $spanStatus = $tools->constructParagraphLabelDot('xs', 'primary', 'Status : ' . $DbHelperTools->getNameParamByCode($d->status));
                $row[] = $spanActive . $spanTypeDispositif . $spanState . $spanStatus;
                //Informations
                $nbSessions = $d->sessions()->count();
                $started_at = '--';
                if ($d->started_at != null) {
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->started_at);
                    $started_at = $dt->format('d/m/Y');
                }
                $ended_at = '--';
                if ($d->ended_at != null) {
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->ended_at);
                    $ended_at = $dt->format('d/m/Y');
                }
                $pInfos = '<p class="font-size-xs text-info">' . $nbSessions . ' Session(s)</p>';
                $pInfos .= '<p class="font-size-xs">Durée ' . $d->nb_days . ' jours / ' . $d->nb_hours . ' heures</p>';
                $pInfos .= '<p class="font-size-xs">Date début : ' . $started_at . '</p>';
                $pInfos .= '<p class="font-size-xs">Date fin : ' . $ended_at . '</p>';
                $pInfos .= '<p class="font-size-xs">Nb stagiaires max : ' . $d->max_nb_trainees . '</p>';
                //$pInfos.='<p class="font-size-xs">Nb dates à programmer : '.$d->nb_dates_to_program.'</p>';
                $row[] = $pInfos;
                //Date creation
                $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                $row[] = $created_at . $updated_at;
                //Actions
                $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formAf(' . $d->id . ')" title="Édition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewAf(' . $d->id . ')" title="Détails"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
                $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteAf(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
                $btn_pdf = '<a target="_blank" title="Visualiser la fiche technique" href="/pdf/af/technical/sheet/' . $d->id . '/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>';
                $btn_pdf_download = ' <a target="_blank" title="Télécharger la fiche technique" href="/pdf/af/technical/sheet/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>';
                $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                        data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                            <ul class="navi flex-column navi-hover py-2">
                                <li class="navi-item">
                                    ' . $btn_pdf . '
                                    ' . $btn_pdf_download . '
                                </li>
                            </ul>
                        </div>
                    </div>';

                $userid = auth()->user()->id;
                $roles = auth()->user()->roles;

                if ($roles[0]->code == 'FORMATEUR') {
                    $row[] = $btn_view;
                } else {
                    $row[] = $btn_edit . $btn_view . $btn_more;
                }

                $data[] = $row;
            }
        } else {
            //dd($request->start);
            //dd($request->length);
            $datas = Action::latest();
            if ($request->isMethod('post')) {
                if ($request->has('filter')) {
                    if ($request->has('filter_text') && !empty($request->filter_text)) {
                        $datas->where('code', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('title', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('description', 'like', '%' . $request->filter_text . '%');
                    }
                    if ($request->has('filter_start') && $request->has('filter_end')) {
                        if (!empty($request->filter_start) && !empty($request->filter_end)) {
                            $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                            $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                            $datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                        }
                    }
                    if ($request->has('filter_state') && !empty($request->filter_state)) {
                        $datas->where('state', $request->filter_state);
                    }
                    if ($request->has('filter_status') && !empty($request->filter_status)) {
                        $datas->where('status', $request->filter_status);
                    }
                    if ($request->has('filter_device_type') && !empty($request->filter_device_type)) {
                        $datas->where('device_type', $request->filter_device_type);
                    }
                    if ($request->has('filter_activation') && !empty($request->filter_activation)) {
                        $is_active = ($request->filter_activation == 'a') ? 1 : 0;
                        $datas->where('is_active', $is_active);
                    }
                    if ($request->has('filter_formation_id') && !empty($request->filter_formation_id)) {
                        $datas->where('formation_id', $request->filter_formation_id);
                    }
                } else {
                    $datas = Action::orderByDesc('id');
                }
            }

            $recordsTotal = count($datas->get());

            if ($request->length > 0) {
                $start = (int) $request->start;
                $length = (int) $request->length;
                $datas->skip($start)->take($length);
            }
            $udatas = $datas->orderByDesc('id')->get();
            foreach ($udatas as $d) {
                $href = '/view/af/' . $d->id;
                $row = array();
                //ID
                $row[] = $d->id;
                //<th>Formation</th>
                $spanUknownDate = ($d->is_uknown_date === 1) ? '<p class="text-warning font-size-xs"><i class="fas fa-exclamation-triangle text-warning"></i> Pas de dates connues actuellement</p>' : '';
                $row[] = $spanUknownDate . '<p class="text-primary"><a href="' . $href . '">' . $d->code . '</a></p><p><a href="' . $href . '">' . $d->title . '</a></p><p class="text-info font-size-xs">' . $d->formation->categorie->name . '</p><p class="text-info font-size-xs">PF : ' . $d->formation->title . ' (' . $d->formation->code . ')</p>';
                //<th>Type / Etat / Status</th>
                $labelActive = 'Désactivé';
                $cssClassActive = 'danger';
                if ($d->is_active == 1) {
                    $labelActive = 'Activé';
                    $cssClassActive = 'success';
                }
                $spanActive = $tools->constructParagraphLabelDot('xs', $cssClassActive, $labelActive);
                $spanTypeDispositif = $tools->constructParagraphLabelDot('xs', 'info', $d->device_type);
                $spanState = $tools->constructParagraphLabelDot('xs', 'primary', 'Etat : ' . $DbHelperTools->getNameParamByCode($d->state));
                $spanStatus = $tools->constructParagraphLabelDot('xs', 'primary', 'Status : ' . $DbHelperTools->getNameParamByCode($d->status));
                $row[] = $spanActive . $spanTypeDispositif . $spanState . $spanStatus;
                //Informations
                $nbSessions = $d->sessions()->count();
                $started_at = '--';
                if ($d->started_at != null) {
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->started_at);
                    $started_at = $dt->format('d/m/Y');
                }
                $ended_at = '--';
                if ($d->ended_at != null) {
                    $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->ended_at);
                    $ended_at = $dt->format('d/m/Y');
                }
                $pInfos = '<p class="font-size-xs text-info">' . $nbSessions . ' Session(s)</p>';
                $pInfos .= '<p class="font-size-xs">Durée ' . $d->nb_days . ' jours / ' . $d->nb_hours . ' heures</p>';
                $pInfos .= '<p class="font-size-xs">Date début : ' . $started_at . '</p>';
                $pInfos .= '<p class="font-size-xs">Date fin : ' . $ended_at . '</p>';
                $pInfos .= '<p class="font-size-xs">Nb stagiaires max : ' . $d->max_nb_trainees . '</p>';
                //$pInfos.='<p class="font-size-xs">Nb dates à programmer : '.$d->nb_dates_to_program.'</p>';
                $row[] = $pInfos;
                //Date creation
                $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                $row[] = $created_at . $updated_at;
                //Actions
                $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formAf(' . $d->id . ')" title="Édition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewAf(' . $d->id . ')" title="Détails"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
                $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteAf(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
                $btn_pdf = '<a target="_blank" title="Visualiser la fiche technique" href="/pdf/af/technical/sheet/' . $d->id . '/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>';
                $btn_pdf_download = ' <a target="_blank" title="Télécharger la fiche technique" href="/pdf/af/technical/sheet/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>';
                $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                        data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                            <ul class="navi flex-column navi-hover py-2">
                                <li class="navi-item">
                                    ' . $btn_pdf . '
                                    ' . $btn_pdf_download . '
                                </li>
                            </ul>
                        </div>
                    </div>';
                $row[] = $btn_edit . $btn_view . $btn_more;
                $data[] = $row;
            }
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

    public function getAfs()
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $data = $meta = [];

        $datas = Action::selectRaw('af_actions.*, pf_formations.title as pf_title, DATE_FORMAT(af_actions.started_at, "%d/%m/%Y") as start_date')
        ->join('pf_formations', 'pf_formations.id', 'af_actions.formation_id')->latest();
        $recordsTotal = count($datas->get());

        $udatas = $datas->orderByDesc('id')->get();

        $result = [
            "recordsTotal" => $recordsTotal,
            "datas" => $udatas,
        ];
        return response()->json($result);
    }


    public function getPfs()
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $data = $meta = [];

        $datas = Formation::latest();
        $recordsTotal = count($datas->get());

        $udatas = $datas->orderByDesc('id')->get();

        $result = [
            "recordsTotal" => $recordsTotal,
            "datas" => $udatas,
        ];
        return response()->json($result);
    }
    //-----------------------------------------------------------------------//

    public function formAf($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Action::findOrFail($row_id);
        }
        $DbHelperTools = new DbHelperTools();
        //ETATS
        $states_af = $DbHelperTools->getParamsByParamCode('AF_STATES');
        //STATUS
        $status_af = $DbHelperTools->getParamsByParamCode('AF_STATUS');
        //BPF_MAIN_OBJECTIVE
        $bpf_main_params = $DbHelperTools->getParamsByParamCode('BPF_MAIN_OBJECTIVE');
        //BPF_SPECIALITY
        $bpf_speciality_params = $DbHelperTools->getParamsByParamCode('BPF_SPECIALITY');
        //AF_SESSION_TYPES
        //$session_types = $DbHelperTools->getParamsByParamCode('AF_SESSION_TYPES');
        //AF_DISPOSITIF_TYPES
        $device_types = $DbHelperTools->getParamsByParamCode('AF_DISPOSITIF_TYPES');
        //$lieux = $DbHelperTools->getParamsByParamCode('TRAINING_SITE');
        $lieux = Ressource::select('id', 'name', 'address_training_location')->where([['type', 'RES_TYPE_LIEU'], ['is_internal', 0]])->get();
        //templates
        //$templates=$DbHelperTools->getPlanningTemplates();
        $session_types = $templates = null;
        $herited_account_codes = false;
        $herited_analytic_codes = false;
        if ($row_id == 0) {
            //AF_SESSION_TYPES
            $session_types = $DbHelperTools->getParamsByParamCode('AF_SESSION_TYPES');
            //templates
            $templates = $DbHelperTools->getPlanningTemplates();

            /* acounting & analytical codes */
            $herited_codes = Formation::whereRaw('accounting_code is not null or analytical_codes is not null')
                ->get(['id', 'accounting_code', 'analytical_codes'])
                ->toArray();
            $herited_account_codes = array_column($herited_codes, 'accounting_code', 'id');
            $herited_analytic_codes = array_column($herited_codes, 'analytical_codes', 'id');
        }
        return view('pages.af.form', ['row' => $row, 'states_af' => $states_af, 'status_af' => $status_af, 'bpf_main_params' => $bpf_main_params, 'bpf_speciality_params' => $bpf_speciality_params, 'device_types' => $device_types, 'session_types' => $session_types, 'templates' => $templates, 'lieux' => $lieux, 'herited_account_codes' => $herited_account_codes, 'herited_analytic_codes' => $herited_analytic_codes]);
    }

    public function formAfTest($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Action::findOrFail($row_id);
        }
        $DbHelperTools = new DbHelperTools();
        //ETATS
        $states_af = $DbHelperTools->getParamsByParamCode('AF_STATES');
        //STATUS
        $status_af = $DbHelperTools->getParamsByParamCode('AF_STATUS');
        //BPF_MAIN_OBJECTIVE
        $bpf_main_params = $DbHelperTools->getParamsByParamCode('BPF_MAIN_OBJECTIVE');
        //BPF_SPECIALITY
        $bpf_speciality_params = $DbHelperTools->getParamsByParamCode('BPF_SPECIALITY');
        //AF_SESSION_TYPES
        //$session_types = $DbHelperTools->getParamsByParamCode('AF_SESSION_TYPES');
        //AF_DISPOSITIF_TYPES
        $device_types = $DbHelperTools->getParamsByParamCode('AF_DISPOSITIF_TYPES');
        //$lieux = $DbHelperTools->getParamsByParamCode('TRAINING_SITE');
        $lieux = Ressource::select('id', 'name', 'address_training_location')->where([['type', 'RES_TYPE_LIEU'], ['is_internal', 0]])->get();
        //templates
        //$templates=$DbHelperTools->getPlanningTemplates();
        $session_types = $templates = null;
        if ($row_id == 0) {
            //AF_SESSION_TYPES
            $session_types = $DbHelperTools->getParamsByParamCode('AF_SESSION_TYPES');
            //templates
            $templates = $DbHelperTools->getPlanningTemplates();
        }
        return view('pages.af.form', ['row' => $row, 'states_af' => $states_af, 'status_af' => $status_af, 'bpf_main_params' => $bpf_main_params, 'bpf_speciality_params' => $bpf_speciality_params, 'device_types' => $device_types, 'session_types' => $session_types, 'templates' => $templates, 'lieux' => $lieux]);
    }

    public function storeFormAf(Request $request)
    {
        //dd($request->all());
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $rules = [
                'formation_id' => 'required',
            ];
            $messages = [
                'formation_id.required' => 'Le produit de formation est obligatoire !',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $msg = '<p>Veuillez vérifier les erreurs ci-dessous : </p>';
                foreach ($errors->get('formation_id') as $message) {
                    $msg .= '<p class="text-danger">' . $message . '</p>';
                }
                $success = false;
            } else {
                $DbHelperTools = new DbHelperTools();
                $started_at = Carbon::createFromFormat('d/m/Y', $request->started_at);
                $ended_at = Carbon::createFromFormat('d/m/Y', $request->ended_at);
                //$nbdaysToProgram =$request->nb_dates_to_program;
                //$session_type = $request->session_type;
                $dataAf = array(
                    'id' => $request->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'code' => $DbHelperTools->generateAfCode($request->formation_id, $request->id),
                    'nb_days' => $request->nb_days,
                    'nb_hours' => $request->nb_hours,

                    'nb_pratical_hours' => $request->nb_pratical_hours,
                    'nb_pratical_days' => $request->nb_pratical_days,

                    'is_uknown_date' => $request->is_uknown_date,
                    'bpf_main_objective' => $request->bpf_main_objective,
                    'bpf_training_specialty' => $request->bpf_training_specialty,
                    'device_type' => $request->device_type,
                    'max_nb_trainees' => $request->max_nb_trainees,
                    'accounting_code' => $request->accounting_code,
                    'analytical_code' => $request->analytical_code,
                    'nb_groups' => $request->nb_groups,
                    'state' => $request->state,
                    'status' => $request->status,
                    'is_active' => $request->is_active,
                    'training_site' => $request->training_site,
                    'other_training_site' => $request->other_training_site,
                    'formation_id' => $request->formation_id,
                    'started_at' => $started_at,
                    'ended_at' => $ended_at,
                );
                //dd($dataAf);exit();
                $row_id = $DbHelperTools->manageAF($dataAf);
                $success = true;
                $msg = 'L\'action de formation a été enregistrée avec succès';
                //Creer une session par défaut
                if ($row_id > 0) {
                    //Importer les prix depuis le produit de formation
                    $successA = $DbHelperTools->mainCopyPricesFromPfToAf($row_id);
                    //Importer la fiche technique principale sur le produit de formation
                    $successB = $DbHelperTools->mainCopySheetFromPfToAf($row_id);

                    $type_pf = $DbHelperTools->getParamPFormation($request->formation_id, 'PF_TYPE_FORMATION');
                    //Creér une session par defaut (seulement dans le cas insert AF pas Update)
                    $is_uknown_date = ($request->has('is_uknown_date')) ? 1 : 0;
                    if ($request->id == 0 && $is_uknown_date == 0) {
                        /* $arrayAfData = array(
                                'title' => $request->title,
                                'description' => $request->description,
                                'nb_days' => $request->nb_days,
                                'nb_hours' => $request->nb_hours,
                                'is_uknown_date' => $request->is_uknown_date,
                                'nb_dates_to_program' => $request->nb_dates_to_program,
                                'nb_total_dates_to_program' => $request->nb_total_dates_to_program,
                                'max_nb_trainees' => $request->max_nb_trainees,
                                'session_type' => $request->session_type,
                                'training_site' => $request->training_site,
                                'other_training_site' => $request->other_training_site,
                                'is_active' => $request->is_active,
                                'started_at' => $started_at->format('Y-m-d'),
                                'planning_template_id' => $request->planning_template_id,
                                'af_id' => $row_id,
                            );
                            $s=$DbHelperTools->createSessionFromPf($arrayAfData); */

                        //Si produit de type == diplomante
                        //if($type_pf=='PF_TYPE_DIP'){
                        if ($request->has('IDS_P_FORMATIONS') && !empty($request->IDS_P_FORMATIONS)) {
                            $IDS_P_FORMATIONS = $request->IDS_P_FORMATIONS;
                            if (count($IDS_P_FORMATIONS) > 0) {
                                $rows = Formation::whereIn('id', $IDS_P_FORMATIONS)->get();
                                foreach ($rows as $row) {
                                    $posted_data = $request->FORM_FORMATIONS[$row->id];
                                    $nb_dates_to_program = $posted_data['NB_DATES_TO_PROGRAM'];
                                    $nb_total_dates_to_program = $posted_data['NB_TOTAL_DATES_TO_PROGRAM'];
                                    $session_type = $posted_data['SESSION_TYPE'];
                                    $planning_template_id = $posted_data['PLANNING_TEMPLATE'];

                                    if ($type_pf == 'PF_TYPE_DIP') {
                                        $arrayAfData = array(
                                            'title' => $row->title,
                                            'description' => $row->description,
                                            'nb_days' => $row->nb_days,
                                            'nb_hours' => $row->nb_hours,
                                            'is_uknown_date' => 0,
                                            'nb_dates_to_program' => $nb_dates_to_program,
                                            'nb_total_dates_to_program' => $nb_total_dates_to_program,
                                            'max_nb_trainees' => $request->max_nb_trainees,
                                            'session_type' => $session_type,
                                            'training_site' => $request->training_site,
                                            'other_training_site' => $request->other_training_site,
                                            'is_active' => 1,
                                            'started_at' => null,
                                            'planning_template_id' => $planning_template_id,
                                            'af_id' => $row_id,
                                        );
                                    } else {
                                        $arrayAfData = array(
                                            'title' => $request->title,
                                            'description' => $request->description,
                                            'nb_days' => $request->nb_days,
                                            'nb_hours' => $request->nb_hours,
                                            'is_uknown_date' => $request->is_uknown_date,
                                            'nb_dates_to_program' => $nb_dates_to_program,
                                            'nb_total_dates_to_program' => $nb_total_dates_to_program,
                                            'max_nb_trainees' => $request->max_nb_trainees,
                                            'session_type' => $session_type,
                                            'training_site' => $request->training_site,
                                            'other_training_site' => $request->other_training_site,
                                            'is_active' => $request->is_active,
                                            'started_at' => $started_at->format('Y-m-d'),
                                            'planning_template_id' => $planning_template_id,
                                            'af_id' => $row_id,
                                        );
                                    }

                                    $nb_session_duplication = ($row->nb_session_duplication > 0) ? $row->nb_session_duplication : 1;
                                    for ($i = 1; $i <= $nb_session_duplication; $i++) {
                                        $s = $DbHelperTools->createSessionFromPf($arrayAfData);
                                    }
                                }
                            }
                        }
                        //}
                    }
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function viewAf($row_id)
    {
        $page_title = 'Fiche de l\'action de formation';
        $page_description = '';
        $row = $template = null;
        $show_proposal = '';
        if (isset($_GET['v']) && !empty($_GET['v'])) {
            $show_proposal = ($_GET['v'] == "p") ? true : false;
        }
        $show_proposal = (isset($_GET['v']) && !empty($_GET['v'])) ? $_GET['v'] : '';
        $state = $status = $bpf_training_specialty = $bpf_main_objective = $session_type = $training_site = '';
        if ($row_id > 0) {
            $row = Action::findOrFail($row_id);
            $DbHelperTools = new DbHelperTools();
            $state = $DbHelperTools->getNameParamByCode($row->state);
            $status = $DbHelperTools->getNameParamByCode($row->status);
            $bpf_training_specialty = $DbHelperTools->getNameParamByCode($row->bpf_training_specialty);
            $bpf_main_objective = $DbHelperTools->getNameParamByCode($row->bpf_main_objective);
            $session_type = $DbHelperTools->getNameParamByCode($row->session_type);
            //$training_site = ($row->training_site != 'OTHER') ? $DbHelperTools->getNameParamByCode($row->training_site) : '';
            $training_site = ($row->training_site != 'OTHER') ? $row->training_site : '';
            //planning_template_id
            if ($row->planning_template_id) {
                $tpl = Planningtemplate::select('name')->where('id', $row->planning_template_id)->first();
                $template = $tpl['name'];
            }
        }
        return view('pages.af.view', ['row' => $row, 'state' => $state, 'status' => $status, 'session_type' => $session_type, 'bpf_main_objective' => $bpf_main_objective, 'bpf_training_specialty' => $bpf_training_specialty, 'template' => $template], compact('page_title', 'page_description', 'training_site', 'show_proposal'));
    }

    public function constructViewContentAf($viewtype, $row_id)
    {
        $row = $sessions = $lieux = $groups = $enrollments = $members = null;
        $af_sheet_id = 0;
        if ($row_id > 0) {
            $row = Action::findOrFail($row_id);
            $sessions = Session::select('id', 'code', 'title')->where('af_id', $row_id)->orderBy('title')->get();
            $sheet = Sheet::select('id')->where('action_id', $row_id)->whereNull('formation_id')->first();
            $af_sheet_id = (isset($sheet->id) && $sheet->id > 0) ? $sheet->id : 0;
            $groups = Group::select('id', 'title')->where('af_id', $row_id)->get();
            $lieux = Ressource::select('id', 'name', 'address_training_location')->where([['type', 'RES_TYPE_LIEU'], ['is_internal', 0]])->get();
            //  $ids_enrollments = Enrollment::select('id')->where([['af_id', $row_id], ['enrollment_type', 'S']])->get()->pluck('id');
            //  if (count($ids_enrollments) > 0) {whereIn('enrollment_id', $ids_enrollments)->
            // $members = Member::where([['af_enrollments.af_id', $row_id],['af_schedulecontacts.is_former', 0]])
            //     ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
            //     ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
            //     ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')->orderBy('en_contacts.firstname','asc')->get(['af_members.*'])->unique();
            $members = Member::where([['af_enrollments.af_id', $row_id], ['af_enrollments.enrollment_type', 'S']])
                // ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')->orderBy('en_contacts.firstname', 'asc')->get(['af_members.*'])->unique();
            //   }
            $intervenant = Member::where([['af_enrollments.af_id', $row_id], ['af_schedulecontacts.is_former', 1]])
                ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')->orderBy('en_contacts.firstname', 'asc')->get(['af_members.*'])->unique();
        }
        return view('pages.af.construct.view', compact('row', 'viewtype', 'sessions', 'af_sheet_id', 'lieux', 'groups', 'members', 'intervenant')); //,'enrollments'
    }

    public function getSessionDates($session_id)
    {
        $session = null;
        $sessiondates = null;
        $number_of_hours_planned = $number_of_dates_planned = 0;
        $sessiondatesArray = [];
        if ($session_id > 0) {
            $session = Session::findOrFail($session_id);
            $DbHelperTools = new DbHelperTools();
            $sessiondatesArray = $DbHelperTools->getSessionPlanning($session_id, '', '');
            //dd($sessiondatesArray);
            $number_of_hours_planned = Sessiondate::where('session_id', $session_id)->sum('duration');
            $number_of_dates_planned = Sessiondate::select('id')->where('session_id', $session_id)->count();
        }
        return view('pages.af.session.dates', ['session' => $session, 'sessiondates' => $sessiondates, 'sessiondatesArray' => $sessiondatesArray, 'number_of_dates_planned' => $number_of_dates_planned, 'number_of_hours_planned' => $number_of_hours_planned]);
    }

    public function formSession($row_id, $af_id)
    {
        $row = $af = null;
        if ($row_id > 0) {
            $row = Session::findOrFail($row_id);
        }
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
        }

        $DbHelperTools = new DbHelperTools();
        //ETATS
        $states_af = $DbHelperTools->getParamsByParamCode('AF_STATES');
        //AF_SESSION_TYPES
        $session_types = $DbHelperTools->getParamsByParamCode('AF_SESSION_TYPES');
        //templates
        $templates = $DbHelperTools->getPlanningTemplates();
        //$lieux = $DbHelperTools->getParamsByParamCode('TRAINING_SITE');
        $lieux = Ressource::select('id', 'name', 'address_training_location')->where([['type', 'RES_TYPE_LIEU'], ['is_internal', 0]])->get();
        $evaluation_modes = Formation::EVALUATION_MODES;

        return view('pages.af.session.form', ['row' => $row, 'states_af' => $states_af, 'session_types' => $session_types, 'templates' => $templates, 'af_id' => $af_id, 'af' => $af, 'lieux' => $lieux, 'evaluation_modes' => $evaluation_modes]);
    }

    public function formCertificationSession($af_id)
    {
        return view('pages.af.construct.certifications.form', ['af_id' => $af_id]);
    }

    public function formSaveCertificationSession(Request $request, $af_id)
    {
        if (!$request->isMethod('post')) {
            die('No Access');
        }
        $DbHelperTools = new DbHelperTools();
        $pfs_ids = $request->pfs_ids;
        $pfs_ids = array_filter($pfs_ids, function ($p) {
            return is_numeric($p);
        });

        try {
            $af = Action::find($af_id);
            $pfs = Formation::whereIn('id', $pfs_ids)->get();

            $parents_correspondance = [];
            foreach ($pfs as $pf) {
                //HIERARCHIE
                $session_mode = ($pf->is_evaluation) ? 'SESSION' : 'HIERARCHIE';
                $dataSession = array(
                    'id' => 0,
                    'code' => $DbHelperTools->generateSessionCode($af_id, 0),
                    'title' => $pf->title,
                    'description' => $pf->description,
                    'nb_days' => $pf->nb_days,
                    'nb_hours' => $pf->nb_hours,
                    'is_uknown_date' => $af->is_uknown_date,
                    'nb_dates_to_program' => null,
                    'nb_total_dates_to_program' => null,
                    'max_nb_trainees' => null,
                    'session_type' => null,
                    'state' => 'AF_STATES_OPEN',
                    'training_site' => null,
                    'other_training_site' => null,
                    'is_active' => 1,
                    'is_main_session' => 1,
                    'started_at' => $af->started_at,
                    'ended_at' => $af->ended_at,
                    'planning_template_id' => null,
                    'timestructure_id' => $pf->timestructure_id,
                    'ects' => $pf->ects,
                    'coefficient' => $pf->coefficient,
                    'session_parent_id' => null,
                    'af_id' => $af_id,
                    'session_mode' => $session_mode,
                    'is_evaluation' => 1,
                );
                $session_id = $DbHelperTools->manageSession($dataSession);

                /* Manage parents */
                $parents_correspondance[$pf->id] = $session_id;
            }

            //dd($parents_correspondance);

            foreach ($pfs as $pf) {
                if (array_key_exists($pf->id, $parents_correspondance)) {
                    $session_id = $parents_correspondance[$pf->id];
                    if (array_key_exists($pf->parent_id, $parents_correspondance)) {
                        $sess_parent = $parents_correspondance[$pf->parent_id];
                        Session::where('id', $session_id)->update(['session_parent_id' => $sess_parent]);
                    }
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }


    public function formSessionTest($row_id, $af_id)
    {
        $row = $af = null;
        if ($row_id > 0) {
            $row = Session::findOrFail($row_id);
        }
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
        }

        $DbHelperTools = new DbHelperTools();
        //ETATS
        $states_af = $DbHelperTools->getParamsByParamCode('AF_STATES');
        //AF_SESSION_TYPES
        $session_types = $DbHelperTools->getParamsByParamCode('AF_SESSION_TYPES');
        //templates
        $templates = $DbHelperTools->getPlanningTemplates();
        $lieux = $DbHelperTools->getParamsByParamCode('TRAINING_SITE');
        return view('pages.session.forms.form-session', ['row' => $row, 'states_af' => $states_af, 'session_types' => $session_types, 'templates' => $templates, 'af_id' => $af_id, 'af' => $af, 'lieux' => $lieux]);
    }

    public function getSessionInfos($session_id)

    {
        $session = Session::find($session_id);
        //infos sur les jours et les heures plannifier...

        $sessiondatesArray = [];
        $number_of_hours_planned = $number_of_dates_planned = 0;


        $DbHelperTools = new DbHelperTools();
        $sessiondatesArray = $DbHelperTools->getSessionPlanning($session_id, '', '');
        $number_of_hours_planned = Sessiondate::where('session_id', $session_id)->sum('duration');
        $number_of_dates_planned = Sessiondate::select('id')->where('session_id', $session_id)->count();


        //infos sur le modele ...
        $sessionPlanningTemplate = \App\Library\Helpers\Helper::getNamePlanningtemplateStatic($session->planning_template_id);
        $sessionTypeArray = \App\Library\Helpers\Helper::getNameParamByCodeStatic($session->session_type);
        $session_type = $sessionTypeArray['name'];

        return view('pages.session.infos.infos-modal', compact(
            'session',
            'sessionPlanningTemplate',
            'sessionTypeArray',
            'session_type',
            'sessiondatesArray',
            'number_of_hours_planned',
            'number_of_dates_planned'
        ));
    }

    public function storeFormSession(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';

        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $started_at = Carbon::createFromFormat('d/m/Y', $request->started_at);
            $nbdaysToProgram = $request->nb_dates_to_program;
            $session_type = $request->session_type;
            $ended_at = null;
            $datesToProgram = [];
            if ($nbdaysToProgram > 0) {
                $datesToProgram = $DbHelperTools->getDatesToProgram($started_at->format('Y-m-d'), (int)$nbdaysToProgram, $session_type);
                if (collect($datesToProgram)->count() > 0) {
                    $dt_end = collect($datesToProgram)->last();
                    if ($dt_end) {
                        $ended_at = Carbon::createFromFormat('Y-m-d', $dt_end);
                    }
                }
            }

            if ($request->is_duplicate == 1) {
                $dataSession = array(
                    'id' => $request->id,
                    'code' => $DbHelperTools->generateSessionCode($request->af_id, $request->id),
                    'title' => $request->title,
                    'description' => $request->description,
                    'session_mode' => $request->session_mode,
                    'nb_days' => $request->nb_days,
                    'nb_hours' => $request->nb_hours,
                    'is_uknown_date' => $request->is_uknown_date,
                    'nb_dates_to_program' => $nbdaysToProgram,
                    'nb_total_dates_to_program' => $request->nb_total_dates_to_program,
                    'max_nb_trainees' => $request->max_nb_trainees,
                    'session_type' => $session_type,
                    'state' => $request->state,
                    'is_duplicate' => $request->is_duplicate,
                    'nb_seances' => $request->nb_de_seances,
                    'nb_decoupages' => $request->nb_de_decoupages,
                    'training_site' => $request->training_site,
                    'other_training_site' => $request->other_training_site,
                    'is_active' => $request->is_active,
                    'is_main_session' => 1,
                    'started_at' => $started_at,
                    'ended_at' => $ended_at,
                    'planning_template_id' => $request->planning_template_id,
                    'af_id' => $request->af_id,
                    'is_evaluation' => $request->is_evaluation,
                    'evaluation_mode' => $request->evaluation_mode,
                    'ects' => $request->ects,
                    'coefficient' => $request->coefficient,
                );

                $session_id = $DbHelperTools->manageSession($dataSession);
                if ($session_id > 0) {
                    $success = true;
                    $msg = 'La session a été enregistrée avec succès';
                    $DbHelperTools->sessionPlanningProcessPlanified($session_id, $dataSession);
                }
            } else {
                $dataSession = array(
                    'id' => $request->id,
                    'code' => $DbHelperTools->generateSessionCode($request->af_id, $request->id),
                    'title' => $request->title,
                    'description' => $request->description,
                    'session_mode' => $request->session_mode,
                    'nb_days' => $request->nb_days,
                    'nb_hours' => $request->nb_hours,
                    'is_uknown_date' => $request->is_uknown_date,
                    'nb_dates_to_program' => $nbdaysToProgram,
                    'nb_total_dates_to_program' => $request->nb_total_dates_to_program,
                    'max_nb_trainees' => $request->max_nb_trainees,
                    'session_type' => $session_type,
                    'state' => $request->state,
                    'training_site' => $request->training_site,
                    'other_training_site' => $request->other_training_site,
                    'is_active' => $request->is_active,
                    'is_main_session' => 1,
                    'started_at' => $started_at,
                    'ended_at' => $ended_at,
                    'planning_template_id' => $request->planning_template_id,
                    'af_id' => $request->af_id,
                    'is_evaluation' => $request->is_evaluation,
                    'evaluation_mode' => $request->evaluation_mode,
                    'ects' => $request->ects,
                    'coefficient' => $request->coefficient,
                );

                $session_id = $DbHelperTools->manageSession($dataSession);
                if ($session_id > 0) {
                    $success = true;
                    $msg = 'La session a été enregistrée avec succès';
                    $DbHelperTools->sessionPlanningProcess($session_id);
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function getSessionsGridList($af_id)
    {
        $row = $sessions = null;
        if ($af_id > 0) {
            $row = Action::findOrFail($af_id);
            $sessions = Session::where('af_id', $af_id)->where('is_internship_period', 0)->where('is_evaluation', 0)->get();
        }
        return view('pages.af.session.gridlist', ['row' => $row, 'sessions' => $sessions]);
    }

    public function searchSessionGridList(Request $request, $af_id)
    {
        $row = $sessions = null;


        if ($af_id > 0) {
            $row = Action::findOrFail($af_id);
            $sessions = Session::where('af_id', $af_id)->latest();

            if ($request->has('filter')) {

                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $sessions->where('title', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('code', 'like', '%' . $request->filter_text . '%');
                }

                /*   if ($request->has('filter_start') && $request->has('filter_end')) {
                       if (!empty($request->filter_start) && !empty($request->filter_end)) {
                           $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                           $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                           $sessions->whereBetween('started_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                       }
                   }*/
            }
        }


        return view('pages.af.session.gridlist', ['row' => $row, 'sessions' => $sessions->get()]);
    }

    public function storeFormSessionDate(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            //dd($request->all());
            $DbHelperTools = new DbHelperTools();
            $session_id = $request->session_id;
            if ($request->has('sessiondates')) {
                if (count($request->sessiondates) > 0) {
                    foreach ($request->sessiondates as $key => $data) {
                        $planning_date = (isset($data['planning_date']) && !empty($data['planning_date'])) ? Carbon::createFromFormat('d/m/Y', $data['planning_date']) : null;
                        $dataSessionDate = array(
                            'id' => $data['id'],
                            'planning_date' => $planning_date,
                            'duration' => $data['duration'],
                            'session_id' => $data['session_id'],
                        );
                        $sessiondate_id = $DbHelperTools->manageSessiondate($dataSessionDate);
                        if ($sessiondate_id > 0) {
                            if (count($data['schedules']) > 0) {
                                foreach ($data['schedules'] as $ds) {
                                    if (!empty($ds['start_hour']) && !empty($ds['end_hour'])) {
                                        if ($planning_date == null) {
                                            $planning_date = Carbon::now();
                                        }
                                        $start_hour = $planning_date->format('d/m/Y') . ' ' . $ds['start_hour'];
                                        $end_hour = $planning_date->format('d/m/Y') . ' ' . $ds['end_hour'];
                                        $dataSchedule = array(
                                            'id' => $ds['id'],
                                            'type' => $ds['type'],
                                            'start_hour' => Carbon::createFromFormat('d/m/Y H:i', $start_hour),
                                            'end_hour' => Carbon::createFromFormat('d/m/Y H:i', $end_hour),
                                            'duration' => ($ds['duration']) ? $ds['duration'] : 0,
                                            'sessiondate_id' => $sessiondate_id,
                                        );
                                        $schedule_id = $DbHelperTools->manageSchedule($dataSchedule);
                                    }
                                }
                            }
                        }
                    }
                    $success = true;
                    $msg = 'Les dates ont été enregistrée avec succès';
                }
            }
            //dd($session_id);
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formSessionDate($session_id, $sessiondate_id)
    {
        $autorizeS1 = $autorizeS2 = true;
        $row = null;
        $sd = [];
        if ($sessiondate_id > 0) {
            $row = Sessiondate::findOrFail($sessiondate_id);
            $DbHelperTools = new DbHelperTools();
            $sd = $DbHelperTools->getSessiondateSchedules($sessiondate_id);
        }
        if ($session_id > 0) {
            $session = Session::select('planning_template_id')->where('id', $session_id)->first();
            $planning_template_id = $session->planning_template_id;
            if ($planning_template_id > 0) {
                $collectionTypes = Templateperiod::where('planning_template_id', $planning_template_id)->pluck('type')->toArray();
                //$autorizeS1 = $collectionTypes->has('M');
                //$autorizeS2 = $collectionTypes->has('A');
                $autorizeS1 = (in_array('M', $collectionTypes)) ? true : false;
                $autorizeS2 = (in_array('A', $collectionTypes)) ? true : false;
            }
        }
        return view('pages.af.session.sessiondateform', ['row' => $row, 'sd' => $sd, 'session_id' => $session_id, 'autorizeS1' => $autorizeS1, 'autorizeS2' => $autorizeS2]);
    }

    public function getSessionSummaryDates($session_id)
    {
        $sessiondatesArray = [];
        $number_of_hours_planned = $number_of_dates_planned = 0;
        if ($session_id > 0) {
            $session = Session::findOrFail($session_id);
            $DbHelperTools = new DbHelperTools();
            $sessiondatesArray = $DbHelperTools->getSessionPlanning($session_id, '', '');
            $number_of_hours_planned = Sessiondate::where('session_id', $session_id)->sum('duration');
            $number_of_dates_planned = Sessiondate::select('id')->where('session_id', $session_id)->count();
        }
        return view('pages.af.session.summary', ['session' => $session, 'sessiondatesArray' => $sessiondatesArray, 'number_of_hours_planned' => $number_of_hours_planned, 'number_of_dates_planned' => $number_of_dates_planned]);
    }

    public function sdtEnrollments(Request $request, $af_id, $enrollment_type)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Enrollment::where([['af_id', $af_id], ['enrollment_type', $enrollment_type]])->latest();
        /* if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('code', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('title', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('description', 'like', '%'.$request->filter_text.'%');
                }
            }else{
                $datas=Enrollment::orderByDesc('id');
            }
        } */
        $udatas = $datas->orderByDesc('id')->get();
        $urlViewEntity = '/view/entity/';
        foreach ($udatas as $d) {
            $row = array();
            //SUB
            $row[] = '';
            //ID
            $row[] = $d->id;
            //<th>Type</th>
            $entity_id = ($d->entity) ? $d->entity->id : 0;
            $entity_name = ($d->entity) ? $d->entity->name : '';
            $row[] = ($d->entity && $d->entity->entity_type == 'S') ? 'Société' : 'Particulier';
            $ref = ($d->entity) ? $d->entity->ref : '--';
            //Nom
            $spanName = '<div class="text-dark-75 mb-2">' . $ref . '</div>';
            $spanName .= '<div class="text-dark-75 font-weight-bolder mb-2"><a href="' . $urlViewEntity . $entity_id . '">' . $entity_name . '</a></div>';
            $row[] = $spanName;
            //Infos
            $spanPlanif = "";
            //$spanPlanif = $DbHelperTools->getPlanifEnrollment($d->id,$d->action->id);
            $cssClassInscrits = Helper::getCssClassForHoursPlanned($d->members->count(), $d->nb_participants);

            //$spanNbParticipants = '<p class="text-'.$cssClassInscrits.' font-size-xs font-weight-bold mb-1">'.$d->members->count().' stagiaires inscrits / '.$d->nb_participants.' programmés</p>';
            $spanNbParticipants = '<ul class="list-unstyled mb-2">';
            $labelEn = ($d->enrollment_type == 'F') ? 'Intervenants' : 'Participants';
            $spanNbParticipants .= '<li class="font-size-xs font-weight-bold">' . $labelEn . ' : <ul>';
            $spanNbParticipants .= '<li class="text-' . $cssClassInscrits . '"> Inscrits : ' . $d->members->count() . ' / ' . $d->nb_participants . '</li>';
            //$spanNbParticipants .='<li class="text-'.$cssClassInscrits.'"> Planifiés : 0 / '.$d->members->count().'</li>';
            $spanNbParticipants .= '</ul></li></ul>';
            $row[] = $spanNbParticipants . ' ' . $spanPlanif;
            //Tarif
            $row[] = '<p class="font-size-xs font-weight-bold mb-1">' . $d->price . ' € / ' . $DbHelperTools->getNameParamByCode($d->price_type) . '</p>';
            //Date creation
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . (isset($d->created_at) && $d->created_at != null ? $d->created_at->format('d/m/Y H:i') : ''));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . (isset($d->updated_at) && $d->updated_at != null ? $d->updated_at->format('d/m/Y H:i') : ''));
            $row[] = $created_at . $updated_at;
            //Actions
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formEnrollment(' . $d->id . ',1)" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewEnrollment(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            $type = "'ENROLLMENT'";
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteEnrollmentMember(' . $d->id . ',' . $type . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';

            $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                    data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                <a style="cursor: pointer;" onclick="_formEstimate(0,' . $af_id . ',' . $entity_id . ')" class="navi-link"> <span class="navi-icon"><i class="fa fas fa-euro-sign"></i></span> <span class="navi-text">Devis</span> </a>
                            </li>
                        </ul>
                    </div>
                </div>';

            $row[] = $btn_edit . $btn_delete . $btn_more;
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
        ];
        return response()->json($result);
    }

    public function storeUserAccount($request, $role)
    {
        $id = "";
        $result = [];
        $datas = [];
        if (!empty($request->email)) {
            $datas = User::latest()->where('login', 'like', '%' . $request->email . '%')
                ->orWhere('email', 'like', '%' . $request->email . '%');
        }

        $response = $datas->get();

        $data = $request;
        $data["password"] = 'crfpe21*';
        $data["login"] = $request->email;
        $data["active"] = 1;

        if (count($response) > 0) {
            $success = false;
            $msg = 'Cette adresse mail ' . $request->email . ' est déjà utilisé pour un autre compte';
        } else {
            if (!empty($request->get('email'))) {
                $DbHelperTools = new DbHelperTools();
                $user_id = $DbHelperTools->manageUserFromAf($data->toArray(), $id);
                //On supprime si exist
                $DbHelperTools->detachRolesUser($user_id);
                //Add roles
                if (!empty($role)) {
                    $DbHelperTools->attachUserRoles($user_id, $role);
                }
                $success = true;
                $msg = 'L\'utilisateur de cette adresse mail ' . $request->email . 'a été enregistrée avec succès';
            } else {
                $success = false;
                $msg = 'L\'adresse mail de ' . $request->get('firstname') . ' ' . $request->get('lastname') . ' n\'existe pas';
            }
        }


        $result = [
            'success' => $success,
            'msg' => $msg,
        ];
        return $result;
    }


    public function storeUserAccount2($request, $role)
    {

        //dd($request->get('email'));
        $id = "";
        $result = [];
        $datas = [];
        if (!empty($request->get('email'))) {
            $datas = User::where('login', 'like', '%' . $request->get('email') . '%')
                ->orWhere('email', 'like', '%' . $request->get('email') . '%')->get();
        }

        $data = $request;
        $data["password"] = 'crfpe21*';
        $data["login"] = $request->get('email');
        $data["active"] = 1;

        if (count($datas) > 0) {
            $success = false;
            $msg = 'Cette adresse mail ' . $request->get('email') . ' est déjà utilisé pour un autre compte';
        } else {
            if (!empty($request->get('email'))) {
                $DbHelperTools = new DbHelperTools();
                $user_id = $DbHelperTools->manageUserFromAf($data, $id);
                //On supprime si exist
                $DbHelperTools->detachRolesUser($user_id);
                //Add roles
                if (!empty($role)) {
                    $DbHelperTools->attachUserRoles($user_id, $role);
                }
                $success = true;
                $msg = 'L\'utilisateur de cette adresse mail ' . $request->get('email') . 'a été enregistrée avec succès';
            } else {
                $success = false;
                $msg = 'L\'adresse mail de ' . $request->get('firstname') . ' ' . $request->get('lastname') . ' n\'existe pas';
            }
        }


        $result = [
            'success' => $success,
            'msg' => $msg,
        ];
        return $result;
    }

    public function createAccountByAf(Request $request)
    {
        // $datas = Contact::latest()
        $arr = array();
        $i = 0;

        $channel = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/users.log'),
        ]);

        if (($request->count) > 0 || $request->data == 'all') {
            $DbHelperTools = new DbHelperTools();

            if ($request->type == "inscription") {
                if ($request->data == 'all') {
                    $generated = 0;
                    $datas = Contact::select('en_contacts.*')->join('af_members', 'af_members.contact_id', 'en_contacts.id')
                        ->join('af_enrollments', 'af_members.enrollment_id', 'af_enrollments.id')
                        ->where([['af_id', $request->af_id], ['enrollment_type', 'S']])
                        ->get();

                    if (count($datas) > 0) {
                        foreach ($datas as $key => $val) {
                            if ($val != null) {
                                Log::stack(['slack', $channel])->info("-----CREATION COMPTE DU CONTACT: {$val->id} - PAR AF/INSCRIPTION/ALL");
                                $rsp =  $DbHelperTools->storeUserAccountPersonne($val, $request->role, $request->root());
                                if (!$rsp['success']) {
                                    $arr[$i] = $rsp;
                                    $i++;
                                } else {
                                    $generated++;
                                }
                            } else {
                                $result = [
                                    'success' => false,
                                    'msg' => "Les informations de contact ne sont pas valide",
                                ];
                                $arr[$i] = $result;
                                $i++;
                            }
                        }
                        array_unshift($arr, [
                            'success' => true,
                            'msg' => "$generated comptes créés.",
                        ]);
                    } else {
                        $result = [
                            'success' => false,
                            'msg' => "Il n'y a pas de(s) contact(s) pour cette sélection",
                        ];
                        $arr[$i] = $result;
                        $i++;
                    }
                } else {
                    $enrollments = explode(',', $request->data);
                    foreach ($enrollments as $key => $enrollment) {
                        // $resultenrollement = Enrollment::findOrFail($enrollment);
                        $members = Member::where('enrollment_id', $enrollment)->pluck('contact_id');
                        // dd($members);
                        // $contact = Contact::where('entitie_id', $resultenrollement->entitie_id)->get()->toArray();
                        if (count($members) > 0) {
                            $datas = Contact::whereIn('id', $members)->get();
                            // dd($datas);
                            foreach ($datas as $key => $val) {
                                if ($val != null) {
                                    Log::stack(['slack', $channel])->info("-----CREATION COMPTE DU CONTACT: {$val->id} - PAR AF/INSCRIPTION/SELECTED");
                                    $arr[$i] =  $DbHelperTools->storeUserAccountPersonne($val, $request->role, $request->root());
                                    $i++;
                                } else {
                                    $result = [
                                        'success' => false,
                                        'msg' => "Les informations de contact ne sont pas valide",
                                    ];

                                    $arr[$i] = $result;
                                    $i++;
                                }
                            }
                        } else {
                            $result = [
                                'success' => false,
                                'msg' => "Y a pas de(s) membre(s) pour cette sélection",
                            ];

                            $arr[$i] = $result;
                            $i++;
                        }
                    }
                }
            } else if ($request->type == "intervenant") {
                if ($request->data == 'all') {
                    $generated = 0;

                    $datas = Contact::select('en_contacts.*')->join('af_members', 'af_members.contact_id', 'en_contacts.id')
                        ->join('af_enrollments', 'af_members.enrollment_id', 'af_enrollments.id')
                        ->where([['af_id', $request->af_id], ['enrollment_type', 'F']])
                        ->get();
                    if (count($datas) > 0) {
                        foreach ($datas as $key => $val) {
                            if ($val != null) {
                                Log::stack(['slack', $channel])->info("-----CREATION COMPTE DU CONTACT: {$val->id} - PAR AF/INTERVENANT/ALL");
                                $rsp =  $DbHelperTools->storeUserAccountPersonne($val, $request->role, $request->root());
                                if (!$rsp['success']) {
                                    $arr[$i] = $rsp;
                                    $i++;
                                } else {
                                    $generated++;
                                }
                            } else {
                                $result = [
                                    'success' => false,
                                    'msg' => "Y a pas un email pour ce contact",
                                ];

                                $arr[$i] = $result;
                                $i++;
                            }
                        }
                        array_unshift($arr, [
                            'success' => true,
                            'msg' => "$generated comptes créés.",
                        ]);
                    } else {
                        $result = [
                            'success' => false,
                            'msg' => "Y a pas un contact pour cette sélection",
                        ];

                        $arr[$i] = $result;
                        $i++;
                    }
                } else {
                    $idsmembers = explode(',', $request->data);
                    foreach ($idsmembers as $key => $member) {
                        $members = Member::where('id', $member)->whereNotNull('contact_id')->pluck('contact_id');
                        if (count($members) > 0) {
                            $datas = Contact::whereIn('id', $members)->select('*')->get();
                            foreach ($datas as $key => $val) {
                                if ($val != null) {
                                    Log::stack(['slack', $channel])->info("-----CREATION COMPTE DU CONTACT: {$val->id} - PAR AF/INTERVENANT/SELECTED");
                                    $arr[$i] =  $DbHelperTools->storeUserAccountPersonne($val, $request->role, $request->root());
                                    $i++;
                                } else {
                                    $result = [
                                        'success' => false,
                                        'msg' => "Y a pas un email pour ce contact",
                                    ];

                                    $arr[$i] = $result;
                                    $i++;
                                }
                            }
                        } else {
                            $result = [
                                'success' => false,
                                'msg' => "Y a pas un membre pour cette sélection",
                            ];

                            $arr[$i] = $result;
                            $i++;
                        }
                    }
                }
            } else if ($request->type == "personnes") {
                if ($request->data == 'all') {
                    $generated = 0;
                    $contacts = Contact::where('is_active', 1)->take(10)->get();
                    if (count($contacts) > 0) {
                        foreach ($contacts as $key => $contact) {
                            Log::stack(['slack', $channel])->info("-----CREATION COMPTE DU CONTACT: {$contact->id} - PAR PERSONNES/ALL");
                            $rsp =  $DbHelperTools->storeUserAccountPersonne($contact, $request->role, $request->root());
                            if (!$rsp['success']) {
                                $arr[$i] = $rsp;
                                $i++;
                            } else {
                                $generated++;
                            }
                        }
                        array_unshift($arr, [
                            'success' => true,
                            'msg' => "$generated comptes créés.",
                        ]);
                    } else {
                        $result = [
                            'success' => false,
                            'msg' => "Y a pas un contact pour cette sélection",
                        ];

                        $arr[$i] = $result;
                        $i++;
                    }
                } else {
                    $contacts = explode(',', $request->data);
                    if (count($contacts) > 0) {
                        foreach ($contacts as $key => $val) {
                            $contact = Contact::where('id', $val)->first();
                            Log::stack(['slack', $channel])->info("-----CREATION COMPTE DU CONTACT: {$contact->id} - PAR PERSONNES/SELECTED");
                            $arr[$i] =  $DbHelperTools->storeUserAccountPersonne($contact, $request->role, $request->root());
                            $i++;
                        }
                    } else {
                        $result = [
                            'success' => false,
                            'msg' => "Y a pas un contact pour cette sélection",
                        ];

                        $arr[$i] = $result;
                        $i++;
                    }
                }
            }
        }

        return $arr;
    }

    public function formEnrollment($af_id, $row_id, $type)
    {
        /* 
            $type==1 ==> Inscription normale
            $type==2 ==> Fichier parcours sup
        */
        $row = null;
        $nb_unknown_members = 0;
        $price_id = 0;
        if ($type == 2) {
            return view('pages.af.enrollment.stagiaires.form-from-files', ['row' => $row, 'af_id' => $af_id, 'price_id' => $price_id, 'nb_unknown_members' => $nb_unknown_members]);
        }
        if ($row_id > 0) {
            $row = Enrollment::findOrFail($row_id);
            $price = Price::where([['price', $row->price], ['price_type', $row->price_type]])->first();
            $price_id = ($price) ? $price->id : 0;
            $nb_unknown_members = Member::where('enrollment_id', $row_id)->whereNull('contact_id')->get()->count();
        }
        return view('pages.af.enrollment.stagiaires.form', ['row' => $row, 'af_id' => $af_id, 'price_id' => $price_id, 'nb_unknown_members' => $nb_unknown_members]);
    }

    public function storeFormEnrollment(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {

            $DbHelperTools = new DbHelperTools();
            $price = Price::find($request->price_id);

            if ($request->has('file_id')) {
                if ($request->file_id > 0) {
                    $file_contact_ids = FileContact::select('contact_id')->where('file_id', $request->file_id)->pluck('contact_id');
                    $entitie_ids = Contact::select('entitie_id')->whereIn('id', $file_contact_ids)->pluck('entitie_id');
                    //dd($entitie_ids->count());
                    if ($entitie_ids->count() > 0) {
                        foreach ($entitie_ids as $entity_id) {
                            if ($request->id == 0) {
                                $nb_enrollments = Enrollment::select('id')->where([['af_id', $request->af_id], ['entitie_id', $entity_id], ['enrollment_type', 'S']])->count();
                                //dd($nb_enrollments);
                                if ($nb_enrollments == 0) {
                                    $dataEnrollment = array(
                                        'id' => $request->id,
                                        'entitie_id' => $entity_id,
                                        'nb_participants' => 1,
                                        'price' => ($price) ? $price->price : 0,
                                        'price_type' => ($price) ? $price->price_type : null,
                                        'af_id' => $request->af_id,
                                        'enrollment_type' => $request->enrollment_type,
                                    );
                                    $enrollment_id = $DbHelperTools->manageEnrollment($dataEnrollment);
                                    if ($enrollment_id > 0) {
                                        //suprimer tous les stagiaires inscrits
                                        $ids = Member::select('id')->where([['contact_id', '>', 0], ['enrollment_id', $enrollment_id]])->pluck('id');
                                        if (count($ids) > 0) {
                                            $deletedRows = $DbHelperTools->massDeletes($ids, 'member', 1);
                                        }
                                        //contacts    
                                        $contacts_ids = Contact::select('id')->where('entitie_id', $entity_id)->whereIn('id', $file_contact_ids)->pluck('id');
                                        if ($contacts_ids->count() > 0) {
                                            foreach ($contacts_ids as $contact_id) {
                                                $rs_mb = Member::select('id')->where([['contact_id', $contact_id], ['enrollment_id', $enrollment_id]])->first();
                                                $id = ($rs_mb) ? $rs_mb['id'] : 0;
                                                $data_member = array(
                                                    'id' => $id,
                                                    'unknown_contact_name' => null,
                                                    'contact_id' => $contact_id,
                                                    'enrollment_id' => $enrollment_id,
                                                );
                                                $member_id = $DbHelperTools->manageMember($data_member);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $success = true;
                    $msg = 'L\'inscription à été enregistrée avec succès';
                }
                return response()->json([
                    'success' => $success,
                    'msg' => $msg,
                ]);
                exit();
            }
            //dd($request->all());

            //Client déjà inscrit
            if ($request->id == 0 && $request->entitie_id > 0) {
                $nb_enrollments = Enrollment::select('id')->where([['af_id', $request->af_id], ['entitie_id', $request->entitie_id], ['enrollment_type', 'S']])->count();
                if ($nb_enrollments > 0) {
                    $msg = 'Client déjà inscrit !';
                    return response()->json([
                        'success' => $success,
                        'msg' => $msg,
                    ]);
                }
            }
            $dataEnrollment = array(
                'id' => $request->id,
                'entitie_id' => $request->entitie_id,
                'nb_participants' => $request->nb_participants,
                'price' => ($price) ? $price->price : 0,
                'price_type' => ($price) ? $price->price_type : null,
                'af_id' => $request->af_id,
                'enrollment_type' => $request->enrollment_type,
            );
            $enrollment_id = $DbHelperTools->manageEnrollment($dataEnrollment);
            if ($enrollment_id > 0) {
                //suprimer tous les stagiaires inscrits
                $ids = Member::select('id')->where([['contact_id', '>', 0], ['enrollment_id', $enrollment_id]])->pluck('id');
                if (count($ids) > 0) {
                    $deletedRows = $DbHelperTools->massDeletes($ids, 'member', 1);
                }
                if ($request->has('members')) {
                    if (count($request->members) > 0) {
                        foreach ($request->members as $contact_id) {
                            $rs_mb = Member::select('id')->where([['contact_id', $contact_id], ['enrollment_id', $enrollment_id]])->first();
                            $id = ($rs_mb) ? $rs_mb['id'] : 0;
                            $data_member = array(
                                'id' => $id,
                                'unknown_contact_name' => null,
                                'contact_id' => $contact_id,
                                'enrollment_id' => $enrollment_id,
                            );
                            $member_id = $DbHelperTools->manageMember($data_member);
                        }
                    }
                }
                //Uknown members
                if ($request->has('nb_unknown_members')) {
                    if ($request->nb_unknown_members > 0) {
                        $nb_unknown_members = $request->nb_unknown_members;
                        for ($i = 1; $i <= $nb_unknown_members; $i++) {
                            $name = 'STAGIAIRE-N' . $i;
                            $rs = Member::select('id')->where([['unknown_contact_name', $name], ['enrollment_id', $enrollment_id]])->whereNull('contact_id')->first();
                            $id = ($rs) ? $rs['id'] : 0;
                            $data_member = array(
                                'id' => $id,
                                'unknown_contact_name' => $name,
                                'contact_id' => null,
                                'enrollment_id' => $enrollment_id,
                            );
                            $member_id = $DbHelperTools->manageMember($data_member);
                        }
                    }
                }

                $success = true;
                $msg = 'L\'inscription à été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function deleteEnrollment($enrollment_id)
    {
        /**
         * forceDelete
         */
        $success = false;
        $DbHelperTools = new DbHelperTools();
        if ($enrollment_id) {
            $ids_members = Member::where('enrollment_id', $enrollment_id)->get()->pluck('id');
            if (count($ids_members) > 0) {
                DB::table('af_schedulecontacts')->whereIn('member_id', $ids_members)->delete();
                $deletedRows = $DbHelperTools->massDeletes($ids_members, 'member', 1);
            }
            $deletedRows = $DbHelperTools->massDeletes([$enrollment_id], 'enrollment', 1);
            if ($deletedRows)
                $success = true;
        }
        return response()->json(['success' => $success]);
    }

    public function getMembers($enrollment_id)
    {
        $enrollment = $members = null;
        if ($enrollment_id > 0) {
            $enrollment = Enrollment::findOrFail($enrollment_id);
            $members = Member::where('enrollment_id', $enrollment_id)->get();
        }
        return view('pages.af.enrollment.members', ['members' => $members, 'enrollment' => $enrollment]);
    }

    public function deleteMember($member_id)
    {
        $success = false;
        $DbHelperTools = new DbHelperTools();
        if ($member_id) {
            //af_schedulecontacts
            DB::table('af_schedulecontacts')->where('member_id', $member_id)->delete();
            $deletedRows = $DbHelperTools->massDeletes([$member_id], 'member', 1);
            if ($deletedRows)
                $success = true;
        }
        return response()->json(['success' => $success]);
    }

    public function deleteScheduleContact($schedulecontact_id)
    {
        $success = false;
        $DbHelperTools = new DbHelperTools();
        if ($schedulecontact_id) {
            $schedulecontact = Schedulecontact::find($schedulecontact_id);
            $schedulemember = Member::find($schedulecontact->member_id);
            $schedulecontactinfo = Contact::find($schedulemember->contact_id);
            $start_hour = $schedulecontact->schedule->start_hour;
            $end_hour = $schedulecontact->schedule->end_hour;
            $session = $schedulecontact->schedule->sessiondate->session;
            $af = $schedulecontact->schedule->sessiondate->session->af;
           
            if($start_hour){
                $start_time = Carbon::parse($start_hour)->format('H:i');
                $start_date = Carbon::parse($start_hour)->format('Y-m-d');
            }
            if($end_hour){
                $end_time = Carbon::parse($end_hour)->format('H:i');
                $end_date = Carbon::parse($end_hour)->format('Y-m-d');
            }

            if($session){
                $title_session=$session->title;
            }

            if($af){
                $num_af=$session->code;
                $intitule_af=$session->title;
            }
         
            /* Send Mail */
            $fullname = ucfirst($schedulecontactinfo->firstname ?? '') . ' ' . ucfirst($schedulecontactinfo->lastname ?? '');
            $content = "Nous vous informons que le formateur <b>$fullname</b> a été supprimé pour l'Af portant le numéro <b>$num_af</b>, l'intitulé <b>$intitule_af</b> ainsi que de la session <b>$title_session</b> <br/> - Date de début : $start_date<br/> - Date de fin : $end_date<br/> - Heure de début : $start_time<br/> - Heure de fin : $end_time .<br/><br/>";
            $content .= "<b>Suppression du formateur pour une séance</b><br/>";
            $header = "Environnement de formation pour CRFPE";
            $footer = "Plateforme de formation SOLARIS";
            
            Mail::send('pages.email.model', ['htmlMain' => $content, 'htmlHeader' => $header, 'htmlFooter' => $footer], function ($m){
                $m->from(auth()->user()->email);
                $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
                $m->to('severinebernaert@crfpe.fr')->subject('SOLARIS : Suppression du formateur pour une séance');
            });
       
            $deletedRows = $DbHelperTools->massDeletes([$schedulecontact_id], 'schedulecontact', 1);
            if ($deletedRows)
                $success = true;
                
        }
        return response()->json(['success' => $success]);
    }

    public function deleteScheduleGroup($schedulecontact_id, $schedulegroup_id)
    {
        $success = false;
        $members_ids = Member::select('id')->where('group_id', $schedulegroup_id)->pluck('id')->toArray();
        $DbHelperTools = new DbHelperTools();
        if ($members_ids) {
            // $deletedRows = $DbHelperTools->deleteScheduleGroup([$schedulecontact_id], 'schedulecontact', 1);
            $deletedRows = Schedulecontact::whereIn('member_id', $members_ids)->where('schedule_id', $schedulecontact_id)->forceDelete();
            if ($deletedRows)
                $success = true;
        }
        return response()->json(['success' => $success]);
    }

    public function treeSchedulesContacts($af_id, $mode)
    {
        //$mode : withcontacts or nocontacts
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $tab = $DbHelperTools->queryBuilderForTree($af_id, 0, 0, 0, 0, null, null);
        $datas = [];
        if (count($tab) > 0) {
            foreach ($tab as $session_id => $session_datas) {
                //dd($session_datas);
                $datas[] = array(
                    "id" => $session_id,
                    "text" => $session_datas['title'] . ' (' . $session_datas['code'] . ')',
                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                    "icon" => "fa fa-folder text-info",
                    "parent" => '#'
                );
                $sessiondates = $session_datas['sessiondates'];
                if (count($sessiondates) > 0) {
                    foreach ($sessiondates as $sessiondate_id => $date_datas) {
                        $planning_date = (isset($date_datas['planning_date']) && !empty($date_datas['planning_date'])) ? Carbon::createFromFormat('Y-m-d', $date_datas['planning_date']) : null;
                        $datas[] = array(
                            "id" => 'D' . $sessiondate_id,
                            "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "icon" => "fa fa-calendar text-primary",
                            "parent" => $session_id
                        );
                        $schedules = $date_datas['schedules'];
                        if (count($schedules) > 0) {
                            foreach ($schedules as $schedule_id => $schedule_datas) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule_datas['start_hour']);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule_datas['end_hour']);
                                $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');
                                $duration = Helper::convertTime($schedule_datas['duration']);
                                $checkbox_disabled = false;
                                $datas[] = array(
                                    "id" => 'SCHEDULE' . $schedule_id,
                                    "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                    "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                    "icon" => "fa fa-folder text-dark",
                                    "parent" => 'D' . $sessiondate_id
                                );
                                if ($mode == 'withcontacts') {
                                    $schedulecontacts = $schedule_datas['schedulecontacts'];
                                    if (count($schedulecontacts) > 0) {
                                        //Les intervenants
                                        $datas[] = array(
                                            "id" => 'TITLE_FORMERS' . $schedule_id,
                                            "text" => '<span class="text-warning">Liste des intervenants : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule_id
                                        );
                                        foreach ($schedulecontacts as $schedulecontact_id => $schedulecontact_datas) {
                                            if ($schedulecontact_datas['is_former'] == 1) {
                                                /* if($schedulecontact_datas['entity_type']=='P'){
                                                    $entityName = $schedulecontact_datas['entity_type'] . ': ' .  $schedulecontact_datas['entity_ref'];
                                                }else{
                                                    $entityName = $schedulecontact_datas['entity_type'] . ': ' . $schedulecontact_datas['entity_name'] . ' (' . $schedulecontact_datas['entity_ref'] . ')';
                                                }
                                                $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>'; */

                                                $price = '';
                                                $type_former_intervention = $schedulecontact_datas['contact_type_former_intervention'];
                                                $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule_datas['duration'], $schedulecontact_datas['price'], $type_former_intervention);
                                                $total_cost = ($scf_total_cost > 0) ? ' - coût total : ' . $scf_total_cost . ' €' : '';
                                                if ($schedulecontact_datas['price'] > 0) {
                                                    $price = $schedulecontact_datas['price'] . ' €/' . $DbHelperTools->getNameParamByCode($schedulecontact_datas['price_type']) . $total_cost;
                                                } else {
                                                    $price = $total_cost;
                                                }
                                                $contractNumber = ($schedulecontact_datas['contract_id'] > 0) ? (' (' . $schedulecontact_datas['contractNumber'] . ')') : '';
                                                $type_of_intervention = ($schedulecontact_datas['type_of_intervention']) ? (' - Type : ' . $DbHelperTools->getNameParamByCode($schedulecontact_datas['type_of_intervention'])) : '';
                                                $spanMemberName = ($schedulecontact_datas['contact_id'] > 0) ? ('<span class="text-dark">' . ($schedulecontact_datas['contact_firstname'] . ' ' . $schedulecontact_datas['contact_lastname']) . '<span>') : '';
                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $schedulecontact_id . ')" data-toggle="tooltip" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                $type_of_intervention = $type_former_intervention;
                                                $intervention = $contractNumber . $type_of_intervention;
                                                $iPrice = ($price) ? $price : '';
                                                $btnInfosIntervenant = $btnRemuneration = $infosIntervenant = '';
                                                if (!$type_of_intervention != 'Interne') {
                                                    $btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration(' . $af_id . ',' . $schedulecontact_datas['member_id'] . ')" data-toggle="tooltip" title="Rémunération"><i class="' . $tools->getIconeByAction('PRICE') . ' text-success"></i></a>';
                                                    $btnInfosIntervenant = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $intervention . ' - ' . $iPrice . '"><i class="fas fa-info-circle text-' . ($iPrice ? 'primary' : 'warning') . '"></i></a>';
                                                }
                                                $datas[] = array(
                                                    "id" => 'SCONTACT' . $schedulecontact_id,
                                                    "text" => $spanMemberName . $btnRemuneration . $btnDelete . $btnInfosIntervenant,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                    "icon" => "far fa-id-badge text-dark",
                                                    "parent" => 'SCHEDULE' . $schedule_id
                                                );
                                            }
                                        }
                                        //Les stagiaires
                                        $datas[] = array(
                                            "id" => 'TITLE_STAGIAIRES' . $schedule_id,
                                            "text" => '<span class="text-warning">Liste des groupes et stagiaires : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule_id
                                        );
                                        foreach ($schedulecontacts as $schedulecontact_id => $schedulecontact_datas) {
                                            if ($schedulecontact_datas['is_former'] == 0) {
                                                if ($schedulecontact_datas['group_id'] == null) {
                                                    $entity_type = $schedulecontact_datas['entity_type'];
                                                    if ($schedulecontact_datas['entity_type'] == 'P') {
                                                        $entityName = $schedulecontact_datas['entity_type'] . ': ' .  $schedulecontact_datas['entity_ref'];
                                                    } else {
                                                        $entityName = $schedulecontact_datas['entity_type'] . ': ' . $schedulecontact_datas['entity_name'] . ' (' . $schedulecontact_datas['entity_ref'] . ')';
                                                    }
                                                    $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';

                                                    $member_name = ($schedulecontact_datas['contact_id'] > 0) ? ($schedulecontact_datas['contact_firstname'] . ' ' . $schedulecontact_datas['contact_lastname']) : $schedulecontact_datas['unknown_contact_name'];
                                                    $textContact = $member_name . ' - ' . $entity_type;

                                                    $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $schedulecontact_id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                    $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';

                                                    $datas[] = array(
                                                        "id" => 'SCONTACT' . $schedulecontact_id,
                                                        "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                        "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                        "icon" => "fa fa-user text-dark",
                                                        "parent" => 'SCHEDULE' . $schedule_id
                                                    );
                                                }
                                            }
                                        }
                                        //les membres des groupes
                                        $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
                                        $groups = Group::select('id', 'title')->where('af_id', $af_id)->get();
                                        foreach ($groups as $gp) {

                                            $members_ids = Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->where('group_id', $gp->id)->pluck('id');
                                            $rs_schedulecontactsGroup = Schedulecontact::select('id', 'member_id', 'price', 'price_type', 'contract_id', 'type_of_intervention')->whereIn('member_id', $members_ids)->where('schedule_id', $schedule_id)->get();
                                            if (count($rs_schedulecontactsGroup) > 0) {
                                                $datas[] = array(
                                                    "id" => 'GROUP' . $gp->id . '-' . $schedule_id,
                                                    "text" => '<span class="text-primary">' . $gp->title . '</span>',
                                                    "state" => array('opened' => false, 'checkbox_disabled' => true),
                                                    "icon" => "fa fa-users text-primary",
                                                    "parent" => 'SCHEDULE' . $schedule_id
                                                );
                                                foreach ($rs_schedulecontactsGroup as $sc) {
                                                    $entity_type = $sc->member->enrollment->entity->entity_type;
                                                    if ($sc->member->enrollment->entity->entity_type == 'P') {
                                                        $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                    } else {
                                                        $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                    }

                                                    $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                    $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                    $textContact = $member_name . ' - ' . $entity_type;
                                                    $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                    $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';
                                                    $datas[] = array(
                                                        "id" => 'SCONTACT' . $sc->id,
                                                        "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                        "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                        "icon" => "fa fa-user text-dark",
                                                        "parent" => 'GROUP' . $gp->id . '-' . $schedule_id
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        //dd($datas);
        return response()->json($datas);


        //Start Old version
        $datas = [];
        $sessions = Session::select('id', 'title', 'code')->where('af_id', $af_id)->where('is_evaluation', 0)->orderBy('started_at', 'asc')->get();
        if (count($sessions) > 0) {
            foreach ($sessions as $session) {
                //Session
                $datas[] = array(
                    "id" => $session->id,
                    "text" => $session->title . ' (' . $session->code . ')',
                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                    "icon" => "fa fa-folder text-info",
                    "parent" => '#'
                );
                //Session dates
                $sessiondates = Sessiondate::select('id', 'planning_date')->where('session_id', $session->id)->get();
                if (count($sessiondates) > 0) {
                    foreach ($sessiondates as $sd) {
                        //$planning_date = Carbon::createFromFormat('Y-m-d',$sd->planning_date);
                        $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                        $datas[] = array(
                            "id" => 'D' . $sd->id,
                            "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "icon" => "fa fa-calendar text-primary",
                            "parent" => $session->id
                        );
                        //Schedules : séances
                        $rs_schedules = Schedule::select('id', 'start_hour', 'end_hour', 'duration')->where('sessiondate_id', $sd->id)->get();
                        if (count($rs_schedules) > 0) {
                            foreach ($rs_schedules as $schedule) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');

                                $duration = Helper::convertTime($schedule->duration);

                                //Intervenants
                                $checkbox_disabled = false;
                                $datas[] = array(
                                    "id" => 'SCHEDULE' . $schedule->id,
                                    "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                    "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                    "icon" => "fa fa-folder text-dark",
                                    "parent" => 'D' . $sd->id
                                );
                                //Schedulecontacts : les inscrits
                                if ($mode == 'withcontacts') {
                                    $schedulecontacts_array = $DbHelperTools->getSchedulesContactsByScheduleId($schedule->id);
                                    //dd($schedulecontacts_array);
                                    //Intervenants
                                    $rs_schedulecontacts_formers = (array_key_exists('FORMERS', $schedulecontacts_array)) ? $schedulecontacts_array['FORMERS'] : [];
                                    //$rs_schedulecontacts_formers = Schedulecontact::select('id','member_id','price','price_type','contract_id','type_of_intervention')->where([['schedule_id', $schedule->id], ['is_former', 1]])->get();
                                    if (count($rs_schedulecontacts_formers) > 0) {
                                        $datas[] = array(
                                            "id" => 'TITLE_FORMERS' . $schedule->id,
                                            "text" => '<span class="text-warning">Liste des intervenants : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                        foreach ($rs_schedulecontacts_formers as $scf) {
                                            if ($scf->member->enrollment->entity->entity_type == 'P') {
                                                $entityName = $scf->member->enrollment->entity->entity_type . ': ' .  $scf->member->enrollment->entity->ref;
                                            } else {
                                                $entityName = $scf->member->enrollment->entity->entity_type . ': ' . $scf->member->enrollment->entity->name . ' (' . $scf->member->enrollment->entity->ref . ')';
                                            }
                                            $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';
                                            $price = '';
                                            $type_former_intervention = $scf->member->contact->type_former_intervention;
                                            $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule->duration, $scf->price, $type_former_intervention);
                                            $total_cost = ($scf_total_cost > 0) ? ' - coût total : ' . $scf_total_cost . ' €' : '';
                                            if ($scf->price > 0) {
                                                $price = $scf->price . ' €/' . $DbHelperTools->getNameParamByCode($scf->price_type) . $total_cost;
                                            } else {
                                                $price = $total_cost;
                                            }
                                            $contractNumber = ($scf->contract_id > 0) ? (' (' . $scf->contract->number . ')') : '';
                                            $type_of_intervention = ($scf->type_of_intervention) ? (' - Type : ' . $DbHelperTools->getNameParamByCode($scf->type_of_intervention)) : '';

                                            //$pTypeIntervention = '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $scf->member->contact->type_former_intervention . $contractNumber . $type_of_intervention . '</p>';
                                            //$pPrice = ($price) ? '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $price . '</p>' : '';


                                            $spanMemberName = ($scf->member->contact) ? ('<span class="text-dark">' . ($scf->member->contact->firstname . ' ' . $scf->member->contact->lastname) . '<span>') : '';
                                            $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $scf->id . ')" data-toggle="tooltip" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                            $type_of_intervention = $scf->member->contact->type_former_intervention;
                                            $intervention = $type_of_intervention . $contractNumber . $type_of_intervention;
                                            $iPrice = ($price) ? $price : '';
                                            $btnInfosIntervenant = '';
                                            $btnRemuneration = '';
                                            $infosIntervenant = '';
                                            if (!$type_of_intervention != 'Interne') {
                                                $btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration(' . $af_id . ',' . $scf->member_id . ')" data-toggle="tooltip" title="Rémunération"><i class="' . $tools->getIconeByAction('PRICE') . ' text-success"></i></a>';

                                                $btnInfosIntervenant = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $intervention . ' - ' . $iPrice . '"><i class="fas fa-info-circle text-' . ($iPrice ? 'primary' : 'warning') . '"></i></a>';
                                            }
                                            $datas[] = array(
                                                "id" => 'SCONTACT' . $scf->id,
                                                "text" => $spanMemberName . $btnRemuneration . $btnDelete . $btnInfosIntervenant,
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "far fa-id-badge text-dark",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                        }
                                    }
                                    //Les stagiaires inscrits non appartient a un group
                                    $rs_schedulecontacts = (array_key_exists('STUDENTS', $schedulecontacts_array)) ? $schedulecontacts_array['STUDENTS'] : [];
                                    //$rs_schedulecontacts = Schedulecontact::select('id','member_id','price','price_type','contract_id','type_of_intervention')->where([['schedule_id', $schedule->id], ['is_former', 0]])->get();
                                    if (count($rs_schedulecontacts) > 0) {
                                        $datas[] = array(
                                            "id" => 'TITLE_STAGIAIRES' . $schedule->id,
                                            "text" => '<span class="text-warning">Liste des groupes et stagiaires : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                        foreach ($rs_schedulecontacts as $sc) {
                                            if ($sc->member->group_id == null) {

                                                $entity_type = $sc->member->enrollment->entity->entity_type;

                                                if ($sc->member->enrollment->entity->entity_type == 'P') {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                } else {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                }

                                                $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                $textContact = $member_name . ' - ' . $entity_type;

                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';

                                                $datas[] = array(
                                                    "id" => 'SCONTACT' . $sc->id,
                                                    "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                    "icon" => "fa fa-user text-dark",
                                                    "parent" => 'SCHEDULE' . $schedule->id
                                                );
                                            }
                                        }
                                    }
                                    //les membres des groupes
                                    $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
                                    $groups = Group::select('id', 'title')->where('af_id', $af_id)->get();
                                    foreach ($groups as $gp) {

                                        $members_ids = Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->where('group_id', $gp->id)->pluck('id');
                                        $rs_schedulecontactsGroup = Schedulecontact::select('id', 'member_id', 'price', 'price_type', 'contract_id', 'type_of_intervention')->whereIn('member_id', $members_ids)->where('schedule_id', $schedule->id)->get();
                                        //where([['schedule_id', $schedule->id], ['is_former', 1]])
                                        if (count($rs_schedulecontactsGroup) > 0) {
                                            $datas[] = array(
                                                "id" => 'GROUP' . $gp->id . '-' . $schedule->id,
                                                "text" => '<span class="text-primary">' . $gp->title . '</span>',
                                                "state" => array('opened' => false, 'checkbox_disabled' => true),
                                                "icon" => "fa fa-users text-primary",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                            foreach ($rs_schedulecontactsGroup as $sc) {
                                                $entity_type = $sc->member->enrollment->entity->entity_type;
                                                if ($sc->member->enrollment->entity->entity_type == 'P') {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                } else {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                }

                                                $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                $textContact = $member_name . ' - ' . $entity_type;
                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';
                                                $datas[] = array(
                                                    "id" => 'SCONTACT' . $sc->id,
                                                    "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                    "icon" => "fa fa-user text-dark",
                                                    "parent" => 'GROUP' . $gp->id . '-' . $schedule->id
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return response()->json($datas);
        //End Old version
    }


    public function treeSchedulesContactsFilter(Request $request, $af_id, $mode)
    {
        $userid = auth()->user()->id;
        $roles = auth()->user()->roles;
        $contactid = auth()->user()->contact_id;

        //$mode : withcontacts or nocontacts
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $sessions = $start = $end = null;
        $session_id = $group_id = $member_id = $member_former_id = 0;
        if ($request->isMethod('post')) {

            if ($request->has('session_id')) {
                if ($request->session_id > 0) {
                    $session_id = (int) $request->session_id;
                }
            }

            if ($request->has('group_id')) {
                $group_id = (int) $request->group_id;
            }

            if ($request->has('member_id')) {
                $member_id = (int) $request->member_id;
            }

            if ($request->has('inter_id')) {
                $member_former_id = (int) $request->inter_id;
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $d1 = Carbon::createFromFormat('d/m/Y', $request->start_date);
                    $d2 = Carbon::createFromFormat('d/m/Y', $request->end_date);
                    $start = $d1->format('Y-m-d');
                    $end = $d2->format('Y-m-d');
                }
            }
        }

        if ($roles[0]->code == 'FORMATEUR') {
            $tab = $DbHelperTools->queryBuilderForTreeFrm($af_id, $session_id, $group_id, $member_id, $member_former_id, $start, $end);
        } else {
            $tab = $DbHelperTools->queryBuilderForTree($af_id, $session_id, $group_id, $member_id, $member_former_id, $start, $end);
        }

        // dd($tab);

        $datas = [];
        if (count($tab) > 0) {
            $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
            foreach ($tab as $session_id => $session_datas) {
                $datas[] = array(
                    "id" => $session_id,
                    "text" => $session_datas['title'] . ' (' . $session_datas['code'] . ')',
                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                    "icon" => "fa fa-folder text-info",
                    "parent" => '#'
                );
                $sessiondates = $session_datas['sessiondates'];

                $groupsIds = $session_datas['groupsIds'];
                $groups = Group::select('id', 'title')->whereIn('id', $groupsIds)->get();
                if (count($sessiondates) > 0) {
                    foreach ($sessiondates as $sessiondate_id => $date_datas) {
                        $planning_date = (isset($date_datas['planning_date']) && !empty($date_datas['planning_date'])) ? Carbon::createFromFormat('Y-m-d', $date_datas['planning_date']) : null;
                        $datas[] = array(
                            "id" => 'D' . $sessiondate_id,
                            "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "icon" => "fa fa-calendar text-primary",
                            "parent" => $session_id
                        );
                        $schedules = $date_datas['schedules'];
                        if (count($schedules) > 0) {
                            foreach ($schedules as $schedule_id => $schedule_datas) {
                                //dd($schedule_datas);
                                //$start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule_datas['start_hour']);
                                //$end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule_datas['end_hour']);
                                //$text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');
                                $text = $schedule_datas['start_hour']->format('H') . 'h' . $schedule_datas['start_hour']->format('i') . ' - ' . $schedule_datas['end_hour']->format('H') . 'h' . $schedule_datas['end_hour']->format('i');
                                $duration = Helper::convertTime($schedule_datas['duration']);
                                $checkbox_disabled = false;
                                $datas[] = array(
                                    "id" => 'SCHEDULE' . $schedule_id,
                                    "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                    "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                    "icon" => "fa fa-folder text-dark",
                                    "parent" => 'D' . $sessiondate_id
                                );

                                if ($mode == 'withcontacts') {
                                    $schedulecontacts = $schedule_datas['schedulecontacts'];
                                    //dump($schedulecontacts);
                                    if (count($schedulecontacts) > 0) {
                                        //Les intervenants
                                        $datas[] = array(
                                            "id" => 'TITLE_FORMERS' . $schedule_id,
                                            "text" => '<span class="text-warning">Liste des intervenants : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule_id
                                        );
                                        //if(false){
                                        foreach ($schedulecontacts as $schedulecontact_id => $schedulecontact_datas) {
                                            //dd($schedulecontact_datas);
                                            if ($roles[0]->code == 'FORMATEUR') {
                                                if ($schedulecontact_datas['is_former'] == 1 && $schedulecontact_datas['contact_id'] == $contactid) {
                                                    $price = '';
                                                    $type_former_intervention = $schedulecontact_datas['contact_type_former_intervention'];
                                                    $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule_datas['duration'], $schedulecontact_datas['price'], $type_former_intervention);
                                                    $total_cost = ($scf_total_cost > 0) ? ' - coût total : ' . $scf_total_cost . ' €' : '';
                                                    if ($schedulecontact_datas['price'] > 0) {
                                                        $price = $schedulecontact_datas['price'] . ' €/' . $DbHelperTools->getNameParamByCode($schedulecontact_datas['price_type']) . $total_cost;
                                                    } else {
                                                        $price = $total_cost;
                                                    }
                                                    $contractNumber = ($schedulecontact_datas['contract_id'] > 0) ? (' (' . $schedulecontact_datas['contractNumber'] . ')') : '';
                                                    $type_of_intervention = ($schedulecontact_datas['type_of_intervention']) ? (' - Type : ' . $DbHelperTools->getNameParamByCode($schedulecontact_datas['type_of_intervention'])) : '';
                                                    $spanMemberName = ($schedulecontact_datas['contact_id'] > 0) ? ('<span class="text-dark">' . ($schedulecontact_datas['contact_firstname'] . ' ' . $schedulecontact_datas['contact_lastname']) . '<span>') : '';
                                                    //dd($spanMemberName);
                                                    $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $schedulecontact_id . ')" data-toggle="tooltip" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                    $type_of_intervention = $type_former_intervention;
                                                    $intervention = $contractNumber . $type_of_intervention;
                                                    $iPrice = ($price) ? $price : '';
                                                    $btnInfosIntervenant = $btnRemuneration = $infosIntervenant = '';
                                                    if (!$type_of_intervention != 'Interne') {
                                                        $btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration(' . $af_id . ',' . $schedulecontact_datas['member_id'] . ')" data-toggle="tooltip" title="Rémunération"><i class="' . $tools->getIconeByAction('PRICE') . ' text-success"></i></a>';
                                                        $btnInfosIntervenant = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $intervention . ' - ' . $iPrice . '"><i class="fas fa-info-circle text-' . ($iPrice ? 'primary' : 'warning') . '"></i></a>';
                                                    }
                                                    $pointage = $DbHelperTools->getPointingInfos($schedulecontact_datas['pointing']);

                                                    $btnPointage = '<a style="cursor: pointer;" class="ml-2 mr-2" data-toggle="tooltip" title="Pointage" onclick="_formPointage(' . $schedulecontact_id . ')"><i class="fas fa-edit text-primary"></i></a>';
                                                    if ($roles[0]->code == 'FORMATEUR') {
                                                        $datas[] = array(
                                                            "id" => 'SCONTACT' . $schedulecontact_id,
                                                            "text" => $spanMemberName . $btnInfosIntervenant . $pointage . $btnPointage,
                                                            //"text" => 'inter',
                                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                            "icon" => "far fa-id-badge text-dark",
                                                            "parent" => 'SCHEDULE' . $schedule_id
                                                        );
                                                    } else {
                                                        $datas[] = array(
                                                            "id" => 'SCONTACT' . $schedulecontact_id,
                                                            "text" => $spanMemberName . $btnRemuneration . $btnDelete . $btnInfosIntervenant . $pointage . $btnPointage,
                                                            //"text" => 'inter',
                                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                            "icon" => "far fa-id-badge text-dark",
                                                            "parent" => 'SCHEDULE' . $schedule_id
                                                        );
                                                    }
                                                    //dd($datas);
                                                }
                                            } else {
                                                if ($schedulecontact_datas['is_former'] == 1) {
                                                    $price = '';
                                                    $type_former_intervention = $schedulecontact_datas['contact_type_former_intervention'];
                                                    $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule_datas['duration'], $schedulecontact_datas['price'], $type_former_intervention);
                                                    $total_cost = ($scf_total_cost > 0) ? ' - coût total : ' . $scf_total_cost . ' €' : '';
                                                    
                                                    if ($schedulecontact_datas['price'] > 0) {
                                                        $price = $schedulecontact_datas['price'] . ' €/' . $DbHelperTools->getNameParamByCode($schedulecontact_datas['price_type']) . $total_cost;
                                                    } else{
                                                        $price = $total_cost;
                                                    }
                                                    $contractNumber = ($schedulecontact_datas['contract_id'] > 0) ? (' (' . $schedulecontact_datas['contractNumber'] . ')') : '';
                                                    $type_of_intervention = ($schedulecontact_datas['type_of_intervention']) ? (' - Type : ' . $DbHelperTools->getNameParamByCode($schedulecontact_datas['type_of_intervention'])) : '';
                                                    $spanMemberName = ($schedulecontact_datas['contact_id'] > 0) ? ('<span class="text-dark">' . ($schedulecontact_datas['contact_firstname'] . ' ' . $schedulecontact_datas['contact_lastname']) . '<span>') : '';
                                                    //dd($spanMemberName);
                                                    $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $schedulecontact_id . ')" data-toggle="tooltip" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                    $type_of_intervention = $type_former_intervention;
                                                    $intervention = $contractNumber . $type_of_intervention;
                                                    $iPrice =  $schedulecontact_datas['price'] ;
                                                    $btnInfosIntervenant = $btnRemuneration = $infosIntervenant = '';
                                                    if (!$type_of_intervention != 'Interne') {
                                                        $btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration(' . $af_id . ',' . $schedulecontact_datas['member_id'] . ')" data-toggle="tooltip" title="Rémunération"><i class="' . $tools->getIconeByAction('PRICE') . ' text-success"></i></a>';
                                                        $btnInfosIntervenant = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $intervention . ' - ' . $iPrice . '"><i class="fas fa-info-circle text-' . ($iPrice ? 'primary' : 'warning') . '"></i></a>';
                                                    }
                                                    $pointage = $DbHelperTools->getPointingInfos($schedulecontact_datas['pointing']);

                                                    $btnPointage = '<a style="cursor: pointer;" class="ml-2 mr-2" data-toggle="tooltip" title="Pointage" onclick="_formPointage(' . $schedulecontact_id . ')"><i class="fas fa-edit text-primary"></i></a>';
                                                    if ($roles[0]->code == 'FORMATEUR') {
                                                        $datas[] = array(
                                                            "id" => 'SCONTACT' . $schedulecontact_id,
                                                            "text" => $spanMemberName . $btnRemuneration . $btnInfosIntervenant . $pointage . $btnPointage,
                                                            //"text" => 'inter',
                                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                            "icon" => "far fa-id-badge text-dark",
                                                            "parent" => 'SCHEDULE' . $schedule_id
                                                        );
                                                    } else {
                                                        $datas[] = array(
                                                            "id" => 'SCONTACT' . $schedulecontact_id,
                                                            "text" => $spanMemberName . $btnRemuneration . $btnDelete . $btnInfosIntervenant . $pointage . $btnPointage,
                                                            //"text" => 'inter',
                                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                            "icon" => "far fa-id-badge text-dark",
                                                            "parent" => 'SCHEDULE' . $schedule_id
                                                        );
                                                    }
                                                    //dd($datas);
                                                }
                                            }
                                        }

                                        //Les stagiaires inscrits non appartient a un group
                                        $datas[] = array(
                                            "id" => 'TITLE_STAGIAIRES' . $schedule_id,
                                            "text" => '<span class="text-warning">Liste des groupes et stagiaires : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule_id
                                        );
                                        $rs_schedulecontactsGroupIds = [];
                                        foreach ($schedulecontacts as $schedulecontact_id => $schedulecontact_datas) {

                                            if ($schedulecontact_datas['is_former'] == 0) {
                                                if ($schedulecontact_datas['group_id'] == null) {
                                                    $entity_type = $schedulecontact_datas['entity_type'];
                                                    if ($schedulecontact_datas['entity_type'] == 'P') {
                                                        $entityName = $schedulecontact_datas['entity_type'] . ': ' .  $schedulecontact_datas['entity_ref'];
                                                    } else {
                                                        $entityName = $schedulecontact_datas['entity_type'] . ': ' . $schedulecontact_datas['entity_name'] . ' (' . $schedulecontact_datas['entity_ref'] . ')';
                                                    }
                                                    //$spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';

                                                    $member_name = ($schedulecontact_datas['contact_id'] > 0) ? ($schedulecontact_datas['contact_firstname'] . ' ' . $schedulecontact_datas['contact_lastname']) : $schedulecontact_datas['unknown_contact_name'];
                                                    $textContact = $member_name . ' - ' . $entity_type;

                                                    $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $schedulecontact_id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                    $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';
                                                    if ($roles[0]->code == 'FORMATEUR') {
                                                        $datas[] = array(
                                                            "id" => 'SCONTACT' . $schedulecontact_id,
                                                            "text" => $textContact . $btnInfosEntity,
                                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                            "icon" => "fa fa-user text-dark",
                                                            "parent" => 'SCHEDULE' . $schedule_id
                                                        );
                                                    } else {
                                                        $datas[] = array(
                                                            "id" => 'SCONTACT' . $schedulecontact_id,
                                                            "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                            "icon" => "fa fa-user text-dark",
                                                            "parent" => 'SCHEDULE' . $schedule_id
                                                        );
                                                    }
                                                }
                                            }

                                            if ($schedulecontact_datas['group_id'] > 0) {
                                                foreach ($groups as $gp) {
                                                    if ($schedulecontact_datas['group_id'] == $gp->id) {
                                                        $rs_schedulecontactsGroupIds[$gp->id][] = $schedulecontact_datas;
                                                    }
                                                }
                                            }
                                        }
                                        //les membres des groupes
                                        //if(false){
                                        //$ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
                                        //$groups=Group::select('id','title')->where('af_id',$af_id)->get();
                                        //dump($groups);
                                        foreach ($groups as $gp) {
                                            $rs_schedulecontactsGroup = (array_key_exists($gp->id, $rs_schedulecontactsGroupIds)) ? $rs_schedulecontactsGroupIds[$gp->id] : [];
                                            if (count($rs_schedulecontactsGroup) > 0) {
                                                $btnDeleteGroup = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleGroup(' . $schedule_id  . ',' . $gp->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce groupe"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                if ($roles[0]->code == 'FORMATEUR') {
                                                    $datas[] = array(
                                                        "id" => 'GROUP' . $gp->id . '-' . $schedule_id,
                                                        "text" => '<span class="text-primary">' . $gp->title . '</span>' . ' (' . count($rs_schedulecontactsGroup) . ') ',
                                                        "state" => array('opened' => false, 'checkbox_disabled' => true),
                                                        "icon" => "fa fa-users text-primary",
                                                        "parent" => 'SCHEDULE' . $schedule_id
                                                    );
                                                } else {
                                                    $datas[] = array(
                                                        "id" => 'GROUP' . $gp->id . '-' . $schedule_id,
                                                        "text" => '<span class="text-primary">' . $gp->title . '</span>' . ' (' . count($rs_schedulecontactsGroup) . ') ' . $btnDeleteGroup,
                                                        "state" => array('opened' => false, 'checkbox_disabled' => true),
                                                        "icon" => "fa fa-users text-primary",
                                                        "parent" => 'SCHEDULE' . $schedule_id
                                                    );
                                                }
                                                foreach ($rs_schedulecontactsGroup as $schedulecontact_datas) {
                                                    $schedulecontact_id = $schedulecontact_datas['schedulecontact_id'];
                                                    $entity_type = $schedulecontact_datas['entity_type'];
                                                    //dd($schedulecontact_id);
                                                    if ($schedulecontact_datas['entity_type'] == 'P') {
                                                        $entityName = $schedulecontact_datas['entity_type'] . ': ' .  $schedulecontact_datas['entity_ref'];
                                                    } else {
                                                        $entityName = $schedulecontact_datas['entity_type'] . ': ' . $schedulecontact_datas['entity_name'] . ' (' . $schedulecontact_datas['entity_ref'] . ')';
                                                    }
                                                    $member_name = ($schedulecontact_datas['contact_id'] > 0) ? ($schedulecontact_datas['contact_firstname'] . ' ' . $schedulecontact_datas['contact_lastname']) : $schedulecontact_datas['unknown_contact_name'];
                                                    $textContact = $member_name . ' - ' . $entity_type;

                                                    $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $schedulecontact_id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                    $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';

                                                    if ($roles[0]->code == 'FORMATEUR') {
                                                        $datas[] = array(
                                                            "id" => 'SCONTACT' . $schedulecontact_id,
                                                            "text" => $textContact . $btnInfosEntity,
                                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                            "icon" => "fa fa-user text-dark",
                                                            "parent" => 'GROUP' . $gp->id . '-' . $schedule_id
                                                        );
                                                    } else {
                                                        $datas[] = array(
                                                            "id" => 'SCONTACT' . $schedulecontact_id,
                                                            "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                            "icon" => "fa fa-user text-dark",
                                                            "parent" => 'GROUP' . $gp->id . '-' . $schedule_id
                                                        );
                                                    }
                                                }
                                            }


                                            //$members_ids = Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->where('group_id',$gp->id)->pluck('id');
                                            //$rs_schedulecontactsGroup = Schedulecontact::select('id','member_id','price','price_type','contract_id','type_of_intervention')->whereIn('member_id', $members_ids)->where('schedule_id',$schedule_id)->get();
                                            //dd($rs_schedulecontactsGroup);
                                            // $nbrMembreInGroup = Member::select('id')->where('group_id', $schedulegroup_id)->pluck('id')->count();
                                            //where([['schedule_id', $schedule_id], ['is_former', 1]])
                                            //if(count($rs_schedulecontactsGroup)>0){
                                            /* $btnDeleteGroup = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleGroup(' .$schedule_id  . ','.$gp->id.')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce groupe"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                        $datas [] = array(
                                                            "id" => 'GROUP' . $gp->id.'-'.$schedule_id,
                                                            "text" => '<span class="text-primary">'.$gp->title.'</span>'.' ('.count($rs_schedulecontactsGroup).') '.$btnDeleteGroup,
                                                            "state" => array('opened' => false, 'checkbox_disabled' => true),
                                                            "icon" => "fa fa-users text-primary",
                                                            "parent" => 'SCHEDULE' . $schedule_id
                                                        ); */

                                            /* foreach ($rs_schedulecontactsGroup as $sc) {
                                                            $entity_type=$sc->member->enrollment->entity->entity_type;
                                                            if($sc->member->enrollment->entity->entity_type=='P'){
                                                                $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                            }else{
                                                                $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                            }
                                                            
                                                            $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                            $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                            $textContact = $member_name.' - '.$entity_type;
                                                            $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                            $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="'.$entityName.'"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';
                                                            $datas [] = array(
                                                                "id" => 'SCONTACT' . $sc->id,
                                                                "text" => $textContact.$btnDelete.$btnInfosEntity,
                                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                                "icon" => "fa fa-user text-dark",
                                                                "parent" => 'GROUP' . $gp->id.'-'.$schedule_id
                                                            );
                                                            //dd($datas);
                                                        } */

                                            //}
                                        }
                                        //}
                                        //}
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return response()->json($datas);
    }
    public function treeSchedulesContactsFilterOld(Request $request, $af_id, $mode)
    {
        $sessions = $start = $end = null;
        $session_id = 0;
        $arr_sessions_ids = [];
        if ($request->isMethod('post')) {

            if ($request->has('session_id')) {
                if ($request->session_id > 0) {
                    $session_id = (int) $request->session_id;
                    $arr_sessions_ids[] = (int) $request->session_id;
                }
            }

            if ($request->has('group_id')) {
                $group_id = $request->group_id;
                $DbHelperTools = new DbHelperTools();
                $arr_sessions_ids = $DbHelperTools->getSessionsIdsByGroup($group_id, $start, $end);
                //dd($arr_sessions_ids);
            }

            if ($request->has('member_id')) {
                $member_id = $request->member_id;
                $DbHelperTools = new DbHelperTools();
                $arr_sessions_ids = $DbHelperTools->getSessionsIdsByMembre($member_id, $start, $end);
                //dd($arr_sessions_ids);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $d1 = Carbon::createFromFormat('d/m/Y', $request->start_date);
                    $d2 = Carbon::createFromFormat('d/m/Y', $request->end_date);
                    $start = $d1->format('Y-m-d');
                    $end = $d2->format('Y-m-d');
                }
            }

            // if ($request->has('group_id')) {
            //     $group_id=$request->group_id;
            //     $DbHelperTools = new DbHelperTools();
            //     $arr_sessions_ids=$DbHelperTools->getSessionsIdsByGroup($group_id,$start,$end);
            //     //dd($arr_sessions_ids);
            // }

            if (isset($start) && isset($end)) {
                $dt_start = $start;
                $dt_end = $end;
                if (count($arr_sessions_ids) > 0) {
                    $sessions = Session::select('id', 'title', 'code')->where('af_id', $af_id)->whereIn('id', $arr_sessions_ids)
                        ->whereIn('id', function ($query) use ($dt_start, $dt_end) {
                            $query->select('session_id')
                                ->from(with(new Sessiondate)->getTable())
                                ->whereBetween('planning_date', [$dt_start, $dt_end]);
                        })
                        ->orderBy('started_at', 'asc')->get();
                } else {
                    $sessions = Session::select('id', 'title', 'code')->where('af_id', $af_id)
                        ->whereIn('id', function ($query) use ($dt_start, $dt_end) {
                            $query->select('session_id')
                                ->from(with(new Sessiondate)->getTable())
                                ->whereBetween('planning_date', [$dt_start, $dt_end]);
                        })
                        ->orderBy('started_at', 'asc')->get();
                }
            }
        }
        //dd($session_id);
        //$mode : withcontacts or nocontacts
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $datas = [];
        //dd($start);
        // if($start==null && $end==null){
        //     if(count($arr_sessions_ids)>0){
        //         $sessions = Session::select('id','title','code')
        //         ->where('af_id', $af_id)->whereIn('id',$arr_sessions_ids)
        //         ->orderBy('started_at','asc')
        //         ->get(); 
        //     }else{
        //         $sessions = Session::select('id','title','code')
        //         ->where('af_id', $af_id)
        //         ->orderBy('started_at','asc')
        //         ->get();
        //     }
        // }
        // $arrayEtudiantsIds = [];
        // $arrayIntervenantIds = [];
        $arrayGroupsIds = [];
        $intersectMembresIds = [];
        $arrayMembersIds = array();
        // if ($request->has('group_id')) {
        if ($request->group_id) {
            $arrayGroupsIds = $arrayMembersIds = Member::select('id')->where('group_id', $request->group_id)->pluck('id')->unique()->toArray();
            $intersectMembresIds = $DbHelperTools->getSessionsIdsByGroup($request->group_id)->toArray();
        }
        if ($request->member_id) {
            // if(count($arrayMembersIds) > 0)
            //     if(in_array($request->member_id, $arrayMembersIds))
            // $arrayMembersIds[]=$request->member_id;
            $arrayMembersIds = array_intersect((array)$request->member_id, $arrayMembersIds);
            $renderMembersIds = $DbHelperTools->getSessionsIdsByMembre($request->member_id)->toArray();
            if ($intersectMembresIds || $request->group_id) {
                $intersectMembresIds = array_intersect($renderMembersIds, $intersectMembresIds);
                // dd($intersectMembresIds);
            } else {
                $intersectMembresIds = $renderMembersIds;
            }
            $arrayMembersIds = (array)$request->member_id;
        }
        if ($request->inter_id) {
            // $inter = $DbHelperTools->getInervenantsIdsByGroup($request->group_id);
            // dd($inter);
            $arrayMembersIds[] = $request->inter_id;
            $renderMembersIds = $DbHelperTools->getSessionsIdsByMembre($request->inter_id)->toArray();
            if ($intersectMembresIds) {
                $intersectMembresIds = array_intersect($renderMembersIds, $intersectMembresIds);
                if ($arrayGroupsIds)
                    $arrayMembersIds = (array) $request->inter_id;
            } else
                $intersectMembresIds = $renderMembersIds;
        }
        $arr_sessions_ids = $intersectMembresIds;
        // dd($arrayMembersIds);
        // echo count($intersectMembresIds);
        // dd($intersectMembresIds);
        // dd($arr_sessions_ids);
        if (count($arr_sessions_ids) > 0) {
            // if (count($arrayMembersIds) > 0) {
            // $arr_sessions_ids = DB::table('af_schedulecontacts')
            //             ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
            //             ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
            //             ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
            //             ->select('af_sessions.id')
            //             ->whereIn('af_schedulecontacts.member_id', $arrayMembersIds)
            //             ->pluck('af_sessions.id')->unique()->toArray();
            if ($request->session_id && count($arr_sessions_ids) > 0)
                if (!in_array($request->session_id, $arr_sessions_ids))
                    $arr_sessions_ids = [];
                else
                    $arr_sessions_ids = (array)$request->session_id;
        }
        if ($request->session_id && !$request->inter_id && !$request->member_id && !$request->group_id) {
            $arr_sessions_ids = Db::table('af_sessions as sens')
                ->select('sens.id')
                ->where('sens.id', '=', $request->session_id)->pluck('sens.id')->toArray();
            // dd($arr_sessions_ids);
        }

        if ($start && $end) {
            $array_time_id = DB::table('af_sessiondates')
                ->whereBetween('af_sessiondates.planning_date', [$start, $end])
                ->select('af_sessiondates.session_id')
                ->pluck('af_sessiondates.session_id')->toArray();
            // dd($arr_sessions_ids);
            if (count($arr_sessions_ids) > 0) {
                if (!in_array($array_time_id, $arr_sessions_ids))
                    // $arr_sessions_ids = [];
                    $arr_sessions_ids = array_intersect($array_time_id, $arr_sessions_ids);
            } else {
                $arr_sessions_ids = $array_time_id;
                // dd($arr_sessions_ids);
            }
        }
        /*if (count($arr_sessions_ids) <= 0) {
            dd(count($arr_sessions_ids));
            $arr_sessions_ids = DB::table('af_sessions')
                     ->where('af_sessions.af_id', $request->af_id)
                     ->select('af_sessions.id')
                     ->pluck('af_sessions.id');
        }*/

        // dd($request->isMethod('post'));
        $sessions = [];
        if (count($arr_sessions_ids) > 0)
            $sessions = DB::table('af_sessions')
                ->where('af_sessions.af_id', $request->af_id)
                ->where('is_evaluation', 0)
                ->whereIn('id', $arr_sessions_ids)
                ->select('id', 'title', 'code')
                ->get();
        else {
            if (!$request->member_id && !$request->inter_id && !$request->group_id && !$request->session_id && !$request->start_date && !$request->end_date)
                $sessions = DB::table('af_sessions')
                    ->where('af_sessions.af_id', $request->af_id)
                    ->where('is_evaluation', 0)
                    ->select('id', 'title', 'code')
                    ->get();
        }
        // dd($sessions);     
        if (count($sessions) > 0) {
            foreach ($sessions as $session) {
                //Session
                $datas[] = array(
                    "id" => $session->id,
                    "text" => $session->title . ' (' . $session->code . ')',
                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                    "icon" => "fa fa-folder text-info",
                    "parent" => '#'
                );
                //Session dates
                if ($start == null && $end == null) {
                    $sessiondates = Sessiondate::select('id', 'planning_date')->where('session_id', $session->id)->get();
                } else {
                    $from = Carbon::createFromFormat('Y-m-d', $start);
                    $to = Carbon::createFromFormat('Y-m-d', $end);
                    // $sessiondates = Sessiondate::select('id','planning_date')->whereBetween('planning_date', [$from, $to])->where('session_id', $session->id)->get();
                    $sessiondates = Sessiondate::select('id', 'planning_date')->whereBetween('planning_date', [$start, $end])->where('session_id', $session->id)->get();
                    // dd(count($sessiondates));
                }

                if (count($sessiondates) > 0) {
                    foreach ($sessiondates as $sd) {
                        //$planning_date = Carbon::createFromFormat('Y-m-d',$sd->planning_date);
                        $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                        $datas[] = array(
                            "id" => 'D' . $sd->id,
                            "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "icon" => "fa fa-calendar text-primary",
                            "parent" => $session->id
                        );
                        //Schedules : séances
                        $rs_schedules = Schedule::select('id', 'start_hour', 'end_hour', 'duration')->where('sessiondate_id', $sd->id)->get();
                        if (count($rs_schedules) > 0) {
                            foreach ($rs_schedules as $schedule) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');

                                $duration = Helper::convertTime($schedule->duration);

                                //Intervenants
                                $checkbox_disabled = false;
                                $datas[] = array(
                                    "id" => 'SCHEDULE' . $schedule->id,
                                    "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                    "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                    "icon" => "fa fa-folder text-dark",
                                    "parent" => 'D' . $sd->id
                                );
                                //Schedulecontacts : les inscrits
                                if ($mode == 'withcontacts') {
                                    //Intervenants
                                    $rs_schedulecontacts_formers = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 1]])->get();
                                    if (count($rs_schedulecontacts_formers) > 0) {
                                        $datas[] = array(
                                            "id" => 'TITLE_FORMERS' . $schedule->id,
                                            "text" => '<span class="text-warning">Liste des intervenants : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                        foreach ($rs_schedulecontacts_formers as $scf) {
                                            if ($scf->member->enrollment->entity->entity_type == 'P') {
                                                $entityName = $scf->member->enrollment->entity->entity_type . ': ' .  $scf->member->enrollment->entity->ref;
                                            } else {
                                                $entityName = $scf->member->enrollment->entity->entity_type . ': ' . $scf->member->enrollment->entity->name . ' (' . $scf->member->enrollment->entity->ref . ')';
                                            }
                                            $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';
                                            $price = '';
                                            $type_former_intervention = $scf->member->contact->type_former_intervention;
                                            $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule->duration, $scf->price, $type_former_intervention);
                                            $total_cost = ($scf_total_cost > 0) ? ' - coût total : ' . $scf_total_cost . ' €' : '';
                                            if ($scf->price > 0) {
                                                $price = $scf->price . ' €/' . $DbHelperTools->getNameParamByCode($scf->price_type) . $total_cost;
                                            } else {
                                                $price = $total_cost;
                                            }
                                            $contractNumber = ($scf->contract_id > 0) ? (' (' . $scf->contract->number . ')') : '';
                                            $type_of_intervention = ($scf->type_of_intervention) ? (' - Type : ' . $DbHelperTools->getNameParamByCode($scf->type_of_intervention)) : '';

                                            //$pTypeIntervention = '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $scf->member->contact->type_former_intervention . $contractNumber . $type_of_intervention . '</p>';
                                            //$pPrice = ($price) ? '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $price . '</p>' : '';


                                            $spanMemberName = ($scf->member->contact) ? ('<span class="text-dark">' . ($scf->member->contact->firstname . ' ' . $scf->member->contact->lastname) . '<span>') : '';
                                            $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $scf->id . ')" data-toggle="tooltip" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                            $type_of_intervention = $scf->member->contact->type_former_intervention;
                                            $intervention = $type_of_intervention . $contractNumber . $type_of_intervention;
                                            $iPrice = ($price) ? $price : '';

                                            $infosIntervenant = '';
                                            $btnInfosIntervenant = '';
                                            $btnRemuneration = '';
                                            $infosIntervenant = '';
                                            if ($type_of_intervention != 'Interne') {
                                                $btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration(' . $af_id . ',' . $scf->member_id . ')" data-toggle="tooltip" title="Rémunération"><i class="' . $tools->getIconeByAction('PRICE') . ' text-success"></i></a>';
                                                $btnInfosIntervenant = ' <a style="cursor: pointer;" data-toggle="tooltip" data-theme="dark" title="' . $intervention . ' - ' . $iPrice . '"><i class="fas fa-info-circle text-' . ($iPrice ? 'primary' : 'warning') . '"></i></a>';
                                            }
                                            $pointage = $DbHelperTools->getPointingInfos($scf->pointing);

                                            $btnPointage = '<a style="cursor: pointer;" class="ml-2 mr-2" data-toggle="tooltip" title="Pointage" onclick="_formPointage(' . $scf->id . ')"><i class="fas fa-edit text-primary"></i></a>';

                                            $datas[] = array(
                                                "id" => 'SCONTACT' . $scf->id,
                                                "text" => $spanMemberName . $btnRemuneration . $btnDelete . $btnInfosIntervenant . $pointage . $btnPointage,
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "far fa-id-badge text-dark",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                        }
                                    }
                                    //Les stagiaires inscrits non appartient a un group
                                    $rs_schedulecontacts = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 0]])->get();
                                    if (count($rs_schedulecontacts) > 0) {
                                        $datas[] = array(
                                            "id" => 'TITLE_STAGIAIRES' . $schedule->id,
                                            "text" => '<span class="text-warning">Liste des groupes et stagiaires : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                        foreach ($rs_schedulecontacts as $sc) {
                                            if ($sc->member->group_id == null) {

                                                $entity_type = $sc->member->enrollment->entity->entity_type;

                                                if ($sc->member->enrollment->entity->entity_type == 'P') {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                } else {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                }

                                                $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                $textContact = $member_name . ' - ' . $entity_type;

                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';

                                                $datas[] = array(
                                                    "id" => 'SCONTACT' . $sc->id,
                                                    "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                    "icon" => "fa fa-user text-dark",
                                                    "parent" => 'SCHEDULE' . $schedule->id
                                                );
                                            }
                                        }
                                    }
                                    //les membres des groupes
                                    $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
                                    $groups = Group::where('af_id', $af_id)->get();
                                    foreach ($groups as $gp) {

                                        $members_ids = Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->where('group_id', $gp->id)->pluck('id');
                                        $rs_schedulecontactsGroup = Schedulecontact::whereIn('member_id', $members_ids)->where('schedule_id', $schedule->id)->get();
                                        // $nbrMembreInGroup = Member::select('id')->where('group_id', $schedulegroup_id)->pluck('id')->count();
                                        //where([['schedule_id', $schedule->id], ['is_former', 1]])
                                        if (count($rs_schedulecontactsGroup) > 0) {
                                            $btnDeleteGroup = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleGroup(' . $schedule->id  . ',' . $gp->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce groupe"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                            $datas[] = array(
                                                "id" => 'GROUP' . $gp->id . '-' . $schedule->id,
                                                "text" => '<span class="text-primary">' . $gp->title . '</span>' . ' (' . count($rs_schedulecontactsGroup) . ') ' . $btnDeleteGroup,
                                                "state" => array('opened' => false, 'checkbox_disabled' => true),
                                                "icon" => "fa fa-users text-primary",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                            foreach ($rs_schedulecontactsGroup as $sc) {
                                                $entity_type = $sc->member->enrollment->entity->entity_type;
                                                if ($sc->member->enrollment->entity->entity_type == 'P') {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                } else {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                }

                                                $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                $textContact = $member_name . ' - ' . $entity_type;
                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';
                                                $datas[] = array(
                                                    "id" => 'SCONTACT' . $sc->id,
                                                    "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                    "icon" => "fa fa-user text-dark",
                                                    "parent" => 'GROUP' . $gp->id . '-' . $schedule->id
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // Get seance by membres
        if ($request->member_id || $request->inter_id || $request->group_id) {
            $datas = [];
            if (count($sessions) > 0) {
                foreach ($sessions as $session) {
                    //Session
                    $datas[] = array(
                        "id" => $session->id,
                        "text" => $session->title . ' (' . $session->code . ')',
                        "state" => array('opened' => true, 'checkbox_disabled' => false),
                        "icon" => "fa fa-folder text-info",
                        "parent" => '#'
                    );

                    //Session dates
                    // $arrayMembersIds
                    if ($start == null && $end == null) {
                        $sessiondates = Sessiondate::select('af_sessiondates.id', 'af_sessiondates.planning_date')
                            ->join('af_schedules', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
                            ->join('af_schedulecontacts', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
                            ->whereIn('af_schedulecontacts.member_id', $arrayMembersIds)
                            ->where('session_id', $session->id)->distinct()->get();
                        // dd($sessiondates);
                    } else {
                        $from = Carbon::createFromFormat('Y-m-d', $start);
                        $to = Carbon::createFromFormat('Y-m-d', $end);
                        // $sessiondates = Sessiondate::select('id','planning_date')->whereBetween('planning_date', [$from, $to])->where('session_id', $session->id)->get();
                        $sessiondates = Sessiondate::select('af_sessiondates.id', 'af_sessiondates.planning_date')
                            ->join('af_schedules', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
                            ->join('af_schedulecontacts', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
                            ->whereIn('af_schedulecontacts.member_id', $arrayMembersIds)
                            ->whereBetween('planning_date', [$start, $end])->where('session_id', $session->id)->distinct()->get();
                        // dd(count($sessiondates));
                    }
                    if (count($sessiondates) > 0) {
                        foreach ($sessiondates as $sd) {
                            //$planning_date = Carbon::createFromFormat('Y-m-d',$sd->planning_date);
                            $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                            $datas[] = array(
                                "id" => 'D' . $sd->id,
                                "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                                "state" => array('opened' => true, 'checkbox_disabled' => false),
                                "icon" => "fa fa-calendar text-primary",
                                "parent" => $session->id
                            );
                            //Schedules : séances
                            $rs_schedules = Schedule::select('id', 'start_hour', 'end_hour', 'duration')->where('sessiondate_id', $sd->id)->get();
                            if (count($rs_schedules) > 0) {
                                foreach ($rs_schedules as $schedule) {
                                    $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                    $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                    $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');

                                    $duration = Helper::convertTime($schedule->duration);

                                    //Intervenants
                                    $checkbox_disabled = false;
                                    $datas[] = array(
                                        "id" => 'SCHEDULE' . $schedule->id,
                                        "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                        "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                        "icon" => "fa fa-folder text-dark",
                                        "parent" => 'D' . $sd->id
                                    );
                                    //Schedulecontacts : les inscrits
                                    if ($mode == 'withcontacts') {
                                        //Intervenants
                                        $rs_schedulecontacts_formers = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 1]])->get();
                                        if (count($rs_schedulecontacts_formers) > 0) {
                                            $datas[] = array(
                                                "id" => 'TITLE_FORMERS' . $schedule->id,
                                                "text" => '<span class="text-warning">Liste des intervenants : </span>',
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "far fa-arrow-alt-circle-down text-warning",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                            foreach ($rs_schedulecontacts_formers as $scf) {
                                                if ($scf->member->enrollment->entity->entity_type == 'P') {
                                                    $entityName = $scf->member->enrollment->entity->entity_type . ': ' .  $scf->member->enrollment->entity->ref;
                                                } else {
                                                    $entityName = $scf->member->enrollment->entity->entity_type . ': ' . $scf->member->enrollment->entity->name . ' (' . $scf->member->enrollment->entity->ref . ')';
                                                }
                                                $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';
                                                $price = '';
                                                $type_former_intervention = $scf->member->contact->type_former_intervention;
                                                $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule->duration, $scf->price, $type_former_intervention);
                                                $total_cost = ($scf_total_cost > 0) ? ' - coût total : ' . $scf_total_cost . ' €' : '';
                                                if ($scf->price > 0) {
                                                    $price = $scf->price . ' €/' . $DbHelperTools->getNameParamByCode($scf->price_type) . $total_cost;
                                                } else {
                                                    $price = $total_cost;
                                                }
                                                $contractNumber = ($scf->contract_id > 0) ? (' (' . $scf->contract->number . ')') : '';
                                                $type_of_intervention = ($scf->type_of_intervention) ? (' - Type : ' . $DbHelperTools->getNameParamByCode($scf->type_of_intervention)) : '';

                                                //$pTypeIntervention = '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $scf->member->contact->type_former_intervention . $contractNumber . $type_of_intervention . '</p>';
                                                //$pPrice = ($price) ? '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $price . '</p>' : '';


                                                $spanMemberName = ($scf->member->contact) ? ('<span class="text-dark">' . ($scf->member->contact->firstname . ' ' . $scf->member->contact->lastname) . '<span>') : '';
                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $scf->id . ')" data-toggle="tooltip" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                $btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration(' . $af_id . ',' . $scf->member_id . ')" data-toggle="tooltip" title="Rémunération"><i class="' . $tools->getIconeByAction('PRICE') . ' text-success"></i></a>';

                                                $intervention = $scf->member->contact->type_former_intervention . $contractNumber . $type_of_intervention;
                                                $iPrice = ($price) ? $price : '';

                                                $infosIntervenant = '';
                                                $btnInfosIntervenant = ' <a style="cursor: pointer;" data-toggle="tooltip" data-theme="dark" title="' . $intervention . ' - ' . $iPrice . '"><i class="fas fa-info-circle text-' . ($iPrice ? 'primary' : 'warning') . '"></i></a>';

                                                $pointage = $DbHelperTools->getPointingInfos($scf->pointing);

                                                $btnPointage = '<a style="cursor: pointer;" class="ml-2 mr-2" data-toggle="tooltip" title="Pointage" onclick="_formPointage(' . $scf->id . ')"><i class="fas fa-edit text-primary"></i></a>';

                                                $datas[] = array(
                                                    "id" => 'SCONTACT' . $scf->id,
                                                    "text" => $spanMemberName . $btnRemuneration . $btnDelete . $btnInfosIntervenant . $pointage . $btnPointage,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                    "icon" => "far fa-id-badge text-dark",
                                                    "parent" => 'SCHEDULE' . $schedule->id
                                                );
                                            }
                                        }
                                        //Les stagiaires inscrits non appartient a un group
                                        $rs_schedulecontacts = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 0]])->get();
                                        if (count($rs_schedulecontacts) > 0) {
                                            $datas[] = array(
                                                "id" => 'TITLE_STAGIAIRES' . $schedule->id,
                                                "text" => '<span class="text-warning">Liste des groupes et stagiaires : </span>',
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "far fa-arrow-alt-circle-down text-warning",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                            foreach ($rs_schedulecontacts as $sc) {
                                                if ($sc->member->group_id == null) {

                                                    $entity_type = $sc->member->enrollment->entity->entity_type;

                                                    if ($sc->member->enrollment->entity->entity_type == 'P') {
                                                        $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                    } else {
                                                        $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                    }

                                                    $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                    $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                    $textContact = $member_name . ' - ' . $entity_type;

                                                    $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                    $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';

                                                    $datas[] = array(
                                                        "id" => 'SCONTACT' . $sc->id,
                                                        "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                        "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                        "icon" => "fa fa-user text-dark",
                                                        "parent" => 'SCHEDULE' . $schedule->id
                                                    );
                                                }
                                            }
                                        }
                                        //les membres des groupes
                                        $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
                                        $groups = Group::where('af_id', $af_id)->get();
                                        foreach ($groups as $gp) {

                                            $members_ids = Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->where('group_id', $gp->id)->pluck('id');
                                            $rs_schedulecontactsGroup = Schedulecontact::whereIn('member_id', $members_ids)->where('schedule_id', $schedule->id)->get();
                                            // $nbrMembreInGroup = Member::select('id')->where('group_id', $schedulegroup_id)->pluck('id')->count();
                                            //where([['schedule_id', $schedule->id], ['is_former', 1]])
                                            if (count($rs_schedulecontactsGroup) > 0) {
                                                $btnDeleteGroup = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleGroup(' . $schedule->id  . ',' . $gp->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce groupe"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                $datas[] = array(
                                                    "id" => 'GROUP' . $gp->id . '-' . $schedule->id,
                                                    "text" => '<span class="text-primary">' . $gp->title . '</span>' . ' (' . count($rs_schedulecontactsGroup) . ') ' . $btnDeleteGroup,
                                                    "state" => array('opened' => false, 'checkbox_disabled' => true),
                                                    "icon" => "fa fa-users text-primary",
                                                    "parent" => 'SCHEDULE' . $schedule->id
                                                );
                                                foreach ($rs_schedulecontactsGroup as $sc) {
                                                    $entity_type = $sc->member->enrollment->entity->entity_type;
                                                    if ($sc->member->enrollment->entity->entity_type == 'P') {
                                                        $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                    } else {
                                                        $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                    }

                                                    $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                    $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                    $textContact = $member_name . ' - ' . $entity_type;
                                                    $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                    $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';
                                                    $datas[] = array(
                                                        "id" => 'SCONTACT' . $sc->id,
                                                        "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                        "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                        "icon" => "fa fa-user text-dark",
                                                        "parent" => 'GROUP' . $gp->id . '-' . $schedule->id
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return response()->json($datas);
    }

    public function treeSchedulesContactSession($session_id, $mode)
    {
        //$mode : withcontacts or nocontacts
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $datas = [];
        $sessions = Session::where('id', $session_id)->where('is_evaluation', 0)->get();
        if (count($sessions) > 0) {
            foreach ($sessions as $session) {
                //Session
                $datas[] = array(
                    "id" => $session->id,
                    "text" => $session->code,
                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                    "icon" => "fa fa-folder text-info",
                    "parent" => '#'
                );
                //Session dates
                $sessiondates = Sessiondate::where('session_id', $session->id)->get();
                if (count($sessiondates) > 0) {
                    foreach ($sessiondates as $sd) {
                        //$planning_date = Carbon::createFromFormat('Y-m-d',$sd->planning_date);
                        $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                        $datas[] = array(
                            "id" => 'D' . $sd->id,
                            "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "icon" => "fa fa-calendar text-primary",
                            "parent" => $session->id
                        );
                        //Schedules : séances
                        $rs_schedules = Schedule::where('sessiondate_id', $sd->id)->get();
                        if (count($rs_schedules) > 0) {
                            foreach ($rs_schedules as $schedule) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');

                                $duration = Helper::convertTime($schedule->duration);

                                //Intervenants
                                $checkbox_disabled = false;
                                $datas[] = array(
                                    "id" => 'SCHEDULE' . $schedule->id,
                                    "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                    "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                    "icon" => "fa fa-folder text-dark",
                                    "parent" => 'D' . $sd->id
                                );
                                //Schedulecontacts : les inscrits
                                if ($mode == 'withcontacts') {
                                    //Intervenants
                                    $rs_schedulecontacts_formers = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 1]])->get();
                                    if (count($rs_schedulecontacts_formers) > 0) {
                                        $datas[] = array(
                                            "id" => 'TITLE_FORMERS' . $schedule->id,
                                            "text" => '<span class="text-warning">Liste des intervenants : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                        foreach ($rs_schedulecontacts_formers as $scf) {

                                            $entityName = $scf->member->enrollment->entity->entity_type . ': ' . $scf->member->enrollment->entity->name . ' (' . $scf->member->enrollment->entity->ref . ')';
                                            $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';
                                            $price = '';
                                            $type_former_intervention = $scf->member->contact->type_former_intervention;
                                            $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule->duration, $scf->price, $type_former_intervention);
                                            //$total_cost = ($scf_total_cost > 0) ? ' - coût total : ' . $scf_total_cost . ' €' : '';
                                            $total_cost = ($scf_total_cost > 0) ? '<span class="text-primary"> - coût total : ' . $scf_total_cost . ' €</span>' : '';
                                            if ($scf->price > 0) {
                                                $price = '<span class="text-primary"> ' . $scf->price . ' €/' . $DbHelperTools->getNameParamByCode($scf->price_type) . $total_cost . '</span>';
                                            } else {
                                                $price = $total_cost;
                                            }
                                            $contractNumber = ($scf->contract_id > 0) ? ' (' . $scf->contract->number . ')' : '';
                                            $type_of_intervention = ($scf->type_of_intervention) ? ' - Type : ' . $DbHelperTools->getNameParamByCode($scf->type_of_intervention) : '';
                                            $pTypeIntervention = '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $scf->member->contact->type_former_intervention . $contractNumber . $type_of_intervention . '</p>';
                                            $pPrice = ($price) ? '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $price . '</p>' : '';
                                            $spanMemberName = ($scf->member->contact) ? '<span class="text-dark">' . ($scf->member->contact->firstname . ' ' . $scf->member->contact->lastname) . '<span>' : '';

                                            $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $scf->id . ')" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                            $btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration(' . $session->af_id . ',' . $scf->member_id . ')" title="Rémunération"><i class="' . $tools->getIconeByAction('PRICE') . ' text-success"></i></a>';

                                            $datas[] = array(
                                                "id" => 'SCONTACT' . $scf->id,
                                                "text" => $spanMemberName . $btnRemuneration . $btnDelete . $pTypeIntervention . $pPrice,
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "far fa-id-badge text-dark",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                        }
                                    }
                                    //Les stagiaires inscrits
                                    $rs_schedulecontacts = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 0]])->get();
                                    if (count($rs_schedulecontacts) > 0) {
                                        $datas[] = array(
                                            "id" => 'TITLE_STAGIAIRES' . $schedule->id,
                                            "text" => '<span class="text-warning">Liste des stagiaires : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                        foreach ($rs_schedulecontacts as $sc) {
                                            $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                            $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                            $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                            $textContact = $member_name;
                                            $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                            $datas[] = array(
                                                "id" => 'SCONTACT' . $sc->id,
                                                "text" => $textContact . $btnDelete . $spanEntity,
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "fa fa-user text-dark",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return response()->json($datas);
    }

    /* public function formScheduleContact($af_id,$type){
        return view('pages.af.schedule.form',['af_id'=>$af_id,'type'=>$type]);
    } */
    public function storeFormScheduleContact(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            //dd($request->all());
            $DbHelperTools = new DbHelperTools();

            //$is_former=($request->planification_type == 2)?1:0;
            $is_former = ($request->affectation_type == 'F') ? 1 : 0;
            $schedules_ids = [];
            //S1,S2,S3,S4,S5,S6
            if ($request->has('schedules_ids') && !empty($request->schedules_ids)) {
                $myArrayDatas = explode(',', $request->schedules_ids);
                if (count($myArrayDatas) > 0) {
                    foreach ($myArrayDatas as $dt) {
                        $tab_id = explode('SCHEDULE', $dt);
                        (isset($tab_id[1])) ? ($schedules_ids[] = $tab_id[1]) : '';
                    }
                }
            }
            $group_ids = $group_members_ids = [];
            if ($request->has('group_ids') && !empty($request->group_ids)) {
                $group_ids = $request->group_ids;
            }
            if ($request->has('group_members_ids') && !empty($request->group_members_ids)) {
                $group_members_ids = $request->group_members_ids;
            }
            $groupments_ids = [];
            if ($request->has('groupments_ids') && !empty($request->groupments_ids)) {
                $groupments_ids = $request->groupments_ids;
            }
            $members_ids = [];
            //get all groups of a groupment
            if (count($groupments_ids) > 0) {
                $group_ids = Groupmentgroup::select('group_id')->whereIn('groupment_id', $groupments_ids)->pluck('group_id');
            }
            //get all group members
            if (count($group_ids) > 0) {
                $members_ids = Member::select('id')->whereIn('group_id', $group_ids)->pluck('id');
            }
            //Select parmi les contacts d'un groupe
            if (count($group_members_ids) > 0) {
                $members_ids = $group_members_ids;
            }

            if ($request->has('members_ids') && !empty($request->members_ids)) {
                $members_ids = $request->members_ids;
            }
            if (count($members_ids) == 0) {
                $msg = ($is_former == 1) ? 'Veuillez sélectionner un intervanant !' : 'Veuillez sélectionner les inscrits !';
            }
            if (count($schedules_ids) == 0) {
                $msg = 'Veuillez sélectionner les séances !';
            }
            if (count($schedules_ids) == 0 && count($members_ids) == 0) {
                $label = ($is_former == 1) ? 'les intervenants' : 'les contacts';
                $msg = 'Veuillez sélectionner ' . $label . ' et les séances !';
            }

            if (count($schedules_ids) > 0 && count($members_ids)) {
                foreach ($schedules_ids as $schedule_id) {
                    //dd($schedules_ids);
                    $score  = null;
                    $ects  = null;
                    if ($is_former != 1) {
                        $session = Schedule::find($schedule_id)->sessiondate->session;
                        $score  = $session->evaluation_mode == 'PRESENTIEL' ? 12 : null;
                        $ects  = $session->ects;
                    }
                    foreach ($members_ids as $member_id) {
                        $rs = Schedulecontact::select('id')->where([['is_former', $is_former], ['schedule_id', $schedule_id], ['member_id', $member_id]])->get();
                        $schedulecontact = 0;
                        if (count($rs) > 0) {
                            $schedulecontact = $rs[0]['id'];
                        }
                        //dd($schedulecontact);
                        $data = array(
                            'id' => $schedulecontact,
                            'is_former' => $is_former,
                            'is_absent' => 0,
                            'type_absent' => null,
                            'schedule_id' => $schedule_id,
                            'member_id' => $member_id,
                            'contract_id' => null,
                            'score' => $score,
                            'ects' => $ects,
                        );
                        $DbHelperTools->manageSchedulecontact($data);
                    }
                }
                $success = true;
                $msg = 'L\'affectation à été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function sdtSelectMembers(Request $request, $af_id, $enrollment_type)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = [];
        if ($af_id > 0) {
            //if group or groupment
            if (in_array($enrollment_type, ['G', 'GROUPMENT'])) {
                $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
            } else {
                $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', $enrollment_type]])->get()->pluck('id');
            }

            if (count($ids_enrollments) > 0) {
                if (in_array($enrollment_type, ['G', 'GROUPMENT'])) {
                    $datas = Member::whereIn('enrollment_id', $ids_enrollments)->where('group_id', '>', 0)->get();
                } else {
                    $datas = Member::whereIn('enrollment_id', $ids_enrollments)->whereNull('group_id')->get();
                }
            }
        }
        if ($enrollment_type == 'GROUPMENT') {
            $groupments = Groupment::where('af_id', $af_id)->get();
            foreach ($groupments as $groupment) {
                $row = array();
                $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="groupments_ids[]" value="' . $groupment->id . '" class="checkable" />
                        <span></span>
                        </label>';
                $nbr_groups = Groupmentgroup::select('id')->where('groupment_id', $groupment->id)->count();
                $row[] = '<strong>' . $groupment->name . ' <span class="text-info">(' . $nbr_groups . ' groupe(s))</span></strong>';
                $row[] = '';
                $data[] = $row;
                //les groupes
                $rs = Groupmentgroup::select('group_id')->where('groupment_id', $groupment->id)->get();
                foreach ($rs as $gp) {
                    $row = array();
                    $row[] = '';
                    $row[] = $gp->group->title . ' <span class="text-info">(' . $gp->group->members->count() . ' stagiaire(s))</span>';
                    $row[] = '';
                    $data[] = $row;
                }
            }
        } elseif ($enrollment_type == 'G') {
            $groups = Group::where('af_id', $af_id)->get();
            foreach ($groups as $gp) {
                $row = array();
                $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="group_ids[]" value="' . $gp->id . '" class="checkable" />
                        <span></span>
                        </label>';
                $row[] = '<span><strong>' . $gp->title . ' (' . $gp->members->count() . ' stagiaire(s))</strong></span>';
                $row[] = '';
                $data[] = $row;

                $members = Member::whereIn('enrollment_id', $ids_enrollments)->where('group_id', $gp->id)->get();
                foreach ($members as $d) {
                    $row = array();
                    //id
                    $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="group_members_ids[]" value="' . $d->id . '" class="checkable" />
                        <span></span>
                        </label>';
                    //<th>Nom</th>
                    $pEntityType = $entityType = $cssClass = '';
                    if ($d->enrollment->entity) {
                        $cssClass = 'primary';
                        $entityType = 'Particulier';
                        if ($d->enrollment->entity->entity_type == 'S') {
                            $entityType = 'Société';
                            $cssClass = 'info';
                        }
                    }

                    $nameEntity = ($d->enrollment->entity) ? $d->enrollment->entity->name . ' (' : '';
                    $refEntity = ($d->enrollment->entity) ? $d->enrollment->entity->ref . ')' : '';

                    $firstname = ($d->contact) ? $d->contact->firstname : $d->unknown_contact_name;
                    $lastname = ($d->contact) ? $d->contact->lastname : '';
                    $row[] = '<span class="font-size-sm">' . $firstname . ' ' . $lastname . '</span>' . '<p class="font-size-sm text-' . $cssClass . ' mb-1">' . $nameEntity . $refEntity . ' - ' . $entityType . '</p>';
                    //<th>Entité</th>
                    //$row[]=$typeEntity.$nameEntity.$refEntity;
                    //<th>Planif</th>
                    $btn_planif_details = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_showScheduleDetails(' . $d->enrollment->action->id . ',' . $d->id . ')" title="Détails du planning"><i class="' . $tools->getIconeByAction('INFO') . '"></i></button>';
                    //$spanPlanif = $DbHelperTools->getPlanifContact($d->id, $d->enrollment->action->id);
                    $row[] = $btn_planif_details;
                    $data[] = $row;
                }
            }
        } else {
            foreach ($datas as $d) {
                $row = array();
                //<th></th>
                $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="members_ids[]" value="' . $d->id . '" class="checkable" />
                        <span></span>
                        </label>';
                //<th>Nom</th>
                $pEntityType = $entityType = $cssClass = '';
                if ($d->enrollment->entity) {
                    $cssClass = 'primary';
                    $entityType = 'Particulier';
                    if ($d->enrollment->entity->entity_type == 'S') {
                        $entityType = 'Société';
                        $cssClass = 'info';
                    }
                }

                //$typeEntity = ($d->enrollment->entity)?'<p class="font-size-sm text-warning mb-1">'.(($d->enrollment->entity->entity_type=='S')?'Société':'Particulier').'</p>':'';
                $nameEntity = ($d->enrollment->entity) ? $d->enrollment->entity->name . ' (' : '';
                $refEntity = ($d->enrollment->entity) ? $d->enrollment->entity->ref . ')' : '';

                $firstname = ($d->contact) ? $d->contact->firstname : $d->unknown_contact_name;
                $lastname = ($d->contact) ? $d->contact->lastname : '';

                $row[] = '<span class="font-size-sm">' . $firstname . ' ' . $lastname . '</span>' . '<p class="font-size-sm text-' . $cssClass . ' mb-1">' . $nameEntity . $refEntity . ' - ' . $entityType . '</p>';
                //<th>Entité</th>
                //$row[]=$typeEntity.$nameEntity.$refEntity;
                //<th>Planif</th>
                $btn_planif_details = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_showScheduleDetails(' . $d->enrollment->action->id . ',' . $d->id . ')" title="Détails du planning"><i class="' . $tools->getIconeByAction('INFO') . '"></i></button>';
                //$spanPlanif = $DbHelperTools->getPlanifContact($d->id, $d->enrollment->action->id);
                $row[] = $btn_planif_details;

                $data[] = $row;
            }
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
        ];
        return response()->json($result);
    }
    public function getScheduleMemberDetails($af_id, $member_id)
    {
        $d = Member::findOrFail($member_id);
        $entityType = '';
        if ($d->enrollment->entity) {
            $entityType = 'Particulier';
            if ($d->enrollment->entity->entity_type == 'S') {
                $entityType = 'Société';
            }
        }
        $nameEntity = ($d->enrollment->entity) ? $d->enrollment->entity->name . ' (' : '';
        $refEntity = ($d->enrollment->entity) ? $d->enrollment->entity->ref . ')' : '';
        $firstname = ($d->contact) ? $d->contact->firstname : $d->unknown_contact_name;
        $lastname = ($d->contact) ? $d->contact->lastname : '';
        $DbHelperTools = new DbHelperTools();
        $spanPlanif = $DbHelperTools->getPlanifContact($member_id, $af_id);
        return view('pages.af.schedule.details', compact('spanPlanif', 'firstname', 'lastname', 'nameEntity', 'refEntity', 'entityType'));
    }
    public function getScheduleContractDetails($contract_id)
    {
        $DbHelperTools = new DbHelperTools();
        $html = $DbHelperTools->getInterventionContractFormer($contract_id);
        return view('pages.af.schedule.contract-details', compact('html'));
    }
    public function getScheduleContractDetailsByPeriods($contract_id, $start, $end)
    {
        $DbHelperTools = new DbHelperTools();
        $html = $DbHelperTools->getInterventionContractFormer($contract_id, $start, $end);
        return view('pages.af.schedule.contract-details', compact('html'));
    }
    public function selectMembersOptionsAfGroup($af_id, $group_id)
    {
        $result = $datas = [];
        $DbHelperTools = new DbHelperTools();
        // if ($af_id > 0) {
        $datas = Enrollment::select('en_contacts.firstname', 'en_contacts.lastname', 'af_members.id')
            ->join('af_members', 'af_members.enrollment_id', '=', 'af_enrollments.id')
            ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
            ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')
            // ->where([['af_id', $af_id], ['enrollment_type', 'S'],['group_id', $group_id]])->distinct()
            ->where('enrollment_type', 'S');
        if ($af_id > 0)
            $datas->where('af_id', $af_id);
        if ($group_id > 0)
            $datas->where('group_id', $group_id);
        $datas = $datas->distinct()->get()->toArray(); //->pluck('enrollment_id');
        // if (count($ids_enrollments) > 0) {
        //     $datas = Member::whereIn('enrollment_id', $ids_enrollments)->get();
        // }
        // }
        // if (count($datas) > 0) {
        //     foreach ($datas as $member) {
        //         $contactFormer = '';
        //         $contact = $member->contact;
        //         $price = $member->enrollment->price . ' € / ' . $DbHelperTools->getNameParamByCode($member->enrollment->price_type);
        //         if ($contact) {
        //             $typeEntity = ($member->enrollment->entity) ? $member->enrollment->entity->entity_type : '';
        //             $nameEntity = ($member->enrollment->entity) ? $member->enrollment->entity->name . ' (' : '';
        //             $refEntity = ($member->enrollment->entity) ? $member->enrollment->entity->ref . ')' : '';
        //             $entityInfos = $typeEntity . (($member->enrollment->entity->entity_type == 'S') ? ' : ' . $nameEntity . $refEntity : '');
        //             $contactFormer = $contact->firstname . ' ' . $contact->lastname . ' - ' . $contact->type_former_intervention . ' - ' . $entityInfos;
        //             $result[] = ['id' => $member->id, 'name' => $contactFormer];
        //         }
        //     }
        // }
        // dd($datas);
        return response()->json($datas);
    }
    public function selectMembersOptions($af_id)
    {
        $result = $datas = [];
        $DbHelperTools = new DbHelperTools();
        if ($af_id > 0) {
            $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'F']])->get()->pluck('id');
            if (count($ids_enrollments) > 0) {
                $datas = Member::whereIn('enrollment_id', $ids_enrollments)->get();
            }
        }
        if (count($datas) > 0) {
            foreach ($datas as $member) {
                $contactFormer = '';
                $contact = $member->contact;
                $price = $member->enrollment->price . ' € / ' . $DbHelperTools->getNameParamByCode($member->enrollment->price_type);
                if ($contact) {
                    $typeEntity = ($member->enrollment->entity) ? $member->enrollment->entity->entity_type : '';
                    $nameEntity = ($member->enrollment->entity) ? $member->enrollment->entity->name . ' (' : '';
                    $refEntity = ($member->enrollment->entity) ? $member->enrollment->entity->ref . ')' : '';
                    $entityInfos = $typeEntity . (($member->enrollment->entity->entity_type == 'S') ? ' : ' . $nameEntity . $refEntity : '');
                    $contactFormer = $contact->firstname . ' ' . $contact->lastname . ' - ' . $contact->type_former_intervention . ' - ' . $entityInfos;
                    $result[] = ['id' => $member->id, 'name' => $contactFormer];
                }
            }
        }
        return response()->json($result);
    }

    public function sdtRegistrants(Request $request, $enrollment_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = [];
        if ($enrollment_id > 0) {
            $datas = Member::where('enrollment_id', $enrollment_id)->get();
        }
        foreach ($datas as $d) {
            $row = array();
            //<th>Prénom Nom</th>
            $firstname = ($d->contact) ? $d->contact->firstname : $d->unknown_contact_name;
            $lastname = ($d->contact) ? $d->contact->lastname : '';

            //Dans le cas stagiaire inconue
            $btn_add_name_to_unkown_member = '';
            if (!$d->contact) {
                $btn_add_name_to_unkown_member = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formEditUnknownContact(' . $d->id . ',' . $enrollment_id . ')" title="Edit"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            }
            $row[] = $firstname . ' ' . $lastname . $btn_add_name_to_unkown_member;
            //<th>Planif</th>
            $spanPlanif = '';
            if ($d->contact) {
                if ($d->contact->id > 0) {
                    $spanPlanif = $DbHelperTools->getPlanifContact($d->id, $d->enrollment->action->id);
                }
            }
            $row[] = $spanPlanif;

            if ($d->enrollment->enrollment_type == 'F') {
                $row[] = ($d->contact) ? $d->contact->type_former_intervention : '';
            }

            //Actions
            $type = "'MEMBER'";
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteEnrollmentMember(' . $d->id . ',' . $type . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $btn_view_student_status = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewStudentStatus(' . $d->id . ')" title="Afficher les statuts de l\'étudiant"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            $has_schedules = Schedulecontact::where('member_id', $d->id)->count() > 0;
            $btn_student_schedule = $has_schedules ? '' : '<button class="btn btn-sm btn-clean btn-icon" onclick="_setStudentSchedule(' . $d->id . ')" title="Mettre à jour le planning de l\'étudiant"><i class="fa fa-calendar"></i></button>';
            $btn_student_cancellation = !$has_schedules ? '' : '<button class="btn btn-sm btn-clean btn-icon" onclick="_setStudentCancellation(' . $d->id . ')" title="Suspendre l\'étudiant"><i class="fa fa-ban"></i></button>';
            $row[] = $btn_delete . $btn_view_student_status . $btn_student_schedule . $btn_student_cancellation;

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
        ];
        return response()->json($result);
    }

    public function sdtPricesAf(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $row = Action::findOrFail($af_id);
        $datas = $row->prices;
        //dd($datas);
        foreach ($datas as $d) {
            $row = array();
            //ID
            $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" value="' . $d->id . '" class="checkable"/><span></span></label>';
            //<th>Titre</th>
            $row[] = ($d->title) ? $d->title : '-';
            //<th>Type d'entité</th>
            $typeEntite = ($d->entity_type == "S") ? '<p>Société</p>' : (($d->entity_type == "P") ? '<p>Particulier</p>' : '');
            $cssClass = 'info';
            $row[] = $typeEntite;
            //<th>Type de tarif</th>
            $row[] = '<p>' . $DbHelperTools->getNameParamByCode($d->device_type) . '</p>';
            //<th>Tarif</th>
            $row[] = '<p class="text-' . $cssClass . '">' . $d->price . ' € / ' . $DbHelperTools->getNameParamByCode($d->price_type) . '</p>';
            //Actions
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteAfRelPrice(' . $d->id . ',' . $af_id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $row[] = $btn_delete;
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
        ];
        return response()->json($result);
    }

    public function deleteAfRelPrice(Request $request)
    {
        $success = false;
        if ($request->isMethod('delete')) {
            if ($request->has('af_id')) {
                $af_id = $request->af_id;
                $ids_prices = $request->ids_prices;
                if (count($ids_prices) > 0) {
                    DB::table('af_rel_price')->whereIn('price_id', $ids_prices)->where('af_id', $af_id)->delete();
                }
                $success = true;
            }
        }
        return response()->json(['success' => $success]);
    }

    public function formRelAfPrice($af_id)
    {
        return view('pages.af.price.form', ['af_id' => $af_id]);
    }

    public function storeFormRelAfPrice(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            if ($request->has('prices_ids') && !empty($request->prices_ids)) {
                $data = array(
                    'af_id' => $request->af_id,
                    'prices_ids' => $request->prices_ids,
                );
                $success = $DbHelperTools->attachActionFormationPrices($data);
                $msg = 'Le tarif a été enregistré avec succès';
            } else {
                $msg = 'Veuillez sélectionner au moin un tarif !!';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function copyPricesFromPfToAf($af_id)
    {
        $success = false;
        $msg = 'Veuillez réssayer plus tard !';
        if ($af_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $success = $DbHelperTools->mainCopyPricesFromPfToAf($af_id);
            if ($success) {
                $msg = 'Les tarifs ont été enregistrés avec succès';
            } else {
                $msg = 'Pas de tarifs sur le produit de formation';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formEnrollmentIntervenants($af_id, $row_id)
    {
        $row = null;
        $nb_unknown_members = 0;
        $price_id = 0;
        if ($row_id > 0) {
            $row = Enrollment::findOrFail($row_id);
            $price = Price::where([['price', $row->price], ['price_type', $row->price_type]])->first();
            $price_id = ($price) ? $price->id : 0;
        }
        return view('pages.af.enrollment.intervenants.form', ['row' => $row, 'af_id' => $af_id, 'price_id' => $price_id]);
    }

    public function storeFormEnrollmentIntervenants(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $enrollment_id = $request->id;
            $nb_participants = count($request->members);
            if ($request->id == 0 && $request->entitie_id > 0) {
                $rs = Enrollment::select('id', 'nb_participants')->where([['af_id', $request->af_id], ['entitie_id', $request->entitie_id], ['enrollment_type', 'F']])->first();
                if ($rs) {
                    $enrollment_id = $rs->id;
                    $nb_participants = $nb_participants + $rs->nb_participants;
                }
                /* if (count($rs) > 0) {
                    $msg = 'Formateur déjà inscrit !';
                    return response()->json([
                        'success' => $success,
                        'msg' => $msg,
                    ]); 
                } */
            }
            //dd($enrollment_id);
            //$price = Price::find($request->price_id);
            $dataEnrollment = array(
                'id' => $enrollment_id,
                'entitie_id' => $request->entitie_id,
                'nb_participants' => $nb_participants,
                //'price'=>($price)?$price->price:0,
                'price' => null,
                'price_type' => null,
                //'price_type'=>($price)?$price->price_type:null,
                'af_id' => $request->af_id,
                'enrollment_type' => $request->enrollment_type,
            );
            $enrollment_id = $DbHelperTools->manageEnrollment($dataEnrollment);
            if ($enrollment_id > 0) {
                if ($request->has('members')) {
                    if (count($request->members) > 0) {
                        foreach ($request->members as $contact_id) {
                            $nb = Member::select('id')->where([['contact_id', $contact_id], ['enrollment_id', $enrollment_id]])->count();
                            if ($nb == 0) {
                                $rs = Member::select('id')->where([['contact_id', $contact_id], ['enrollment_id', $enrollment_id]])->first();
                                $id = ($rs) ? $rs['id'] : 0;
                                $data_member = array(
                                    'id' => $id,
                                    'unknown_contact_name' => null,
                                    'contact_id' => $contact_id,
                                    'enrollment_id' => $enrollment_id,
                                );
                                $member_id = $DbHelperTools->manageMember($data_member);
                            }
                        }
                    }
                }
                $success = true;
                $msg = 'L\'inscription à été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function sdtEnrollmentsMembers(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = [];
        if ($af_id > 0) {
            $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'F']])->get()->pluck('id');
            if (count($ids_enrollments) > 0) {
                $datas = Member::whereIn('enrollment_id', $ids_enrollments)->get();
            }
        }
        foreach ($datas as $d) {
            $row = array();
            //th
            $row[] = $d->id;
            //<th>Prénom</th>
            $firstname = ($d->contact) ? $d->contact->firstname : $d->unknown_contact_name;
            $row[] = $firstname;
            //Nom
            $lastname = ($d->contact) ? $d->contact->lastname : '';
            $row[] = $lastname;
            //<th>Type</th>
            $row[] = ($d->contact) ? $d->contact->type_former_intervention : '';
            //<th>Etat Planning</th>
            $spanPlanif = '';
            $spanPlanifNbHour = 0;
            $totalCost = 0;

            if ($d->contact) {
                if ($d->contact->id > 0) {
                    $spanPlanif = $DbHelperTools->getPlanifContact($d->id, $d->enrollment->action->id);
                    $tab_hours_by_sessions = $DbHelperTools->getNumberHoursPlannedForContactBySessions($d->id, $d->enrollment->action->id);
                    foreach ($tab_hours_by_sessions as $session_id => $nb_hours) {
                        $session = Session::find($session_id);
                        $spanPlanifNbHour += $session->nb_hours;
                        /*$contract_ids = SessionDate::select('contract_id')
                        ->join('af_schedules','af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
                        ->join('af_schedulecontacts','af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
                        ->where('af_sessiondates.session_id', $session_id)
                        ->where('af_schedulecontacts.member_id', $d->id)->distinct()//->get();
                        ->pluck('contract_id')->toArray();*/
                        // dd($contract_ids);
                        $totalCost = $DbHelperTools->getTotalPriceMembreFormer($d->id);
                        $totalCost = number_format($totalCost, 2) . ' €';
                        // foreach ($contract_ids as $contract_id) {
                        //     //aji hnaya
                        //     $totalCost=$DbHelperTools->getTotalPriceMembreFormer($contract_id);
                        //     $totalCost=number_format($totalCost,2). ' €';
                        // } 
                    }
                }
            }

            if($d->contact->type_former_intervention == "Sur facture"){
                $btn_devis = '<br/><button class="btn btn-sm btn-clean btn-icon" onclick="_selection_af('.$d->id.')" title="Demande de devis"><i class="' . $tools->getIconeByAction('ENVELOPE') . '"></i></button>';
            }
            else{
                $btn_devis = '';
            }

            $row[] = $spanPlanif;
            //<th>Nb heure</th>
            // $row[] = $spanPlanifNbHour .'h';
            $row[] = Helper::convertTime($DbHelperTools->getNbHoursMembreFormer($d->id)) . 'h';
            //<th>Cout</th>
            $row[] = $totalCost . '';
            //Actions
            $type = "'MEMBER'";
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteEnrollmentMember(' . $d->id . ',' . $type . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>'.$btn_devis;
            $row[] = $btn_delete;

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
        ];
        return response()->json($result);
    }
    public function formRemuneration($af_id, $member_id)
    {
        $row = $contactFormer = $types = null;
        if ($member_id > 0) {
            $member = Member::findOrFail($member_id);
            $typeEntity = ($member->enrollment->entity) ? $member->enrollment->entity->entity_type : '';
            $nameEntity = ($member->enrollment->entity) ? $member->enrollment->entity->name . ' (' : '';
            $refEntity = ($member->enrollment->entity) ? $member->enrollment->entity->ref . ')' : '';
            $entityInfos = $typeEntity . (($member->enrollment->entity->entity_type == 'S') ? ' : ' . $nameEntity . $refEntity : '');
            $contactFormer = $member->contact->firstname . ' ' . $member->contact->lastname . ' - ' . $member->contact->type_former_intervention . ' - ' . $entityInfos;
        }
        $DbHelperTools = new DbHelperTools();
        $types = $DbHelperTools->getParamsByParamCode('TYPE_INTERVENTION');
        return view('pages.af.price.remuneration', ['af_id' => $af_id, 'member_id' => $member_id, 'contactFormer' => $contactFormer, 'types' => $types]);
    }
    public function storeFormRemuneration(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();

            $schedulescontacts_ids = [];
            //SC1
            if ($request->has('schedulescontacts_ids') && !empty($request->schedulescontacts_ids)) {
                $myArrayDatas = explode(',', $request->schedulescontacts_ids);
                if (count($myArrayDatas) > 0) {
                    foreach ($myArrayDatas as $dt) {
                        $tab_id = explode('SCFORMER', $dt);
                        (isset($tab_id[1])) ? ($schedulescontacts_ids[] = $tab_id[1]) : '';
                    }
                }
            }
            if (count($schedulescontacts_ids) == 0) {
                $msg = 'Veuillez sélectionner les intervenants !';
            }

            if (count($schedulescontacts_ids) > 0) {
                $price_type = null;

                $price = $request->price;
                //dd($price);
                if ($request->type_former_intervention == "Sur contrat") {
                    $price = ($request->is_other_price == 0) ? $request->price : $request->other_price;
                    $price_type = $request->price_type;
                }
                //dd($price);
                $iSuccess = 0;
                foreach ($schedulescontacts_ids as $sc_id) {
                    $schedulecontact = Schedulecontact::find($sc_id);
                    $duration = $schedulecontact->schedule->duration;
                    $total_cost = ($request->type_former_intervention == "Sur contrat") ? $duration * $price : $price;
                    if ($request->type_former_intervention == "Sur facture") {
                        $price = null;
                    }
                    $data = array(
                        'id' => $sc_id,
                        'is_former' => $schedulecontact->is_former,
                        'price' => $price,
                        'price_type' => $price_type,
                        'total_cost' => $total_cost,
                        'is_absent' => $schedulecontact->is_absent,
                        'type_absent' => $schedulecontact->type_absent,
                        'type_of_intervention' => $request->type_of_intervention,
                        'schedule_id' => $schedulecontact->schedule_id,
                        'member_id' => $schedulecontact->member_id,
                        'contract_id' => $schedulecontact->contract_id,
                    );
                    //dd($data);
                    $row_id = $DbHelperTools->manageSchedulecontact($data);
                    if ($row_id > 0) {
                        $iSuccess++;
                    }
                }
                if ($iSuccess > 0) {
                    $success = true;
                    $msg = 'La rémunération à été enregistrée avec succès';
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function treeSchedulesRessources($af_id, $mode)
    {
        //$mode : withressources or noressources
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $datas = [];
        $sessions = Session::select('id', 'code', 'title')->where('af_id', $af_id)->get();
        if (count($sessions) > 0) {
            foreach ($sessions as $session) {
                //Session
                $datas[] = array(
                    "id" => $session->id,
                    "text" => $session->title . '(' . $session->code . ')',
                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                    "icon" => "fa fa-folder text-info",
                    "parent" => '#'
                );
                //Session dates
                $sessiondates = Sessiondate::select('id', 'planning_date')->where('session_id', $session->id)->get();
                if (count($sessiondates) > 0) {
                    foreach ($sessiondates as $sd) {
                        $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                        $datas[] = array(
                            "id" => 'D' . $sd->id,
                            "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "icon" => "fa fa-calendar text-primary",
                            "parent" => $session->id
                        );
                        //Schedules : séances
                        $rs_schedules = Schedule::select('id', 'start_hour', 'end_hour', 'duration')->where('sessiondate_id', $sd->id)->get();
                        if (count($rs_schedules) > 0) {
                            foreach ($rs_schedules as $schedule) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');
                                $duration = Helper::convertTime($schedule->duration);
                                $checkbox_disabled = false;
                                $datas[] = array(
                                    "id" => 'SCHEDULE' . $schedule->id,
                                    "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                    "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                    "icon" => "fa fa-folder text-dark",
                                    "parent" => 'D' . $sd->id
                                );
                                //Scheduleressources : les ressources
                                if ($mode == 'withressources') {
                                    //Les ressources
                                    $rs_scheduleressources = Scheduleressource::where('schedule_id', $schedule->id)->get();
                                    if (count($rs_scheduleressources) > 0) {
                                        $datas[] = array(
                                            "id" => 'TITLE_RESSOURCES' . $schedule->id,
                                            "text" => '<span class="text-warning">Liste des ressources : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                        foreach ($rs_scheduleressources as $sr) {
                                            $interne = ($sr->ressource && $sr->ressource->type == "RES_TYPE_LIEU") ? (($sr->ressource->is_internal == 1) ? ' - Interne' : ' - Externe') : '';
                                            $ressource_name = ($sr->ressource) ? $sr->ressource->name . ' <span class="text-primary">(' . $DbHelperTools->getNameParamByCode($sr->ressource->type) . ' ' . $interne . ')</span>' : '';
                                            $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleRessource(' . $sr->id . ')" title="Supprimer la ressource"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                            $datas[] = array(
                                                "id" => 'SCONTACT' . $sr->id,
                                                "text" => $ressource_name . $btnDelete,
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "fas fa-laptop-house text-primary",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return response()->json($datas);
    }

    public function storeFormScheduleRessource(Request $request)
    {
        $start_hour = null;
        $reeldate = null;
        $end_hour = null;
        $planning_date = null;
        $haj = [];
        $success = false;
        $msg = 'Ooops !';
        $messages = [];
        $messagesParent = [];
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $schedules_ids = [];
            //S1,S2,S3,S4,S5,S6
            if ($request->has('schedules_ids') && !empty($request->schedules_ids)) {
                $myArrayDatas = explode(',', $request->schedules_ids);
                if (count($myArrayDatas) > 0) {
                    foreach ($myArrayDatas as $dt) {
                        $tab_id = explode('SCHEDULE', $dt);
                        (isset($tab_id[1])) ? ($schedules_ids[] = $tab_id[1]) : '';
                    }
                }
            }
            $ressources_ids = [];
            if ($request->has('ressources_ids') && !empty($request->ressources_ids)) {
                $ressources_ids = $request->ressources_ids;
            }
            if (count($ressources_ids) == 0) {
                $msg = 'Veuillez sélectionner des ressources !';
            }
            if (count($schedules_ids) == 0) {
                $msg = 'Veuillez sélectionner les séances !';
            }
            if (count($schedules_ids) == 0 && count($ressources_ids) == 0) {
                $msg = 'Veuillez sélectionner les ressources et les séances !';
            }
            if (count($schedules_ids) > 0 && count($ressources_ids) > 0) {
                foreach ($schedules_ids as $schedule_id) {
                    foreach ($ressources_ids as $ressource_id) {
                        $rsCheckParent = $DbHelperTools->checkRessourceParentScheduled($ressource_id, $schedule_id);
                        $can_be_scheduled = $rsCheckParent['can_be_scheduled'];
                        if (count($rsCheckParent['messages']) > 0) {
                            $messagesParent[$ressource_id] = $rsCheckParent['messages'];
                        }

                        if ($can_be_scheduled) {
                            $rsCheck = $DbHelperTools->checkIfRessourceAvailableToSchedule($ressource_id, $schedule_id);
                            $toschedule = $rsCheck['toschedule'];
                            if (count($rsCheck['messages']) > 0) {
                                $messages[$ressource_id][] = $rsCheck['messages'];
                            }
                            if ($toschedule == true) {
                                $rs = Scheduleressource::select('id')->where([['schedule_id', $schedule_id], ['ressource_id', $ressource_id]])->get();
                                $scheduleressource_id = 0;
                                if (count($rs) > 0) {
                                    $scheduleressource_id = $rs[0]['id'];
                                }
                                $data = array(
                                    'id' => $scheduleressource_id,
                                    'schedule_id' => $schedule_id,
                                    'ressource_id' => $ressource_id,
                                );
                                $DbHelperTools->manageScheduleressource($data);
                                $success = true;
                                $msg = 'L\'affectation à été enregistrée avec succès';
                            } else {
                                $success = false;
                                $msg = 'La ressource est déjà utilisé';
                            }
                        }
                    }
                }
            }
        }
        $html = $DbHelperTools->generateMessagesControlRessourcesParent($messagesParent);
        $html .= $DbHelperTools->generateMessagesControlRessources($messages);

        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'htmlMessage' => $html,
        ]);
    }

    public function deleteScheduleRessource($scheduleressource_id)
    {
        $success = false;
        $DbHelperTools = new DbHelperTools();
        if ($scheduleressource_id) {
            $deletedRows = $DbHelperTools->massDeletes([$scheduleressource_id], 'scheduleressource', 1);
            if ($deletedRows)
                $success = true;
        }
        return response()->json(['success' => $success]);
    }

    public function formFormerPriceByTypeIntervention($member_id)
    {
        $type_former_intervention = null;
        if ($member_id > 0) {
            $member = Member::findOrFail($member_id);
            $type_former_intervention = $member->contact->type_former_intervention;
        }
        
        // fetch the prices from the database and pass them to the view
        $prices = Price::all()->pluck('name', 'id')->toArray();
        
        return view('pages.af.price.form.formerprice', [
            'type_former_intervention' => $type_former_intervention,
            'prices' => $prices,
        ]);
    }

    public function treeSchedulesFormers($af_id, $member_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $datas = [];

        //Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 1], ['member_id', $member_id]])->get();


        $ids_sessions = DB::table('af_schedulecontacts')
            ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
            ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
            ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
            ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
            ->where([['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.member_id', $member_id], ['af_sessions.af_id', $af_id]])
            ->pluck('af_sessiondates.session_id')->unique();

        $ids_sessiondates = DB::table('af_schedulecontacts')
            ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
            ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
            ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
            ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
            ->where([['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.member_id', $member_id], ['af_sessions.af_id', $af_id]])
            ->pluck('af_schedules.sessiondate_id')->unique();

        $ids_schedules = DB::table('af_schedulecontacts')
            ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
            ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
            ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
            ->select('af_schedulecontacts.id', 'af_schedulecontacts.total_cost', 'af_schedulecontacts.schedule_id', 'af_schedules.sessiondate_id', 'af_sessiondates.planning_date', 'af_sessiondates.session_id', 'af_sessions.id', 'af_sessions.af_id')
            ->where([['af_schedulecontacts.is_former', 1], ['af_schedulecontacts.member_id', $member_id], ['af_sessions.af_id', $af_id]])
            ->pluck('af_schedulecontacts.schedule_id')->unique();



        //dd($r);            
        $sessions = Session::where('af_id', $af_id)->whereIn('id', $ids_sessions)->get();
        if (count($sessions) > 0) {
            foreach ($sessions as $session) {
                //Session
                $datas[] = array(
                    "id" => $session->id,
                    "text" => $session->title . ' (' . $session->code . ')',
                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                    "icon" => "fa fa-folder text-info",
                    "parent" => '#'
                );
                //Session dates
                $sessiondates = Sessiondate::where('session_id', $session->id)->whereIn('id', $ids_sessiondates)->get();
                if (count($sessiondates) > 0) {
                    foreach ($sessiondates as $sd) {
                        $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                        $datas[] = array(
                            "id" => 'D' . $sd->id,
                            "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "icon" => "fa fa-calendar text-primary",
                            "parent" => $session->id
                        );
                        //Schedules : séances
                        $rs_schedules = Schedule::where('sessiondate_id', $sd->id)->whereIn('id', $ids_schedules)->get();
                        if (count($rs_schedules) > 0) {
                            foreach ($rs_schedules as $schedule) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');
                                $duration = Helper::convertTime($schedule->duration);
                                //Intervenants
                                $checkbox_disabled = false;
                                $datas[] = array(
                                    "id" => 'SCHEDULE' . $schedule->id,
                                    "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                    "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                    "icon" => "fa fa-folder text-dark",
                                    "parent" => 'D' . $sd->id
                                );
                                //Intervenants
                                $rs_schedulecontacts_formers = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 1], ['member_id', $member_id]])->get();
                                if (count($rs_schedulecontacts_formers) > 0) {
                                    foreach ($rs_schedulecontacts_formers as $scf) {

                                        $entityName = $scf->member->enrollment->entity->entity_type . ': ' . $scf->member->enrollment->entity->name . ' (' . $scf->member->enrollment->entity->ref . ')';
                                        $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';
                                        $price = '';
                                        $type_former_intervention = $scf->member->contact->type_former_intervention;
                                        $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule->duration, $scf->price, $type_former_intervention);
                                        $total_cost = ($scf_total_cost > 0) ? '<span class="text-primary"> - coût total : ' . $scf_total_cost . ' €</span>' : '';
                                        if ($scf->price == null) {
                                            $price = '';
                                        }else if ($scf->price > 0) {
                                            $price = '' . $scf->price . ' €/' . $DbHelperTools->getNameParamByCode($scf->price_type) . $total_cost;
                                        }else{            
                                            $price = '' . $scf->price . ' €/' . $DbHelperTools->getNameParamByCode($scf->price_type) . $total_cost;
                                        }
                                        $contractNumber = ($scf->contract_id > 0) ? ' (' . $scf->contract->number . ')' : '';

                                        $type_of_intervention = ($scf->type_of_intervention) ? ' - Type : ' . $DbHelperTools->getNameParamByCode($scf->type_of_intervention) : '';
                                        $pTypeIntervention = '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $scf->member->contact->type_former_intervention . $contractNumber . $type_of_intervention . '</p>';
                                        $pPrice = ($price) ? '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $price . '</p>' : '';
                                        $spanMemberName = ($scf->member->contact) ? '<span class="text-dark">' . ($scf->member->contact->firstname . ' ' . $scf->member->contact->lastname) . '<span>' : '';

                                        $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $scf->id . ')" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                        //$btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration('.$scf->id.')" title="Rémunération"><i class="'.$tools->getIconeByAction('PRICE').' text-success"></i></a>';

                                        $datas[] = array(
                                            "id" => 'SCFORMER' . $scf->id,
                                            "text" => $spanMemberName . $btnDelete . $pTypeIntervention . $pPrice,
                                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                                            "icon" => "far fa-id-badge text-dark",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return response()->json($datas);
    }

    public function sdtContracts(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = $datas = $ids_contacts = [];
        $contact_id = 0;
        $enrollments_ids = [];

        $userid = auth()->user()->id;
        $roles = auth()->user()->roles;

        if ($roles[0]->code == 'FORMATEUR') {
            $contactid = DB::table('users')
                ->where('id', $userid)
                ->pluck('contact_id');

            $enrollment_id = DB::table('af_members')->whereIn('contact_id', $contactid)->pluck('enrollment_id');
            $listaf_id = DB::table('af_enrollments')->whereIn('id', $enrollment_id)->pluck('af_id');

            $enrollment_id = DB::table('af_members')->where('contact_id', $contactid)->pluck('enrollment_id');
            if (count($listaf_id) > 0) {
                $enrollments_ids = Enrollment::select('id')->whereIn('id', $enrollment_id)->whereIn('af_id', $listaf_id)->where('enrollment_type', 'F')->pluck('id');
            }

            if (count($enrollments_ids) > 0) {
                $ids_contacts = Member::select('contact_id')->whereIn('enrollment_id', $enrollments_ids)->pluck('contact_id');
            }
            if (count($ids_contacts) > 0) {
                $datas = Contract::whereIn('contact_id', $ids_contacts)->orderBy('id', 'desc')->get();
            }
            foreach ($datas as $d) {
                $row = array();
                $spanAfName = '';
                $rsAfInfos = $DbHelperTools->getAfInfosFromContract($d->id);
                //ID
                $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="contracts[]" value="' . $d->id . '" class="checkable" />
                        <span></span>
                        </label>';
                //<th>Contrat</th>
                //$sc=Schedulecontact::select('id','member_id')->where('contract_id',$d->id)->first();
                //$member_id=($sc && $sc->member_id)?$sc->member_id:0;
                $exist = $DbHelperTools->getSchedulecontactsWithoutContracts($d->contact_id);
                $spanAlert = ($exist) ? '<p class="text-warning font-size-sm"><i class="flaticon-warning-sign text-warning"></i> Il existe des séances non rattachées au contrat</p>' : '';
                $nameContact = $d->contact != null ? ('<br><span class="font-size-sm">' . $d->contact->gender . ' ' . $d->contact->lastname . ' ' . $d->contact->firstname . '</span>') : '';
                $spanAf = ($rsAfInfos['af_id'] > 0 && $af_id < 1) ? '<p><a href="/view/af/' . $rsAfInfos['af_id'] . '">' . $rsAfInfos['title'] . '</a></p>' : '';
                $row[] = '<span class="text-primary">' . $d->number . '</span> ' . $nameContact . $spanAlert . $spanAf;
                //<th>Intervention</th>
                //$row[] = $DbHelperTools->getInterventionContractFormer($d->id);
                //<th>Coût</th>
                $totalCost = $DbHelperTools->getTotalPriceContractFormer($d->id);
                $row[] = number_format($totalCost, 2) . ' €';
                //<th>NB H</th>
                $btn_planif_details = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_showFormerScheduleDetails(' . $d->id . ')" title="Détails du planning"><i class="' . $tools->getIconeByAction('INFO') . '"></i></button>';
                $row[] = Helper::convertTime($DbHelperTools->getNbHoursContractFormer($d->id)) . $btn_planif_details;
                //<th>Etat</th>
                $spanState = $tools->constructParagraphLabelDot('xs', 'primary', 'Etat : ' . $DbHelperTools->getNameParamByCode($d->state));
                $spanStatus = $tools->constructParagraphLabelDot('xs', 'primary', 'Status : ' . $DbHelperTools->getNameParamByCode($d->status));
                $row[] = $spanState . $spanStatus;
                //<th>Infos</th>
                $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                $row[] = $created_at . $updated_at;
                //Actions
                $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formContractFormer(' . $d->id . ',' . $af_id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteContract(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
                $btn_pdf = '<a target="_blank" href="/pdf/contract/' . $d->id . '/1" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF contrat</span></a>';
                $btn_pdf_download = '<a target="_blank" href="/pdf/contract/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger contrat</span></a>';
                //$generateWordFileBtnOption = '<a target="_blank" href="/pdf/contract/' . $d->id . '/3" class="navi-link"><span class="navi-icon"><i class="'.$tools->getIconeByAction('DOCX').'"></i></span> <span class="navi-text">DOCX contrat</span></a>';
                $generateWordFileBtnOption = '';

                $btn_attached_docs = '<a href="javascript:void(0)" onclick="_modalAttachedDocsContract(' . $d->id . ',' . $rsAfInfos['af_id'] . ')" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></span><span class="navi-text">Documents attachés</span></a>';
                
                $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                        data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                            <ul class="navi flex-column navi-hover py-2">
                                <li class="navi-item">
                                    ' . $btn_pdf . '
                                    ' . $btn_attached_docs . '
                                    ' . $generateWordFileBtnOption . '
                                    ' . $btn_pdf_download . '
                                </li>
                            </ul>
                        </div>
                    </div>';

                if ($roles[0]->code == 'FORMATEUR')
                    $row[] = $btn_more;
                else
                    $row[] = $btn_edit . $btn_delete . $btn_more;

                $data[] = $row;
            }
        } else {
            if ($af_id > 0) {
                $enrollments_ids = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'F']])->pluck('id');
            } else {
                $enrollments_ids = Enrollment::select('id')->where('enrollment_type', 'F')->pluck('id');
            }

            if (count($enrollments_ids) > 0) {
                $ids_contacts = Member::select('contact_id')->whereIn('enrollment_id', $enrollments_ids)->pluck('contact_id');
            }
            if (count($ids_contacts) > 0) {
                $datas = Contract::whereIn('contact_id', $ids_contacts)->orderBy('id', 'desc')->get();
            }
            foreach ($datas as $d) {
                $row = array();
                $spanAfName = '';
                $rsAfInfos = $DbHelperTools->getAfInfosFromContract($d->id);
                //ID
                $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="contracts[]" value="' . $d->id . '" class="checkable" />
                        <span></span>
                        </label>';
                //<th>Contrat</th>
                //$sc=Schedulecontact::select('id','member_id')->where('contract_id',$d->id)->first();
                //$member_id=($sc && $sc->member_id)?$sc->member_id:0;
                $exist = $DbHelperTools->getSchedulecontactsWithoutContracts($d->contact_id);
                $spanAlert = ($exist) ? '<p class="text-warning font-size-sm"><i class="flaticon-warning-sign text-warning"></i> Il existe des séances non rattachées au contrat</p>' : '';
                $nameContact = $d->contact != null ? ('<br><span class="font-size-sm">' . $d->contact->gender . ' ' . $d->contact->lastname . ' ' . $d->contact->firstname . '</span>') : '';
                $spanAf = ($rsAfInfos['af_id'] > 0 && $af_id < 1) ? '<p><a href="/view/af/' . $rsAfInfos['af_id'] . '">' . $rsAfInfos['title'] . '</a></p>' : '';
                $row[] = '<span class="text-primary">' . $d->number . '</span> ' . $nameContact . $spanAlert . $spanAf;
                //<th>Intervention</th>
                //$row[] = $DbHelperTools->getInterventionContractFormer($d->id);
                //<th>Coût</th>
                $totalCost = $DbHelperTools->getTotalPriceContractFormer($d->id);
                $row[] = number_format($totalCost, 2) . ' €';
                //<th>NB H</th>
                $btn_planif_details = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_showFormerScheduleDetails(' . $d->id . ')" title="Détails du planning"><i class="' . $tools->getIconeByAction('INFO') . '"></i></button>';
                $row[] = Helper::convertTime($DbHelperTools->getNbHoursContractFormer($d->id)) . $btn_planif_details;
                //<th>Etat</th>
                $spanState = $tools->constructParagraphLabelDot('xs', 'primary', 'Etat : ' . $DbHelperTools->getNameParamByCode($d->state));
                $spanStatus = $tools->constructParagraphLabelDot('xs', 'primary', 'Status : ' . $DbHelperTools->getNameParamByCode($d->status));
                $row[] = $spanState . $spanStatus;
                //<th>Infos</th>
                $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                $row[] = $created_at . $updated_at;
                //Actions
                $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formContractFormer(' . $d->id . ',' . $af_id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteContract(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
                $btn_pdf = '<a target="_blank" href="/pdf/contract/' . $d->id . '/1" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF contrat</span></a>';
                $btn_pdf_download = '<a target="_blank" href="/pdf/contract/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger contrat</span></a>';
                //$generateWordFileBtnOption = '<a target="_blank" href="/pdf/contract/' . $d->id . '/3" class="navi-link"><span class="navi-icon"><i class="'.$tools->getIconeByAction('DOCX').'"></i></span> <span class="navi-text">DOCX contrat</span></a>';
                $generateWordFileBtnOption = '';

                $btn_attached_docs = '<a href="javascript:void(0)" onclick="_modalAttachedDocsContract(' . $d->id . ',' . $rsAfInfos['af_id'] . ')" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></span><span class="navi-text">Documents attachés</span></a>';
                $btn_sign_docs = $d->state != 'SC_GENERATED' || Auth::user()->roles->filter(function ($r) {return $r->profil->code == 'CRFPE';})->isEmpty()
                ? '' 
                : '<a href="javascript:void(0)" onclick="_signContract(' . $d->id . ')" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('SIGN') . '"></i></span><span class="navi-text">Signer</span></a>';
                $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                        data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                            <ul class="navi flex-column navi-hover py-2">
                                <li class="navi-item">
                                    ' . $btn_pdf . '
                                    ' . $btn_attached_docs . '
                                    ' . $generateWordFileBtnOption . '
                                    ' . $btn_pdf_download . '
                                    ' . $btn_sign_docs . '
                                </li>
                            </ul>
                        </div>
                    </div>';
                $row[] = $btn_edit . $btn_delete . $btn_more;
                $data[] = $row;
            }
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
        ];
        return response()->json($result);
    }

    public function formContract($contract_id, $af_id)
    {
        $contract = null;
        //$member_id = 0;
        if ($contract_id > 0) {
            $contract = Contract::findOrFail($contract_id);
        }
        $DbHelperTools = new DbHelperTools();
        //ETATS
        $states = $DbHelperTools->getParamsByParamCode('STATE_CONTRACT');
        //STATUS
        $status = $DbHelperTools->getParamsByParamCode('STATUS_CONTRACT');

        return view('pages.af.document.contract.form', compact('contract', 'af_id', 'states', 'status'));
    }


    public function formselectionafintervenant($member_id)
    {
        $schedules = array();
        $ids_schedule = array();
        $temoin = 0;
        
        $id_contact = DB::table('af_members')->where('id',$member_id)->pluck('contact_id');

        $ids_schedule =  Schedulecontact::select('schedule_id')->where([['member_id',$member_id],['estimate_id',NULL]])->pluck('schedule_id');

        if(count($ids_schedule) != 0){

            $temoin = 0;

                for($i = 0; $i<count($ids_schedule);$i++)
                {
                    $schedules[] = Schedule::findOrFail(intval($ids_schedule[$i]));
                }
         }
         else{
            $temoin = 1;
         }

        return view('pages.af.schedule.form', compact('schedules','member_id','temoin'));
    }

    public function formMailDevis($id_schedule,$member_id)
    {
        $row = null;
        $content = $subject = '';
        $user=auth()->user()->email;

        $id_contact = Member::select('contact_id')->where('id',$member_id)->pluck('contact_id');
        $entitie_id = Contact::select('entitie_id')->where('id',intval($id_contact[0]))->pluck('entitie_id');
        $row = Contact::findOrFail(intval($id_contact[0]));
        // $fullname = $contact->firstname." ".$contact->lastname;

        $Tools = new DbHelperTools();

        $firstname_former = Contact::select('firstname')->where('id',intval($id_contact[0]))->pluck('firstname');
        $lastname_former = Contact::select('lastname')->where('id',intval($id_contact[0]))->pluck('lastname');

        $schedule = Schedule::findOrFail(intval($id_schedule));

        $todayDate = Carbon::now();

        $estimate_number = "DF".$Tools->generateEstimateNumber('ESTIMATE');

        
        $email_model_id = Emailmodel::select('id')->where('code','ENVOI_DEMANDE_DEVIS_FORMATEUR_SUR_FACTURE')->pluck('id');
        $emailmodel = Emailmodel::findOrFail(intval($email_model_id[0])); 
       
        $agreement_type = '';
        $subject = strip_tags($emailmodel->custom_header);
        $content = $emailmodel->custom_content;
        $footer =  $emailmodel->custom_footer;
        

        return view('pages.commerce.agreement.form.maildevis', compact('row', 'content', 'agreement_type', 'subject','user'));
    }

    public function formsendrequesttask($id_schedule, $member_id)
    {
        $id_contact = Member::select('contact_id')->where('id',$member_id)->pluck('contact_id');
        $entitie_id = Contact::select('entitie_id')->where('id',intval($id_contact[0]))->pluck('entitie_id');
        $contact = Contact::findOrFail(intval($id_contact[0]));
        $fullname = $contact->firstname." ".$contact->lastname;

        $Tools = new DbHelperTools();

        $firstname_former = Contact::select('firstname')->where('id',intval($id_contact[0]))->pluck('firstname');
        $lastname_former = Contact::select('lastname')->where('id',intval($id_contact[0]))->pluck('lastname');

        $schedule = Schedule::findOrFail(intval($id_schedule));

        $todayDate = Carbon::now();

        $estimate_number = "DF".$Tools->generateEstimateNumber('ESTIMATE');

        $estimate = Estimate::create([
            'estimate_number' => $estimate_number,
            'estimate_date' => $todayDate,
            'valid_until' => $todayDate,
            'status' => 'DEMANDE ENVOYE',
            'discount_label' => 'Remise',
            'discount_type' => 'Before_tax',
            'entitie_id' => $entitie_id[0],
            'contact_id' => $id_contact[0],
            'af_id' => $schedule->sessiondate->session->af->id,
            'type' => 'INT/FACT',
        ]);

        $id_estimate = $estimate->id;

        $id_schedulecontact = Schedulecontact::select('id')->where([['member_id',$member_id],['schedule_id',$id_schedule]])->pluck('id');

        $schedulecontact = Schedulecontact::findOrFail(intval($id_schedulecontact[0]));

        if($schedulecontact){
                $schedulecontact->estimate_id = $id_estimate;
                $schedulecontact->save();
        }

        $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');

        $task = Task::create([
            'title' => $estimate_number.'_'.'WORFLOW DEMANDE DEVIS FORMATEUR SUR FACTURE',
            'description' => 'Concerne la séance :'. $schedule->sessiondate->session->code.' '.$schedule->sessiondate->session->title.', du: '.$schedule->start_hour.' ,de : '.$schedule->duration.' h',
            'etat_id' => $id_etat_en[0],
            'responsable_id' => 6692,
            'apporteur_id' => 6692,
            'start_date' => $todayDate,
            'callback_mode' => 'solaris',
            'entite_id' => $entitie_id[0],
            'contact_id' => $id_contact[0],
            'af_id' => $schedule->sessiondate->session->af->id,
            'sub_task' => 0,
        ]);

        $id_task = $task->id;
        $user_id = auth()->user()->contact_id;

        $date_eche = $schedule->start_hour;

        $date_new = Carbon::createFromFormat('Y-m-d H:i:s', $date_eche);
        $date_new_eche = $date_new->subDays(15)->format('Y-m-d H:i:s'); 

        $call_backdate = Carbon::now()->addDays(15);

        $sub_task = Task::create([
            'title' => $estimate_number.'_'.'Demande de devis Formateur / Facture à '.$firstname_former[0].' '.$lastname_former[0],
            'description' => 'Concerne la séance :'. $schedule->sessiondate->session->code.' '.$schedule->sessiondate->session->title.', du: '.$schedule->start_hour.' ,de : '.$schedule->duration.' h',
            'etat_id' => $id_etat_en[0],
            'responsable_id' => $user_id,
            'apporteur_id' => $user_id,
            'start_date' => $todayDate,
            'ended_date' => $date_new_eche,
            'callback_date' => $call_backdate,
            'callback_mode' => 'email',
            'entite_id' => $entitie_id[0],
            'contact_id' => $id_contact[0],
            'af_id' => $schedule->sessiondate->session->af->id, 
            'task_parent_id' => $id_task,
            'sub_task' => 1,
        ]);

        $email_model_id = Emailmodel::select('id')->where('code','ENVOI_DEMANDE_DEVIS_FORMATEUR_SUR_FACTURE')->pluck('id');
        $emailmodel = Emailmodel::findOrFail(intval($email_model_id[0])); 

        if($contact->email){

                Mail::send('pages.email.model', ['htmlMain' => $emailmodel->custom_content, 'htmlHeader' => $emailmodel->custom_header, 'htmlFooter' => $emailmodel->custom_footer], function ($m) use ($contact,$fullname,$emailmodel) {
                    $m->from(auth()->user()->email);
                    $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
                    $m->to($contact->email, $fullname)->subject($emailmodel->name);
                });

        }
     }


    public function envoyerdemandealot($member_id, Request $request)
    {
        if ($request->isMethod('post')) { 

            $Tools = new DbHelperTools();

            $id_contact = Member::select('contact_id')->where('id',$member_id)->pluck('contact_id');
            $entitie_id = Contact::select('entitie_id')->where('id',intval($id_contact[0]))->pluck('entitie_id');

            $contact = Contact::findOrFail(intval($id_contact[0]));

            $fullname = $contact->firstname." ".$contact->lastname;

            $firstname_former = Contact::select('firstname')->where('id',intval($id_contact[0]))->pluck('firstname');
            $lastname_former = Contact::select('lastname')->where('id',intval($id_contact[0]))->pluck('lastname');

            $data = $request->post();
            $tabid_sche = array();
            $schedule = array();
            $ids_schedulecontact = array();
            $schedulecontacts = array();
            $ids = array();

            $tabid_sche = $data['tabidsche'];
            $todayDate = Carbon::now();

            for($i = 0;$i < count($tabid_sche); $i++)
            {
                $schedule[] = Schedule::findOrFail(intval($tabid_sche[$i]));
            }

            $estimate_number = "DF".$Tools->generateEstimateNumber('ESTIMATE');

            $estimate = Estimate::create([
                    'estimate_number' => $estimate_number,
                    'estimate_date' => $todayDate,
                    'valid_until' => $todayDate,
                    'status' => 'DEMANDE ENVOYE',
                    'discount_label' => 'Remise',
                    'discount_type' => 'Before_tax',
                    'entitie_id' => $entitie_id[0],
                    'contact_id' => $id_contact[0],
                    'af_id' => $schedule[0]->sessiondate->session->af->id,
                    'type' => 'INT/FACT',
                ]);

                $id_estimate = $estimate->id;

                for($i = 0;$i < count($tabid_sche); $i++){

                    $ids_schedulecontact[] = Schedulecontact::select('id')->where([['member_id',$member_id],['schedule_id',$tabid_sche[$i]]])->pluck('id');
                }

                $str = implode(",",$ids_schedulecontact);
                $result = str_replace(array("[", "]"), '', $str);
                $ids = explode(",", $result);

                for($i = 0;$i < count($ids); $i++){

                    $schedulecontacts[] = Schedulecontact::findOrFail(intval($ids[$i]));
                }

                for($i = 0;$i < count($schedulecontacts); $i++){

                    if($schedulecontacts[$i]){
                        $schedulecontacts[$i]->estimate_id = $id_estimate;
                        $schedulecontacts[$i]->save();
                    }
                }

                $desc_task = "";

                for($i = 0;$i < count($schedule); $i++){
                    $desc_task = $desc_task.'Concerne la séance :'. $schedule[$i]->sessiondate->session->code.' '.$schedule[$i]->sessiondate->session->title.', du: '.$schedule[$i]->start_hour.' ,de : '.$schedule[$i]->duration.' h<br/>';
                }

                $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');

                $task = Task::create([
                    'title' => $estimate_number.'_'.'WORFLOW DEMANDE DEVIS FORMATEUR SUR FACTURE',
                    'description' => $desc_task,
                    'etat_id' => $id_etat_en[0],
                    'responsable_id' => 6692,
                    'apporteur_id' => 6692,
                    'start_date' => $todayDate,
                    'callback_mode' => 'solaris',
                    'entite_id' => $entitie_id[0],
                    'contact_id' => $id_contact[0],
                    'af_id' => $schedule[0]->sessiondate->session->af->id,
                    'sub_task' => 0,
                ]);

                $id_task = $task->id;

                $user_id = auth()->user()->contact_id;

                ////////////////////////////////////////////////////////////

                $earliest_date = Carbon::now();

                for($i = 0;$i < count($schedule); $i++){
                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $schedule[$i]->start_hour);
                    if($date < $earliest_date){
                    $earliest_date = $date;
                    }
                }

                $date_new = Carbon::createFromFormat('Y-m-d H:i:s', $earliest_date);
                $date_new_eche = $date_new->subDays(15)->format('Y-m-d H:i:s'); 

                $call_backdate = Carbon::now()->addDays(15);

                $sub_task = Task::create([
                    'title' => $estimate_number.'_'.'Demande de devis Formateur / Facture à '.$firstname_former[0].' '.$lastname_former[0],
                    'description' => $desc_task,
                    'etat_id' => $id_etat_en[0],
                    'responsable_id' => $user_id,
                    'apporteur_id' => $user_id,
                    'start_date' => $todayDate,
                    'ended_date' => $date_new_eche,
                    'callback_date' => $call_backdate,
                    'callback_mode' => 'email',
                    'entite_id' => $entitie_id[0],
                    'contact_id' => $id_contact[0],
                    'af_id' => $schedule[0]->sessiondate->session->af->id, 
                    'task_parent_id' => $id_task,
                    'sub_task' => 1,
                ]);

                $email_model_id = Emailmodel::select('id')->where('code','ENVOI_DEMANDE_DEVIS_FORMATEUR_SUR_FACTURE')->pluck('id');
                $emailmodel = Emailmodel::findOrFail(intval($email_model_id[0])); 
                if($contact->email){
                        Mail::send('pages.email.model', ['htmlMain' => $emailmodel->custom_content, 'htmlHeader' => $emailmodel->custom_header, 'htmlFooter' => $emailmodel->custom_footer], function ($m) use ($contact,$fullname,$emailmodel) {
                            $m->from(auth()->user()->email);
                            $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
                            $m->to($contact->email, $fullname)->subject($emailmodel->name);
                        });
                }

        }
    }


    public function storeFormContract(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $schedulescontacts_ids = [];
            //SCFORMER
            if ($request->has('schedulescontacts_ids') && !empty($request->schedulescontacts_ids)) {
                $myArrayDatas = explode(',', $request->schedulescontacts_ids);
                if (count($myArrayDatas) > 0) {
                    foreach ($myArrayDatas as $dt) {
                        $tab_id = explode('SCFORMER', $dt);
                        (isset($tab_id[1])) ? ($schedulescontacts_ids[] = $tab_id[1]) : '';
                    }
                }
            }
            if (count($schedulescontacts_ids) == 0) {
                $msg = 'Veuillez sélectionner les lignes d\'intervention !';
            }

            //dd($schedulescontacts_ids);
            if (count($schedulescontacts_ids) > 0) {

                if ($request->id == 0) {
                    $price = Schedulecontact::select('total_cost')->whereIn('id', $schedulescontacts_ids)->sum('total_cost');
                } else {
                    //$rscontract = Contract::select('price')->where('id',$request->id)->first();
                    $price1 = Schedulecontact::select('total_cost')->where('contract_id', $request->id)->sum('total_cost');
                    $price2 = Schedulecontact::select('total_cost')->whereIn('id', $schedulescontacts_ids)->sum('total_cost');
                    $price = $price1 + $price2;
                }

                $data = array(
                    'id' => $request->id,
                    'number' => ($request->id == 0) ? $DbHelperTools->generateContractNumber($request->id) : null,
                    'price' => $price,
                    'accounting_code' => $request->accounting_code,
                    'state' => $request->state,
                    'status' => $request->status,
                    'signed_at' => null,
                    'contact_id' => $request->contact_id,
                );
                //dd($data);
                $contract_id = $DbHelperTools->manageContract($data);
                if ($contract_id > 0) {
                    Schedulecontact::whereIn('id', $schedulescontacts_ids)->update(['contract_id' => $contract_id]);
                    $success = true;
                    $msg = 'Le contrat à été enregistrée avec succès';
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function selectFormersMembersOptions($af_id, $type_former_intervention, $contact_id)
    {

        //$ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'F']])->pluck('id');
        //$ids_members=Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->pluck('id');
        //$ids_members_has_contract=Schedulecontact::select('member_id')->whereIn('member_id', $ids_members)->where('contract_id','>',0)->pluck('member_id')->unique();

        /*
        $type_former_intervention 1 == Sur facture
        $type_former_intervention 2 == Sur contrat
        */
        if ($type_former_intervention == 1) {
            $type_former_intervention = 'Sur facture';
        } elseif ($type_former_intervention == 2) {
            $type_former_intervention = 'Sur contrat';
        } else {
            $type_former_intervention = 'All';
        }
        //dd($type_former_intervention);
        $result = $datas = [];
        $DbHelperTools = new DbHelperTools();
        if ($af_id > 0) {
            $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'F']])->pluck('id');
            if (count($ids_enrollments) > 0) {
                if ($contact_id > 0) {
                    $datas = Member::where('contact_id', $contact_id)->whereIn('enrollment_id', $ids_enrollments)->get();
                } else {
                    $datas = Member::whereIn('enrollment_id', $ids_enrollments)->get();
                }
                //dd($datas);
            }
        } else {
            $ids_enrollments = Enrollment::select('id')->where('enrollment_type', 'F')->pluck('id');
            if (count($ids_enrollments) > 0) {
                $ids_members = Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->pluck('id');
                $ids_members_has_schedules = Schedulecontact::select('member_id')->whereIn('member_id', $ids_members)->pluck('member_id')->unique();
                if ($contact_id > 0) {
                    $datas = Member::where('contact_id', $contact_id)->whereIn('id', $ids_members_has_schedules)->get();
                } else {
                    $datas = Member::whereIn('id', $ids_members_has_schedules)->get();
                }
            }
        }
        if (count($datas) > 0) {
            foreach ($datas as $member) {
                $contactFormer = '';
                $contact = $member->contact;
                $price = $member->enrollment->price . ' € / ' . $DbHelperTools->getNameParamByCode($member->enrollment->price_type);
                if ($contact) {
                    $typeEntity = ($member->enrollment->entity) ? $member->enrollment->entity->entity_type : '';
                    $nameEntity = ($member->enrollment->entity) ? $member->enrollment->entity->name . ' (' : '';
                    $refEntity = ($member->enrollment->entity) ? $member->enrollment->entity->ref . ')' : '';
                    $entityInfos = $typeEntity . (($member->enrollment->entity->entity_type == 'S') ? ' : ' . $nameEntity . $refEntity : '');
                    $contactFormer = $contact->firstname . ' ' . $contact->lastname . ' - ' . $contact->type_former_intervention . ' - ' . $entityInfos;
                    if ($type_former_intervention == 'All') {
                        $result[] = ['id' => $member->id, 'name' => $contactFormer];
                    } else if ($type_former_intervention == $contact->type_former_intervention) {
                        $result[] = ['id' => $member->id, 'name' => $contactFormer];
                    }
                }
            }
        }
        return response()->json($result);
    }
    public function selectFormersContactsOptions($af_id, $contact_id, $type_former_intervention)
    {
        $ids_contacts = [];
        /*
        $type_former_intervention 1 == Sur facture
        $type_former_intervention 2 == Sur contrat
        */
        if ($type_former_intervention == 1) {
            $type_former_intervention = 'Sur facture';
        } elseif ($type_former_intervention == 2) {
            $type_former_intervention = 'Sur contrat';
        } else {
            $type_former_intervention = 'All';
        }
        //dd($type_former_intervention);

        $DbHelperTools = new DbHelperTools();
        if ($af_id > 0) {
            $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'F']])->pluck('id');
            if (count($ids_enrollments) > 0) {
                if ($contact_id > 0) {
                    $ids_contacts = Member::select('contact_id')->where('contact_id', $contact_id)->whereIn('enrollment_id', $ids_enrollments)->pluck('contact_id')->unique();
                } else {
                    $ids_contacts = Member::select('contact_id')->whereIn('enrollment_id', $ids_enrollments)->pluck('contact_id')->unique();
                }
            }
        } else {
            $ids_enrollments = Enrollment::select('id')->where('enrollment_type', 'F')->pluck('id');
            if (count($ids_enrollments) > 0) {
                $ids_members = Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->pluck('id');
                $ids_members_has_schedules = Schedulecontact::select('member_id')->whereIn('member_id', $ids_members)->pluck('member_id')->unique();
                if ($contact_id > 0) {
                    $ids_contacts = Member::select('contact_id')->where('contact_id', $contact_id)->whereIn('id', $ids_members_has_schedules)->pluck('contact_id')->unique();
                } else {
                    $ids_contacts = Member::select('contact_id')->whereIn('id', $ids_members_has_schedules)->pluck('contact_id')->unique();
                }
            }
        }

        $result = $datas = [];
        if (count($ids_contacts) > 0) {
            $datas = Contact::whereIn('id', $ids_contacts)->get();
        }


        if (count($datas) > 0) {
            foreach ($datas as $contact) {
                $contactFormer = '';
                $typeEntity = ($contact->entitie) ? $contact->entitie->entity_type : '';
                $nameEntity = ($contact->entitie) ? $contact->entitie->name . ' (' : '';
                $refEntity = ($contact->entitie) ? $contact->entitie->ref . ')' : '';
                $entityInfos = $typeEntity . (($contact->entitie->entity_type == 'S') ? ' : ' . $nameEntity . $refEntity : '');

                $contactFormer = $contact->firstname . ' ' . $contact->lastname . ' - ' . $contact->type_former_intervention . ' - ' . $entityInfos;

                if ($type_former_intervention == 'All') {
                    $result[] = ['id' => $contact->id, 'name' => $contactFormer];
                } else if ($type_former_intervention == $contact->type_former_intervention) {
                    $result[] = ['id' => $contact->id, 'name' => $contactFormer];
                }
            }
        }
        return response()->json($result);
    }

    public function treeSchedulesFormersContract($af_id, $member_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $datas = [];

        $arrResult = $DbHelperTools->getIdsByMemberAf($member_id, $af_id);
        $ids_sessions = $arrResult['ids_sessions'];
        $ids_sessiondates = $arrResult['ids_sessiondates'];
        $ids_schedules = $arrResult['ids_schedules'];

        if ($af_id > 0) {
            $afs = Action::where('id', $af_id)->get();
        } else {
            $afs = Action::all();
        }

        if (count($afs) > 0) {
            foreach ($afs as $af) {
                $datas[] = array(
                    "id" => 'AF' . $af->id,
                    "text" => $af->title . ' (' . $af->code . ')',
                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                    "icon" => "fa fa-folder text-info",
                    "parent" => '#'
                );
                $sessions = Session::where('af_id', $af->id)->whereIn('id', $ids_sessions)->get();
                if (count($sessions) > 0) {
                    foreach ($sessions as $session) {
                        //Session
                        $datas[] = array(
                            "id" => $session->id,
                            "text" => $session->title . ' (' . $session->code . ')',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "icon" => "fa fa-folder text-info",
                            "parent" => 'AF' . $af->id
                        );
                        //Session dates
                        $sessiondates = Sessiondate::where('session_id', $session->id)->whereIn('id', $ids_sessiondates)->get();
                        if (count($sessiondates) > 0) {
                            foreach ($sessiondates as $sd) {
                                $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                                $datas[] = array(
                                    "id" => 'D' . $sd->id,
                                    "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                                    "icon" => "fa fa-calendar text-primary",
                                    "parent" => $session->id
                                );
                                //Schedules : séances
                                $rs_schedules = Schedule::where('sessiondate_id', $sd->id)->whereIn('id', $ids_schedules)->get();
                                if (count($rs_schedules) > 0) {
                                    foreach ($rs_schedules as $schedule) {
                                        $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                        $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                        $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');
                                        $duration = Helper::convertTime($schedule->duration);
                                        //Intervenants
                                        $checkbox_disabled = ($schedule->total_cost > 0) ? false : true;
                                        $datas[] = array(
                                            "id" => 'SCHEDULE' . $schedule->id,
                                            "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                            "icon" => "fa fa-folder text-dark",
                                            "parent" => 'D' . $sd->id
                                        );
                                        //Intervenants
                                        $rs_schedulecontacts_formers = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 1], ['member_id', $member_id]])->whereNull('contract_id')->get();
                                        if (count($rs_schedulecontacts_formers) > 0) {
                                            foreach ($rs_schedulecontacts_formers as $scf) {

                                                $entityName = $scf->member->enrollment->entity->entity_type . ': ' . $scf->member->enrollment->entity->name . ' (' . $scf->member->enrollment->entity->ref . ')';
                                                $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';
                                                $price = '';
                                                $type_former_intervention = $scf->member->contact->type_former_intervention;
                                                $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule->duration, $scf->price, $type_former_intervention);
                                                $total_cost = ($scf_total_cost > 0) ? '<span class="text-primary"> - coût total : ' . $scf_total_cost . ' €</span>' : '';
                                                if ($scf->price > 0) {
                                                    $price = '' . $scf->price . ' €/' . $DbHelperTools->getNameParamByCode($scf->price_type) . $total_cost;
                                                } else {
                                                    $price = $total_cost;
                                                }
                                                $contractNumber = ($scf->contract_id > 0) ? ' (' . $scf->contract->number . ')' : '';

                                                $type_of_intervention = ($scf->type_of_intervention) ? ' - Type : ' . $DbHelperTools->getNameParamByCode($scf->type_of_intervention) : '';
                                                $pTypeIntervention = '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $scf->member->contact->type_former_intervention . $contractNumber . $type_of_intervention . '</p>';
                                                $pPrice = ($price) ? '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $price . '</p>' : '';
                                                $spanMemberName = ($scf->member->contact) ? '<span class="text-dark">' . ($scf->member->contact->firstname . ' ' . $scf->member->contact->lastname) . '<span>' : '';

                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $scf->id . ')" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                //$btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration('.$scf->id.')" title="Rémunération"><i class="'.$tools->getIconeByAction('PRICE').' text-success"></i></a>';

                                                $datas[] = array(
                                                    "id" => 'SCFORMER' . $scf->id,
                                                    "text" => $spanMemberName . $btnDelete . $pTypeIntervention . $pPrice,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                                                    "icon" => "far fa-id-badge text-dark",
                                                    "parent" => 'SCHEDULE' . $schedule->id
                                                );
                                            }
                                        } else {
                                            $datas[] = array(
                                                "id" => 'INFO' . $schedule->id,
                                                "text" => '<span class="text-warning">Pas de ligne à ajouté</span>',
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "fa fa-info text-warning",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $datas[] = array(
                        "id" => 1,
                        "text" => '<span class="text-warning">Pas de données</span>',
                        "state" => array('opened' => true, 'checkbox_disabled' => true),
                        "icon" => "fa fa-info text-warning",
                        "parent" => '#'
                    );
                }
            }
        }
        return response()->json($datas);
    }
    public function treeSchedulesFormersContractByContact($af_id, $contact_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $datas = [];

        $arrResult = $DbHelperTools->getIdsByContactAf($contact_id, $af_id);
        $ids_members = $arrResult['ids_members'];
        $ids_sessions = $arrResult['ids_sessions'];
        $ids_sessiondates = $arrResult['ids_sessiondates'];
        $ids_schedules = $arrResult['ids_schedules'];

        //dd($ids_members);

        if ($af_id > 0) {
            $afs = Action::where('id', $af_id)->get();
        } else {

            $ids_afs = DB::table('af_members')
                ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                ->select('af_members.id', 'af_members.contact_id', 'af_enrollments.enrollment_type', 'af_enrollments.af_id')
                ->where([['af_members.contact_id', $contact_id], ['af_enrollments.enrollment_type', 'F']])
                ->pluck('af_enrollments.af_id')->unique();

            $afs = Action::whereIn('id', $ids_afs)->get();
        }

        if (count($afs) > 0) {
            foreach ($afs as $af) {
                $datas[] = array(
                    "id" => 'AF' . $af->id,
                    "text" => $af->title . ' (' . $af->code . ')',
                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                    "icon" => "fa fa-folder text-info",
                    "parent" => '#'
                );
                $sessions = Session::where('af_id', $af->id)->whereIn('id', $ids_sessions)->get();
                if (count($sessions) > 0) {
                    foreach ($sessions as $session) {
                        //Session
                        $datas[] = array(
                            "id" => $session->id,
                            "text" => $session->title . ' (' . $session->code . ')',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "icon" => "fa fa-folder text-info",
                            "parent" => 'AF' . $af->id
                        );
                        //Session dates
                        $sessiondates = Sessiondate::where('session_id', $session->id)->whereIn('id', $ids_sessiondates)->get();
                        if (count($sessiondates) > 0) {
                            foreach ($sessiondates as $sd) {
                                $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                                $checkbox_disabled = $DbHelperTools->checkIfSessiondateChecked($sd->id, $ids_schedules, $ids_members);
                                $datas[] = array(
                                    "id" => 'D' . $sd->id,
                                    "text" => ($planning_date != null) ? $planning_date->format('d/m/Y') : 'A programmer',
                                    "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                    "icon" => "fa fa-calendar text-primary",
                                    "parent" => $session->id
                                );
                                //Schedules : séances
                                $rs_schedules = Schedule::where('sessiondate_id', $sd->id)->whereIn('id', $ids_schedules)->get();
                                if (count($rs_schedules) > 0) {
                                    foreach ($rs_schedules as $schedule) {
                                        $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                                        $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                                        $text = $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i');
                                        $duration = Helper::convertTime($schedule->duration);
                                        //Intervenants
                                        $checkbox_disabled = ($schedule->total_cost > 0) ? false : true;
                                        $datas[] = array(
                                            "id" => 'SCHEDULE' . $schedule->id,
                                            "text" => $text . ' <span class="text-success">(' . $duration . ')</span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                            "icon" => "fa fa-folder text-dark",
                                            "parent" => 'D' . $sd->id
                                        );
                                        //Intervenants
                                        $rs_schedulecontacts_formers = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 1]])
                                            ->whereIn('member_id', $ids_members)
                                            //->whereNull('contract_id')
                                            ->get();

                                        if (count($rs_schedulecontacts_formers) > 0) {
                                            foreach ($rs_schedulecontacts_formers as $scf) {

                                                $entityName = $scf->member->enrollment->entity->entity_type . ': ' . $scf->member->enrollment->entity->name . ' (' . $scf->member->enrollment->entity->ref . ')';
                                                $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';
                                                $price = '';
                                                $type_former_intervention = $scf->member->contact->type_former_intervention;
                                                $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule->duration, $scf->price, $type_former_intervention);
                                                $total_cost = ($scf_total_cost > 0) ? '<span class="text-primary"> - coût total : ' . $scf_total_cost . ' €</span>' : '';
                                                if ($scf->price > 0) {
                                                    $price = '' . $scf->price . ' €/' . $DbHelperTools->getNameParamByCode($scf->price_type) . $total_cost;
                                                } else {
                                                    $price = $total_cost;
                                                }
                                                $contractNumber = ($scf->contract_id > 0) ? ' (' . $scf->contract->number . ')' : '';

                                                $type_of_intervention = ($scf->type_of_intervention) ? ' - Type : ' . $DbHelperTools->getNameParamByCode($scf->type_of_intervention) : '';
                                                $pTypeIntervention = '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $scf->member->contact->type_former_intervention . $contractNumber . $type_of_intervention . '</p>';
                                                $pPrice = ($price) ? '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $price . '</p>' : '';
                                                $spanMemberName = ($scf->member->contact) ? '<span class="text-dark">' . ($scf->member->contact->firstname . ' ' . $scf->member->contact->lastname) . '<span>' : '';

                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $scf->id . ')" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                //$btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration('.$scf->id.')" title="Rémunération"><i class="'.$tools->getIconeByAction('PRICE').' text-success"></i></a>';

                                                $checkbox_disabled = ($scf->contract_id > 0) ? true : false;
                                                $datas[] = array(
                                                    "id" => 'SCFORMER' . $scf->id,
                                                    "text" => $spanMemberName . $btnDelete . $pTypeIntervention . $pPrice,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => $checkbox_disabled),
                                                    "icon" => "far fa-id-badge text-dark",
                                                    "parent" => 'SCHEDULE' . $schedule->id
                                                );
                                            }
                                        } else {
                                            $datas[] = array(
                                                "id" => 'INFO' . $schedule->id,
                                                "text" => '<span class="text-warning">Pas de ligne à ajouté</span>',
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "fa fa-info text-warning",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $datas[] = array(
                        "id" => 1,
                        "text" => '<span class="text-warning">Pas de données</span>',
                        "state" => array('opened' => true, 'checkbox_disabled' => true),
                        "icon" => "fa fa-info text-warning",
                        "parent" => '#'
                    );
                }
            }
        }
        return response()->json($datas);
    }

    public function createPdfContract($contract_id, $render_type)
    {
        $contract = Contract::findOrFail($contract_id);
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', 'CONTRAT_TRAVAIL_FORMATEUR')->first();
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $dn = Carbon::now();
        //Header
        $keywordHeader = array(
            '{LOGO_HEADER}',
            '{NUMBER}'
        );
        $keywordHeaderReplace = array(
            public_path('media/logo/logo-light.png'),
            $contract->number
        );
        $htmlHeader = str_replace($keywordHeader, $keywordHeaderReplace, $header);

        $htmlPrice = '';
        $rs_scf = Schedulecontact::where([['is_former', 1], ['contract_id', $contract_id]])->get();
        if (count($rs_scf) > 0) {
            foreach ($rs_scf as $s) {
                $htmlPrice .= '<tr>';
                $type_of_intervention = ($s->type_of_intervention) ? $DbHelperTools->getNameParamByCode($s->type_of_intervention) : '--';
                $htmlPrice .= '<td>' . $type_of_intervention . '</td>';
                $af = '<p style="line-height: 2px;">AF : ' . $s->schedule->sessiondate->session->af->title . '</p>';
                $session = '<p style="line-height: 2px;">Session : ' . $s->schedule->sessiondate->session->code . '</p>';
                $htmlPrice .= '<td>' . $af . $session . '</td>';

                $sd = $s->schedule->sessiondate;

                $planning_date = (isset($sd->planning_date) && !empty($sd->planning_date)) ? Carbon::createFromFormat('Y-m-d', $sd->planning_date) : null;
                $sessiondate = '<p style="line-height: 2px;">' . $planning_date->format('d/m/Y') . '</p>';

                //Séances
                $schedule = $s->schedule;
                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour);
                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour);
                $duration = Helper::convertTime($schedule->duration);
                $textSchedule = '<p style="line-height: 2px;">' . $start_hour->format('H') . 'h' . $start_hour->format('i') . ' - ' . $end_hour->format('H') . 'h' . $end_hour->format('i') . ' (' . $duration . ')</p>';

                $htmlPrice .= '<td>' . $sessiondate . $textSchedule . '</td>';
                $price = '';
                $type_former_intervention = $s->member->contact->type_former_intervention;
                $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule->duration, $s->price, $type_former_intervention);
                $total_cost = ($scf_total_cost > 0) ? '<p style="line-height: 2px;">Coût total : ' . $scf_total_cost . ' €</p>' : '';
                if ($s->price > 0) {
                    $price = '<p style="line-height: 2px;">Tarif : ' . $s->price . ' €/' . $DbHelperTools->getNameParamByCode($s->price_type) . '</p>' . $total_cost;
                } else {
                    $price = $total_cost;
                }
                $htmlPrice .= '<td>' . $price . '</td>';
                $htmlPrice .= '</tr>';
            }
            $htmlPrice .= '<tr>';
            $totalCost = $DbHelperTools->getTotalPriceContractFormer($contract_id);
            $htmlPrice .= '<td colspan="3" style="text-align: right;"><strong>Total : </strong></td><td><strong>' . number_format($totalCost, 2) . ' €</strong></td>';
            $htmlPrice .= '</tr>';
        }
        //Main
        $keyword = array(
            "{GENDER}",
            "{LASTNAME}",
            "{FIRSTNAME}",
            "{ADRESSE}",
            "{CODE_POSTAL}",
            "{VILLE}",
            "{TABLE_HTML}",
            "{DATE_NOW}",
            '{SIGNATURE}',
        );
        //contact
        $cnt = $contract->contact;
        $entity_id = $cnt->entitie->id;
        $adresseRs = Adresse::select('line_1', 'line_2', 'line_3', 'postal_code', 'city', 'country')->where('entitie_id', $cnt->entitie->id)->first();

        $adresse = (isset($adresseRs)) ? $adresseRs['line_1'] . ' ' . $adresseRs['line_2'] . ' ' . $adresseRs['line_3'] : '';
        $postal_code = (isset($adresseRs)) ? $adresseRs['postal_code'] : '';
        $city = (isset($adresseRs)) ? $adresseRs['city'] : '';

        $keyreplace = array(
            $cnt->gender,
            $cnt->lastname,
            $cnt->firstname,
            $adresse,
            $postal_code,
            $city,
            $htmlPrice,
            $dn->format('d/m/Y'),
            public_path('custom/images/signature.png'),
        );
        $htmlMain = str_replace($keyword, $keyreplace, $content);
        //Footer
        $keywordFooter = array(
            '{LOGO_FOOTER}',
            '{ADRESS_FOOTER}',
            '{PHONE_FOOTER}',
            '{FAX_FOOTER}',
            '{EMAIL_FOOTER}',
            '{WEBSITE_FOOTER}',
            '{SIRET_FOOTER}',
        );
        $keywordFooterReplace = array(
            public_path('media/logo/footer.jpg'),
            $DbHelperTools->getSettingByName('company_address', 'app'),
            $DbHelperTools->getSettingByName('company_phone', 'app'),
            $DbHelperTools->getSettingByName('company_fax', 'app'),
            $DbHelperTools->getSettingByName('company_email', 'app'),
            $DbHelperTools->getSettingByName('company_website', 'app'),
            $DbHelperTools->getSettingByName('company_siret', 'app'),
        );
        $htmlFooter = str_replace($keywordFooter, $keywordFooterReplace, $footer);

        $pdf = PDF::loadView('pages.pdf.model', ['htmlMain' => $htmlMain, 'htmlHeader' => $htmlHeader, 'htmlFooter' => $htmlFooter]);
        if ($render_type == 1) {
            return $pdf->stream();
        }
        if ($render_type == 3) {
        }
        //return true; 
        return $pdf->download($contract->number . '-' . time() . '.pdf');
    }

    public function viewAfSheet($af_id)
    {
        $sheet = $sheetParams = null;
        if ($af_id > 0) {
            $sheet = Sheet::where('action_id', $af_id)->whereNull('formation_id')->first();
            if ($sheet) {
                $sheetParams = Sheetparam::where([['sheet_id', $sheet->id]])->get();
            }
        }
        $DbHelperTools = new DbHelperTools();
        $state_params = $DbHelperTools->getParamsByParamCode('PF_STATE_SHEETS');
        return view('pages.af.sheet.view', ['sheet' => $sheet, 'sheetParams' => $sheetParams, 'state_params' => $state_params, 'af_id' => $af_id]);
    }

    public function formAfSheet($af_id, $row_id)
    {
        $sheet = $sheetParams = $collectionSheetParams = null;
        if ($row_id > 0) {
            $sheet = Sheet::findOrFail($row_id);
            if ($sheet) {
                $sheetParams = Sheetparam::select('id', 'title', 'content', 'order_show', 'sheet_id', 'param_id')->where([['sheet_id', $sheet->id]])->get()->toArray();
                $collectionSheetParams = collect($sheetParams);
            }
        }
        $params = Param::select('id', 'name')->where([['param_code', 'PF_TYPE_SHEETS'], ['is_active', 1]])->get();
        $DbHelperTools = new DbHelperTools();
        $state_params = $DbHelperTools->getParamsByParamCode('PF_STATE_SHEETS');
        $generatedCode = $generatedVersion = '';
        if ($row_id == 0) {
            $generatedData = $DbHelperTools->generateVesrionAndCodeForAfSheet($af_id);
            $generatedCode = $generatedData['code'];
            $generatedVersion = $generatedData['version'];
        }
        return view('pages.af.sheet.form', ['sheet' => $sheet, 'af_id' => $af_id, 'params' => $params, 'collectionSheetParams' => $collectionSheetParams, 'state_params' => $state_params, 'generatedVersion' => $generatedVersion, 'generatedCode' => $generatedCode]);
    }

    public function storeFormAfSheet(Request $request)
    {
        $data = $request->all();
        //dd($data);
        $success = false;
        $msg = '';
        $success = false;
        $msg = 'Veuillez vérifier tous les champs';
        $DbHelperTools = new DbHelperTools();
        $sheet_id = $DbHelperTools->manageSheets($data);
        if ($sheet_id > 0) {
            $postedDatasParams = ($data['SHEET_PARAM']) ? $data['SHEET_PARAM'] : [];
            if (count($postedDatasParams) > 0) {
                foreach ($postedDatasParams as $param_id => $tab2) {
                    foreach ($tab2 as $sheet_param_id => $content) {
                        $param = Param::where('id', $param_id)->first();
                        $dataSheetparam = array(
                            'id' => $sheet_param_id,
                            'title' => $param->name,
                            'content' => $content,
                            'param_id' => $param_id,
                            'sheet_id' => $sheet_id,
                        );
                        $DbHelperTools->manageSheetsParams($dataSheetparam);
                    }
                }
            }
            $success = true;
            $msg = 'La fiche technique a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function copySheetFromPfToAf($af_id)
    {
        $success = false;
        $msg = 'Veuillez réssayer plus tard !';
        if ($af_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $success = $DbHelperTools->mainCopySheetFromPfToAf($af_id);
            if ($success) {
                $msg = 'La fiche technique a été enregistrée avec succès';
            } else {
                $msg = 'Pas de fiche technique sur le produit de formation';
            }
            /* $af = Action::findOrFail ( $af_id );
            $formation_id=$af->formation->id;
            $sheet_pf = Sheet::where([['formation_id',$formation_id],['is_default',1]])->whereNull('action_id')->first();
            if(isset($sheet_pf->id) && $sheet_pf->id>0){
                $sheet_af = Sheet::where('action_id',$af_id)->whereNull('formation_id')->first();
                $sheet_af_id = (isset($sheet_af->id) && $sheet_af->id>0)?$sheet_af->id:0;
                //dd($sheet_af_id);
                $generatedCode = $generatedVersion = '';
                if($sheet_af_id == 0){
                    $generatedData = $DbHelperTools->generateVesrionAndCodeForAfSheet($af_id);
                    $generatedCode = $generatedData['code'];
                    $generatedVersion = $generatedData['version'];
                }else{
                    $generatedCode = $sheet_af->ft_code;
                    $generatedVersion = $sheet_af->version;
                }
                $data = array(
                    'id'=>$sheet_af_id,
                    'ft_code' => $generatedCode,
                    'version'=>$generatedVersion,
                    'description'=>$sheet_pf->description,
                    'is_default'=>$sheet_pf->is_default,
                    'param_id'=>$sheet_pf->param_id,
                    'formation_id'=>null,
                    'action_id'=>$af_id
                );
                //dd($data);
                $new_sheet_id = $DbHelperTools->manageSheets($data);
                $sheetPfParams=Sheetparam::select('id','title','content','order_show','sheet_id','param_id')->where([['sheet_id',$sheet_pf->id]])->get()->toArray();
                //dd($sheetPfParams);
                foreach($sheetPfParams as $sp){
                    $sheetParam = Sheetparam::select('id')->where([['sheet_id',$new_sheet_id],['param_id',$sp['param_id']]])->get()->first();
                    $sheet_param_id =(isset($sheetParam->id) && $sheetParam->id>0)?$sheetParam->id:0;
                    $sp['id']=$sheet_param_id;
                    $sp['sheet_id']=$new_sheet_id;
                    //dd($sp);
                    $DbHelperTools->manageSheetsParams($sp);
                }
                $success =true;
                $msg = 'La fiche technique a été enregistrée avec succès';
            }else{
                $msg = 'Pas de fiche technique sur le produit de formation';
            } */
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function sdtAfLogs(Request $request, $af_id)
    {
        $dtRequests = $request->all();
        $data = $meta = [];
        //Action de formation
        $results[] = Activity::where([['log_name', 'af_log'], ['subject_type', 'App\Models\Action'], ['subject_id', $af_id]])->orderByDesc('id')->get();
        //Session
        $sessions_ids = Session::select('id')->where('af_id', $af_id)->pluck('id')->toArray();
        $results[] = Activity::where([['log_name', 'af_session_log'], ['subject_type', 'App\Models\Session']])->whereIn('subject_id', $sessions_ids)->orderByDesc('id')->get();
        //Enrollment
        $enrollments_ids = Enrollment::select('id')->where('af_id', $af_id)->pluck('id')->toArray();
        $results[] = Activity::where([['log_name', 'af_enrollment_log'], ['subject_type', 'App\Models\Enrollment']])->whereIn('subject_id', $enrollments_ids)->orderByDesc('id')->get();
        $subjects = array(
            'App\Models\Action' => 'AF',
            'App\Models\Session' => 'Session',
            'App\Models\Enrollment' => 'Inscription',
        );
        $actions = array(
            'created' => 'Création',
            'updated' => 'Mise à jour',
        );
        $enrollment_type = array(
            'S' => 'Stagiaire',
            'F' => 'Formateur',
        );
        foreach ($results as $datas) {
            foreach ($datas as $d) {
                $row = array();
                $row[] = $d->id;
                $row[] = $d->created_at->format('d/m/Y H:i');
                $row[] = ucfirst($d->causer->name ?? '') . ' ' . ucfirst($d->causer->lastname ?? '');
                $actionDescription = '';
                if (in_array($d->subject_type, array('App\Models\Action', 'App\Models\Session'))) {
                    $actionDescription = $actions[$d->description] . ' (' . $subjects[$d->subject_type] . ' : ' . $d->subject->code . ')';
                } elseif ($d->subject_type == 'App\Models\Enrollment') {
                    $actionDescription = $actions[$d->description] . ' (' . $subjects[$d->subject_type] . ' ' . $enrollment_type[$d->subject->enrollment_type] . ')';
                }
                $row[] = $actionDescription;
                $data[] = $row;
            }
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
        ];
        return response()->json($result);
    }

    public function deleteContract(Request $request)
    {
        /**
         * forceDelete
         */

        $success = false;
        if ($request->isMethod('delete')) {
            if ($request->has('contract_id')) {
                $contract_id = $request->contract_id;
                //dd($contract_id);
                $DbHelperTools = new DbHelperTools();
                if ($contract_id > 0) {
                    Schedulecontact::where('contract_id', $contract_id)->update(['contract_id' => null]);
                    $deletedRows = $DbHelperTools->massDeletes([$contract_id], 'contract', 1);
                    if ($deletedRows)
                        $success = true;
                }
            }
        }
        return response()->json(['success' => $success]);
    }

        
    function deleteIntervenantwithoutcontract(Request $request)
    {
        $success = false;
        $msg = 'Oops !';

        if ($request->isMethod('delete')) {
            if ($request->has('contract_id')) {  
                // $contract = DB::table('af_schedulecontacts')->where('id',$request->contract_id)->first();
                // $contract1 = ScheduleContact::find($request->contract_id);
                $contract = ScheduleContact::find($request->contract_id);
                // dd($contract1);
                if ($contract) {
                    $contract->delete();
                    $success = true;
                    $msg = 'Suppression avec succès';
                }
            }
                
        }

        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function checkAfHasUnkownDate($af_id)
    {
        $is_unknown_date = false;
        if ($af_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $is_unknown_date = $DbHelperTools->checkIfAfIsUknownDateAndUpdate($af_id);
        }
        return response()->json(['is_unknown_date' => $is_unknown_date]);
    }

    public function deleteSession(Request $request)
    {
        $success = false;
        if ($request->isMethod('delete')) {
            if ($request->has('session_id')) {
                $session_id = $request->session_id;
                $DbHelperTools = new DbHelperTools();
                if ($session_id > 0) {
                    //af_sessiondates
                    $ids_sessiondates = Sessiondate::select('id')->where('session_id', $session_id)->pluck('id');
                    //dump('******sessiondates*********');
                    //dump($ids_sessiondates);
                    //af_schedules
                    $ids_schedules = Schedule::select('id')->whereIn('sessiondate_id', $ids_sessiondates)->pluck('id');
                    // dump('******schedules*********');
                    // dump($ids_schedules);
                    //af_scheduleressources
                    $ids_scheduleressources = Scheduleressource::select('id')->whereIn('schedule_id', $ids_schedules)->pluck('id');
                    // dump('******schedule resources*********');
                    // dump($ids_scheduleressources);
                    //af_schedulecontacts
                    $ids_schedulecontacts = Schedulecontact::select('id')->whereIn('schedule_id', $ids_schedules)->pluck('id');
                    // dump('******schedule contacts*********');
                    // dump($ids_schedulecontacts);

                    $deletedRows = $DbHelperTools->massDeletes($ids_schedulecontacts, 'schedulecontact', 1);
                    $deletedRows = $DbHelperTools->massDeletes($ids_scheduleressources, 'scheduleressource', 1);
                    $deletedRows = $DbHelperTools->massDeletes($ids_schedules, 'schedule', 1);
                    $deletedRows = $DbHelperTools->massDeletes($ids_sessiondates, 'sessiondate', 1);
                    $deletedRows = $DbHelperTools->massDeletes([$session_id], 'session', 1);
                    $success = true;
                }
            }
        }
        return response()->json(['success' => $success]);
    }

    public function deleteSessionDate(Request $request)
    {
        $success = false;
        if ($request->isMethod('delete')) {
            if ($request->has('sessiondate_id')) {
                $sessiondate_id = $request->sessiondate_id;
                if ($sessiondate_id > 0) {
                    $sessionDate = Sessiondate::find($sessiondate_id);
    
                    if ($sessionDate) {
                        $schedules = $sessionDate->schedules;
    
                        foreach ($schedules as $schedule) {
                            $schedule->schedulecontacts()->forceDelete();
                            $schedule->scheduleressources()->forceDelete();
                            $schedule->forceDelete();
                        }
    
                        $sessionDate->forceDelete();
                        $success = true;
                    }
                }
            }
        }
        return response()->json(['success' => $success]);
    }
    

    public function listSessions()
    {
        $page_title = 'Gestion des sessions';
        $page_description = '';
        return view('pages.session.list', compact('page_title', 'page_description'));
    }


    public function sdtSessions(Request $request, $action_id)


    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];


        if ($action_id > 0) {
            $datas = Session::where('af_id', $action_id)->get();
        } else {
            //$datas = Contact::orderBy('id', 'DESC')->get();
            $datas = Session::latest();
        }


        //filtrage a développer
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {

                //dd($request->all());


                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('title', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('code', 'like', '%' . $request->filter_text . '%');
                }

                /*
                if ($request->has('filter_type') && !empty($request->filter_type)) {
                    $data_temp = DB::table('en_contacts')
                        ->join('en_entities', 'en_contacts.entitie_id', '=', 'en_entities.id')
                        ->where('en_entities.entity_type', 'like', $request->filter_type)
                        ->select('en_contacts.*')
                        ->get();
                    $datas = $data_temp;
                    //$datas->where('entitie_id', $request->filter_type);
                }*/
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('started_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                /*
                if ($request->has('filter_activation') && !empty($request->filter_activation)) {
                    $is_active = ($request->filter_activation == 'a') ? 1 : 0;
                    $datas->where('is_active', $is_active);
                }*/
            }
        }

        $sessions = $datas->where('is_internship_period', 0)->where('is_evaluation', 0)->orderByDesc('id')->get();


        //remplissage tableau:
        foreach ($sessions as $d) {
            $row = array();
            //ID
            $row[] = $d->id;
            //<th>Session</th>

            $cssClass = 'primary';
            $stateArray = Helper::getNameParamByCodeStatic($d->state);
            $state = $stateArray['name'];
            $stateCssClass = $stateArray['css_class'];
            $labelActive = 'Désactivée';
            $cssClassActive = 'danger';
            if ($d->is_active == 1) {
                $labelActive = 'Activée';
                $cssClassActive = 'success';
            }

            $spanName = '<div class="text-body mb-2 ext-dark-60 font-weight-bolder" >' . $d->code . '</div>';
            $spanName .= '<div class="text-body mb-2">' . $d->title . '</div>';
            $spanName .= '<div class="text-body mb-2">' . $d->description . '</div>';
            $spanName .= '<div class="text-body mb-2">
            <span class="label label-outline-' . $stateCssClass . ' label-pill label-inline mr-2 mb-2">
            ' . $state . '
            </span>
            <span class="label label-outline-' . $cssClassActive . ' label-pill label-inline mr-2 mb-2">
            ' . $labelActive . '
            </span>

        </div>';

            $row[] = $spanName;


            //AF
            if ($action_id == 0) {
                $spanName = '<div class="text-body mb-2"><a href="/view/af/' . $d->af->id . '">' . $d->af->code . '</a></div>';
                $spanName .= '<div class="text-body mb-2"><a href="/view/af/' . $d->af->id . '">' . $d->af->title . '</a></div>';
                $row[] = $spanName;
            }
            //<th>Date debut-fin</th>
            $date_s = strtotime($d->started_at);
            $date_f = strtotime($d->ended_at);
            $started_at = $tools->constructParagraphLabelDot('xs', 'success', 'D : ' . date('d/m/Y H:i', $date_s));
            $ended_at = $tools->constructParagraphLabelDot('xs', 'danger', 'F : ' . date('d/m/Y H:i', $date_f));
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $started_at . $ended_at . $created_at . $updated_at;


            //<th>Infos</th>
            $btn_info = '<button class="btn btn-sm btn-clean btn-icon" onclick="_infosSession(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('INFO') . '"></i></button>';
            $row[] = $btn_info;

            //<th>Actions</th>
            //Actions
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formSession(' . $d->id . ',' . $d->action_id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            //$btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewContact(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            //$btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteContact(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $btn_pdf = '<a target="_blank" href="/pdf/attendance-absence-sheet/' . $d->af->id . '/1/0" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF FP</span> </a>';
            $btn_pdf_download = ' <a target="_blank" href="/pdf/attendance-absence-sheet/' . $d->af->id . '/2/0" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger FP</span> </a>';

            $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                    data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                ' . $btn_pdf . '
                                ' . $btn_pdf_download . '
                            </li>
                        </ul>
                    </div>
                </div>';

            $row[] = $btn_edit . $btn_more;
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
        ];
        return response()->json($result);
    }
    public function sdtPfFormationsToSessions(Request $request, $pf_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = [];
        if ($pf_id > 0) {
            $type_pf = $DbHelperTools->getParamPFormation($pf_id, 'PF_TYPE_FORMATION');
            if ($type_pf == 'PF_TYPE_DIP') {
                $ids = $DbHelperTools->filter_ids_product($pf_id);
                if (count($ids) > 0) {
                    $datas = Formation::where('autorize_af', 0)->whereIn('id', $ids)->get();
                }
            } else {
                $datas = Formation::where('id', $pf_id)->get();
            }
        }
        $session_types = $DbHelperTools->getParamsByParamCode('AF_SESSION_TYPES');
        $templates = $DbHelperTools->getPlanningTemplates();

        foreach ($datas as $d) {
            $row = array();
            // <th></th>
            $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" name="IDS_P_FORMATIONS[]" value="' . $d->id . '" class="checkable"/><span></span></label>';
            // <th>Infos</th>
            $optionsSessionType = '';
            foreach ($session_types as $st) {
                $optionsSessionType .= '<option value="' . $st['code'] . '">' . $st['name'] . '</option>';
            }
            $optionsTemplates = '';
            foreach ($templates as $tpl) {
                $optionsTemplates .= '<option value="' . $tpl['id'] . '">' . $tpl['name'] . '</option>';
            }


            $htmlSelectTypeSession = '
                    <div class="form-group">
                        <label>Type de session :</label>
                        <select name="FORM_FORMATIONS[' . $d->id . '][SESSION_TYPE]" class="form-control">
                            <option value="">Sélectionnez</option>
                            ' . $optionsSessionType . '
                        </select>
                    </div>';
            $htmlSelectPlanningTemplate = '
                    <div class="form-group">
                        <label>Modèle de planification des séances :</label>
                        <select name="FORM_FORMATIONS[' . $d->id . '][PLANNING_TEMPLATE]" class="form-control">
                            <option value="">Sélectionnez</option>
                            ' . $optionsTemplates . '
                        </select>
                    </div>';


            $htmlForm = '
                    <p class="text-primary">Session : ' . $d->title . '</p>
                    <p class="text-info">Nb duplication : ' . $d->nb_session_duplication . '</p>
                    <div class="form-group">
                        <label>Nb dates connues à programmer
                        </label>
                        <input class="form-control" type="number" name="FORM_FORMATIONS[' . $d->id . '][NB_DATES_TO_PROGRAM]" value="1"/>
                    </div>
                    <div class="form-group">
                        <label>Nb dates totales à programmer </label>
                        <input class="form-control " type="number" name="FORM_FORMATIONS[' . $d->id . '][NB_TOTAL_DATES_TO_PROGRAM]" value="1"/>
                    </div>
                    ' . $htmlSelectTypeSession . $htmlSelectPlanningTemplate;
            $row[] = $htmlForm;
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
        ];
        return response()->json($result);
    }
    public function selectGroupsOptions($af_id)
    {
        $result = [];
        $rows = Group::select('id', 'title')->where('af_id', $af_id)->get();
        $result[] = ['id' => 0, 'name' => 'Tous'];
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $result[] = ['id' => $row['id'], 'name' => $row['title']];
            }
        }
        return response()->json($result);
    }

    public function formPointage($schedulecontact_id)
    {
        $schedulecontact = Schedulecontact::select('id', 'pointing', 'is_abs_justified', 'contract_id')->where('id', $schedulecontact_id)->first();
        return view('pages.af.schedule.pointage.form', compact('schedulecontact'));
    }

    public function viewStudentStatus($member_id)
    {
        $member = Member::findOrFail($member_id);
        return view('pages.af.enrollment.stagiaires.student-status', compact('member'));
    }

    public function viewStudentSchedule($member_id)
    {
        $member = Member::findOrFail($member_id);
        return view('pages.af.enrollment.stagiaires.student-schedule', compact('member'));
    }

    public function viewStudentCancellation($member_id)
    {
        $member = Member::findOrFail($member_id);
        return view('pages.af.enrollment.stagiaires.student-cancellation', compact('member'));
    }

    public function importStudentSchedule(Request $request, $member_id)
    {
        $success = false;
        $msg = 'Erreur lors d\'import.';
        $start_date = DateTime::createFromFormat('d/m/Y H:i:s', $request->start_date . ' 00:00:00');
        if ($request->isMethod('post') && $start_date) {
            // $DbHelperTools = new DbHelperTools();
            $schedules = Schedulecontact::select('af_schedulecontacts.*')->join('af_schedules', 'af_schedules.id', 'af_schedulecontacts.schedule_id')
                ->join('af_sessiondates', 'af_schedules.sessiondate_id', 'af_sessiondates.id')
                ->where('af_schedulecontacts.member_id', $member_id)
                ->where('af_sessiondates.planning_date', '>=', $start_date)
                ->get()->toArray();
            if (count($schedules) > 0) {
                try {
                    foreach ($schedules as &$sc) {
                        unset($sc['id']);
                        $sc['pointing'] = 'not_pointed';
                        $sc['is_abs_justified'] = 0;
                        $sc['pointed_at'] = null;
                        $sc['pointed_by'] = null;
                        $sc['is_absent'] = 0;
                        $sc['type_of_intervention'] = null;
                        $sc['score'] = null;
                        $sc['ects'] = null;
                        $sc['member_id'] = $request->new_member;
                        $sc['contract_id'] = null;
                        $sc['deleted_at'] = null;
                        $sc['validated_at'] = null;
                        $sc['is_sent_sage_paie'] = 0;
                        $sc['created_at'] = new DateTime();
                        $sc['updated_at'] = new DateTime();
                    }

                    Schedulecontact::insert($schedules);
                    $member = Member::find($member_id);
                    $new_member = Member::find($request->new_member);
                    $new_member->group_id = $member->group_id;
                    $new_member->save();

                    $success = true;
                    $msg = 'Scéances importé avec succès.';
                } catch (Exception $e) {
                    // dd($e);
                }
            } else {
                $msg = 'Aucune séance trouvée pour cet étudiant, dans cette période.';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formScore($schedulecontact_id)
    {
        $schedulecontact = Schedulecontact::select('id', 'score', 'ects', 'contract_id')->where('id', $schedulecontact_id)->first();
        return view('pages.af.schedule.score.form', compact('schedulecontact'));
    }

    public function storeFormPointage(Request $request)
    {

        $userid = auth()->user()->id;
        $roles = auth()->user()->roles;

        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            //dd($request->all());
            $DbHelperTools = new DbHelperTools();
            if ($request->has('schedulecontact_id') && $request->has('pointing')) {
                $pointing = $request->pointing;
                $is_abs_justified = $request->has('is_abs_justified') && $pointing === 'absent';
                $data = [
                    'pointing' => $pointing,
                    'is_abs_justified' => $is_abs_justified,
                    'pointed_at' => Carbon::now(),
                    'pointed_by' => Auth::user()->id,
                ];
                $schedulecontact = Schedulecontact::find($request->schedulecontact_id);
                $schedulemember = Member::find($schedulecontact->member_id);
                $schedulecontactinfo = Contact::find($schedulemember->contact_id);
                $start_hour = $schedulecontact->schedule->start_hour;
                $end_hour = $schedulecontact->schedule->end_hour;
                $session = $schedulecontact->schedule->sessiondate->session;
                $af = $schedulecontact->schedule->sessiondate->session->af;

                if($start_hour){
                    $start_time = Carbon::parse($start_hour)->format('H:i');
                    $start_date = Carbon::parse($start_hour)->format('Y-m-d');
                }
                if($end_hour){
                    $end_time = Carbon::parse($end_hour)->format('H:i');
                    $end_date = Carbon::parse($end_hour)->format('Y-m-d');
                }
    
                if($session){
                    $title_session=$session->title;
                }
    
                if($af){
                    $num_af=$session->code;
                    $intitule_af=$session->title;
                }

                $hour = $schedulecontact->schedule->start_hour;
                if ($roles[0]->code == 'FORMATEUR') {

                    $dateseance = Carbon::createFromFormat('Y-m-d H:i:s', $hour);
                    $dateabsc = Carbon::createFromFormat('Y-m-d H:i:s', $data["pointed_at"]);
                    $dateabs=$dateabsc->format('Y-m-d');
                    $result = $dateseance->gt($dateabsc);
                    if ($result) {
                        $success = false;
                        $msg = 'Pas possible de déclarer une présence à cette date.';
                    } else {
                        /* Evaluation : Présentiel */
                        if ($session->is_evaluation && $session->evaluation_mode == 'PRESENTIEL' && $pointing != 'not_pointed') {
                            $data['score'] = $pointing == 'present' || $is_abs_justified ? 12 : 8;
                            $data['ects'] = $data['score'] >= 10 ? $session->ects : 0;
                        }
                        try {
                            Schedulecontact::where('id', $request->schedulecontact_id)->update($data);
                            $success = true;

                             /* Send Mail */
                            $fullname = ucfirst($schedulecontactinfo->firstname ?? '') . ' ' . ucfirst($schedulecontactinfo->lastname ?? '');
                            $content = "Nous vous informons que le formateur <b>$fullname</b> a été absent pour l'Af portant le numéro <b>$num_af</b>, l'intitulé <b>$intitule_af</b> ainsi que de la session <b>$title_session</b> <br/> - Date de début : $start_date<br/> - Date de fin : $end_date<br/> - Heure de début : $start_time<br/> - Heure de fin : $end_time .<br/><br/>";
                            $content .= "<b>Absence du formateur</b><br/>";
                            $header = "Environnement de formation pour CRFPE";
                            $footer = "Plateforme de formation SOLARIS";

                            Mail::send('pages.email.model', ['htmlMain' => $content, 'htmlHeader' => $header, 'htmlFooter' => $footer], function ($m){
                                $m->from(auth()->user()->email);
                                $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
                                $m->to('severinebernaert@crfpe.fr')->subject('SOLARIS : Absence du formateur');
                            });

                        } catch (Exception $e) {
                        }
                    }
                } else {
                    $dateseance = Carbon::createFromFormat('Y-m-d H:i:s', $hour);
                    $dateabsc = Carbon::createFromFormat('Y-m-d H:i:s', $data["pointed_at"]);
                    $dateabs=$dateabsc->format('Y-m-d');
                    $result = $dateseance->gt($dateabsc);

                    /* Evaluation : Présentiel */
                    if ($session->is_evaluation && $session->evaluation_mode == 'PRESENTIEL' && $pointing != 'not_pointed') {
                        $data['score'] = $pointing == 'present' || $is_abs_justified ? 12 : 8;
                        $data['ects'] = $data['score'] >= 10 ? $session->ects : 0;
                    }
                    try {
                        Schedulecontact::where('id', $request->schedulecontact_id)->update($data);
                        $success = true;
                        $msg = 'Pointage mis a jour avec succès';

                          /* Send Mail */
                        $fullname = ucfirst($schedulecontactinfo->firstname ?? '') . ' ' . ucfirst($schedulecontactinfo->lastname ?? '');
                        $content = "Nous vous informons que le formateur <b>$fullname</b> a été absent pour l'Af portant le numéro <b>$num_af</b>, l'intitulé <b>$intitule_af</b> ainsi que de la session <b>$title_session</b> <br/> - Date de début : $start_date<br/> - Date de fin : $end_date<br/> - Heure de début : $start_time<br/> - Heure de fin : $end_time .<br/><br/>";
                        $content .= "<b>Absence du formateur</b><br/>";
                        $header = "Environnement de formation pour CRFPE";
                        $footer = "Plateforme de formation SOLARIS";

                        Mail::send('pages.email.model', ['htmlMain' => $content, 'htmlHeader' => $header, 'htmlFooter' => $footer], function ($m){
                            $m->from(auth()->user()->email);
                            $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
                            $m->to('severinebernaert@crfpe.fr')->subject('SOLARIS : Absence du formateur');
                        });

                    } catch (Exception $e) {
                    }
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formScores(Request $request, $block_id = 3)
    {
        $members_ids = [];
        $sessions_ids = [];
        if ($request->has('schedules_ids') && !empty($request->schedules_ids)) {
            foreach (explode(',', $request->schedules_ids) as $sc_id) {
                if (!isset(explode('SCONTACT', $sc_id)[1])) {
                    continue;
                }
                $reg = $block_id == 3 ? '/(SCONTACT)|\-/' : '/(SCONTACT[0-9]+)|\-/';
                $sc = preg_split($reg, $sc_id);
                // var_dump($sc_id);
                // var_dump($sc);
                // exit;
                if ($block_id == 3 && isset($sc[3])) {
                    $sessions_ids[] = $sc[1];
                    $members_ids[] = $sc[3];
                } elseif ($block_id == 2 && isset($sc[3])) {
                    $sessions_ids[] = $sc[3];
                    $members_ids[] = $sc[2];
                }
            }

            $sessions_ids = array_unique($sessions_ids);
            $members_ids = array_unique($members_ids);

            // dd($sessions_ids, $members_ids);

            $schedulecontacts = Schedulecontact::select([
                'af_schedulecontacts.id',
                'af_schedulecontacts.score',
                'af_schedulecontacts.score_oral',
                'af_schedulecontacts.ects as contact_ects',
                'en_contacts.firstname',
                'en_contacts.lastname',
                'af_sessions.id as session_id',
                'af_sessions.title',
                'af_sessions.ects as session_ects',
            ])
                ->leftJoin('af_members', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                ->leftJoin('en_contacts', 'af_members.contact_id', '=', 'en_contacts.id')
                ->leftJoin('af_schedules', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
                ->leftJoin('af_sessiondates', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
                ->leftJoin('af_sessions', 'af_sessiondates.session_id', '=', 'af_sessions.id')
                ->whereIn('af_members.id', $members_ids)
                ->whereIn('af_sessions.id', $sessions_ids)
                // ->where('en_contacts.is_former','!=',1)
                ->get();

            $scores = [];
            foreach ($schedulecontacts as $sc) {
                $session_id = $sc->session_id;
                if (!isset($scores[$session_id])) {
                    $scores[$session_id] = ['session' => $sc->title, 'contacts' => []];
                }
                $scores[$session_id]['contacts'][] = $sc;
            }

            $myArrayDatas = explode(',', $request->schedules_ids);
            if (count($myArrayDatas) > 0) {
                foreach ($myArrayDatas as $dt) {
                    $tab_id = explode('SCONTACT', $dt);
                    (isset($tab_id[1])) ? ($schedules_ids[] = $tab_id[1]) : 0;
                }
            }
        }
        return view('pages.af.schedule.score.form', compact('scores'));
    }
    public function storeFormScores(Request $request)
    {
        try {
            if (!empty($request->score) || !empty($request->score_oral)) {
                foreach ($request->score as $id => $score) {
                    $contact = Schedulecontact::find($id);
                    $contact->score = $request->score[$id];
                    $contact->score_oral = $request->score_oral[$id];
                    $contact->ects = $request->ects[$id];
                    $contact->save();
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Erreur lors de la mise à jour des notes de ' . ($contact->member->contact->firstname . ' ' . $contact->member->contact->lastname),
            ]);
        }
        return response()->json([
            'success' => true,
            'msg' => 'Modification faite avec succès',
        ]);
    }
    public function formCommittee($member_id,$id)
    {
        $ids = explode(",", $id);

        $member = Member::find($member_id);
        $periodes = Timestructure::select([
            'pf_timestructures.id as ts_id',
            'pf_timestructures.name as ts_name',
            'af_sessions.id as session_id',
            'af_sessions.title as session',
            'en_contacts.firstname as firstname',
            'en_contacts.lastname as lastname',
            'en_contacts.email as email',
            'en_contacts.pro_phone as phone',
            'en_contacts.pro_mobile as mobile',
            'af_sessions.coefficient as coefficient',
            'af_schedulecontacts.score as score',
            'af_schedulecontacts.score_oral as score_oral',
            'af_schedulecontacts.id as id',
            'af_schedulecontacts.pointing as pointing',
            'af_schedulecontacts.ects as ects',
            'af_committee_decisions.id as id_committee',
            'af_committee_decisions.comment as comment',
            'af_committee_decisions.next_todo_comment as next_todo_comment',
            'af_committee_decisions.send_transcript as send_transcript',
            'af_committee_decisions.send_comment_mail as send_comment_mail',
        ])
            ->join('af_sessions', 'af_sessions.timestructure_id', '=', 'pf_timestructures.id')
            ->join('af_sessiondates', 'af_sessiondates.session_id', '=', 'af_sessions.id')
            ->join('af_schedules', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
            ->join('af_schedulecontacts', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
            ->join('af_members', 'af_schedulecontacts.member_id', '=', 'af_members.id')
            ->join('en_contacts', 'af_members.contact_id', '=', 'en_contacts.id')
            ->leftJoin('af_committee_decisions', function ($q) {
                $q->on('af_committee_decisions.timestructure_id', '=', 'pf_timestructures.id')
                    ->on('af_committee_decisions.member_id', '=', 'af_members.id');
            })
            ->where('af_members.id', $member_id)
            ->whereIn('af_schedulecontacts.id', $ids)
            ->where('pf_timestructures.name', 'like', 'SEMESTRE%')
            ->groupBy('af_schedulecontacts.id')
            ->orderBy('pf_timestructures.sort')->get();
        $data = [];
        $errors = [];

        $array=$periodes->toArray();

        foreach ($array as $element) {
            if ($element['pointing'] == "not_pointed") {
                $errors[] = $element['session'] . ': Vous devez pointer';
            }

            if ($element['pointing'] == "present" && $element['score'] === null && $element['score_oral'] === null) {
                $errors[] = $element['session'] . ': Vous devez saisir la note pour cet étudiant';
            }
        }

        if (count($periodes) > 0) {
            $etudiant = $periodes[0]['firstname'] . ' ' . $periodes[0]['lastname'];
            $email = $periodes[0]['email'];
            $phone = !empty($periodes[0]['phone']) ? $periodes[0]['phone'] : $periodes[0]['mobile'];
            foreach ($periodes as &$periode) {
                if ($periode->ects === null) {
                    $errors[] = $periode->session . ': Vous devez saisir l\'ECTS pour cet étudiant';
                }
                if (!($periode->coefficient > 0)) {
                    $errors[] = $periode->session . ': Vous devez saisir un coefficient valid pour ce module';
                }
                if (!empty($errors)) {
                    break;
                }
                if (!array_key_exists($periode->ts_id, $data)) {
                    $data[$periode->ts_id] = ['cumul_notes' => 0, 'cumul_ects' => 0, 'cumul_coef' => 0];
                }
                /* if (!array_key_exists($periode->session_id, $data[$periode->ts_id])) {
                    $data[$periode->ts_id][$periode->session_id] = ['cumul_notes' => 0, 'cumul_ects' => 0];
                } */
                $data[$periode->ts_id]['periode'] = $periode->ts_name;
                $data[$periode->ts_id]['cumul_notes'] += $periode->score * $periode->coefficient;
                $data[$periode->ts_id]['cumul_ects'] += $periode->ects;
                $data[$periode->ts_id]['cumul_coef'] += $periode->coefficient;
                $data[$periode->ts_id]['committee'] = [
                    "id" => $periode->id_committee,
                    "comment" => $periode->comment,
                    "next_todo_comment" => $periode->next_todo_comment,
                    "send_transcript" => $periode->send_transcript,
                    "send_comment_mail" => $periode->send_comment_mail,
                ];
            }
        }

        return view('pages.af.session.committee', compact('member_id', 'member', 'data', 'etudiant', 'email', 'phone', 'errors'));
    }
    public function storeFormCommittee(Request $request)
    {
        try {
            if (is_array($request->comment)) {
                foreach ($request->comment as $ts => $comment) {
                    $member_id = $request->member_id;
                    $next_todo_comment = $request->next_todo_comment[$ts];
                    $send_transcript = is_array($request->send_transcript) && isset($request->send_transcript[$ts]);
                    $send_comment_mail = is_array($request->send_comment_mail) && isset($request->send_comment_mail[$ts]);

                    $committeeDecision = CommitteeDecision::firstOrNew(['id' => $request->committee_id[$ts]]);
                    $committeeDecision->comment = $comment;
                    $committeeDecision->member_id = $member_id;
                    $committeeDecision->timestructure_id = $ts;
                    $committeeDecision->next_todo_comment = $next_todo_comment;
                    $committeeDecision->send_transcript = $send_transcript;
                    $committeeDecision->send_comment_mail = $send_comment_mail;
                    $committeeDecision->save();

                    /* Cancellation */
                    $member = Member::find($member_id);
                    if (isset($request->stop_reason) && $request->stop_reason != 'no_action') {
                        $member->stop_reason = $request->stop_reason;
                        $member->effective_date = DateTime::createFromFormat('d/m/Y', $request->effective_date);
                        if ($request->stop_reason == 'suspend') {
                            $member->resumption_date = DateTime::createFromFormat('d/m/Y', $request->resumption_date);
                        }

                        if ($member->effective_date && ($member->stop_reason != 'suspend' || $member->resumption_date)) {
                            $member->save();
                        }

                        Schedulecontact::join('af_schedules', 'af_schedules.id', 'af_schedulecontacts.schedule_id')
                            ->where('af_schedulecontacts.member_id', $member_id)
                            ->where('af_schedules.start_hour', '>', $member->effective_date->format('Y-m-d 00:00:00'))
                            ->forceDelete();

                        /* Task */
                        $motif = Member::STOP_REASON_TXT[$member->stop_reason];
                        $stdName = $member->contact->firstname . ' ' . $member->contact->lastname;
                        $af = $member->enrollment->action;
                        $effective_date = $member->effective_date->format('d/m/Y');

                        $params = Param::where('param_code', 'like', 'TASK_%')->get();
                        $responsable_id = Contact::where('email', 'severinebernaert@crfpe.fr')->first()->id ?? null;

                        $en_cours = Param::where([['param_code', 'Etat'], ['code', 'En cours'], ['is_active', 1]])->pluck('id')->first();

                        Task::create([
                            'title' => "Étudiant $stdName - $motif ({$af->code})",
                            'description' => "{$af->title}, $stdName, $motif, $effective_date",
                            'apporteur_id' => $responsable_id,
                            'priority' => 'normal',
                            'responsable_id' => $responsable_id,
                            'type_id' => $params->where('code', 'STD_' . strtoupper($member->stop_reason))->first()->id ?? null,
                            'start_date' => new DateTime(),
                            'ended_date' => $member->effective_date,
                            'source_id' => $params->where('code', 'COMMITTEE')->first()->id ?? null,
                            'callback_mode_id' => $params->where('code', 'CALLBACK_SOLARIS')->first()->id ?? null,
                            'reponse_mode_id' => $params->where('code', 'RESPONSE_SOLARIS')->first()->id ?? null,
                            'entite_id' => $member->contact->entitie->id,
                            'contact_id' => $member->contact->id,
                            'af_id' => $af->id,
                            'pf_id' => $af->formation->id,
                            'etat_id' => $en_cours ?? null,
                        ]);

                        /* Task : resumption */
                        if ($request->stop_reason == 'suspend') {
                            $resumption_date = $member->resumption_date->format('d/m/Y');
                            $responsable_id = Contact::where(['firstname' => 'Cécile', 'lastname' => 'GOEMINNE'])->first()->id ?? null;
                            $en_cours = Param::where([['param_code', 'Etat'], ['code', 'En cours'], ['is_active', 1]])->pluck('id')->first();

                            Task::create([
                                'title' => "Étudiant $stdName - Reprise de suspension ({$af->code})",
                                'description' => "{$af->title}, $stdName, Reprise de suspension, $resumption_date",
                                'apporteur_id' => $responsable_id,
                                'priority' => 'normal',
                                'responsable_id' => $responsable_id,
                                'type_id' => $params->where('code', 'STD_RESUMPTION')->first()->id ?? null,
                                'start_date' => new DateTime(),
                                'ended_date' => $member->resumption_date,
                                'source_id' => $params->where('code', 'COMMITTEE')->first()->id ?? null,
                                'callback_mode_id' => $params->where('code', 'CALLBACK_SOLARIS')->first()->id ?? null,
                                'reponse_mode_id' => $params->where('code', 'RESPONSE_SOLARIS')->first()->id ?? null,
                                'entite_id' => $member->contact->entitie->id,
                                'contact_id' => $member->contact->id,
                                'af_id' => $af->id,
                                'pf_id' => $af->formation->id,
                                'etat_id' => $en_cours ?? null,
                            ]);
                        }
                    } else {
                        $member->stop_reason = null;
                        $member->effective_date = null;
                        $member->resumption_date = null;
                        $member->save();
                    }
                }
            }
        } catch (Exception $e) {
            // dd($e);
            return response()->json([
                'success' => false,
                'msg' => 'Erreur lors de la mise à jour des notes de ',
            ]);
        }
        return response()->json([
            'success' => true,
            'msg' => 'Modification faite avec succès',
        ]);
    }

    public function cancelStudent(Request $request, $member_id)
    {
        try {
            /* Cancellation */
            $member = Member::find($member_id);
            if (isset($request->stop_reason) && $request->stop_reason != 'no_action') {
                $member->stop_reason = $request->stop_reason;
                $member->effective_date = DateTime::createFromFormat('d/m/Y', $request->effective_date);
                if ($request->stop_reason == 'suspend') {
                    $member->resumption_date = DateTime::createFromFormat('d/m/Y', $request->resumption_date);
                }
    
                if ($member->effective_date && ($member->stop_reason != 'suspend' || $member->resumption_date)) {
                    $member->save();
                }
    
                Schedulecontact::join('af_schedules', 'af_schedules.id', 'af_schedulecontacts.schedule_id')
                    ->where('af_schedulecontacts.member_id', $member_id)
                    ->where('af_schedulecontacts.pointing', 'not_pointed')
                    ->where('af_schedules.start_hour', '>', $member->effective_date->format('Y-m-d 00:00:00'))
                    ->forceDelete();
    
                /* Task */
                $motif = Member::STOP_REASON_TXT[$member->stop_reason];
                $stdName = $member->contact->firstname . ' ' . $member->contact->lastname;
                $af = $member->enrollment->action;
                $effective_date = $member->effective_date->format('d/m/Y');
    
                $params = Param::where('param_code', 'like', 'TASK_%')->get();
                $responsable_id = Contact::where('email', 'severinebernaert@crfpe.fr')->first()->id ?? null;
                $en_cours = Param::where([['param_code', 'Etat'], ['code', 'En cours'], ['is_active', 1]])->pluck('id')->first();
    
                Task::create([
                    'title' => "Étudiant $stdName - $motif ({$af->code})",
                    'description' => "{$af->title}, $stdName, $motif, $effective_date",
                    'apporteur_id' => $responsable_id,
                    'priority' => 'normal',
                    'responsable_id' => $responsable_id,
                    'type_id' => $params->where('code', 'STD_' . strtoupper($member->stop_reason))->first()->id ?? null,
                    'start_date' => new DateTime(),
                    'ended_date' => $member->effective_date,
                    'source_id' => $params->where('code', 'COMMITTEE')->first()->id ?? null,
                    'callback_mode_id' => $params->where('code', 'CALLBACK_SOLARIS')->first()->id ?? null,
                    'reponse_mode_id' => $params->where('code', 'RESPONSE_SOLARIS')->first()->id ?? null,
                    'entite_id' => $member->contact->entitie->id,
                    'contact_id' => $member->contact->id,
                    'af_id' => $af->id,
                    'pf_id' => $af->formation->id,
                    'etat_id' => $en_cours ?? null,
                ]);
    
                /* Task : resumption */
                if ($request->stop_reason == 'suspend') {
                    $resumption_date = $member->resumption_date->format('d/m/Y');
                    $responsable_id = Contact::where(['firstname' => 'Cécile', 'lastname' => 'GOEMINNE'])->first()->id ?? null;
                    $en_cours = Param::where([['param_code', 'Etat'], ['code', 'En cours'], ['is_active', 1]])->pluck('id')->first();
    
                    Task::create([
                        'title' => "Étudiant $stdName - Reprise de suspension ({$af->code})",
                        'description' => "{$af->title}, $stdName, Reprise de suspension, $resumption_date",
                        'apporteur_id' => $responsable_id,
                        'priority' => 'normal',
                        'responsable_id' => $responsable_id,
                        'type_id' => $params->where('code', 'STD_RESUMPTION')->first()->id ?? null,
                        'start_date' => new DateTime(),
                        'ended_date' => $member->resumption_date,
                        'source_id' => $params->where('code', 'COMMITTEE')->first()->id ?? null,
                        'callback_mode_id' => $params->where('code', 'CALLBACK_SOLARIS')->first()->id ?? null,
                        'reponse_mode_id' => $params->where('code', 'RESPONSE_SOLARIS')->first()->id ?? null,
                        'entite_id' => $member->contact->entitie->id,
                        'contact_id' => $member->contact->id,
                        'af_id' => $af->id,
                        'pf_id' => $af->formation->id,
                        'etat_id' => $en_cours ?? null,
                    ]);
                }
            } else {
                $member->stop_reason = null;
                $member->effective_date = null;
                $member->resumption_date = null;
                $member->save();
            }
            return response()->json([
                'success' => true,
                'msg' => 'Annulation faite avec succès',
            ]);
        } catch (Exception $e) {}

        return response()->json([
            'success' => false,
            'msg' => 'Erreur Inconnue',
        ]);
    }

    function validateContractScheduleContacts(Request $request)
    {
        $success = false;
        $msg = 'Oops !';

        if ($request->isMethod('post')) {
            if ($request->has('contract_id')) {
                $schedulecontacts_ids = [];
                //dd($request->contract_id);
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    $schedule_ids = Schedulecontact::select('schedule_id')
                        ->where([['contract_id', $request->contract_id], ['is_former', 1], ['pointing', 'present']])
                        ->whereNull('validated_at')->pluck('schedule_id')->unique();
                    if (count($schedule_ids) > 0) {
                        $sessiondate_ids = Schedule::select('sessiondate_id')->whereIn('id', $schedule_ids)->pluck('sessiondate_id')->unique();
                        if (count($sessiondate_ids) > 0) {
                            $from = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                            $to = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                            $sessiondate_ids = Sessiondate::select('id')->whereIn('id', $sessiondate_ids)->where('planning_date', '<=', $to)->pluck('id');
                            if (count($sessiondate_ids) > 0) {
                                $schedules_ids = Schedule::select('id')->whereIn('sessiondate_id', $sessiondate_ids)->pluck('id')->unique();
                                if (count($schedules_ids) > 0) {
                                    $schedulecontacts_ids = Schedulecontact::select('id')
                                        ->whereIn('schedule_id', $schedules_ids)
                                        ->where([['contract_id', $request->contract_id], ['is_former', 1], ['pointing', 'present']])
                                        ->whereNull('validated_at')
                                        ->pluck('id');
                                }
                            }
                        }
                    }
                }
                //dd($schedulecontacts_ids);
                if (count($schedulecontacts_ids) > 0) {
                    Schedulecontact::where('contract_id', $request->contract_id)
                        ->whereIn('id', $schedulecontacts_ids)
                        ->where('pointing', 'present')->where('is_former', 1)->whereNull('validated_at')
                        ->update(
                            [
                                'validated_at' => Carbon::now(),
                                'validated_by' => Auth::user()->id,
                            ]
                        );
                } else {
                    Schedulecontact::where('contract_id', $request->contract_id)->where('pointing', 'present')->where('is_former', 1)->whereNull('validated_at')
                        ->update(
                            [
                                'validated_at' => Carbon::now(),
                                'validated_by' => Auth::user()->id,
                            ]
                        );
                }
                $success = true;
                $msg = 'Validation avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    function deleteContractScheduleContacts(Request $request)
    {
        $success = false;
        $msg = 'Oops !';

        if ($request->isMethod('post')) {
            if ($request->has('contract_id')) {   
                $contract=Contract::where('id', $request->contract_id)->first();
                if ($contract) {
                    $contract->delete();
                    $success = true;
                    $msg = 'Suppression avec succès'; 
                }
            }
                
        }
        
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function getContentTabDocsAf($block_id, $af_id)
    {
        $members =  Member::where('af_schedulecontacts.is_former', 0)
            ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
            ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
            ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')->orderBy('en_contacts.firstname', 'asc')->get(['af_members.*'])->unique();
        return view('pages.af.construct.partials', compact('block_id', 'af_id', 'members'));
    }
    public function getContentTabCertsAf($block_id, $af_id)
    {
        $datafilter = new stdClass();
        $periodes = Timestructure::where('name', 'LIKE', 'SEMESTRE%')->orderBy('name')->get(['id', 'name']);
        $datafilter->periodes = $periodes;
        return view('pages.af.construct.certifications.partials', compact('block_id', 'af_id', 'datafilter'));
    }
    public function generateCertificatesFromAf($af_id)
    {
        $success = false;
        if ($af_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $rows =  Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->pluck('id');
            foreach ($rows as $enrollment_id) {
                $cc = Certificate::select('id')->where([['af_id', $af_id], ['type', 'employer'], ['enrollment_id', $enrollment_id]])->first();
                $certificate_id = ($cc) ? $cc->id : 0;
                $data = array(
                    'id' => $certificate_id,
                    'number' => ($certificate_id == 0) ? $DbHelperTools->generateCertificateNumber($certificate_id) : null,
                    'status' => 'draft',
                    'signed_at' => null,
                    'cancelled_at' => null,
                    'session_id' => null,
                    'af_id' => $af_id,
                    'enrollment_id' => $enrollment_id,
                );
                $row_id = $DbHelperTools->manageCertificate($data);
                $success = true;
                //dd($row_id);
            }
            $msg = 'Les attestations ont été générées avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function generateStudentsCertificatesFromAf($af_id)
    {
        $success = false;
        if ($af_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $rows = Member::select('af_members.contact_id')
            ->join('af_enrollments', 'af_members.enrollment_id', 'af_enrollments.id')
            ->where([['af_enrollments.af_id', $af_id], ['af_enrollments.enrollment_type', 'S']])
            ->where('af_members.contact_id', '!=', null)
            ->groupBy('af_members.contact_id')
            ->pluck('af_members.contact_id');
            foreach ($rows as $contact_id) {
                $cc = Certificate::select('id')->where([['af_id', $af_id], ['type', 'student'], ['contact_id', $contact_id]])->first();
                $certificate_id = ($cc) ? $cc->id : 0;
                $data = array(
                    'id' => $certificate_id,
                    'number' => ($certificate_id == 0) ? $DbHelperTools->generateCertificateNumber($certificate_id) : null,
                    'status' => 'draft',
                    'signed_at' => null,
                    'cancelled_at' => null,
                    'session_id' => null,
                    'af_id' => $af_id,
                    'contact_id' => $contact_id,
                    'type' => 'student',
                );
                $row_id = $DbHelperTools->manageCertificate($data);
                $success = true;
                //dd($row_id);
            }
            $msg = 'Les attestations ont été générées avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function getJsonTimeStructure($id_selected, $param, $is_eval = false)
    {
        /* 
        $param==0 ==> display normal
        $param==1 ==> display in modal
        */
        $tools = new PublicTools();
        $categories = Timestructurecategory::all();
        $datas = [];
        $af = Action::find($id_selected);
        $DbHelperTools = new DbHelperTools();

        $pf = Formation::find($af->formation_id);

        foreach ($categories as $c) {
            $timestructures = Timestructure::where('category_id', $c->id)->orderBy('sort', 'ASC')->get();
            $datas[] = array(
                "id" => 'C' . $c->id,
                "text" => $c->name,
                "state" => array('opened' => true),
                "icon" => 'fa fa-folder',
                "parent" => '#'
            );
            if (count($timestructures) > 0) {
                foreach ($timestructures as $t) {
                    $fa = 'folder';
                    $classCss = 'success';
                    $disabled_select = true;
                    if ($t->parent_id > 0) {
                        $fa = 'file';
                        $classCss = 'info';
                        $disabled_select = false;
                    }

                    $icon = "fa fa-" . $fa . " text-" . $classCss;
                    $selected = ($t->id === $id_selected) ? true : false;

                    $datas[] = array(
                        "id" => 'S' . $t->id,
                        "text" => $t->name,
                        "state" => array('opened' => true, 'disabled' => $disabled_select, 'selected' => $selected),
                        "icon" => $icon,
                        "parent" => ($t->parent_id > 0) ? 'S' . $t->parent_id : 'C' . $t->id
                    );

                    $btnCheck = '<input type="hidden" name="af_sessions[' . $pf->id . ']" id="af_sessions' . $pf->id . '" class="">  ';
                    $type_formation = '-';

                    foreach ($pf->params as $pm) {
                        if ($pm->param_code == 'PF_TYPE_FORMATION') {
                            $type_formation = $pm->name;
                        }
                    }

                    $result = $DbHelperTools->getSubPfsRecursivly($pf, $t->id, true /* is_root */);
                    $datas = array_merge($datas, $result['datas']);
                    // dd($datas);

                    if (/* empty($result['datas']) && */$pf->is_evaluation && $pf->timestructure_id == $t->id) {
                        $datas[] = array(
                            "id" => $pf->id,
                            "text" => $btnCheck . $pf->title . '<span class="text-info">(ECTS: ' . ($pf->ects ?? '-') . ' ; Type: ' . $type_formation . ')</span>',
                            "state" => array('opened' => true, 'checkbox_disabled' => false),
                            "checkbox" => $btnCheck,
                            "icon" => 'fa fa-file text-default',
                            "parent" => 'S' . $t->id
                        );
                    }
                }
            }
        }
        //$timestructures=Timestructure::all();
        return response()->json($datas);
    }

    public function getJsonTimeSessionStructure(Request $request, $id_selected, $id_block = 0)
    {
        $tools = new PublicTools();
        $categories = Timestructurecategory::all();
        $datas = [];
        $af = Action::find($id_selected);
        $pf = Formation::with('categorie')->find($id_selected);
        $DbHelperTools = new DbHelperTools();

        $periode = $request->filter_periode;
        $start = $request->filter_start ? Carbon::createFromFormat('d/m/Y', $request->filter_start) : false;
        $end = $request->filter_end ? Carbon::createFromFormat('d/m/Y', $request->filter_end) : false;

        foreach ($categories as $c) {
            $timestructures = Timestructure::where('category_id', $c->id)->orderBy('sort', 'ASC')->get();
            $datas[] = array(
                "id" => 'C' . $c->id,
                "text" => $c->name,
                "state" => array('opened' => true),
                "icon" => 'fa fa-folder',
                "parent" => '#'
            );
            if (count($timestructures) > 0) {
                foreach ($timestructures as $t) {
                    if (!empty($periode) && strpos($t->name, 'SEMESTRE') !== FALSE && $t->id != $periode) {
                        continue;
                    }

                    $fa = 'folder';
                    $classCss = 'success';
                    $disabled_select = true;
                    if ($t->parent_id > 0) {
                        $fa = 'file';
                        $classCss = 'info';
                        $disabled_select = false;
                    }

                    $icon = "fa fa-" . $fa . " text-" . $classCss;
                    $selected = ($t->id === $id_selected) ? true : false;

                    // $datas [] = array (
                    //     "id" => 'S'.$t->id,
                    //     "text" => $t->name,
                    //     "state" => array('opened'=>false,'disabled'=>$disabled_select,'selected'=>$selected),
                    //     "icon" => $icon,
                    //     "parent" => ($t->parent_id>0)?'S'.$t->parent_id:'C'.$t->id
                    // );
                    //sessions
                    $sessions = Session::where('af_id', $af->id)->where('is_evaluation', 1)->where('timestructure_id', $t->id);
                    if ($start) {
                        $sessions = $sessions->whereExists(function ($query) use ($start) {
                            $query->select("af_sessiondates.id")
                                ->from('af_sessiondates')
                                ->whereRaw('af_sessiondates.session_id = af_sessions.id')
                                ->where('af_sessiondates.planning_date', '>=', $start->format('Y-m-d'));
                        });
                    }
                    if ($end) {
                        $sessions = $sessions->whereExists(function ($query) use ($end) {
                            $query->select("af_sessiondates.id")
                                ->from('af_sessiondates')
                                ->whereRaw('af_sessiondates.session_id = af_sessions.id')
                                ->where('af_sessiondates.planning_date', '<=', $end->format('Y-m-d'));
                        });
                    }
                    $sessions = $sessions->get();
                    // var_dump($sessions->toSql());exit;
                    $ss_array = $sessions->toArray();

                    if (count($sessions) > 0 || strpos($t->name, 'SEMESTRE') === FALSE) {
                        $datas[] = array(
                            "id" => 'S' . $t->id,
                            "text" => $t->name,
                            "state" => array('opened' => true, 'disabled' => $disabled_select, 'selected' => $selected),
                            "icon" => $icon,
                            "parent" => ($t->parent_id > 0) ? 'S' . $t->parent_id : 'C' . $t->id
                        );
                    }

                    foreach ($sessions as $ss) {
                        $item_parent = array_filter($ss_array, function ($p) use ($ss) {
                            return $p['id'] == $ss->session_parent_id;
                        });
                        $parent_exits = !empty($item_parent);

                        $type_formation = '-';
                        if (isset($pf->params)) {
                            foreach ($pf->params as $pm) {
                                if ($pm->param_code == 'PF_TYPE_FORMATION') {
                                    $type_formation = $pm->name;
                                }
                            }
                        }

                        $infos = $id_block == 1 ? ('<span class="text-info">(ECTS: ' . ($pf->ects ?? '-') . ' ; Coëf: ' . $ss->coefficient . ' ; Type: ' . $type_formation . ')</span>') : '';

                        $btnEdit = ' <a style="cursor: pointer;" class="mr-2" onclick="_formSession(' . $ss->id . ')" title="Editer"><i class="' . $tools->getIconeByAction('EDIT') . ' text-primary"></i></a>';
                        $datas[] = array(
                            "id" => 'SS' . $ss->id,
                            "text" => $ss->title . $infos . $btnEdit,
                            "state" => array('opened' => false),
                            "icon" => 'fa fa-file text-info',
                            "parent" => $parent_exits ? 'SS' . $ss->session_parent_id : 'S' . $t->id
                        );

                        /* Sessions date */
                        $s_dates = Sessiondate::where('session_id', $ss->id);
                        if ($start) {
                            $s_dates = $s_dates->where('planning_date', '>=', $start->format('Y-m-d'));
                        }
                        if ($end) {
                            $s_dates = $s_dates->where('planning_date', '<=', $end->format('Y-m-d'));
                        }
                        $s_dates = $s_dates->get();

                        foreach ($s_dates as $dt) {
                            $datas[] = array(
                                "id" => 'DT' . $dt->id,
                                "text" => Carbon::createFromFormat('Y-m-d', $dt->planning_date)->format('d/m/Y'),
                                "state" => array('opened' => true),
                                "icon" => 'fa fa-calendar text-danger',
                                "parent" => 'SS' . $ss->id
                            );

                            /* Sessions schedules */
                            $s_schedules = Schedule::where('sessiondate_id', $dt->id)->get();
                            foreach ($s_schedules as $schedule) {
                                $start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->start_hour)->format('H\hi');
                                $end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $schedule->end_hour)->format('H\hi');
                                // $btnCheck = '<input type="hidden" name="af_sessions['.$schedule->id.']" id="af_sessions'.$schedule->id.'">  ';
                                $datas[] = array(
                                    "id" => 'SCHEDULE' . $schedule->id,
                                    "text" => /* $btnCheck. */ "$start_hour - $end_hour",
                                    "state" => array('opened' => true),
                                    "icon" => 'fa fa-clock text-default',
                                    "parent" => 'DT' . $dt->id
                                );

                                if ($id_block == 2) {
                                    $af_id = $af->id;
                                    //Intervenants
                                    $rs_schedulecontacts_formers = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 1]])->get();
                                    if (count($rs_schedulecontacts_formers) > 0) {
                                        $datas[] = array(
                                            "id" => 'TITLE_FORMERS' . $schedule->id,
                                            "text" => '<span class="text-warning">Liste des intervenants : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                        foreach ($rs_schedulecontacts_formers as $scf) {
                                            if ($scf->member->enrollment->entity->entity_type == 'P') {
                                                $entityName = $scf->member->enrollment->entity->entity_type . ': ' .  $scf->member->enrollment->entity->ref;
                                            } else {
                                                $entityName = $scf->member->enrollment->entity->entity_type . ': ' . $scf->member->enrollment->entity->name . ' (' . $scf->member->enrollment->entity->ref . ')';
                                            }
                                            $spanEntity = ' <span class="text-info"> (' . $entityName . ')</span>';
                                            $price = '';
                                            $type_former_intervention = $scf->member->contact->type_former_intervention;
                                            $scf_total_cost = $DbHelperTools->getCostScheduleContact($schedule->duration, $scf->price, $type_former_intervention);
                                            $total_cost = ($scf_total_cost > 0) ? ' - coût total : ' . $scf_total_cost . ' €' : '';
                                            if ($scf->price > 0) {
                                                $price = $scf->price . ' €/' . $DbHelperTools->getNameParamByCode($scf->price_type) . $total_cost;
                                            } else {
                                                $price = $total_cost;
                                            }
                                            $contractNumber = ($scf->contract_id > 0) ? (' (' . $scf->contract->number . ')') : '';
                                            $type_of_intervention = ($scf->type_of_intervention) ? (' - Type : ' . $DbHelperTools->getNameParamByCode($scf->type_of_intervention)) : '';

                                            //$pTypeIntervention = '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $scf->member->contact->type_former_intervention . $contractNumber . $type_of_intervention . '</p>';
                                            //$pPrice = ($price) ? '<p class="text-primary mb-0 ml-4"><i class="fas fa-info-circle"></i> ' . $price . '</p>' : '';


                                            $spanMemberName = ($scf->member->contact) ? ('<span class="text-dark">' . ($scf->member->contact->firstname . ' ' . $scf->member->contact->lastname) . '<span>') : '';
                                            $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $scf->id . ')" data-toggle="tooltip" title="Supprimer cet intervenant"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                            $type_of_intervention = $scf->member->contact->type_former_intervention;
                                            $intervention = $type_of_intervention . $contractNumber . $type_of_intervention;
                                            $iPrice = ($price) ? $price : '';

                                            $infosIntervenant = '';
                                            $btnInfosIntervenant = '';
                                            $btnRemuneration = '';
                                            $infosIntervenant = '';
                                            if ($type_of_intervention != 'Interne') {
                                                $btnRemuneration = ' <a style="cursor: pointer;" class="mr-2" onclick="_formRemuneration(' . $af_id . ',' . $scf->member_id . ')" data-toggle="tooltip" title="Rémunération"><i class="' . $tools->getIconeByAction('PRICE') . ' text-success"></i></a>';
                                                $btnInfosIntervenant = ' <a style="cursor: pointer;" data-toggle="tooltip" data-theme="dark" title="' . $intervention . ' - ' . $iPrice . '"><i class="fas fa-info-circle text-' . ($iPrice ? 'primary' : 'warning') . '"></i></a>';
                                            }
                                            $pointage = $DbHelperTools->getPointingInfos($scf->pointing);

                                            $btnPointage = '<a style="cursor: pointer;" class="ml-2 mr-2" data-toggle="tooltip" title="Pointage" onclick="_formPointage(' . $scf->id . ')"><i class="fas fa-edit text-primary"></i></a>';

                                            $datas[] = array(
                                                "id" => 'SCONTACT' . $scf->id,
                                                "text" => $spanMemberName . $btnRemuneration . $btnDelete . $btnInfosIntervenant . $pointage . $btnPointage,
                                                "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                "icon" => "far fa-id-badge text-dark",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                        }
                                    }
                                    //Les stagiaires inscrits non appartient a un group
                                    $rs_schedulecontacts = Schedulecontact::where([['schedule_id', $schedule->id], ['is_former', 0]])->get();
                                    if (count($rs_schedulecontacts) > 0) {
                                        $datas[] = array(
                                            "id" => 'TITLE_STAGIAIRES' . $schedule->id,
                                            "text" => '<span class="text-warning">Liste des groupes et stagiaires : </span>',
                                            "state" => array('opened' => true, 'checkbox_disabled' => true),
                                            "icon" => "far fa-arrow-alt-circle-down text-warning",
                                            "parent" => 'SCHEDULE' . $schedule->id
                                        );
                                        foreach ($rs_schedulecontacts as $sc) {
                                            if ($sc->member->group_id == null) {

                                                $entity_type = $sc->member->enrollment->entity->entity_type;

                                                if ($sc->member->enrollment->entity->entity_type == 'P') {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                } else {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                }

                                                $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                $textContact = $member_name . ' - ' . $entity_type;

                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';

                                                $datas[] = array(
                                                    "id" => 'SCONTACT' . $sc->id,
                                                    "text" => $textContact . $btnDelete . $btnInfosEntity,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                    "icon" => "fa fa-user text-dark",
                                                    "parent" => 'SCHEDULE' . $schedule->id
                                                );
                                            }
                                        }
                                    }
                                    //les membres des groupes
                                    $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
                                    $groups = Group::where('af_id', $af_id)->get();
                                    foreach ($groups as $gp) {

                                        $members_ids = Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->where('group_id', $gp->id)->pluck('id');
                                        $rs_schedulecontactsGroup = Schedulecontact::whereIn('member_id', $members_ids)->where('schedule_id', $schedule->id)->get();
                                        // $nbrMembreInGroup = Member::select('id')->where('group_id', $schedulegroup_id)->pluck('id')->count();
                                        //where([['schedule_id', $schedule->id], ['is_former', 1]])
                                        if (count($rs_schedulecontactsGroup) > 0) {
                                            $btnDeleteGroup = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleGroup(' . $schedule->id  . ',' . $gp->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce groupe"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                            $datas[] = array(
                                                "id" => 'GROUP' . $gp->id . '-' . $schedule->id,
                                                "text" => '<span class="text-primary">' . $gp->title . '</span>' . ' (' . count($rs_schedulecontactsGroup) . ') ' . $btnDeleteGroup,
                                                "state" => array('opened' => false, 'checkbox_disabled' => true),
                                                "icon" => "fa fa-users text-primary",
                                                "parent" => 'SCHEDULE' . $schedule->id
                                            );
                                            foreach ($rs_schedulecontactsGroup as $sc) {
                                                $entity_type = $sc->member->enrollment->entity->entity_type;
                                                if ($sc->member->enrollment->entity->entity_type == 'P') {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                } else {
                                                    $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                }
                                                /* if ($sc->member->enrollment->entity) {
                                                    // dump($sc->member->enrollment);
                                                    $entity_type=$sc->member->enrollment->entity->entity_type;
                                                    if($sc->member->enrollment->entity->entity_type=='P'){
                                                        $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->ref;
                                                    }else{
                                                        $entityName = $sc->member->enrollment->entity->entity_type . ': ' . $sc->member->enrollment->entity->name . ' (' . $sc->member->enrollment->entity->ref . ')';
                                                    }
                                                } else {
                                                    $entityName = 'NONE : '.$sc->member->id;
                                                } */

                                                $spanEntity = ' <p class="text-info mb-0 ml-4"> (' . $entityName . ')</p>';
                                                $member_name = ($sc->member->contact) ? ($sc->member->contact->firstname . ' ' . $sc->member->contact->lastname) : $sc->member->unknown_contact_name;
                                                $textContact = $member_name . ' - ' . $entity_type;
                                                $btnDelete = ' <a style="cursor: pointer;" class="mr-2" onclick="_deleteScheduleContact(' . $sc->id . ')" data-toggle="tooltip" data-theme="dark" title="Supprimer ce stagiaire"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';
                                                $btnInfosEntity = ' <a style="cursor: pointer;" class="mr-2" data-toggle="tooltip" data-theme="dark" title="' . $entityName . '"><i class="' . $tools->getIconeByAction('INFO') . ' text-primary"></i></a>';

                                                $btnScore = '<a style="cursor: pointer;" class="ml-2 mr-2" data-toggle="tooltip" title="Pointage" onclick="_formPointage(' . $sc->id . ')"><i class="fas fa-edit text-primary"></i></a>';
                                                $datas[] = array(
                                                    "id" => 'SCONTACT' . $sc->id . '-' . $sc->member->id . '-' . $ss->id,
                                                    "text" => $textContact . $btnDelete . $btnInfosEntity . $btnScore,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => false),
                                                    "icon" => "fa fa-user text-dark",
                                                    "parent" => 'GROUP' . $gp->id . '-' . $schedule->id
                                                );

                                                $contact_id = $sc->member->contact->id;
                                                $contact_absences = Schedulecontact::selectRaw('af_schedulecontacts.pointing, COUNT(af_schedulecontacts.pointing) as number')
                                                    ->join('af_schedules', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
                                                    ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                                                    ->join('af_members', 'af_members.id', '=', 'af_schedulecontacts.member_id')
                                                    ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                                                    ->where('en_contacts.id', $contact_id)
                                                    ->where('af_sessiondates.session_id', $ss->id)
                                                    ->where('af_schedulecontacts.pointing', '!=', 'present')
                                                    ->groupBy('af_schedulecontacts.pointing')
                                                    ->get()/* ->toArray() */;

                                                $absences = 0;
                                                $non_pointed = 0;
                                                foreach ($contact_absences as $abs) {
                                                    if ($abs->pointing == 'not_pointed') {
                                                        $non_pointed = $abs->number;
                                                    } else {
                                                        $absences = $abs->number;
                                                    }
                                                }

                                                $note = "<span class='label label-sm label-primary label-pill label-inline'>Note écrite : " . ($sc->score ?? '-') . "</span> "
                                                ."<span class='label label-sm label-primary label-pill label-inline'>Note orale : " . ($sc->score_oral ?? '-') . "</span> "
                                                ;
                                                $pointage = $DbHelperTools->getPointingInfos($sc->pointing, $sc->is_abs_justified);

                                                $datas[] = array(
                                                    "id" => 'INFO' . $sc->id,
                                                    "text" => $note . $pointage,
                                                    "state" => array('opened' => true, 'checkbox_disabled' => true),
                                                    "icon" => "",
                                                    "parent" => 'SCONTACT' . $sc->id . '-' . $sc->member->id . '-' . $ss->id
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        //$timestructures=Timestructure::all();
        return response()->json($datas);
    }

    public function getJsonTimeMembersStructure(Request $request, $id_selected)
    {
        /* 
        $param==0 ==> display normal
        $param==1 ==> display in modal
        */
        $tools = new PublicTools();
        $categories = Timestructurecategory::all();
        $datas = [];
        $af = Action::find($id_selected);
        $pf = Formation::with('categorie')->find($id_selected);
        $DbHelperTools = new DbHelperTools();

        $periode = $request->filter_periode;

        foreach ($categories as $c) {
            $timestructures = Timestructure::where('category_id', $c->id)->orderBy('sort', 'ASC')->get();
            $datas[] = array(
                "id" => 'C' . $c->id,
                "text" => $c->name,
                "state" => array('opened' => true, 'checkbox_disabled' => true),
                "icon" => 'fa fa-folder',
                "parent" => '#'
            );
            if (count($timestructures) > 0) {
                $timestructures_p = array_filter($timestructures->all(), function ($t) {
                    return $t->parent_id <= 0; /* MODELES (EJE,...) */
                });
                $timestructures_c = array_filter($timestructures->all(), function ($t) {
                    return $t->parent_id > 0; /* ANNEES / SEMESTRE */
                });
                foreach ($timestructures_p as $t) {
                    $fa = 'folder';
                    $classCss = 'success';
                    $icon = "fa fa-" . $fa . " text-" . $classCss;
                    $datas[] = array(
                        "id" => 'MOD' . $t->id,
                        "text" => $t->name,
                        "state" => array('opened' => true, 'checkbox_disabled' => true),
                        "icon" => $icon,
                        "parent" => 'C' . $t->id
                    );

                    /* Groupes */
                    //les membres des groupes
                    $ids_enrollments = Enrollment::select('id')->where([['af_id', $af->id], ['enrollment_type', 'S']])->get()->pluck('id');

                    $groups = Group::select('af_groups.id', 'af_groups.title')
                        ->join('af_members', 'af_members.group_id', '=', 'af_groups.id')
                        ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                        ->join('af_schedules', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
                        ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                        ->join('af_sessions', 'af_sessiondates.session_id', '=', 'af_sessions.id')
                        ->where('is_evaluation', 1)
                        ->whereIn('af_sessions.timestructure_id', array_map(function ($t) {
                            return $t->id;
                        }, $timestructures->all()))
                        ->groupby('af_groups.id');

                    if ($request->has('filter_group_id') && !empty($request->filter_group_id)) {
                        $groups = $groups->where('af_groups.id', $request->filter_group_id);
                    }

                    $groups = $groups->get();

                    foreach ($groups as $gp) {
                        $referent = Contact::find($gp->ref_contact_id);
                        $referent_name = ($referent) ? ($referent->firstname . ' ' . $referent->lastname) : '-';
                        $grp_ref = "<span class='label label-sm label-primary label-pill label-inline'>Référent : " . ($referent_name) . "</span> ";

                        $datas[] = array(
                            "id" => "GROUP{$gp->id}-{$t->id}",
                            "text" => "{$gp->title} - {$grp_ref}",
                            "state" => array('opened' => false, 'checkbox_disabled' => false),
                            "icon" => 'fa fa-users text-primary',
                            "parent" => 'MOD' . $t->id
                        );

                        $members = Member::whereIn('enrollment_id', $ids_enrollments)->where('group_id', $gp->id)->get();
                        foreach ($members as $member) {
                            // $entity_type=$member->enrollment->entity->entity_type;
                            $member_name = ($member->contact) ? ($member->contact->firstname . ' ' . $member->contact->lastname) : $member->unknown_contact_name;

                            $global_avg = 0;
                            $global_avg_oral = 0;
                            $global_ects = 0;
                            $global_coeff = 0;

                            foreach ($timestructures_c as $ts) {
                                if (!empty($periode) && strpos($t->name, 'SEMESTRE') !== FALSE && $t->id != $periode) {
                                    continue;
                                }

                                // $sessions = Session::select([
                                //     'af_sessions.*',
                                //     'af_schedulecontacts.id as id',
                                //     'af_schedulecontacts.score as member_score',
                                //     'af_schedulecontacts.score_oral as member_score_oral',
                                //     'af_schedulecontacts.ects as member_ects',
                                //     'af_schedulecontacts.pointing as member_pointing',
                                //     'af_schedulecontacts.is_abs_justified as member_is_abs_justified',
                                // ])
                                //     ->join('af_sessiondates', 'af_sessiondates.session_id', '=', 'af_sessions.id')
                                //     ->join('af_schedules', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
                                //     ->join('af_schedulecontacts', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
                                //     ->join('af_members', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                                //     ->where('af_id', $af->id)
                                //     ->where('is_evaluation', 1)
                                //     ->where('timestructure_id', $ts->id)
                                //     ->where('af_schedulecontacts.member_id', $member->id)
                                //     ->where('af_schedulecontacts.pointing', '!=', 'not_pointed')
                                //     ->groupby('af_sessions.id')
                                //     ->get();

                                    $sessions = Session::select([
                                        'af_sessions.*',
                                        'af_schedulecontacts.id as id',
                                        'af_schedulecontacts.score as member_score',
                                        'af_schedulecontacts.score_oral as member_score_oral',
                                        'af_schedulecontacts.ects as member_ects',
                                        'af_schedulecontacts.pointing as member_pointing',
                                        'af_schedulecontacts.is_abs_justified as member_is_abs_justified',
                                      ])
                                        ->join('af_sessiondates', 'af_sessiondates.session_id', '=', 'af_sessions.id')
                                        ->join('af_schedules', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
                                        ->join('af_schedulecontacts', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
                                        ->join('af_members', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                                        ->where('af_id', $af->id)
                                        ->where('is_evaluation', 1)
                                        ->where('timestructure_id', $ts->id)
                                        ->where('af_schedulecontacts.member_id', $member->id)
                                        ->whereNotNull('af_schedulecontacts.pointing')
                                        ->groupby('af_sessions.id')
                                        ->get();
                                      
                                $cumul_score = 0;
                                $cumul_score_oral = 0;
                                $cumul_ects = 0;
                                $cumul_coeff = 0;
                                foreach ($sessions as $ss) {
                                    // dd($ss);
                                    if ($ss->member_score && $ss->coefficient) {
                                        $cumul_score += $ss->member_score * $ss->coefficient;
                                    }
                                    if ($ss->member_score_oral && $ss->coefficient) {
                                        $cumul_score_oral += $ss->member_score_oral * $ss->coefficient;
                                    }
                                    if (($ss->member_score_oral || $ss->member_score) && $ss->coefficient) {
                                        $cumul_ects += $ss->member_ects ?? 0;
                                        $cumul_coeff += $ss->coefficient ?? 0;
                                    }

                                    $score = $ss->member_score ? number_format($ss->member_score, 2) : '-';
                                    $score_oral = $ss->member_score_oral ? number_format($ss->member_score_oral, 2) : '-';
                                    $ects = $ss->member_ects ?? '-';

                                    $note = '<span class="label label-sm label-primary label-inline ml-2 mb-2">Note écrite: ' . $score . '</span>'
                                        . '<span class="label label-sm label-primary label-inline ml-2 mb-2">Note orale: ' . $score_oral . '</span>'
                                        . '<span class="label label-sm label-primary label-inline ml-2 mb-2">ETCS: ' . $ects . '</span>';
                                    $pointage = $DbHelperTools->getPointingInfos($ss->member_pointing, $ss->member_is_abs_justified);

                                    $datas[] = array(
                                        "id" => "SCONTACT{$ss->id}-{$ts->id}-{$member->id}-{$gp->id}-{$t->id}",
                                        "text" => $ss->title . $note . $pointage,
                                        "state" => array('opened' => false, 'checkbox_disabled' => false),
                                        "icon" => "fa fa-file text-success",
                                        "parent" => "TS{$ts->id}-{$member->id}-{$gp->id}-{$t->id}"
                                    );
                                    
                                }

                                $is_period = strpos($ts->name, 'SEMESTRE') !== FALSE;

                                if ($is_period && $sessions->isEmpty()) {
                                    continue;
                                }

                                $fa = 'file';
                                $classCss = 'info';
                                $icon = "fa fa-" . $fa . " text-" . $classCss;


                                // $textTimeSt = $member_name.' - '.$entity_type.' - '."( Moyenne : $moy - ECTS : $ects )";
                                $textTimeSt = $ts->name;

                                if ($is_period) {
                                    $moy = $cumul_coeff > 0 ? number_format($cumul_score / $cumul_coeff, 2) : '-';
                                    $moy_oral = $cumul_coeff > 0 ? number_format($cumul_score_oral / $cumul_coeff, 2) : '-';
                                    $ects = $cumul_ects > 0 ? $cumul_ects : '-';

                                    $global_avg += $cumul_coeff > 0 ? $cumul_score / $cumul_coeff : 0;
                                    $global_avg_oral += $cumul_coeff > 0 ? $cumul_score_oral / $cumul_coeff : 0;
                                    $global_ects += $cumul_ects > 0 ? $cumul_ects : 0;
                                    $global_coeff += $cumul_coeff > 0 ? 1 : 0;

                                    $ids = [];
                                    foreach ($sessions as $session) {
                                        if (isset($session->id)) {
                                            $ids[] = $session->id;
                                        }
                                    }

                                    $textTimeSt .= '<span class="label label-sm label-info label-inline ml-2 mb-2">Moyenne écrite : ' . $moy . '</span>'
                                        .'<span class="label label-sm label-info label-inline ml-2 mb-2">Moyenne orale : ' . $moy_oral . '</span>'
                                        . '<span class="label label-sm label-info label-inline ml-2 mb-2">ETCS: ' . $ects . '</span>';
                                    $textTimeSt .= ' <a style="cursor: pointer;" class="mr-2" onclick="_memberCommittee(' . $member->id . ','. json_encode($ids) .')" data-toggle="tooltip" data-theme="success" title="Commitée"><i class="fa fa-clipboard-check text-primary"></i></a>';
                                    $textTimeSt .= ' <a style="cursor: pointer;" class="mr-2" onclick="_memberTranscript(' . $member->id . ', ' . $ts->id . ')" data-toggle="tooltip" data-theme="success" title="Bulletin de l\'étudiant pour le semestre"><i class="fa fa-file text-primary"></i></a>';
                                }

                                $datas[] = array(
                                    "id" => "TS{$ts->id}-{$member->id}-{$gp->id}-{$t->id}",
                                    "text" => $textTimeSt,
                                    "state" => array('opened' => true, 'checkbox_disabled' => true),
                                    "icon" => $icon,
                                    "parent" => $ts->parent_id != $t->id ? "TS{$ts->parent_id}-{$member->id}-{$gp->id}-{$t->id}" : "MB{$member->id}-{$gp->id}-{$t->id}"
                                );
                            }

                            $global_avg = $global_coeff > 0 ? number_format($global_avg / $global_coeff, 2) : '-';
                            $global_avg_oral = $global_coeff > 0 ? number_format($global_avg_oral / $global_coeff, 2) : '-';
                            $global_ects = $global_coeff > 0 ? $global_ects : '-';

                            $textContact = $member_name . '<span class="label label-sm label-black label-inline ml-2 mb-2">Moyenne écrite : ' . $global_avg . '</span>'
                                . '<span class="label label-sm label-black label-inline ml-2 mb-2">Moyenne orale : ' . $global_avg_oral . '</span>'
                                . '<span class="label label-sm label-black label-inline ml-2 mb-2">ETCS: ' . $global_ects . '</span>';

                            $stop_reason_txt = [
                                "stop" => 'Exclus',
                                "suspend" => "Suspendu",
                                "cancel" => "Abondanné",
                            ];
                            if (!empty($member->stop_reason)) {
                                $textContact .= ' <span class="label label-sm label-warning label-inline ml-2 mb-2">' . $stop_reason_txt[$member->stop_reason] . '</span>';
                            }

                            $datas[] = array(
                                "id" => "MB{$member->id}-{$gp->id}-{$t->id}",
                                "text" => $textContact,
                                "state" => array('opened' => false, 'checkbox_disabled' => false),
                                "icon" => "fa fa-user text-dark",
                                "parent" => "GROUP{$gp->id}-{$t->id}"
                            );
                        }
                    }
                }
            }
        }
        //$timestructures=Timestructure::all();
        return response()->json($datas);
    }
    public function sdtSelectSessionsMembers(Request $request, $af_id, $enrollment_type)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = [];
        if ($af_id > 0) {
            //if group or groupment
            if (in_array($enrollment_type, ['G', 'GROUPMENT'])) {
                $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
            } else {
                $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', $enrollment_type]])->get()->pluck('id');
            }
            if (count($ids_enrollments) > 0) {

                if (in_array($enrollment_type, ['G', 'GROUPMENT'])) {
                    $datas = Member::whereIn('enrollment_id', $ids_enrollments)->where('group_id', '>', 0)->get();
                } else {
                    $datas = Member::whereIn('enrollment_id', $ids_enrollments)->whereNull('group_id')->get();
                    //dd(count($datas));
                }
            }
        }
        //dd($datas);

        if ($enrollment_type == 'GROUPMENT') {
            $groupments = Groupment::where('af_id', $af_id)->get();
            foreach ($groupments as $groupment) {
                $row = array();
                $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="groupments_ids[]" value="' . $groupment->id . '" class="checkable" />
                        <span></span>
                        </label>';
                $nbr_groups = Groupmentgroup::select('id')->where('groupment_id', $groupment->id)->count();
                $row[] = '<strong>' . $groupment->name . ' <span class="text-info">(' . $nbr_groups . ' groupe(s))</span></strong>';
                $row[] = '';
                $data[] = $row;
                //les groupes
                $rs = Groupmentgroup::select('group_id')->where('groupment_id', $groupment->id)->get();
                foreach ($rs as $gp) {
                    $row = array();
                    $row[] = '';
                    $row[] = $gp->group->title . ' <span class="text-info">(' . $gp->group->members->count() . ' stagiaire(s))</span>';
                    $row[] = '';
                    $data[] = $row;
                }
            }
        } elseif ($enrollment_type == 'G') {
            $groups = Group::where('af_id', $af_id)->get();
            foreach ($groups as $gp) {
                $row = array();
                $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="group_ids[]" value="' . $gp->id . '" class="checkable" />
                        <span></span>
                        </label>';
                $row[] = '<span><strong>' . $gp->title . ' (' . $gp->members->count() . ' stagiaire(s))</strong></span>';
                $row[] = '';
                $data[] = $row;

                $members = Member::whereIn('enrollment_id', $ids_enrollments)->where('group_id', $gp->id)->get();
                foreach ($members as $d) {
                    $row = array();
                    //id
                    $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="group_members_ids[]" value="' . $d->id . '" class="checkable" />
                        <span></span>
                        </label>';
                    //<th>Nom</th>
                    $pEntityType = $entityType = $cssClass = '';
                    if ($d->enrollment->entity) {
                        $cssClass = 'primary';
                        $entityType = 'Particulier';
                        if ($d->enrollment->entity->entity_type == 'S') {
                            $entityType = 'Société';
                            $cssClass = 'info';
                        }
                    }

                    $nameEntity = ($d->enrollment->entity) ? $d->enrollment->entity->name . ' (' : '';
                    $refEntity = ($d->enrollment->entity) ? $d->enrollment->entity->ref . ')' : '';

                    $firstname = ($d->contact) ? $d->contact->firstname : $d->unknown_contact_name;
                    $lastname = ($d->contact) ? $d->contact->lastname : '';
                    $row[] = '<span class="font-size-sm">' . $firstname . ' ' . $lastname . '</span>' . '<p class="font-size-sm text-' . $cssClass . ' mb-1">' . $nameEntity . $refEntity . ' - ' . $entityType . '</p>';
                    //<th>Entité</th>
                    //$row[]=$typeEntity.$nameEntity.$refEntity;
                    //<th>Planif</th>
                    $btn_planif_details = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_showScheduleDetails(' . $d->enrollment->action->id . ',' . $d->id . ')" title="Détails du planning"><i class="' . $tools->getIconeByAction('INFO') . '"></i></button>';
                    //$spanPlanif = $DbHelperTools->getPlanifContact($d->id, $d->enrollment->action->id);
                    $row[] = $btn_planif_details;
                    $data[] = $row;
                }
            }
        } else {
            foreach ($datas as $d) {
                $row = array();
                //<th></th>
                $row[] = '<label class="checkbox checkbox-single">
                        <input type="checkbox" name="members_ids[]" value="' . $d->id . '" class="checkable" />
                        <span></span>
                        </label>';
                //<th>Nom</th>
                $pEntityType = $entityType = $cssClass = '';
                if ($d->enrollment->entity) {
                    $cssClass = 'primary';
                    $entityType = 'Particulier';
                    if ($d->enrollment->entity->entity_type == 'S') {
                        $entityType = 'Société';
                        $cssClass = 'info';
                    }
                }

                //$typeEntity = ($d->enrollment->entity)?'<p class="font-size-sm text-warning mb-1">'.(($d->enrollment->entity->entity_type=='S')?'Société':'Particulier').'</p>':'';
                $nameEntity = ($d->enrollment->entity) ? $d->enrollment->entity->name . ' (' : '';
                $refEntity = ($d->enrollment->entity) ? $d->enrollment->entity->ref . ')' : '';

                $firstname = ($d->contact) ? $d->contact->firstname : $d->unknown_contact_name;
                $lastname = ($d->contact) ? $d->contact->lastname : '';

                $row[] = '<span class="font-size-sm">' . $firstname . ' ' . $lastname . '</span>' . '<p class="font-size-sm text-' . $cssClass . ' mb-1">' . $nameEntity . $refEntity . ' - ' . $entityType . '</p>';
                //<th>Entité</th>
                //$row[]=$typeEntity.$nameEntity.$refEntity;
                //<th>Planif</th>
                $btn_planif_details = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_showScheduleDetails(' . $d->enrollment->action->id . ',' . $d->id . ')" title="Détails du planning"><i class="' . $tools->getIconeByAction('INFO') . '"></i></button>';
                //$spanPlanif = $DbHelperTools->getPlanifContact($d->id, $d->enrollment->action->id);
                $row[] = $btn_planif_details;

                $data[] = $row;
            }
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
        ];
        return response()->json($result);
    }
    public function sdtStudentStatus(Request $request, $member_id)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Studentstatus::where('member_id', $member_id)->latest();
        if ($request->isMethod('post')) {
        }
        $udatas = $datas->orderByDesc('id')->get();
        //dd($codes);
        $translateArray = array(
            'student' => 'Etudiant',
            'apprentices' => 'Apprentis',
            'employees' => 'Salariés',
            'jobseeker' => 'Demandeur d’emploi',
        );
        foreach ($udatas as $d) {
            $row = array();
            // <th>Date</th>
            $dtStart = Carbon::createFromFormat('Y-m-d', $d->start_date);
            $dtEnd = Carbon::createFromFormat('Y-m-d', $d->end_date);
            $row[] = $dtStart->format('d/m/Y') . ' - ' . $dtEnd->format('d/m/Y');
            // <th>Statut</th>
            $row[] = $translateArray[$d->student_status];
            //Actions
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formStudentStatus(' . $d->id . ')" title="Édition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteStudentStatus(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $row[] = $btn_edit.$btn_delete;
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
        ];
        return response()->json($result);
    }

    public function sdtStudents(Request $request)
    {
        $dtRequests = $request->all();

        $enrollments = Enrollment::where([['af_id', $dtRequests['af_id']], ['enrollment_type', 'S']])->latest()->get();
        $data = [];
        foreach ($enrollments as $e) {
            $members = $e->members;

            foreach ($members as $m) {
                $group = $m->group;
                $contact = $m->contact;

                if (!$contact) {
                    continue;
                }

                $row = array();
                $row[] = $contact->firstname . ' ' . $contact->lastname;
                $row[] = $group ? $group->title : '';
                $btn_check = '<button class="btn btn-sm btn-clean btn-icon StdSchedule" data-std="' . $contact->id . '" onclick="_copyStudentSchedule(' . $m->id . ')" title="Copier le planning"><i class="fa fa-check"></i></button>';
                $row[] = $btn_check;
                $data[] = $row;
            }
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
        ];
        return response()->json($result);
    }

    public function formStudentStatus($member_id, $student_status_id)
    {
        $row = null;
        if ($student_status_id > 0) {
            $row = Studentstatus::findOrFail($student_status_id);
        }
        return view('pages.af.enrollment.stagiaires.form-student-status', compact('row', 'member_id'));
    }

    public function storeFormStudentStatus(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            $start_date = Carbon::createFromFormat('d/m/Y', $request->start_date);
            $end_date = Carbon::createFromFormat('d/m/Y', $request->end_date);
            if ($end_date > $start_date) {
                $data = array(
                    "id" => $request->id,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "student_status" => $request->student_status,
                    "member_id" => $request->member_id,
                );
                $student_status_id = $DbHelperTools->manageStudentStatus($data);
                $success = true;
                $msg = 'Le statut a été enregistrée avec succès';
            } else {
                $msg = 'Attention, La date de fin est inférieur a la date de début !!!';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function deleteStudentStatus(Request $request, $member_id)
    {
        $success = false;
        $msg = 'Erreur';
        if ($request->isMethod('delete')) {
            try {
                Studentstatus::find($member_id)->delete();
                $msg = 'Le statut a été supprimé avec succès';
                $success = true;
            } catch (Exception $e) {
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function createPdfTranscript($af_id, $member_id, $timestructure_id)
    {
        $member = Member::findOrFail($member_id);
        $contact = $member->contact;
        $entitie = $contact->entitie;
        $address = empty($entitie->adresses) ? false : $entitie->adresses->first();
        $timestructure = Timestructure::findOrFail($timestructure_id);

        $referent = Contact::find($member->group->ref_contact_id);
        $referent_name = $referent ? ($referent->firstname . ' ' . $referent->lastname) : '';

        $address_line = '';
        if ($address) {
            if (!empty($address->line_1)) {
                $address_line .= $address->line_1;
            }
            if (!empty($address->line_2)) {
                $address_line .= ' ' . $address->line_2;
            }
            if (!empty($address->line_3)) {
                $address_line .= ' ' . $address->line_3;
            }
        }

        $sessions = Session::select(['af_sessions.*', 'af_schedulecontacts.score as member_score', 'af_schedulecontacts.score_oral as member_score_oral', 'af_schedulecontacts.ects as member_ects'])
            ->join('af_sessiondates', 'af_sessiondates.session_id', '=', 'af_sessions.id')
            ->join('af_schedules', 'af_schedules.sessiondate_id', '=', 'af_sessiondates.id')
            ->join('af_schedulecontacts', 'af_schedulecontacts.schedule_id', '=', 'af_schedules.id')
            ->join('af_members', 'af_schedulecontacts.member_id', '=', 'af_members.id')
            ->where('af_id', $af_id)
            ->where('is_evaluation', 1)
            ->where('timestructure_id', $timestructure_id)
            ->where('af_schedulecontacts.member_id', $member_id)
            ->groupby('af_sessions.id')
            ->get();

        $cumul_scores = $cumul_scores_oral = $cumul_ects = $cumul_coeff = $cumul_coeff_oral = 0;
        $score_exists = $score_oral_exists = false;

        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', 'TRANSCRIPT')->first();
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;

        $keywordHeader = array(
            '{LOGO_HEADER}',
        );
        $keywordHeaderReplace = array(
            public_path('media/logo/logo-light.png'),
        );
        $htmlHeader = str_replace($keywordHeader, $keywordHeaderReplace, $header);

        //MAIN
        $table_head = '<tr>'
            . '<th>Matières</th>'
            . '<th>Evaluations et certification écrite</th>'
            . '<th>Evaluations et certification orale</th>'
            . '<th>Crédits ECTS</th>'
            . '</tr>';

        $table_body = '';
        foreach ($sessions as $sc) {
            $parent = Session::find($sc->session_parent_id);
            if (!$parent) {
                $parent = $sc;
            }

            $m_score = $sc->member_score;
            $m_score_oral = $sc->member_score_oral;

            $table_body .= '<tr>'
                . '<td>'
                . '<strong>' . $parent->title . '</strong>'
                . '<br><span class="sub-session">' . $sc->title . '</span>'
                . '</td>'
                . '<td>' . ($m_score !== null ? number_format($m_score, 2, ',', ' ') : '') . (!$sc->coefficient ? '<br>Coefficient non saisi' : '') . '</td>'
                . '<td>' . ($m_score_oral !== null ? number_format($m_score_oral, 2, ',', ' ') : '') . (!$sc->coefficient ? '<br>Coefficient non saisi' : '') . '</td>'
                . '<td>' . number_format($sc->member_ects, 2, ',', ' ') . '</td>'
                . '</tr>';

            if ($m_score !== null) {
                $score_exists = true;
                $cumul_scores += $sc->member_score * $sc->coefficient;
                $cumul_coeff += $sc->coefficient;
            }
            
            if ($m_score_oral !== null) {
                $score_oral_exists = true;
                $cumul_scores_oral += $sc->member_score_oral * $sc->coefficient;
                $cumul_coeff_oral += $sc->coefficient;
            }

            $cumul_ects += $sc->member_ects;

            if (!$sc->coefficient) {
                break;
            }
        }

        $score_avg = $cumul_coeff ? $cumul_scores / $cumul_coeff : 0;
        $score_avg_oral = $cumul_coeff_oral ? $cumul_scores_oral / $cumul_coeff_oral : 0;

        if ($score_exists && $score_oral_exists) {
            $score_avg_total = ($score_avg + $score_avg_oral) / 2;
        } elseif ($score_oral_exists) {
            $score_avg_total = $score_avg_oral;
        } else {
            $score_avg_total = $score_avg;
        }

        $table_body .= '<tr>'
            . '<td>Moyennes</td>'
            . '<td>' . ($score_exists ? number_format($score_avg, 2, ',', ' ') : '') . '</td>'
            . '<td>' . ($score_oral_exists ? number_format($score_avg_oral, 2, ',', ' ') : '') . '</td>'
            . '<td></td>';


        $table_body .= '<tr>'
            . '<td>Moyenne total</td>'
            . '<td colspan="2">' . number_format($score_avg_total, 2, ',', ' ') . '</td>'
            . '<td></td>'
            . '</tr><tr>'
            . '<td colspan="3">Total crédits ECTS</td>'
            . '<td>' . number_format($cumul_ects, 2, ',', ' ') . '</td>'
            . '</tr>';

        $table_observations = '<tr>'
            . '<td>Observations</td>'
            . '<td></td>'
            . '</tr>';

        $keyword = array(
            "{MEMBER_GROUP}",
            "{MEMBER_NUM}",
            "{MEMBER_TUTEUR}",
            "{MEMBER_REFERENT_FORMER}",
            "{MEMBER_FIRSTNAME}",
            "{MEMBER_LASTNAME}",
            "{MEMBER_ADDRESS}",
            "{MEMBER_ZIPCODE}",
            "{MEMBER_CITY}",
            "{PERIOD}",
            "{TABLE_HEADER}",
            "{TABLE_BODY}",
            "{OBSERVATIONS_TABLE}",
        );

        $keyreplace = array(
            $member->group->title,
            $entitie->ref,
            '',
            $referent_name,
            $contact->firstname,
            $contact->lastname,
            $address_line,
            $address ? $address->postal_code : '',
            $address ? $address->city : '',
            $timestructure->name,
            $table_head,
            $table_body,
            $table_observations,
        );
        $htmlMain = str_replace($keyword, $keyreplace, $content);
        //Footer
        $keywordFooter = array(
            '{CITY_TRANSCRIPT}',
            '{DATE_TRANSCRIPT}',
        );
        $keywordFooterReplace = array(
            config('global.company_city'),
            date('d/m/Y'),
        );
        $htmlFooter = str_replace($keywordFooter, $keywordFooterReplace, $footer);

        $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
        return $pdf->stream();
    }
}
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a

// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a

// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a
// a

// a
// a
// a
// a
// a

