<?php

namespace App\Http\Controllers;

use PDF;
use Mail;
use Carbon\Carbon;
use App\Models\Param;
use App\Models\Price;
use App\Models\Action;
use App\Mail\OfferMail;
use App\Models\Entitie;
use App\Models\Session;
use App\Models\Contract;
use App\Models\Schedule;
use Carbon\CarbonPeriod;
use App\Models\Formation;
use App\Models\Helpindex;
use App\Models\Ressource;
use App\Models\Sessiondate;
use Illuminate\Http\Request;
use App\Models\Documentmodel;
use App\Models\Emailmodel;
use App\Models\Timestructure;
use App\Models\Templateperiod;
use App\Models\Member;
use App\Library\Helpers\Helper;
use App\Mail\ValidatedContract;

use App\Models\Schedulecontact;
use App\Models\Planningtemplate;
use App\Models\Scheduleressource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Library\Services\PublicTools;
use App\Models\Timestructurecategory;
use Illuminate\Support\Facades\Config;
use App\Library\Services\DbHelperTools;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function logs()
    {
        $page_title = 'La liste des logs';
        $page_description = '';
        return view('pages.log.list', compact('page_title', 'page_description'));
    }

    public function sdtLogs(Request $request)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Activity::orderByDesc('id')->get();
        foreach ($datas as $d) {
            $row = array();
            $row['id'] = $d->id;
            $row['log_name'] = $d->log_name;
            $row['description'] = $d->description;
            $row['subject_type'] = $d->subject_type;
            $row['subject_id'] = $d->subject_id;
            $row['causer_type'] = $d->causer_type;
            $row['causer_id'] = $d->causer_id;
            $row['properties'] = $d->properties;
            $created_at = '<span class="label label-outline-info label-pill label-inline mb-1">C : ' . $d->created_at->format('d/m/Y H:i') . '</span>';
            //$updated_at='<span class="label label-outline-warning label-pill label-inline">M : '.$d->updated_at->format('d/m/Y H:i').'</span>';
            //$row['infos']=$created_at.$updated_at;
            $row['infos'] = $created_at;
            //Actions
            $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewLog(' . $d->id . ')" title="Afficher les détails"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteLog(' . $d->id . ')" title="Supprimer"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $row['Actions'] = $btn_view . $btn_delete;
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

    public function selectOptions(Request $request)
    {
        $result = [];
        if ($request->isMethod('post')) {
            if ($request->has(['param_code'])) {
                $params = Param::select('id', 'code', 'name')->where('param_code', $request->param_code)->get();
                if (count($params) > 0) {
                    foreach ($params as $p) {
                        $value = ($request->use_code == 1) ? $p['code'] : $p['id'];
                        //$value=($request->param_code=="TYPE_OF_SOCIETE")?$p['code']:$value;
                        $result[] = ['id' => $value, 'name' => $p['name']];
                    }
                }
            }
        }
        return response()->json($result);
    }

    public function selectEntitiesOptions($entity_id, $entity_type, $is_former)
    {
        //$is_former == 2 pour afficher toutes les entités
        $result = [];
        if ($is_former != 2) {
            if ($entity_id > 0 && $entity_type != '') {
                $entities = Entitie::select('id', 'ref', 'name', 'entity_type','is_former')->where([['id', '!=', $entity_id], ['entity_type', $entity_type], ['is_active', 1]])->get();
            } else {
                $entities = Entitie::select('id', 'ref', 'name', 'entity_type','is_former')->where('is_active', 1)->get();
            }
        } else {
            $entities = Entitie::select('id', 'ref', 'name', 'entity_type','is_former')->where([['id', '!=', $entity_id], ['entity_type', $entity_type], ['is_active', 1]])->get();
        }
        if (count($entities) > 0) {
            foreach ($entities as $en) {
                $spanFormer=($en['is_former'])?' (Formateur)':'';
                $result[] = ['id' => $en['id'], 'name' => ($en['name'] . ' - ' . $en['ref'] . ' - ' . $en['entity_type'].' '.$spanFormer)];
            }
        }
        return response()->json($result);
    }

    public function selectFormationsOptions($autorize_af)
    {
        $result = [];
        $rows = Formation::select('id', 'code', 'title')->where('autorize_af', $autorize_af)->whereNull('parent_id')->get();
        if (count($rows) > 0) {
            foreach ($rows as $pf) {
                $result[] = ['id' => $pf['id'], 'name' => ($pf['title'] . ' (' . $pf['code'] . ')')];
            }
        }
        return response()->json($result);
    }

    public function selectPricesOptions($af_id, $entitie_id)
    {
        $result = [];
        /* if(!empty($device_type) && $device_type!='ALL'){
           $rows=Price::select('id','price','price_type')->where('device_type',$device_type)->get();
        }else{
            $rows=Price::select('id','price','price_type')->get();
        } */
        $af = Action::findOrFail($af_id);
        $device_type = $af->device_type;
        $entity_type = '';
        if ($entitie_id > 0) {
            $entity = Entitie::find($entitie_id);
            $entity_type = $entity->entity_type;
        }
        $attachedAfPricesIds = $af->prices()->pluck('id')->toArray();
        $rows = [];
        if ($entity_type) {
            $p_device_type = '';
            if ($entity_type == 'S') {
                if ($device_type == 'INTER') {
                    $p_device_type = 'PRICE_DISPOSITIF_ENTREPRISE_TYPE_INTER';
                } elseif ($device_type == 'INTRA') {
                    $p_device_type = 'PRICE_DISPOSITIF_ENTREPRISE_TYPE_INTRA';
                }
            } elseif ($entity_type == 'P') {
                if ($device_type == 'INTER') {
                    $p_device_type = 'PRICE_DISPOSITIF_PARTICULIER_TYPE_INTER';
                }
            }
            //dd($p_device_type);
            if ($p_device_type) {
                $rows = Price::select('id', 'price', 'price_type', 'entity_type', 'device_type')->whereIn('id', $attachedAfPricesIds)->where([['device_type', $p_device_type], ['entity_type', $entity_type]])->get();
            } else {
                $rows = Price::select('id', 'price', 'price_type', 'entity_type', 'device_type')->whereIn('id', $attachedAfPricesIds)->where('entity_type', $entity_type)->get();
            }
        }
        if (count($rows) > 0) {
            $DbHelperTools = new DbHelperTools();
            foreach ($rows as $p) {
                $typeEntite = ($p['entity_type'] == "S") ? '<p>Tarif société</p>' : (($p['entity_type'] == "P") ? '<p>Tarif particulier</p>' : '');
                $result[] = ['id' => $p['id'] , 'name' => $p['title'] . ' - ' . $typeEntite . ' - ' . $DbHelperTools->getNameParamByCode($p['device_type']) . ' - ' . $p['price'] . '€ / ' . $DbHelperTools->getNameParamByCode($p['price_type'])];
            }
        }
        return response()->json($result);
    }
    public function selectPricesByEntityTypeOptions($af_id, $entity_type)
    {
        $result = [];
        $af = Action::findOrFail($af_id);
        $device_type = $af->device_type;
        $attachedAfPricesIds = $af->prices()->pluck('id')->toArray();
        $rows = [];
        if ($entity_type) {
            $p_device_type = '';
            if ($entity_type == 'S') {
                if ($device_type == 'INTER') {
                    $p_device_type = 'PRICE_DISPOSITIF_ENTREPRISE_TYPE_INTER';
                } elseif ($device_type == 'INTRA') {
                    $p_device_type = 'PRICE_DISPOSITIF_ENTREPRISE_TYPE_INTRA';
                }
            } elseif ($entity_type == 'P') {
                if ($device_type == 'INTER') {
                    $p_device_type = 'PRICE_DISPOSITIF_PARTICULIER_TYPE_INTER';
                }
            }
            //dd($p_device_type);
            if ($p_device_type) {
                $rows = Price::select('id', 'price', 'price_type', 'entity_type', 'device_type')->whereIn('id', $attachedAfPricesIds)->where([['device_type', $p_device_type], ['entity_type', $entity_type]])->get();
            } else {
                $rows = Price::select('id', 'price', 'price_type', 'entity_type', 'device_type')->whereIn('id', $attachedAfPricesIds)->where('entity_type', $entity_type)->get();
            }
        }
        if (count($rows) > 0) {
            $DbHelperTools = new DbHelperTools();
            foreach ($rows as $p) {
                $typeEntite = ($p['entity_type'] == "S") ? '<p>Tarif société</p>' : (($p['entity_type'] == "P") ? '<p>Tarif particulier</p>' : '');
                $result[] = ['id' => $p['id'] , 'name' => $p['title'] . ' - ' . $typeEntite . ' - ' . $DbHelperTools->getNameParamByCode($p['device_type']) . ' - ' . $p['price'] . '€ / ' . $DbHelperTools->getNameParamByCode($p['price_type'])];
            }
        }
        return response()->json($result);
    }

    public function selectSessionsOptions($af_id)
    {
        $result = [];
        $rows = Session::select('id', 'code', 'title')->where('af_id', $af_id)->get();
        if (count($rows) > 0) {
            foreach ($rows as $s) {
                //$result[]=['id'=>$s['id'],'name'=>($s['title'].' ('.$s['code'].')')];
                $result[] = ['id' => $s['id'], 'name' => $s['code']];
            }
        }
        return response()->json($result);
    }

    public function params()
    {
        $page_title = 'Paramétrages';
        $page_description = '';
        return view('pages.param.list', compact('page_title', 'page_description'));
    }

    public function sdtParams(Request $request)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Param::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('param_code', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('param_name', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('code', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('name', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                //filter_param_code
                if ($request->has('filter_param_code') && !empty($request->filter_param_code)) {
                    $datas->where('param_code', $request->filter_param_code);
                }
                if ($request->has('filter_activation') && !empty($request->filter_activation)) {
                    $is_active = ($request->filter_activation == 'a') ? 1 : 0;
                    $datas->where('is_active', $is_active);
                }
            } else {
                $datas = Param::orderByDesc('id');
            }
        }
        $udatas = $datas->orderByDesc('id')->get();
        foreach ($udatas as $d) {
            $row = array();
            //ID
            $row[] = $d->id;
            //<th>Type</th>
            $row[] = '<p class="text-info">' . $d->param_code . '</p><p>' . $d->param_name . '</p>';
            //<th>Paramétrage</th>
            $row[] = '<p class="text-' . $d->css_class . '">' . $d->code . '</p><p>' . $d->name . '</p>';
            //<th>Css</th>
            $row[] = $tools->constructParagraphLabelDot('lg', $d->css_class, $d->css_class);
            //Date creation
            $labelActive = 'Désactivé';
            $cssClassActive = 'danger';
            if ($d->is_active == 1) {
                $labelActive = 'Activé';
                $cssClassActive = 'success';
            }
            $spanActive = $tools->constructParagraphLabelDot('xs', $cssClassActive, $labelActive);
            if(isset($d->created_at)){
                $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            }else{
                $created_at = null;
            }
                
            if(isset($d->updated_at)){
                $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            }else{
                $updated_at = null;
            }
                
            $row[] = $spanActive . $created_at . $updated_at;
            //Actions
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formParam(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewParam(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteParam(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $row[] = $btn_edit;
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

    public function formParam($row_id)
    {
        $paramCodes = Config::get('params.params');
        $cssClass = Config::get('params.css_class');
        //dd($cssClass);

        $row = null;
        if ($row_id > 0) {
            $row = Param::findOrFail($row_id);
        }
        return view('pages.param.form', ['row' => $row, 'paramCodes' => $paramCodes, 'cssClass' => $cssClass[0]]);
    }

    public function storeFormParam(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            $rules = [
                'code' => ($request->id > 0) ? 'required' : 'required|unique:App\Models\Param',
            ];
            $messages = [
                'code.unique' => 'Ce code est déjà utilisé !',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $msg = '<p>Veuillez vérifier les erreurs ci-dessous : </p>';
                foreach ($errors->get('code') as $message) {
                    $msg .= '<p class="text-danger">' . $message . '</p>';
                }
                $success = false;
            } else {
                $DbHelperTools = new DbHelperTools();
                //dd($request->all());
                $row_id = $DbHelperTools->manageParams($request->all());
                $success = true;
                $msg = 'Le paramétrage a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    

    public function inseeApi(Request $request)
    {
        $datas = [];
        if ($request->isMethod('post')) {
            if ($request->has('name') && !empty($request->name)) {
                $API_INSEE_URL = env('API_INSEE_URL', '');
                $API_INSEE_TOKEN = env('API_INSEE_TOKEN', '');
                if (isset($API_INSEE_URL) && isset($API_INSEE_TOKEN)) {
                    //$url = $API_INSEE_URL.'/siren?q=periode(denominationUniteLegale:"HAVET")';
                    $url = $API_INSEE_URL . '/siret?q=denominationUniteLegale:"' . $request->name . '"';
                    $response = Http::withToken($API_INSEE_TOKEN)->get($url);
                    if ($response->ok() && $response->successful()) {
                        if ($response->status() === 200) {
                            $json = $response->json();
                            //dd($json);
                            $etablissements = (isset($json['etablissements'])) ? $json['etablissements'] : [];
                            if (count($etablissements) > 0) {
                                //dd($etablissements);
                                foreach ($etablissements as $u) {
                                    $denominationUniteLegale = '';
                                    if (isset($u['uniteLegale'])) {
                                        $denominationUniteLegale = $u['uniteLegale']['denominationUniteLegale'];
                                    }
                                    $datas[] = array(
                                        'siren' => $u['siren'],
                                        'siret' => $u['siret'],
                                        'denomination' => $denominationUniteLegale,
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        return response()->json($datas);
    }

    public function ptemplates()
    {
        $page_title = 'Gestion des modèles de plannification';
        $page_description = '';
        return view('pages.pl_template.list', compact('page_title', 'page_description'));
    }

    public function sdtPtemplates(Request $request)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Planningtemplate::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('code', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('name', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                if ($request->has('filter_activation') && !empty($request->filter_activation)) {
                    $is_active = ($request->filter_activation == 'a') ? 1 : 0;
                    $datas->where('is_active', $is_active);
                }
            } else {
                $datas = Planningtemplate::orderByDesc('id');
            }
        }
        $udatas = $datas->orderByDesc('id')->get();
        foreach ($udatas as $d) {
            $row = array();
            //ID
            $row[] = $d->id;
            //<th>Modèle</th>
            $row[] = '<p class="text-info">' . $d->code . '</p><p>' . $d->name . '</p>';
            //Planification
            //morning_period
            $morning_period = Templateperiod::where([['planning_template_id', $d->id], ['type', 'M']])->first();
            //afternoon_period
            $afternoon_period = Templateperiod::where([['planning_template_id', $d->id], ['type', 'A']])->first();
            $htmlTable = '';
            if ($morning_period || $afternoon_period) {
                $htmlTable = '<table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th scope="col">Période</th>
                            <th scope="col">Heure début</th>
                            <th scope="col">Heure fin</th>
                            <th scope="col">Durée</th>
                        </tr>
                    </thead>
                    <tbody>';

                $spanMorning = $spanAfternoon = '';
                if ($morning_period) {
                    $sh = Carbon::createFromFormat('Y-m-d H:i:s', $morning_period->start_hour);
                    $se = Carbon::createFromFormat('Y-m-d H:i:s', $morning_period->end_hour);
                    //$spanMorning = '<p>Matin : <span class="text-success">'.$sh->format('H:i').'</span> - <span class="text-success">'.$se->format('H:i').'</span> => Durée <span class="text-info">'.$morning_period->duration.'</span></p>';

                    $htmlTable .= '
                        <tr>
                            <th>Matin </th>
                            <td><span class="text-success">' . $sh->format('H:i') . '</span></td>
                            <td><span class="text-success">' . $se->format('H:i') . '</span></td>
                            <td><span class="text-primary">' . $morning_period->duration . '</span></td>
                        </tr>';
                }
                if ($afternoon_period) {
                    $ash = Carbon::createFromFormat('Y-m-d H:i:s', $afternoon_period->start_hour);
                    $ase = Carbon::createFromFormat('Y-m-d H:i:s', $afternoon_period->end_hour);
                    //$spanAfternoon = '<p>Après midi : <span class="text-success">'.$ash->format('H:i').'</span> - <span class="text-success">'.$ase->format('H:i').'</span> => Durée <span class="text-info">'.$afternoon_period->duration.'</span></p>';
                    $htmlTable .= '
                        <tr>
                            <th>Après midi </th>
                            <td><span class="text-success">' . $ash->format('H:i') . '</span></td>
                            <td><span class="text-success">' . $ase->format('H:i') . '</span></td>
                            <td><span class="text-primary">' . $afternoon_period->duration . '</span></td>
                        </tr>';
                }
                $spanDurationTotal = '';
                if ($d->duration > 0) {
                    //$spanDurationTotal = '<p>Durée total jour : <span class="text-primary">'.$d->duration.'</span></p>';

                    $htmlTable .= '
                        <tr>
                            <th colspan="3">Durée total jour </th>
                            <td><span class="text-primary">' . $d->duration . '</span></td>
                        </tr>';
                }

                $htmlTable .= '</tbody></table>';
            }

            //$row[]=$spanMorning.$spanAfternoon.$spanDurationTotal;
            $row[] = $htmlTable;
            //Date creation
            $labelActive = 'Désactivé';
            $cssClassActive = 'danger';
            if ($d->is_active == 1) {
                $labelActive = 'Activé';
                $cssClassActive = 'success';
            }
            $spanActive = $tools->constructParagraphLabelDot('xs', $cssClassActive, $labelActive);
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $spanActive . $created_at . $updated_at;
            //Actions
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formPtemplate(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewPtemplate(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deletePtemplate(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $row[] = $btn_edit;
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

    public function formPtemplate($row_id)
    {
        $row = $morning_period = $afternoon_period = null;
        if ($row_id > 0) {
            $row = Planningtemplate::findOrFail($row_id);
            //morning_period
            $morning_period = Templateperiod::where([['planning_template_id', $row_id], ['type', 'M']])->first();
            //dd($morning_period);
            //afternoon_period
            $afternoon_period = Templateperiod::where([['planning_template_id', $row_id], ['type', 'A']])->first();
        }
        return view('pages.pl_template.form', ['row' => $row, 'morning_period' => $morning_period, 'afternoon_period' => $afternoon_period]);
    }

    public function storeFormPtemplate(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            $rules = [
                'code' => ($request->id > 0) ? 'required' : 'required|unique:App\Models\Planningtemplate',
            ];
            $messages = [
                'code.unique' => 'Ce code est déjà utilisé !',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $msg = '<p>Veuillez vérifier les erreurs ci-dessous : </p>';
                foreach ($errors->get('code') as $message) {
                    $msg .= '<p class="text-danger">' . $message . '</p>';
                }
                $success = false;
            } else {
                $DbHelperTools = new DbHelperTools();
                $dataPlTemplate = array(
                    "id" => $request->id,
                    "is_active" => $request->is_active,
                    "order_show" => $request->order_show,
                    "code" => $request->code,
                    "name" => $request->name,
                    "duration" => $request->duration,
                );
                $row_id = $DbHelperTools->managePlanningTemplate($dataPlTemplate);
                //morning_period
                if (!empty($request->m_start_hour) && !empty($request->m_end_hour)) {
                    $dataMorningPeriod = array(
                        "id" => $request->m_id,
                        "type" => $request->m_type,
                        "start_hour" => Carbon::createFromFormat('H:i', $request->m_start_hour),
                        "end_hour" => Carbon::createFromFormat('H:i', $request->m_end_hour),
                        "duration" => $request->m_duration,
                        "planning_template_id" => $request->id,
                    );
                    $row_m_id = $DbHelperTools->manageTemplatePeriod($dataMorningPeriod);
                } else {
                    //On supprime la période du matin
                    $morning_period = Templateperiod::where([['planning_template_id', $row_id], ['type', 'M']])->first();
                    ($morning_period) ? $morning_period->forceDelete() : '';
                }
                //afternoon_period
                if (!empty($request->a_start_hour) && !empty($request->a_end_hour)) {
                    $dataAfternoonPeriod = array(
                        "id" => $request->a_id,
                        "type" => $request->a_type,
                        "start_hour" => Carbon::createFromFormat('H:i', $request->a_start_hour),
                        "end_hour" => Carbon::createFromFormat('H:i', $request->a_end_hour),
                        "duration" => $request->a_duration,
                        "planning_template_id" => $request->id,
                    );
                    $row_a_id = $DbHelperTools->manageTemplatePeriod($dataAfternoonPeriod);
                } else {
                    //on supprime la période après midi
                    $afternoon_period = Templateperiod::where([['planning_template_id', $row_id], ['type', 'A']])->first();
                    ($afternoon_period) ? $afternoon_period->forceDelete() : '';
                }
                $success = true;
                $msg = 'Le modèle a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function prices()
    {
        $page_title = 'Gestion des tarifications';
        $page_description = '';
        return view('pages.price.list', compact('page_title', 'page_description'));
    }

    public function sdtPrices(Request $request)
    {
        $DbHelperTools = new DbHelperTools();
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Price::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('device_type', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('price', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('price_type', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
            } else {
                $datas = Price::orderByDesc('id');
            }
        }
        $udatas = $datas->orderByDesc('id')->get();
        foreach ($udatas as $d) {
            $row = array();
            //ID
            $row[] = $d->id;
            //<th>Titre</th>
            $row[] = ($d->title) ? $d->title : '-';
            //<th>Type d'entité</th>
            $typeEntite = ($d->entity_type == "S") ? '<p>Société</p>' : (($d->entity_type == "P") ? '<p>Particulier</p>' : '');
            $cssClass = 'info';
            if ($d->is_former_price == 1) {
                $cssClass = 'success';
                $typeEntite = '<p class="text-' . $cssClass . '">Tarif formateur</p>';
            }
            $row[] = $typeEntite;
            //<th>Type de tarif</th>
            $row[] = '<p>' . $DbHelperTools->getNameParamByCode($d->device_type) . '</p>';
            //<th>Tarif</th>
            $row[] = '<p class="text-' . $cssClass . '">' . $d->price . ' € / ' . $DbHelperTools->getNameParamByCode($d->price_type) . '</p>';
            //<th>Infos</th>Diffusé site Non applicable Sur devis
            $spanInfos = ($d->is_broadcast == 1) ? '<p class="mb-0 text-info">Diffusé site</p>' : '';
            $spanInfos .= ($d->is_forbidden == 1) ? '<p class="mb-0 text-danger">Non applicable</p>' : '';
            $spanInfos .= ($d->is_ondemande == 1) ? '<p class="mb-0 text-warning">Sur devis</p>' : '';
            $row[] = $spanInfos;
            //Date creation
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at . $updated_at;
            //Actions
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formPrice(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewPrice(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deletePrice(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $row[] = $btn_edit;
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

    public function formPrice($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Price::findOrFail($row_id);
        }
        return view('pages.price.form', ['row' => $row]);
    }

    public function formPriceByType($is_former, $row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Price::findOrFail($row_id);
        }
        return view('pages.price.formbytype', ['row' => $row, 'is_former' => $is_former]);
    }

    public function storeFormPrice(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        //dd($request->all());
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $searched_price_id = 0;
            if ($request->id == 0) {
                $searchData = array(
                    "entity_type" => $request->entity_type,
                    "device_type" => $request->device_type,
                    "price" => $request->price,
                    "price_type" => $request->price_type,
                    "is_former_price" => ($request->is_former_price) ? 1 : 0,
                );
                $searched_price_id = $DbHelperTools->getPriceId($searchData);
                if ($searched_price_id > 0) {
                    $success = false;
                    $msg = 'Ce tarif est déjà enregistré !';
                    return response()->json([
                        'success' => $success,
                        'msg' => $msg,
                    ]);
                }
            }
            $data = array(
                "id" => $request->id,
                "title" => $request->title,
                "entity_type" => $request->entity_type,
                "device_type" => $request->device_type,
                "price" => $request->price,
                "price_type" => $request->price_type,
                "accounting_code" => $request->accounting_code,
                "is_broadcast" => $request->is_broadcast,
                "is_forbidden" => $request->is_forbidden,
                "is_ondemande" => $request->is_ondemande,
                "is_former_price" => $request->is_former_price,
            );
            $row_id = $DbHelperTools->managePrice($data);
            $success = true;
            $msg = 'La tarification a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function sdtSelectPrices(Request $request, $pf_id, $af_id)
    {
        $DbHelperTools = new DbHelperTools();
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $ids_prices = [];
        if ($pf_id > 0) {
            $pf = Formation::findOrFail($pf_id);
            $ids_prices = $pf->prices()->pluck('id');
        }
        $device_type = null;
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
            $device_type = $af->device_type;//'INTRA' INTER;
            $ids_prices = $af->prices()->pluck('id');
        }

        $rdatas = Price::where('is_former_price', 0)->latest();
        if (isset($device_type)) {
            $rdatas->where('device_type', 'like', '%' . $device_type . '%');
        }
        $datas = $rdatas->orderByDesc('id')->get();
        foreach ($datas as $d) {
            $row = array();
            //ID
            $checked = '';
            if (count($ids_prices) && $ids_prices->contains($d->id)) {
                $checked = 'checked';
            }
            $row[] = '<label class="checkbox checkbox-single">
                    <input type="checkbox" name="prices_ids[]" value="' . $d->id . '" class="checkable" ' . $checked . '/>
                    <span></span>
                    </label>';
            //<th>Titre</th>
            $row[] = $d->title;
            //<th>Type d'entité</th>
            $typeEntite = ($d->entity_type == "S") ? '<p>Société</p>' : (($d->entity_type == "P") ? '<p>Particulier</p>' : '');
            $cssClass = 'info';
            $row[] = $typeEntite;
            //<th>Type de tarif</th>
            $row[] = '<p>' . $DbHelperTools->getNameParamByCode($d->device_type) . '</p>';
            //<th>Tarif</th>
            $row[] = '<p class="text-' . $cssClass . '">' . $d->price . ' € / ' . $DbHelperTools->getNameParamByCode($d->price_type) . '</p>';
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

    public function selectPricesOptionsFormers()
    {
        $result = [];
        $rows = Price::select('id','title', 'price', 'price_type')->where('is_former_price', 1)->get();
        if (count($rows) > 0) {
            $DbHelperTools = new DbHelperTools();
            foreach ($rows as $p) {
                $result[] = ['id' => $p['price'], 'name' => $p['title'] . ' - ' . $p['price'] . '€ / ' . $DbHelperTools->getNameParamByCode($p['price_type'])];
            }
        }
        return response()->json($result);
    }

    public function ressources()
    {
        $page_title = 'Gestion des ressources';
        $page_description = '';
        return view('pages.ressource.list', compact('page_title', 'page_description'));
    }

    public function sdtRessources(Request $request)
    {
        $DbHelperTools = new DbHelperTools();
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Ressource::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('type', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('name', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                if ($request->has('filter_type') && !empty($request->filter_type)) {
                    $datas->where('type', $request->filter_type);
                }
                if ($request->has('filter_activation') && !empty($request->filter_activation)) {
                    $is_active = ($request->filter_activation == 'a') ? 1 : 0;
                    $datas->where('is_active', $is_active);
                }
                if ($request->has('filter_dispo') && !empty($request->filter_dispo)) {
                    $is_dispo = ($request->filter_dispo == 'd') ? 1 : 0;
                    $datas->where('is_dispo', $is_dispo);
                }
            } else {
                $datas = Ressource::orderByDesc('id');
            }
        }
        $udatas = $datas->orderByDesc('id')->get();
        foreach ($udatas as $d) {
            $row = array();
            //ID
            $row[] = $d->id;
            //<th>Type</th>
            $interne = ($d->type == "RES_TYPE_LIEU") ? (($d->is_internal == 1) ? '<p class="text-primary">Interne</p>' : '<p class="text-warning">Externe</p>') : '';
            $row[] = $DbHelperTools->getNameParamByCode($d->type) . $interne;
            //<th>Name</th>
            $parent = ($d->parent_ressource != null) ? '<p class="text-primary">Parente : ' . $d->parent_ressource->name . '</p>' : '';
            $row[] = $d->name . $parent;
            //<th>Infos</th>
            $spanActive = ($d->is_active == 1) ? $tools->constructParagraphLabelDot('xs', 'success', 'Activé') : $tools->constructParagraphLabelDot('xs', 'danger', 'Non activé');
            $spanDispo = ($d->is_dispo == 1) ? $tools->constructParagraphLabelDot('xs', 'success', 'Disponible') : $tools->constructParagraphLabelDot('xs', 'danger', 'Non disponible');
            $row[] = $spanActive . $spanDispo;
            //Date creation
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at . $updated_at;
            //Actions
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formRessource(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewRessource(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteRessource(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $row[] = $btn_edit;
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

    public function formRessource($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Ressource::findOrFail($row_id);
        }
        $DbHelperTools = new DbHelperTools();
        $ressource_types = $DbHelperTools->getParamsByParamCode('RES_TYPES');
        return view('pages.ressource.form', ['row' => $row, 'ressource_types' => $ressource_types]);
    }

    public function storeFormRessource(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        //dd($request->all());
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $data = array(
                "id" => $request->id,
                "type" => $request->type,
                "name" => $request->name,
                "is_active" => $request->is_active,
                "is_dispo" => $request->is_dispo,
                "is_internal" => $request->is_internal,
                "ressource_id" => $request->ressource_id,
                "address_training_location" => $request->address_training_location,
            );
            $row_id = $DbHelperTools->manageRessource($data);
            $success = true;
            $msg = 'La ressource a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function selectRessourcesOptions($res_id, $type)
    {
        $result = [];
        $rows = Ressource::select('id', 'type', 'name')->where([['id', '!=', $res_id], ['type', $type]])->get();
        if (count($rows) > 0) {
            $DbHelperTools = new DbHelperTools();
            foreach ($rows as $p) {
                $result[] = ['id' => $p['id'], 'name' => $p['name']];
            }
        }
        return response()->json($result);
    }

    public function sdtSelectRessources(Request $request, $ressource_type)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = [];
        if ($ressource_type != 'ALL') {
            $datas = Ressource::where([['type', $ressource_type], ['is_active', 1], ['is_dispo', 1]])->get();
        } else {
            $datas = Ressource::where([['is_active', 1], ['is_dispo', 1]])->get();
        }
        foreach ($datas as $d) {
            $row = array();
            //<th></th>
            $row[] = '<label class="checkbox checkbox-single">
                    <input type="checkbox" name="ressources_ids[]" value="' . $d->id . '" class="checkable" />
                    <span></span>
                    </label>';
            //<th>Nom</th>
            $type = ' - <span class="text-primary">' . $DbHelperTools->getNameParamByCode($d->type) . '</span>';
            $interne = ($d->type == "RES_TYPE_LIEU") ? (($d->is_internal == 1) ? ' - <span class="text-primary">Interne</span>' : ' - <span class="text-warning">Externe</span>') : '';
            $row[] = $d->name . $type . $interne;
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

    public function documents()
    {
        $page_title = 'Gestion des modèles de documents';
        $page_description = '';
        $documentmodels = Documentmodel::select('id', 'code', 'name')->get();
        return view('pages.document.list', compact('page_title', 'page_description', 'documentmodels'));
    }

    public function emails()
    {
        $page_title = 'Gestion des modèles des emails';
        $page_description = '';
        $emailmodels = Emailmodel::select('id', 'code', 'name')->get();
        return view('pages.email.list', compact('page_title', 'page_description', 'emailmodels'));
    }

    /* public function sdtDocuments(Request $request)
    {
        $tools=new PublicTools();
        $dtRequests = $request->all();
        $data=$meta=[];
        $datas = Documentmodel::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('code', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('name', 'like', '%'.$request->filter_text.'%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if(!empty($request->filter_start) && !empty($request->filter_end)){
                        $start = Carbon::createFromFormat('d/m/Y',$request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y',$request->filter_end);
                        $datas->whereBetween('created_at', [$start." 00:00:00", $end." 23:59:59"]);
                    }
                }
                if ($request->has('filter_activation') && !empty($request->filter_activation)) {
                    $is_active = ($request->filter_activation=='a')?1:0;
                    $datas->where('is_active',$is_active);
                }
            }else{
                $datas=Documentmodel::orderByDesc('id');
            }
        }
        $udatas=$datas->orderByDesc('id')->get();
        foreach ($udatas as $d) {
            $row=array();
                //ID
                $row[]=$d->id;
                //<th>Modèle</th>
                $row[]='<p class="text-info">'.$d->code.'</p><p>'.$d->name.'</p>';
                //date
                $created_at = $tools->constructParagraphLabelDot('xs','primary','C : '.$d->created_at->format('d/m/Y H:i'));
                $updated_at = $tools->constructParagraphLabelDot('xs','warning','M : '.$d->updated_at->format('d/m/Y H:i'));
                $row[]=$created_at.$updated_at;
                //Actions
                $btn_edit='<button class="btn btn-sm btn-clean btn-icon" onclick="_formPtemplate('.$d->id.')" title="Edition"><i class="'.$tools->getIconeByAction('EDIT').'"></i></button>';
                $btn_view='<button class="btn btn-sm btn-clean btn-icon" onclick="_viewPtemplate('.$d->id.')" title="Edition"><i class="'.$tools->getIconeByAction('VIEW').'"></i></button>';
                $btn_delete='<button class="btn btn-sm btn-clean btn-icon" onclick="_deletePtemplate('.$d->id.')" title="Suppression"><i class="'.$tools->getIconeByAction('DELETE').'"></i></button>';
                $row[]=$btn_edit;
            $data[]=$row;
        }
        $sort  = ! empty($dtRequests['sort']['sort']) ? $dtRequests['sort']['sort'] : 'asc';
        $field = ! empty($dtRequests['sort']['field']) ? $dtRequests['sort']['field'] : 'ID';
        $page    = ! empty($dtRequests['pagination']['page']) ? (int)$dtRequests['pagination']['page'] : 1;
        $perpage = ! empty($dtRequests['pagination']['perpage']) ? (int)$dtRequests['pagination']['perpage'] : -1;
        $pages = 1;
        $total = count($data); // total items in array
        $meta = [
            'page'    => $page,
            'pages'   => $pages,
            'perpage' => $perpage,
            'total'   => $total,
            'sort'  => $sort,
            'field' => $field,
        ];
        $result = [
            'meta' => $meta,
            'data' => $data,
        ];
        return response()->json($result);
    } */
    public function formDocument($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $documentmodel = Documentmodel::findOrFail($row_id);
        }
        return view('pages.document.form', ['documentmodel' => $documentmodel]);
    }

    public function formNewDocument()
    {
        return view('pages.document.form.new-document');
    }

    public function storeFormDocument(Request $request)
    {
        // dd($request);
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        //dd($request->all());
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $data = array(
                "id" => $request->id,
                "code" => ($request->id == 0) ? $DbHelperTools->generateDocumentCode() : null,
                "name" => $request->name,
                "default_header" => ($request->has('default_header')) ? $request->default_header : null,
                "custom_header" => $request->custom_header,
                "custom_content" => $request->custom_content,
                "default_content" => ($request->has('default_content')) ? $request->default_content : null,
                "custom_footer" => $request->custom_footer,
                "default_footer" => ($request->has('default_footer')) ? $request->default_footer : null,
            );
            //dd($data);
            $row_id = $DbHelperTools->manageDocumentmodel($data);
            $success = true;
            $msg = 'La modèle a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function restoreDocument($row_id)
    {
        $success = false;
        if ($row_id > 0) {
            $documentmodel = Documentmodel::findOrFail($row_id);
            $documentmodel->custom_header = $documentmodel->default_header;
            $documentmodel->custom_content = $documentmodel->default_content;
            $documentmodel->custom_footer = $documentmodel->default_footer;
            $documentmodel->save();
            $id = $documentmodel->id;
            if ($id > 0) {
                $success = true;
            }
        }
        return response()->json([
            'success' => $success,
        ]);
    }

    public function createPdfOverview($documentmodel_id)
    {
        $dm = Documentmodel::find($documentmodel_id);
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $dn = Carbon::now();
        $pdf = PDF::loadView('pages.pdf.model', ['htmlMain' => $content, 'htmlHeader' => $header, 'htmlFooter' => $footer]);
        return $pdf->stream();
        //return $pdf->download('MODELE-'.time().'.pdf');
    }

    public function geoApiSearchCitiesByCodePostal($codePostal)
    {
        $datas = [];
        if ($codePostal > 0) {
            $API_GEO_URL = env('API_GEO_URL', '');
            $API_ADRESSE_URL = env('API_ADRESSE_URL', '');
            if (isset($API_GEO_URL) && isset($API_GEO_URL)) {
                //https://geo.api.gouv.fr/communes?codePostal=44380
                $url = $API_GEO_URL . '?codePostal=' . $codePostal;
                $response = Http::get($url);
                if ($response->ok() && $response->successful()) {
                    if ($response->status() === 200) {
                        $json = $response->json();
                        if (count($json) > 0) {
                            foreach ($json as $j) {
                                $datas[] = array('city' => $j['nom']);
                            }
                        }
                    }
                }
            }
        }
        return response()->json($datas);
    }

    public function selectEntitiesByTypeOptions($entity_type)
    {
        //entity_type == P ou S ou all ou with contacts & users (CU)
        $result = [];
        if (in_array($entity_type, ['P', 'S'])) {
            $entities = Entitie::select('id', 'ref', 'name', 'entity_type')->where([['entity_type', $entity_type], ['is_active', 1]])->get();
        } elseif ($entity_type == 'CU') {
            $entities = Entitie::select('en_entities.id', 'en_entities.ref', 'en_entities.name', 'en_entities.entity_type')
            ->join('en_contacts', 'en_contacts.entitie_id', 'en_entities.id')
            ->join('users', 'en_contacts.id', 'users.contact_id')
            ->where('users.active', 1)
            ->groupBy('en_entities.id')
            ->get();
        } else {
            $entities = Entitie::select('id', 'ref', 'name', 'entity_type')->where('is_active', 1)->get();
        }

        if (count($entities) > 0) {
            foreach ($entities as $en) {
                $result[] = ['id' => $en['id'], 'name' => ($en['name'] . ' - ' . $en['ref'] . ' - ' . $en['entity_type'])];
            }
        }
        return response()->json($result);
    }
    public function selectEntitiesByTypeStagiaireFormerOptions($entity_type,$is_former)
    {
        //entity_type == P ou S ou all
        $result = [];
        if (in_array($entity_type, ['P', 'S'])) {
            $entities = Entitie::select('id', 'ref', 'name', 'entity_type')->where([['entity_type', $entity_type],['is_active', 1],['is_former', $is_former]])->get();
        } else {
            $entities = Entitie::select('id', 'ref', 'name', 'entity_type')->where([['is_active', 1],['is_former', $is_former]])->get();
        }

        if (count($entities) > 0) {
            foreach ($entities as $en) {
                $result[] = ['id' => $en['id'], 'name' => ($en['name'] . ' - ' . $en['ref'] . ' - ' . $en['entity_type'])];
            }
        }
        return response()->json($result);
    }

    public function selectActionsByOptions()
    {

        $result = [];

        $actions = Action::get();

        if (count($actions) > 0) {
            foreach ($actions as $action) {
                $result[] = ['id' => $actions['id'], 'code' => ($action['code'] )];
            }
        }
        return response()->json($result);
    }

    public function sendOfferMail()
    {
        $email = 'hbriere@havetdigital.fr';
        $offer = [
            'title' => 'Deals of the Day',
            'url' => 'https://www.havetdigital.fr'
        ];

        Mail::to($email)->send(new OfferMail($offer));

        dd("Mail sent!");
    }

    public function structureTemps()
    {
        $page_title = 'Structure temporelle';
        $page_description = '';
        return view('pages.structure_temporelle.list', compact('page_title', 'page_description'));
    }

    public function formStructure($row_id)
    {
        $row = $model = null;
        $default_order_show = $parent_id = 0;
        if ($row_id > 0) {
            //$row = Categorie::findOrFail ( $row_id );
            // $row = Categorie::withTrashed()->where('id', $row_id)->first();
            $row = Timestructure::findOrFail($row_id);
        }
        // $DbHelperTools = new DbHelperTools();
        // $default_order_show = $DbHelperTools->generateOrderShowForCategorie($parent_id);
        return view('pages.structure_temporelle.form', compact('row', 'model'));
    }

    public function formModel($row_id)
    {
        $row = null;
        $default_order_show = $parent_id = 0;
        if ($row_id > 0) {
            //$row = Categorie::findOrFail ( $row_id );
            // $row = Categorie::withTrashed()->where('id', $row_id)->first();
            $row = Timestructurecategory::findOrFail($row_id);
        }
        // $DbHelperTools = new DbHelperTools();
        // $default_order_show = $DbHelperTools->generateOrderShowForCategorie($parent_id);
        return view('pages.structure_temporelle.model-form', compact('row'));
    }

    public function selectModelsOptions($model_id)
    {
        $result = [];

        if ($model_id > 0) {
            $models = Timestructurecategory::select('id', 'code', 'name')->where([['id', '!=', $model_id], ['is_active', 1]])->get();
        } else {
            $models = Timestructurecategory::select('id', 'code', 'name')->where([['is_active', 1]])->get();
        }


        if (count($models) > 0) {
            foreach ($models as $model) {
                $result[] = ['id' => $model['id'], 'code_name' => ($model['code'] . ' - ' . $model['name'])];
            }
        }
        return response()->json($result);
    }

    public function selectParentsOptions($parent_id)
    {
        $result = [];

        if ($parent_id > 0) {
            $parents = Timestructure::select('id', 'name')->where([['id', '!=', $parent_id]])->get();
        } else {
            $parents = Timestructure::select('id', 'name')->get();
        }


        if (count($parents) > 0) {
            foreach ($parents as $parent) {
                $result[] = ['id' => $parent['id'], 'name' => $parent['name'] , 'parentOfParent' => ($parent['parent_id']!=null?:0)];
            }
        }
        return response()->json($result);
    }

    public function storeFormStructure(Request $request)
    {
        $success = false;
        $msg = '';
        $data = $request->all();


        $DbHelperTools = new DbHelperTools();

        $data = [
            "id" => $request->id,
            "model_id" => $request->model_id,
            "order_show" => $request->order_show,
            "name" => $request->name,
            "parent_id" => $request->parent_id,
        ];


        //dd($request->birth_date);exit();
        $row_id = $DbHelperTools->manageStructure($data);


        $success = true;
        $msg = 'Le client a été enregistrée avec succès';


        return response()->json(['success' => $success,
            'msg' => $msg,]);
    }

    public function storeFormModel(Request $request)
    {
        $success = false;
        $msg = '';
        $data = $request->all();


        $DbHelperTools = new DbHelperTools();

        $data = [
            "id" => $request->id,
            "code" => $request->code,
            "order_show" => $request->order_show,
            "name" => $request->name,
            "is_active" => $request->is_active,
        ];


        //dd($request->birth_date);exit();
        $row_id = $DbHelperTools->manageModel($data);


        $success = true;
        $msg = 'Le modèle a été enregistrée avec succès';


        return response()->json(['success' => $success,
            'msg' => $msg,]);
    }


    public function srcStructures($structure_id, $with_trashed)
    {
        /* if ($with_trashed == 1) {
            $structures = Timestructure::withTrashed();
        } else {
            $structures = Timestructure::select('*')->orderBy('sort')->get();
        } */
        $categories=Timestructurecategory::all();
        //$categories->orderByDesc('order_show')->get();
        $datas = [];
        foreach($categories as $c){

        $structures = Timestructure::where('category_id',$c->id)->orderBy('sort')->get();

        $datas [] = array(
                "id" => 'C'.$c->id,
                "text" => $c->name,
                "state" => array('opened' => true),
                "icon" => 'fa fa-folder',
                "parent" => '#'
        );

        foreach ($structures as $s) {
            $fa = 'folder';
            $classCss = 'success';
            $labelIsActif = '';

            $disabled_select = false;

            if ($structure_id == $s->id) {
                $disabled_select = true;
            }

            if ($s->parent_id > 0) {
                $fa = 'file';
            }
            $icon = "fa fa-" . $fa . " text-" . $classCss;

            if ($with_trashed == 1 && $s->deleted_at) {
                $disabled_select = true;
            }

            if ($structure_id > 0) {
                $text = $s->name;
            } else {
                $tools = new PublicTools();

                $nameText = '<a style="cursor: pointer;" class="jstree-anchor mr-2" onclick="_formStructure(' . $s->id . ')">' . $s->name . '</a>';
                $btnEdit = '<a style="cursor: pointer;" class="mr-2" onclick="_formStructure(' . $s->id . ')" title="Editer ce niveau"><i class="' . $tools->getIconeByAction('EDIT') . ' text-primary"></i></a>';

                $btnDelete = $labelInfo = '';

                $btnDelete = '<a style="cursor: pointer;" class="mr-2" onclick="_deleteCategorie(' . $s->id . ')" title="Supprimer ce niveau"><i class="' . $tools->getIconeByAction('DELETE') . ' text-danger"></i></a>';


                $btn_archive = $btn_unarchive = '';
                if ($s->deleted_at != null) {
                    $btn_unarchive = '<a style="cursor: pointer;" class="mr-2" onclick="_unarchiveCategorie(' . $s->id . ')" title="Désarchiver ce niveau"><i class="' . $tools->getIconeByAction('UNARCHIVE') . ' text-warning"></i></a>';
                    //un grain archivé ne devrait plus être modifiable.
                    $btnEdit = '';
                } else {
                    $btn_archive = '<a style="cursor: pointer;" class="mr-2" onclick="_archiveCategorie(' . $s->id . ')" title="Archiver ce niveau"><i class="' . $tools->getIconeByAction('ARCHIVE') . ' text-warning"></i></a>';
                }
                $text = $nameText . $btnEdit . $btnDelete . $btn_archive . $btn_unarchive . $labelIsActif . $labelInfo;
            }
            $datas [] = array(
                "id" => $s->id,
                "text" => $text,
                "state" => array('opened' => true, 'disabled' => $disabled_select),
                "icon" => $icon,
                "parent" => ($s->parent_id > 0) ? $s->parent_id : 'C'.$c->id
            );

        }
        }

        return response()->json($datas);
    }



    public function sdtModels(Request $request, $model_id)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        if ($model_id > 0) {
            $datas = Timestructurecategory::where(['id' => $model_id]);
        } else {
            $datas = Timestructurecategory::latest();
        }
        $models = $datas->orderByDesc('id')->get();


        foreach ($models as $model) {
            $row = array();

            //ID:
            $row[] = $model->id;

            //<th>Code</th>
            $row[] = $model->code;


            //<th>Name</th>
            $row[] = $model->name;

            //<th>Sort</th>
            $row[] = $model->sort;

            //<th>Infos</th>
            $labelActive = 'Désactivé';
            $cssClassActive = 'danger';
            if ($model->is_active == 1) {
                $labelActive = 'Activé';
                $cssClassActive = 'success';
            }
            $spanActive = $tools->constructParagraphLabelDot('xs', $cssClassActive, $labelActive);

            $row[] = $spanActive;

            //<th>Dates</th>
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $model->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $model->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at . $updated_at;

            //<th>Actions</th>
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formModel(' . $model->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';

            $row[] = $btn_edit;
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
    public function scheduleRrooms()
    {
        $page_title = 'Planning des ressources';
        $page_description = '';
        $dNow = Carbon::now();
        $startOfWeek=$dNow->startOfWeek()->format('Y-m-d');
        return view('pages.ressource.agenda.schedule-rooms', compact('page_title', 'page_description','startOfWeek'));
    }
    public function scheduleRroomsfrm()
    {
        $page_title = 'Planning des ressourcessss';
        $page_description = '';
        $dNow = Carbon::now();
        $startOfWeek=$dNow->startOfWeek()->format('Y-m-d');
        return view('pages.ressource.agenda.schedule-roomsfrm', compact('page_title', 'page_description','startOfWeek'));
    }
    public function getAgenda($start_date,$nb_days,$af_id){
        $DbHelperTools = new DbHelperTools();
        $data = $meta = [];
 
        $dates=$DbHelperTools->getPeriodDates($start_date,(int)$nb_days);

        return view('pages.ressource.agenda.headtable', compact('dates','start_date','nb_days','af_id'));
    }
    public function sdtScheduleRrooms(Request $request,$start_date,$nb_days)
    {
        $userid = auth()->user()->id;
        $roles= auth()->user()->roles;
        $DbHelperTools = new DbHelperTools();
        $data = $meta = [];

        if($roles[0]->code=='APPRENANT' || $roles[0]->code=='FORMATEUR'){
            $contactid = DB::table('users')
            ->where('id', $userid)
            ->pluck('contact_id');
           
            $enrollment_id = DB::table('af_members')->whereIn('contact_id',$contactid)->pluck('enrollment_id');
            
            $member_id = DB::table('af_members')->where('contact_id',$contactid)->pluck('id');
            $af_id = DB::table('af_enrollments')->whereIn('id',$enrollment_id)->pluck('af_id');    
            $schedules_contact=Schedulecontact::select('schedule_id')->whereIn('member_id',$member_id)->pluck('schedule_id')->unique();
            //var_dump($schedules_contact);die();
                    //$ids_ressources=Ressource::select('id')->where([['type','RES_TYPE_LIEU'],['is_internal',1]])->pluck('id');
            $ressources=Ressource::select('id','name')->where([['type','RES_TYPE_LIEU'],['is_active',1]])->get();
            // dd($ressources);
            foreach ($ressources as $d) {
                    // dd($ss);
                    $ids_schedules=Scheduleressource::select('schedule_id')->where('ressource_id',$d->id)->whereIn('schedule_id',$schedules_contact)->pluck('schedule_id')->unique();
                    $ids_sessiondates=Schedule::select('sessiondate_id')->whereIn('id',$ids_schedules)->orderBy('start_hour','asc')->get();


                    /**** si on prend id de scdule */

                    //var_dump($d->name);
                     $row = array();
                    //Salle
                     $row[] = $d->name;
                    //var_dump($row);
                    //<th>les dates</th>
                    //$dNow = Carbon::now();
                    //$start_date=$dNow->format('Y-m-d');
                    $dates=$DbHelperTools->getPeriodDates($start_date,$nb_days);
                    if(count($dates)>0){
                        foreach($dates as $date){
                            $htmlDate='';
                            //$sessiondates = Sessiondate::select('id','session_id')->where('planning_date', $date)->whereIn('id',$ids_sessiondates)->get();

                            $qb = DB::table('af_sessiondates')
                            ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                            ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                            ->select(
                                'af_sessiondates.*',
                                )->where('af_sessiondates.planning_date',$date)
                                ->whereIn('af_sessiondates.id', $ids_sessiondates);
                            if(count($af_id)>0){
                                $qb->whereIn('af_actions.id',$af_id);
                            }    
                            $sessiondates=$qb->get();
                            //dd($sessiondates);

                            if(count($sessiondates)){
                                foreach($sessiondates as $sd){
                                    $htmlDate.=$DbHelperTools->getSessionDateInfos($sd->id,$ids_schedules);
                                }
                            }
                            $row[] =$htmlDate;
                        }
                    }
                    $data[]=$row;
        }
        }else{
            $af_id=0;
            if($request->has('af_id')){
                $af_id=(int) $request->af_id;
            }

            //$ids_ressources=Ressource::select('id')->where([['type','RES_TYPE_LIEU'],['is_internal',1]])->pluck('id');
            $ressources=Ressource::select('id','name')->where([['type','RES_TYPE_LIEU'],['is_active',1]])->get();
            // dd($ressources);
            foreach ($ressources as $d) {
                $ids_schedules=Scheduleressource::select('schedule_id')->where('ressource_id',$d->id)->pluck('schedule_id')->unique();
                $ids_sessiondates=Schedule::select('sessiondate_id')->whereIn('id',$ids_schedules)->orderBy('start_hour','asc')->get();
                /**** si on prend id de scdule */
                $row = array();
                //Salle
                $row[] = $d->name;
                //<th>les dates</th>
                //$dNow = Carbon::now();
                //$start_date=$dNow->format('Y-m-d');
                $dates=$DbHelperTools->getPeriodDates($start_date,$nb_days);
                if(count($dates)>0){
                    foreach($dates as $date){
                        $htmlDate='';
                        //$sessiondates = Sessiondate::select('id','session_id')->where('planning_date', $date)->whereIn('id',$ids_sessiondates)->get();

                        $qb = DB::table('af_sessiondates')
                        ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                        ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                        ->select(
                            'af_sessiondates.*',
                            )->where('af_sessiondates.planning_date',$date)
                            ->whereIn('af_sessiondates.id', $ids_sessiondates);
                        if($af_id>0){
                            $qb->where('af_actions.id',$af_id);
                        }    
                        $sessiondates=$qb->get();
                        //dd($sessiondates);

                        if(count($sessiondates)){
                            foreach($sessiondates as $sd){
                                $htmlDate.=$DbHelperTools->getSessionDateInfos($sd->id,$ids_schedules);
                            }
                        }
                        $row[] =$htmlDate;
                    }
                }
                $data[]=$row;
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

        $sortby1= $new_arr = [];
		$sortby1 = array_unique($data, SORT_REGULAR); // remove duplicate values.
		$i=0;

        foreach($sortby1 as $k=>$v)
		{
			$new_arr[$i++]=$v;
		}
		$aa=$reeldata=[]; $a=0;
		foreach($new_arr as $key=>$val)
		{
			//$aa[$key]= array_filter($val,create_function('$a','return $a !== "";'));
			$aa= count(array_filter($val,create_function('$a','return $a !== "";')));
			if($aa>1){
				$reeldata[]=$val;
			}
		}


        $result = [
            'meta' => $meta,
            'data' => $reeldata,
        ];
        return response()->json($result);
    }

    public function sdtScheduleRroomsFrm(Request $request,$start_date,$nb_days)
    {
        $userid = auth()->user()->id;
        //$roles= $user->roles;
        $contactid = DB::table('users')
        ->where('id', $userid)
        ->pluck('contact_id');
        
        $memberid = DB::table('af_members')->where('contact_id',$contactid)->pluck('id');
        $schedule_id = DB::table('af_schedulecontacts')->where('member_id',$memberid)->pluck('schedule_id')->unique();
        // echo($contactid); exit;

        // $request->af_id=11;
        $DbHelperTools = new DbHelperTools();
        $data = $meta = [];
        $af_id=0;
        if($request->has('af_id')){
            $af_id=(int) $request->af_id;
        }
        //dd($af_id);
        //$ids_ressources=Ressource::select('id')->where([['type','RES_TYPE_LIEU'],['is_internal',1]])->pluck('id');
        $ressources=Ressource::select('id','name')->where([['type','RES_TYPE_LIEU'],['is_active',1]])->get();
        foreach ($ressources as $d) {
            $ids_schedules=Scheduleressource::select('schedule_id')->where('ressource_id',$d->id)->pluck('schedule_id')->unique();
                    // echo($ids_schedules); exit;

            $ids_sessiondates=Schedule::select('sessiondate_id')->whereIn('id',$schedule_id)->orderBy('start_hour','asc')->get();
            /**** si on prend id de scdule */
            $row = array();
            //Salle
            $row[] = $d->name;
            //<th>les dates</th>
            //$dNow = Carbon::now();
            //$start_date=$dNow->format('Y-m-d');
            $dates=$DbHelperTools->getPeriodDates($start_date,$nb_days);
            if(count($dates)>0){
                foreach($dates as $date){
                    $htmlDate='';
                    //$sessiondates = Sessiondate::select('id','session_id')->where('planning_date', $date)->whereIn('id',$ids_sessiondates)->get();

                    $qb = DB::table('af_sessiondates')
                    ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                    ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
                    ->select(
                        'af_sessiondates.*',
                        )->where('af_sessiondates.planning_date',$date)
                        ->whereIn('af_sessiondates.id', $ids_sessiondates);
                    if($af_id>0){
                        $qb->where('af_actions.id',$af_id);
                    }    
                    $sessiondates=$qb->get();
                    //dd($sessiondates);

                    if(count($sessiondates)){
                        foreach($sessiondates as $sd){
                            $htmlDate.=$DbHelperTools->getSessionDateInfos($sd->id,$ids_schedules);
                        }
                    }
                    $row[] =$htmlDate;
                }
            }
            $data[]=$row;
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

    public function getDateByParamNextPrevious($next_previous,$current_start_date){
        //$next_previous=1 --> PREVIOUS $next_previous=2 --> NEXT
        //$current_start_date='2021-09-22';
        if($next_previous==1){
            $current = Carbon::createFromFormat('Y-m-d', $current_start_date);
            $start=$current->startOfWeek();
            $endWeek=$start->subDay();
            $date=$endWeek->startOfWeek()->format('Y-m-d');
        }elseif($next_previous==2){
            $current = Carbon::createFromFormat('Y-m-d', $current_start_date);
            $end=$current->endOfWeek();
            $date=$end->addDay()->format('Y-m-d');
        }elseif($next_previous==3){
            $current = Carbon::createFromFormat('d-m-Y', $current_start_date);
            $start=$current->startOfWeek();
            $date=$start->format('Y-m-d');
        }
        
        return response()->json(['date'=>$date]);
    }
    public function payments()
    {
        $page_title = 'Contrôle de paie';
        $page_description = '';
        return view('pages.payments.index', compact('page_title', 'page_description'));
    }
    public function sdtContractsControle(Request $request)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = $datas = $ids_contacts = [];
        $req = Contract::latest();
        $is_filter=0;
        $start=$end=null;
        $spanLabel='<span class="label label-light-success label-rounded">X</span>';
        //dd($request->all());
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                $is_filter=$request->filter;
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $filter_start=$start->format('Y/m/d');
                        $filter_end=$end->format('Y/m/d');
                        
                        $filter_start2=$start->format('Y-m-d');
                        $filter_end2=$end->format('Y-m-d');
                        //dd($filter_start2);
                    }
                }
            }
        }
        $datas = $req->orderByDesc('id')->get();
        foreach ($datas as $d) {
            $rsArrayDates=$DbHelperTools->getStartDateEndDateContractFormer($d->id);
            $date_start_contract=$rsArrayDates['start_contract'];
            $date_end_contract=$rsArrayDates['end_contract'];
            $date_start_contract_fr=$date_end_contract_fr='--';
            $permission=true;
            if($is_filter==1){
                if(isset($start) && isset($end)){
                    $permission=false;
                    if(isset($date_start_contract) && isset($date_end_contract)){
                        $d1 = Carbon::createFromFormat('Y-m-d', $date_start_contract);
                        $d2 = Carbon::createFromFormat('Y-m-d', $date_end_contract);
                        $date_start_contract_fr=$d1->format('d-m-Y');
                        $date_end_contract_fr=$d2->format('d-m-Y');
                        if($d1->lte($end) && $d2->gte($start)){
                            $permission=true;
                        }else{
                            $permission=false;
                        }
                    }
                }
            }
            //dd($permission);
            if($permission){
                $row = array();
                //ID
                $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" name="contracts[]" value="' . $d->id . '" class="checkable" /><span></span></label>';
                // <th>Name (Etat)</th>
                $checked=$DbHelperTools->checkRequiredComptaFields($d->contact_id);
                $spanAlert=($checked)?'':'<p><a href="/contacts/'.$d->contact_id.'" class="text-warning" target="_blank"><i class="flaticon-warning-sign text-warning"></i> Détails</a></p>';
                $nameContact = $d->contact != null ? '<br>'.$d->contact->gender.' '.$d->contact->lastname.' '.$d->contact->firstname :'';
                $row[] = $nameContact.$spanAlert;
                // <th>CDD</th>
                $tabResult=$DbHelperTools->getNbHoursAndPricesContractFormer($d->id,'present',$filter_start2,$filter_end2);
                $pNumber='<p class="text-primary">'.$d->number.'</p>';
                $pDateStart='<p class="text-info">Début : '.$date_start_contract_fr.'</p>';
                $pDateEnd='<p class="text-warning">Fin : '.$date_end_contract_fr.'</p>';

                $totalCost=$DbHelperTools->getTotalCostContractFormer($d->id,$filter_start2,$filter_end2);
                $pPrice='<p class="text-success">Coût : '.$totalCost.'€ </p>';

                $htmlDetails='<p class="text-success">Pointé : '.number_format($tabResult['total_cost'],2).'€ /'.Helper::convertTime($tabResult['nb_hours']).'</p>';
                $tabNp=$DbHelperTools->getNbHoursAndPricesContractFormer($d->id,'not_pointed',$filter_start2,$filter_end2);
                $htmlDetails.='<p class="text-danger">Non pointé : '.number_format($tabNp['total_cost'],2).'€ /'.Helper::convertTime($tabNp['nb_hours']).'</p>';
                $tabAbsent=$DbHelperTools->getNbHoursAndPricesContractFormer($d->id,'absent',$filter_start2,$filter_end2);
                $htmlDetails.='<p class="text-warning">Absent : '.number_format($tabAbsent['total_cost'],2).'€ /'.Helper::convertTime($tabAbsent['nb_hours']).'</p>';

                $row[] = $pNumber.$pDateStart.$pDateEnd.$pPrice.$htmlDetails;
                // <th>CDD < 60j</th>
                $statesArray=$DbHelperTools->getStateContractFormer($d->id,$date_start_contract,$date_end_contract,$filter_start,$filter_end);
                $row[] = ($statesArray['is_cdd_less_60_days'])?$spanLabel:'';
                // <th>Bulletin C</th>
                $row[] = ($statesArray['is_bulletin_c'])?$spanLabel:'';
                // <th>DSN/FCDD</th>
                $row[] = ($statesArray['is_dsn'])?$spanLabel:'';
                // <th>Sommeil</th>
                $row[] = ($statesArray['is_sommeil'])?$spanLabel:'';
                // <th>Nb Jours</th>
                $row[] = $DbHelperTools->getNbDaysContractFormer($d->id,$filter_start2,$filter_end2);
                // <th>Nb Heures</th>
                $s1="'".$filter_start2."'";
                $s2="'".$filter_end2."'";
                $btn_planif_details = '<button type="button" class="btn btn-sm btn-clean btn-icon" id="BTN_SHOW_FORMER_SCHEDULE_DETAILS_'.$d->id.'" onclick="_showFormerScheduleDetails(' .$d->id. ','.$s1.','.$s2.')" title="Détails du planning"><i class="' . $tools->getIconeByAction('INFO') . '"></i></button>';
                $row[] = Helper::convertTime($DbHelperTools->getNbHoursContractFormer($d->id,$filter_start2,$filter_end2)).$btn_planif_details;
                // <th>Détails</th>
                $groupmentArray=$DbHelperTools->getRegroupmentContractFormer($d->id,'present',$filter_start2,$filter_end2);
                $pDetails='';
                if(count($groupmentArray)>0){
                    $pDetails.='Pointé';
                    foreach($groupmentArray as $k=>$v){
                        $pDetails.='<p class="text-success">R '.$k.'€ : '.Helper::convertTime($v).'</p>';
                    }
                }
                $row[] = $pDetails;
                // <th>Validé ?</th>
                //Si on valide la ligne on passe toutes les schedules contacts "Présent" a validé
                $btn_validate= '<button class="btn btn-sm btn-clean btn-icon" onclick="_validateContractScheduleContacts(' . $d->id . ')" title="Validation"><i class="' . $tools->getIconeByAction('CHECK') . '"></i></button>';
                $row[] = $btn_validate;                
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
    public function contratsIntervenants()
    {
        $page_title = 'Contrats intervenants';
        $page_description = '';
        return view('pages.contract_intervenants.index', compact('page_title', 'page_description'));
    }
    public function sendemailValidateContractScheduleContacts(Request $request)
    {
        $success=false;
        $msg='Ouups !';
        if ($request->isMethod('post')) {
                $DbHelperTools = new DbHelperTools();
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $filter_start=$start->format('Y/m/d');
                        $filter_end=$end->format('Y/m/d');
                        $filter_start2=$start->format('Y-m-d');
                        $filter_end2=$end->format('Y-m-d');

                        $subject='Résumé de validation de paie sur la période du '.$start->format('d/m/Y').' au '.$end->format('d/m/Y');

                        $ids = DB::table('af_schedulecontacts')
                        ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                        ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                        ->where([['af_schedulecontacts.is_former', 1],['af_schedulecontacts.is_sent_sage_paie',0]])
                        ->whereNotNull('af_schedulecontacts.validated_at')
                        ->whereNotNull('af_schedulecontacts.contract_id')
                        ->whereBetween('af_schedulecontacts.validated_at', [$start, $end])
                        ->pluck('af_schedulecontacts.contract_id')->unique();
                        //dd($ids);
                        $rsContracts = Contract::whereIn('id',$ids)->get();
                        if(isset($rsContracts)){
                            $mailContent='';
                            $mailContent.='<p>Période DU <strong>'.$start->format('d/m/Y').'</strong> AU <strong>'.$end->format('d/m/Y').'</strong></p>';
                            foreach ($rsContracts as $d) {
                                $rsArrayDates=$DbHelperTools->getStartDateEndDateContractFormer($d->id);
                                $date_start_contract=$rsArrayDates['start_contract'];
                                $date_end_contract=$rsArrayDates['end_contract'];
                                $statesArray=$DbHelperTools->getStateContractFormer($d->id,$date_start_contract,$date_end_contract,$filter_start,$filter_end);
                                $cdd = ($statesArray['is_cdd_less_60_days'])?'Oui':'Non';
                                $bulletin_c = ($statesArray['is_bulletin_c'])?'Oui':'Non';
                                $dsn = ($statesArray['is_dsn'])?'Oui':'Non';
                                $sommeil = ($statesArray['is_sommeil'])?'Oui':'Non';
                                $mailContent.='<p><strong>'.$d->contact->gender.' '.$d->contact->lastname.' '.$d->contact->firstname.'</strong> | CDD 60 : '.$cdd.' | Bulletin C : '.$bulletin_c.' | DSN/FCDD : '.$dsn.' | Sommeil : '.$sommeil.'</p>';
                                $nbDays= $DbHelperTools->getNbDaysContractFormer($d->id,$filter_start2,$filter_end2);
                                $nbHours = Helper::convertTime($DbHelperTools->getNbHoursContractFormer($d->id,$filter_start2,$filter_end2));
                                $totalCost=$DbHelperTools->getTotalCostContractFormer($d->id,$filter_start2,$filter_end2);
                                $tabResult=$DbHelperTools->getNbHoursAndPricesContractFormer($d->id,'present',$filter_start2,$filter_end2);
                                $tabNp=$DbHelperTools->getNbHoursAndPricesContractFormer($d->id,'not_pointed',$filter_start2,$filter_end2);
                                $tabAbsent=$DbHelperTools->getNbHoursAndPricesContractFormer($d->id,'absent',$filter_start2,$filter_end2);
                                $mailContent.='<p>- Nb jours : '.$nbDays.' - Nb heures : '.$nbHours.' - Coût : '.$totalCost.'€ - Pointé : '.number_format($tabResult['total_cost'],2).'€ /'.Helper::convertTime($tabResult['nb_hours']).'</p>';
                                $mailContent.='<p>- Pointé : '.number_format($tabResult['total_cost'],2).'€ /'.Helper::convertTime($tabResult['nb_hours']).' - Non pointé : '.number_format($tabNp['total_cost'],2).'€ /'.Helper::convertTime($tabNp['nb_hours']).' - Absent : '.number_format($tabAbsent['total_cost'],2).'€ /'.Helper::convertTime($tabAbsent['nb_hours']).'</p>';
                                //recap
                                $rs = Schedulecontact::where([['is_former', 1],['is_sent_sage_paie',0],['contract_id',$d->id]])
                                ->whereNotNull('validated_at')
                                ->whereBetween('validated_at', [$start, $end])
                                ->get();
                                if(isset($rs)){
                                    foreach($rs as $sc){
                                        $planning_date = Carbon::createFromFormat('Y-m-d', $sc->schedule->sessiondate->planning_date);
                                        $schedule_start_hour = Carbon::createFromFormat('Y-m-d H:i:s', $sc->schedule->start_hour);
                                        $schedule_end_hour = Carbon::createFromFormat('Y-m-d H:i:s', $sc->schedule->end_hour);
                                        $duration = Helper::convertTime($sc->schedule->duration);
                                        $textSchedule = $schedule_start_hour->format('H') . 'h' . $schedule_start_hour->format('i') . ' - ' . $schedule_end_hour->format('H') . 'h' . $schedule_end_hour->format('i').' ('.$duration.')';
                                        $mailContent.='<p>-- AF : '.$sc->schedule->sessiondate->session->af->id.'|Session : '.$sc->schedule->sessiondate->session->code.'|Date : '.$planning_date->format('d/m/Y').'|Séance : '.$textSchedule.'</p>';
                                    }
                                }
                                //dd($mailContent);
                            }
                            $myEmail = Mail::to(Auth::user()->email);
                            $myEmail->send(new ValidatedContract($mailContent,$subject));
                            //dd($mailContent);
                            $success=true;
                            $msg='L\'email à été envoyé avec succès';
                        }else{
                            $msg='Résumé vide ! pas de données à envoyer par email.';
                        }
                    }
                }
        }
        return response()->json(['success' => $success,'msg' => $msg]);
    }
    public function indexes()
    {
        $page_title = 'Gestion des indexes';
        $page_description = '';
        return view('pages.setting.help-indexes', compact('page_title', 'page_description'));
    }
    public function sdtIndexes(Request $request)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Helpindex::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('index', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('type', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('index_date', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                //filter_param_code
                if ($request->has('filter_index_code') && !empty($request->filter_index_code)) {
                    $datas->where('type', $request->filter_index_code);
                }
            }
        }
        $udatas = $datas->orderByDesc('id')->get();
        $typesCodes = Config::get('params.types_indexes');
        $codes=[];
        if(count($typesCodes)>0){
            foreach($typesCodes as $t){
                $codes[$t['code']]=$t['name'];
            }
        }
        //dd($codes);
        foreach ($udatas as $d) {
            $row = array();
            // <th>Type</th>
            $row[] = $codes[$d->type];
            // <th>Index</th>
            $row[] = $d->index;
            // <th>Date</th>
            //dd($d->id);
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->index_date);
            $row[] = $dt->format('d/m/Y');
            // <th>Actions</th>            
            // $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" type="button" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formHelpIndexe(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            //$btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteHelpIndexe(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $row[] = $btn_edit;

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
    public function formHelpindex($row_id)
    {
        $typesCodes = Config::get('params.types_indexes');

        $row = null;
        if ($row_id > 0) {
            $row = Helpindex::findOrFail($row_id);
        }
        return view('pages.setting.form.helpindex', compact('row','typesCodes'));
    }

    public function storeFormHelpindex(Request $request)
    {
        $success = false;
        $msg = 'Ouups';
        //dd($request->all());
        if ($request->isMethod('post')) {
            if($request->id>0){
                $index_date = Carbon::createFromFormat('d/m/Y', $request->index_date);
                $row = Helpindex::findOrFail($request->id);
                $row->index = $request->index;
                $row->index_date = $index_date;
                $row->save();
                $success = true;
                $msg = 'Index a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    public function sdtFormersWithoutContracts(Request $request)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $listFormersWithoutContrats=$DbHelperTools->getFormersWithoutContracts();
        foreach ($listFormersWithoutContrats as $lf) {
                $href = '/view/af/' . $lf->af_id;
                $row = array();
                    //$row[] =$lf->member_id;
                    //$row[] =$lf->contact_id;
                    $row[] ='<p class="font-size-sm">'.$lf->firstname.' '.$lf->lastname.'</p>';
                    $row[] = '<p class="text-primary font-size-xs"><a href="' . $href . '">' . $lf->code . '</a></p><p class="font-size-xs"><a href="' . $href . '">' . $lf->title . '</a></p>';

                    //$row[] =$lf->title.' ('.$lf->code.')';
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
}