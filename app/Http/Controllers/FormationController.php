<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Param;
use App\Models\Sheet;
use App\Models\Categorie;
use App\Models\Formation;
use App\Models\Sheetparam;
use Illuminate\Http\Request;
use App\Models\Timestructure;
use Illuminate\Support\Facades\DB;
use App\Library\Services\PublicTools;
use App\Models\Timestructurecategory;
use App\Library\Services\DbHelperTools;
use App\Models\Action;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Emailmodel;
use App\Models\Member;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use function Psy\debug;

class FormationController extends Controller
{
    const PF_TYPE_FORMATION = "PF_TYPE_FORMATION";
    const PF_STATUS_FORMATION = "PF_STATUS_FORMATION";
    const PF_STATE_FORMATION = "PF_STATE_FORMATION";

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = 'Produits de formation';
        $page_description = '';
        return view('pages.formation.list', compact('page_title', 'page_description'));
    }

    public function sdtFormations(Request $request)
    {
        $tools=new PublicTools();
        $dtRequests = $request->all();
        $data=$meta=[];
        $datas = Formation::whereNull('parent_id')->latest();

        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('code', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('title', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('description', 'like', '%'.$request->filter_text.'%');

                    $ids_categories=Categorie::select('id')->where('code', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('name', 'like', '%'.$request->filter_text.'%')
                    ->orWhere('description', 'like', '%'.$request->filter_text.'%')->pluck('id');
                    //dd($ids_categories);
                    if(count($ids_categories)>0){
                        $datas->orWhereIn('categorie_id', $ids_categories);
                    }

                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if(!empty($request->filter_start) && !empty($request->filter_end)){
                        $start = Carbon::createFromFormat('d/m/Y',$request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y',$request->filter_end);
                        $datas->whereBetween('created_at', [$start." 00:00:00", $end." 23:59:59"]);
                    }
                }

                //filter_status
                if ($request->has('filter_status') && !empty($request->filter_status)) {
                    $datas->whereHas('params', function ($query) use ($request){
                        $query->where('param_id', $request->filter_status);
                    });
                }
                //filter_type
                if ($request->has('filter_type') && !empty($request->filter_type)) {
                    $datas->whereHas('params', function ($query) use ($request){
                        $query->where('param_id', $request->filter_type);
                    });
                }
                //filter_state
                if ($request->has('filter_state') && !empty($request->filter_state)) {
                    $datas->whereHas('params', function ($query) use ($request){
                        $query->where('param_id', $request->filter_state);
                    });
                }
                //categorie_id
                if ($request->has('filter_categories_ids') && !empty($request->filter_categories_ids)) {
                    $myArrayIds = explode(',', $request->filter_categories_ids);
                    if(count($myArrayIds)>0){
                        $datas->whereIn('categorie_id', $myArrayIds);
                    }
                }
            }else{
                $datas=Formation::whereNull('parent_id')->orderByDesc('id');
            }
        }
        $recordsTotal=count($datas->get());
        //dd($recordsTotal);
        if($request->length>0){
            $start=(int) $request->start;
            $length=(int) $request->length;
            $datas->skip($start)->take($length);
        }
        $fdatas=$datas->orderByDesc('id')->get();
        foreach ($fdatas as $d) {
            $href='/view/formation/'.$d->id;
            $row=array();
            //<th>ID</th>
            $row[]=$d->id;
            //Formation
            $spanCode='<p><span class="text-primary font-size-xs"> Code: <a href="'.$href.'">'.$d->code.'</a></span></p>';
            $socle='<p class="text-muted">'.$d->categorie->name.'</p>';
            $title = '<p><a href="'.$href.'">'.$d->title.'</a></p>';
            $row[]=$spanCode.$socle.$title;
            //Type / Status / Etat
            $formationParams=$d->params->pluck('code','param_code');
            //dd($formationParams);
            $spanParams='';
            if(count($formationParams)>0){
                foreach($formationParams as $param_code=>$code){
                    $rowParam = Param::where([['param_code',$param_code],['code',$code]])->first();
                    $label='';
                    if($param_code==self::PF_TYPE_FORMATION){
                        $label='Type : ';
                    }elseif($param_code==self::PF_STATUS_FORMATION){
                        $label='Status : ';
                    }elseif($param_code==self::PF_STATE_FORMATION){
                        $label='Etat : ';
                    }
                    $spanParams.=$tools->constructParagraphLabelDot('sm',$rowParam->css_class,$label.$rowParam->name);
                }
            }
            $row[]=$spanParams;

            //Dates
            $created_at = $tools->constructParagraphLabelDot('xs','primary','C : '.$d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs','warning','M : '.$d->updated_at->format('d/m/Y H:i'));
            $row[]=$created_at.$updated_at;

            //Informations
            $row[]='';

            //Actions
            $btn_edit='<button class="btn btn-sm btn-clean btn-icon" onclick="_formFormation('.$d->id.',0,0)" title="Editer"><i class="'.$tools->getIconeByAction('EDIT').'"></i></button>';
            $btn_view='<button class="btn btn-sm btn-clean btn-icon" onclick="_viewFormation('.$d->id.')" title="Afficher les détails"><i class="'.$tools->getIconeByAction('VIEW').'"></i></button>';
            $btn_delete='<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteFormation('.$d->id.')" title="Supprimer"><i class="'.$tools->getIconeByAction('DELETE').'"></i></button>';
            $btn_archive='<button class="btn btn-sm btn-clean btn-icon" onclick="_archiveFormation('.$d->id.')" title="Archiver"><i class="'.$tools->getIconeByAction('ARCHIVE').'"></i></button>';
            $row[]=$btn_edit.$btn_view.$btn_delete.$btn_archive;

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
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=> $recordsTotal,
        ];
        return response()->json($result);
    }

    public function formFormation($row_id,$type=0)
    {
        //$type==1 =>Creation
        //$type==0 =>Edition

        $formationParams=[];
        $row = null;
        if ($row_id > 0) {
          $row = Formation::findOrFail ( $row_id );
          $formationParams=$row->params->pluck('id');
        }
        $DbHelperTools=new DbHelperTools();
        //PF_TYPE_FORMATION
        $type_params=$DbHelperTools->getParamsByParamCode('PF_TYPE_FORMATION');
        //PF_STATUS_FORMATION
        $status_params=$DbHelperTools->getParamsByParamCode('PF_STATUS_FORMATION');
        //PF_STATE_FORMATION
        $states_params=$DbHelperTools->getParamsByParamCode('PF_STATE_FORMATION');
        //BPF_MAIN_OBJECTIVE
        $bpf_main_params=$DbHelperTools->getParamsByParamCode('BPF_MAIN_OBJECTIVE');
        //BPF_SPECIALITY
        $bpf_speciality_params=$DbHelperTools->getParamsByParamCode('BPF_SPECIALITY');

        return view('pages.formation.form',['row'=>$row,'type'=>$type,'formationParams'=>$formationParams,'type_params'=>$type_params,'status_params'=>$status_params,'states_params'=>$states_params,'bpf_main_params'=>$bpf_main_params,'bpf_speciality_params'=>$bpf_speciality_params]);
    }

    public function storeFormFormation(Request $request)
    {
        //dd($request->all());exit();
        $success = false;
        $msg = '';

        $rules = [
            //'code' => 'required',
            'title' => 'required',
        ];
        $messages = [
            //'code' => 'Le code est unique',
            'title' => 'Le titre est obligatoire',
        ];
        $pf_id=0;
        $validator = Validator::make($request->all(),$rules,$messages);
        if ($validator->fails()) {
            $success = false;
            $msg = 'Veuillez vérifier tous les champs';
        }else {
            $DbHelperTools=new DbHelperTools();
            //dd($request->all());
            $data=array(
                'id'=>$request->id,
                'code'=>($request->id==0)?$DbHelperTools->generatePfCode($request->id,$request->categorie_id):null,
                'title'=>$request->title,
                'description'=>$request->description,
                'max_availability'=>$request->max_availability,
                'nb_days'=>$request->nb_days,
                'nb_hours'=>$request->nb_hours,
                'nb_pratical_days'=>$request->nb_pratical_days,
                'nb_pratical_hours'=>$request->nb_pratical_hours,
                'bpf_main_objective'=>$request->bpf_main_objective,
                'bpf_training_specialty'=>$request->bpf_training_specialty,
                'accounting_code'=>$request->accounting_code,
                'analytical_codes'=>$request->analytical_codes,
                'autorize_af'=>$request->autorize_af,
                'categorie_id'=>$request->categorie_id,
                'param_type_id'=>$request->param_type_id,
                'param_status_id'=>$request->param_status_id,
                'param_state_id'=>$request->param_state_id,
                'parent_id'=>$request->parent_id,
                'sort'=>$request->sort,
                'product_type'=>$request->product_type,
                'nb_session_duplication'=>$request->nb_session_duplication,
                'nb_sessiondates'=>$request->nb_sessiondates,
                'timestructure_id'=>$request->timestructure_id,
                'timestructure_sort'=>$request->timestructure_sort,
                //evaluation
                'is_evaluation'=>$request->is_evaluation,
                'ects'=>$request->ects,
                'coefficient'=>$request->coefficient,
            );
            //dd($data);
            $pf_id = $DbHelperTools->manageFormation($data);
            if($pf_id>0){
                $success = true;
                $msg = 'Le produit de formation a été enregistré avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'pf_id' => $pf_id,
        ]);
    }

    public function getParamsByParamCode($param_code)
    {
        $DbHelperTools=new DbHelperTools();
        $params=$DbHelperTools->getParamsByParamCode($param_code);
        //dd($params);
        return response()->json($params);
    }

    public function getJsonCategories($id_selected) {
		$categories=Categorie::all();
        $datas = [];
        if(count($categories)>0){
            foreach($categories as $c){

                $fa = 'folder';
				$classCss = 'success';
                $disabled_select=true;
				if ($c->categorie_id > 0) {
					$fa = 'file';
					$classCss = 'info';
                    $disabled_select=false;
				}

                $icon="fa fa-".$fa." text-".$classCss;

                $selected=($c->id===$id_selected)?true:false;

                $datas [] = array (
                    "id" => $c->id,
                    "text" => $c->name,
                    "state" => array('opened'=>false,'disabled'=>$disabled_select,'selected'=>$selected),
                    "icon" => $icon,
                    "parent" => ($c->categorie_id>0)?$c->categorie_id:'#'
                );
            }
        }
        return response()->json($datas);
	}

    public function viewFormation($row_id){
        $page_title = '';
        $page_description = '';
        $formationParams= [];
        $row = null;
        $html_categorie_path='';
        $type_pf='';
        if ($row_id > 0) {
            $row = Formation::findOrFail ( $row_id );
            $formationParams=$row->params->pluck('name','param_code');
            $DbHelperTools=new DbHelperTools();
            $html_categorie_path = $DbHelperTools->getFullPathCategories($row->categorie_id);
            $page_title = $html_categorie_path;
            $page_description = '';
            $type_pf=$DbHelperTools->getParamPFormation($row_id,'PF_TYPE_FORMATION');
            //PF_TYPE_DIP
        }
        return view('pages.formation.view',['formation'=>$row,'formationParams'=>$formationParams,'html_categorie_path'=>$html_categorie_path],compact('page_title', 'page_description','type_pf'));
    }

    public function defaultSheetFormation($formation_id){
        $defaultSheet=Sheet::select('id')->where([['formation_id',$formation_id],['is_default',1]])->first();
        $default_sheet_id = 0;
        if($defaultSheet){
            $default_sheet_id = $defaultSheet['id'];
        }
        return response()->json(['default_sheet_id'=>$default_sheet_id]);
    }

    public function listSheetsFormation($formation_id){
        return view('pages.sheet.list',['formation_id'=>$formation_id]);
    }

    public function viewSheet($formation_id,$row_id){
        $sheet = $sheetParams = null;
        if ($row_id > 0) {
            $sheet = Sheet::findOrFail ( $row_id );
            if($sheet){
                $sheetParams=Sheetparam::where([['sheet_id',$sheet->id]])->get();
            }
        }
        $DbHelperTools=new DbHelperTools();
        $state_params=$DbHelperTools->getParamsByParamCode('PF_STATE_SHEETS');
        return view('pages.sheet.view',['sheet'=>$sheet,'sheetParams'=>$sheetParams,'state_params'=>$state_params,'formation_id'=>$formation_id]);
    }

    public function sdtSheetsFormation(Request $request,$formation_id){
        $tools=new PublicTools();
        $dtRequests = $request->all();
        $data=$meta=[];
        $datas=Sheet::where('formation_id',$formation_id)->orderByDesc('version')->get();
        foreach ($datas as $d) {
            $row=array();
            $row['ID']=$d->id;
            $spanDefaultSheet='';
            if($d->is_default===1){
                $spanDefaultSheet=$tools->constructParagraphLabelDot('xs','success','Fiche par défault');
            }
            //$spanState='<p><span class="label label-outline-warning label-pill label-inline mr-2">'.$d->state->name.'</span></p>';
            $spanState=$tools->constructParagraphLabelDot('xs','warning',$d->state->name);
            //$spanCode='<p><span class="text-primary font-size-xs mr-2">'.$d->ft_code.'</span></p>';
            $spanCode=$tools->constructParagraphLabelDot('xs','primary',$d->ft_code);

            $row['Code']=$spanCode.$spanDefaultSheet.$spanState;
            $row['Version']=$tools->constructSpanLabel('xs','label-outline-primary label-pill mr-2',$d->version);

            $created_at = $tools->constructParagraphLabelDot('xs','primary','C : '.$d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs','warning','M : '.$d->updated_at->format('d/m/Y H:i'));
            $row['Infos']=$created_at.$updated_at;
            //Actions
            $btn_edit='<button class="btn btn-sm btn-clean btn-icon" onclick="_formSheet('.$d->formation_id.','.$d->id.')" title="Éditer"><i class="'.$tools->getIconeByAction('EDIT').'"></i></button>';
            /* $btn_view='<button class="btn btn-sm btn-clean btn-icon" onclick="_viewSheet('.$d->id.')" title="Détails du produit de formation"><i class="'.$tools->getIconeByAction('VIEW').'"></i></button>';
              */
            $btn_delete='<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteSheet('.$d->id.')" title="Supprimer"><i class="'.$tools->getIconeByAction('DELETE').'"></i></button>';

            $row['Actions']=$btn_edit.$btn_delete;
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
    }

    public function formSheet($formation_id,$row_id){
        $sheet = $sheetParams = $collectionSheetParams=null;
        if ($row_id > 0) {
            $sheet = Sheet::findOrFail ( $row_id );
            if($sheet){
                $sheetParams=Sheetparam::select('id','title','content','order_show','sheet_id','param_id')->where([['sheet_id',$sheet->id]])->get()->toArray();
                $collectionSheetParams = collect($sheetParams);
            }
        }
        $params = Param::select('id','name')->where([['param_code', 'PF_TYPE_SHEETS'],['is_active',1]])->get();
        $DbHelperTools=new DbHelperTools();
        $state_params=$DbHelperTools->getParamsByParamCode('PF_STATE_SHEETS');
        $generatedCode = $generatedVersion = '';
        $defaultSheet=$collectionDefaultSheetParams=null;
        if($row_id == 0){
            $generatedData = $DbHelperTools->generateVesrionAndCodeForSheet($formation_id);
            $generatedCode = $generatedData['code'];
            $generatedVersion = $generatedData['version'];
            //deault sheet
            $defaultSheet = Sheet::select('id','description')->where([['formation_id', $formation_id],['is_default',1]])->first();
            if($defaultSheet && $defaultSheet->id>0){
                $defaultSheetParams=Sheetparam::select('id','content','param_id')->where([['sheet_id',$defaultSheet->id]])->get()->toArray();
                $collectionDefaultSheetParams = collect($defaultSheetParams);
                //dd($collectionDefaultSheetParams);
            }
        }
        return view('pages.sheet.form',['sheet'=>$sheet,'defaultSheet'=>$defaultSheet,'collectionDefaultSheetParams'=>$collectionDefaultSheetParams,'formation_id'=>$formation_id,'params'=>$params,'collectionSheetParams'=>$collectionSheetParams,'state_params'=>$state_params,'generatedVersion'=>$generatedVersion,'generatedCode'=>$generatedCode]);
    }

    public function storeFormSheet(Request $request)
    {
        $data=$request->all();
        $success = false;
        $msg = '';
        $success = false;
        $msg = 'Veuillez vérifier tous les champs';
        $DbHelperTools=new DbHelperTools();
        if((isset($data['is_default']) && $data['is_default']>0) && $data['formation_id']>0){
            $DbHelperTools->resetIsDefaultSheetFormation($data['formation_id']);
        }
        $sheet_id = $DbHelperTools->manageSheets($data);
        if($sheet_id>0){
                $postedDatasParams = ($data['SHEET_PARAM'])?$data['SHEET_PARAM']:[];
                if(count($postedDatasParams)>0){
                    foreach ($postedDatasParams as $param_id=>$tab2) {
                        foreach ($tab2 as $sheet_param_id=>$content) {
                            //echo 'param_id='.$param_id.'*****id_sheet_param = '.$sheet_param_id.'====>'.$content;
                            $param=Param::where('id', $param_id)->first();
                            $dataSheetparam=array(
                                'id'=>$sheet_param_id,
                                'title'=>$param->name,
                                'content'=>$content,
                                'param_id'=>$param_id,
                                'sheet_id'=>$sheet_id,
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

    public function generateCodeForFormation($formation_id,$categorie_id){
        $DbHelperTools=new DbHelperTools();
        $code = '';
        if($categorie_id)
            $code = $DbHelperTools->generatePfCode($formation_id,$categorie_id);
        return response()->json(['code'=>$code]);
    }

    public function deleteFormation($formation_id){
        /**
         * forceDelete
         */
        $success = false;
        $DbHelperTools=new DbHelperTools();
        if($formation_id){
                $ids_sheets = Sheet::where('formation_id', $formation_id)->get()->pluck('id');
                if(count($ids_sheets)>0){
                    $ids_sheetparams = Sheetparam::whereIn('sheet_id', $ids_sheets)->get()->pluck('id');
                    if(count($ids_sheetparams)){
                        $deletedRowsSheetsParams = $DbHelperTools->massDeletes($ids_sheetparams,'sheetparam',1);
                    }
                    $deletedRowsSheets = $DbHelperTools->massDeletes($ids_sheets,'sheet',1);
                }
            $DbHelperTools->deleteFormationParams($formation_id);
            $deletedRows = $DbHelperTools->massDeletes([$formation_id],'formation',1);
            $success = true;
        }
        return response()->json(['success'=>$success]);
    }

    public function archiveFormation($formation_id){
        /**
         * softDelete
         */
        $success = false;
        $DbHelperTools=new DbHelperTools();
        /*
         if($formation_id){
            $deletedRows = $DbHelperTools->massDeletes([$formation_id],'formation',0);
            if($deletedRows>0){
                $ids_sheets = Sheet::where('formation_id', $formation_id)->get()->pluck('id');
                if(count($ids_sheets)>0){
                    $ids_sheetparams = Sheetparam::whereIn('sheet_id', $ids_sheets)->get()->pluck('id');
                    if(count($ids_sheetparams)){
                        $deletedRowsSheetsParams = $DbHelperTools->massDeletes($ids_sheetparams,'sheetparam',0);
                    }
                    $deletedRowsSheets = $DbHelperTools->massDeletes($ids_sheets,'sheet',0);
                }
                $success = true;
            }
        } */
        if($formation_id){
            $success =$DbHelperTools->archiveFormation($formation_id);
        }
        return response()->json(['success'=>$success]);
    }

    public function deleteSheet($sheet_id){
        /**
         * forceDelete
         */
        $success = false;
        $DbHelperTools=new DbHelperTools();
        if($sheet_id){
                $ids_sheets = Sheet::where('id', $sheet_id)->get()->pluck('id');
                if(count($ids_sheets)>0){
                    $ids_sheetparams = Sheetparam::whereIn('sheet_id', $ids_sheets)->get()->pluck('id');
                    if(count($ids_sheetparams)){
                        $deletedRowsSheetsParams = $DbHelperTools->massDeletes($ids_sheetparams,'sheetparam',1);
                    }
                    $deletedRowsSheets = $DbHelperTools->massDeletes($ids_sheets,'sheet',1);
                }
            $success = true;
        }
        return response()->json(['success'=>$success]);
    }

    public function constructViewContentPf($viewtype,$row_id)
    {
        $row = $sessions = null;
        $default_sheet_id = 0;
        if ($row_id > 0) {
            $row = Formation::findOrFail ( $row_id );
            $defaultSheet=Sheet::select('id')->where([['formation_id',$row_id],['is_default',1]])->first();
            if($defaultSheet){
                $default_sheet_id = $defaultSheet['id'];
            }
        }
        return view('pages.formation.construct.view',['row'=>$row,'viewtype'=>$viewtype,'default_sheet_id'=>$default_sheet_id]);
    }

    public function sdtPricesPf(Request $request,$formation_id)
    {
        $tools=new PublicTools();
        $DbHelperTools=new DbHelperTools();
        $dtRequests = $request->all();
        $data=$meta=[];
        $row = Formation::findOrFail ( $formation_id );
        $datas = $row->prices;
        //dd($datas);
        foreach ($datas as $d) {
            $row=array();
            //ID
            $row[]='<label class="checkbox checkbox-single"><input type="checkbox" value="'.$d->id.'" class="checkable"/><span></span></label>';
            //<th>Titre</th>
            $row[] = ($d->title) ? $d->title : '-';
            //<th>Type d'entité</th>
            $typeEntite = ($d->entity_type=="S")?'<p>Société</p>':(($d->entity_type=="P")?'<p>Particulier</p>':'');
            $cssClass='info';
            $row[]=$typeEntite;
            //<th>Type de tarif</th>
            $row[]='<p>'.$DbHelperTools->getNameParamByCode($d->device_type).'</p>';
            //<th>Tarif</th>
            $row[]='<p class="text-'.$cssClass.'">'.$d->price.' € / '.$DbHelperTools->getNameParamByCode($d->price_type).'</p>';
            //Actions
            $btn_delete='<button class="btn btn-sm btn-clean btn-icon" onclick="_deletePfRelPrice('.$d->id.','.$formation_id.')" title="Supprimer"><i class="'.$tools->getIconeByAction('DELETE').'"></i></button>';
            $row[]=$btn_delete;
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
    }

    public function deletePfRelPrice(Request $request){
        $success = false;
        if ($request->isMethod('delete')) {
            if($request->has('pf_id')){
                $pf_id = $request->pf_id;
                $ids_prices= $request->ids_prices;
                if(count($ids_prices)>0){
                    DB::table('pf_rel_price')->whereIn('price_id', $ids_prices)->where('formation_id',$pf_id)->delete();
                }
                $success = true;
            }
        }
        return response()->json(['success'=>$success]);
    }

    public function formRelPfPrice($pf_id){
        return view('pages.formation.price.form',['pf_id'=>$pf_id]);
    }

    public function storeFormRelPfPrice(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
            if ($request->isMethod('post')) {
                $DbHelperTools=new DbHelperTools();
                if ($request->has('prices_ids') && !empty($request->prices_ids)) {
                    $data = array(
                        'formation_id'=>$request->formation_id,
                        'prices_ids'=>$request->prices_ids,
                    );
                    $success = $DbHelperTools->attachFormationPrices($data);
                    $msg = 'Le tarif a été enregistré avec succès';
                }else{
                    $msg = 'Veuillez sélectionner au moin un tarif !!';
                }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function getInfosPFormation($pf_id){
        $success = false;
        $max_availability = $nb_days = $nb_hours = 0;
        $bpf_main_objective = $bpf_training_specialty = '';
        if($pf_id>0){
            $pf=Formation::select('max_availability','nb_days','nb_hours','bpf_main_objective','bpf_training_specialty','nb_pratical_days','nb_pratical_hours')->where('id',$pf_id)->first();
            //dd($pf);
            $max_availability = (isset($pf['max_availability']))?$pf['max_availability']:0;
            $nb_days = (isset($pf['nb_days']))?$pf['nb_days']:0;
            $nb_hours = (isset($pf['nb_hours']))?$pf['nb_hours']:0;
            $bpf_main_objective = (isset($pf['bpf_main_objective']))?$pf['bpf_main_objective']:'';
            $bpf_training_specialty = (isset($pf['bpf_training_specialty']))?$pf['bpf_training_specialty']:'';
            $nb_pratical_hours = (isset($pf['nb_pratical_hours']))?$pf['nb_pratical_hours']:0;
            $nb_pratical_days = (isset($pf['nb_pratical_days']))?$pf['nb_pratical_days']:0;
            $success = true;
        }
        return response()->json([
            'success'=>$success,
            'max_availability'=>$max_availability,
            'nb_days'=>$nb_days,
            'nb_hours'=>$nb_hours,
            'nb_pratical_hours'=>$nb_pratical_hours,
            'nb_pratical_days'=>$nb_pratical_days,
            'bpf_main_objective'=>$bpf_main_objective,
            'bpf_training_specialty'=>$bpf_training_specialty,
        ]);
    }

    public function sdtPfLogs(Request $request,$pf_id)
    {
        $dtRequests = $request->all();
        $data=$meta=[];
        //Action de formation
        $results[]=Activity::where([['log_name','formations_log'],['subject_type','App\Models\Formation'],['subject_id',$pf_id]])->orderByDesc('id')->get();
        $subjects =array(
            'App\Models\Formation'=>'Produit de formation',
        );
        $actions = array(
            'created' => 'Création',
            'updated' => 'Mise à jour',
        );
        foreach($results as $datas){
            foreach ($datas as $d) {
                $row=array();
                $row[]=$d->id;
                $row[]=$d->created_at->format('d/m/Y H:i');
                $row[]=$d->causer->name.' '.$d->causer->lastname;
                $actionDescription = '';
                if(in_array($d->subject_type,array('App\Models\Formation'))){
                   $actionDescription = $actions[$d->description].' ('.$subjects[$d->subject_type].' : '.$d->subject->code.')';
                }
                $row[]=$actionDescription;
                $data[]=$row;
            }
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
    }
    //getHierarchicalStructure
    public function getHierarchicalStructure($pf_id){
        return view('pages.formation.structure.view',compact('pf_id'));
    }

    public function getJsonTreeProductHierarchicalStructure($pf_id) {
        $datas = [];
        if($pf_id>0){
            $tools=new PublicTools();
            $DbHelperTools=new DbHelperTools();
            $ids=$DbHelperTools->filter_ids_product($pf_id);
            $pformations=(count($ids)>0)?Formation::whereIn('id', $ids)->orderBy('sort','ASC')->get():[];
            if(count($pformations)>0){
                foreach($pformations as $pf){
                    $fa = 'folder';
                    $classCss = 'success';
                    if ($pf->parent_id > 0) {
                        $fa = 'file';
                        $classCss = 'info';
                    }
                    $icon="fa fa-".$fa." text-".$classCss;
                    $btnEdit = ' <a style="cursor: pointer;" class="mr-2" onclick="_formFormation('.$pf->id.',1)" title="Editer"><i class="'.$tools->getIconeByAction('EDIT').' text-primary"></i></a>';

                    $type_produit=(isset($pf->product_type) && !empty($pf->product_type))?$DbHelperTools->getNameParamByCode($pf->product_type):'--';
                    $infos=' <span class="text-info">(D/S :'.$pf->nb_sessiondates.' ; Nb S : '.$pf->nb_session_duplication.' Type : '.$type_produit.' ; Nb heures : '.$pf->nb_hours.')</span>';
                    $datas [] = array (
                        "id" => $pf->id,
                        "text" => $pf->title.$infos.$btnEdit,
                        "state" => array('opened'=>false),
                        "icon" => $icon,
                        "parent" => ($pf->parent_id >0)?$pf->parent_id :'#'
                    );
                }
            }
        }
        return response()->json($datas);
	}

    public function getParentProductView($pf_id){
        $selected_parent_id=0;
        $row=null;
        if($pf_id>0){
            $row = Formation::select('id','sort','product_type','parent_id','nb_session_duplication','nb_sessiondates','timestructure_id','timestructure_sort','is_evaluation','ects','coefficient')->where('id',$pf_id)->first();
            if($row){
                $selected_parent_id=$row->parent_id;
            }
        }
        $evaluation_modes = Formation::EVALUATION_MODES;
        return view('pages.formation.structure.parent-select',compact('pf_id','selected_parent_id','row','evaluation_modes'));
    }

    public function selectProductsOptions($pf_id)
    {
        $result = [];
        $rows = Formation::select('id', 'code', 'title')->where('id','!=',$pf_id)->get();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $result[] = ['id' => $row['id'], 'name' => $row['title'].' ('.$row['code'].')'];
            }
        }
        return response()->json($result);
    }

    public function selectTypesProductsOptions()
    {
        $result = [];
        $DbHelperTools=new DbHelperTools();
        $rows=$DbHelperTools->getParamsByParamCode('PF_TYPE_PRODUCT');
        //dd($rows);
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $result[] = ['id' => $row['code'], 'name' => $row['name']];
            }
        }
        return response()->json($result);
    }

    public function getJsonTimeStructure($id_selected,$param) {

        $tools=new PublicTools();
        $categories=Timestructurecategory::all();
        $datas = [];
        $i=0;

        foreach($categories as $c){

            $structures = Timestructure::where('category_id',$c->id)->orderBy('sort')->get()->all();
            foreach($structures as $a){
                $datas [] = array(
                    "id" => 'C'.$a->id,
                    "text" => $a->name,
                    "state" => array('opened' => true),
                    "icon" => 'fa fa-folder',
                    "parent" => '#'
                );
                break;
            }


            if(count($structures)>0){
                foreach($structures as $t){

                    $fa = 'folder';
                    $classCss = 'success';
                    $disabled_select=true;
                    if ($t->parent_id > 0) {
                        $fa = 'file';
                        $classCss = 'info';
                        $disabled_select=false;
                    }

                    $icon="fa fa-".$fa." text-".$classCss;

                    if ($param == 1 && $t->deleted_at) {
                        $disabled_select = true;
                    }

                    $selected=($t->id===$id_selected)?true:false;

                    $datas [] = array (
                        "id" => 'S'.$t->id,
                        "text" => $t->name,
                        "state" => array('opened'=>false,'disabled'=>$disabled_select,'selected'=>$selected),
                        "icon" => $icon,
                        "parent" => ($t->parent_id>0)?'S'.$t->parent_id:'C'.$t->id
                    );

                    //Pfs
                    $pfs=Formation::where('timestructure_id',$t->id)->orderBy('timestructure_sort')->get();
                    $pfs_array = $pfs->toArray();
                    if(count($pfs)>0 && $param==0){
                        foreach($pfs as $pf){
                            $item_parent = array_filter($pfs_array, function ($p) use ($pf) {
                                return $p['id'] == $pf->parent_id;
                            });
                            $parent_exits = !empty($item_parent);

                            $btnEdit = ' <a style="cursor: pointer;" class="mr-2" onclick="_formFormation('.$pf->id.',1)" title="Editer"><i class="'.$tools->getIconeByAction('EDIT').' text-primary"></i></a>';
                            $datas [] = array (
                                "id" => $pf->id,
                                //"text" => $pf->title.(!$parent_exits?$btnEdit:''),
                                "text" => $pf->title.$btnEdit,
                                "state" => array('opened'=>false),
                                "icon" => 'fa fa-file text-info',
                                "parent" => $parent_exits?$pf->parent_id :'S'.$t->id
                            );
                            // break;
                        }
                    }

                }
            }

        }

        return response()->json($datas);
	}

    public function getDepotDevis($af_id){
        $af = Action::find($af_id);
        $contact_id = Auth()->user()->contact_id;
        $tasks = Task::where([
            ['af_id',$af_id],
            ['sub_task',1],
            ['contact_id',$contact_id],
        ])->get();
        $count = count($tasks);
        if($count==1){
            return view('pages.formation.devis.form', ['af' => $af]);
        }
        else if($count==2){
            return view('pages.formation.devis.form_2', ['af' => $af]);
        }
        else if($count==3){
            return view('pages.formation.devis.form_3', ['af' => $af]);
        }
    }

    public function getDevis($devis_id,$member_id,$type){

        $devis = Document::find($devis_id);

        if($type == 1){
            return view('pages.formation.devis.one', ['devis' => $devis,'member_id'=>$member_id]);
        }elseif($type == 2){
            return view('pages.formation.devis.two', ['devis' => $devis,'member_id'=>$member_id]);
        }elseif($type == 3){
            return view('pages.formation.devis.three', ['devis' => $devis,'member_id'=>$member_id]);
        }
    }

    public function getRefusMotif($devis_id,$member_id,$step){
        return view('pages.formation.devis.motif',['devis_id'=>$devis_id,'member_id'=>$member_id,'step'=>$step]);
    }

    public function sendRefusDevis(Request $request){
        $success = true;
        $msg = 'Le mail a été envoyée avec succès';

        $af_id = Document::find($request->devis_id)->action->id;
        $contact = Member::find($request->member_id)->contact;

        if($request->step == 1){
            $email = Emailmodel::where('code','MAIL_REFUS_DEVIS')->first();
        }elseif($request->step == 2){
            $email = Emailmodel::where('code','MAIL_REFUS_CONTRAT_PRESTATION')->first();
        }elseif($request->step == 3){
            $email = Emailmodel::where('code','MAIL_REFUS_FACTURE')->first();
            $etat_id = 106;

            $subTask = Task::where([
                ['af_id',$af_id],
                ['sub_task',1],
                ['contact_id',$contact->id],
            ])->latest()->first();

            $task = Task::where([
                ['af_id',$af_id],
                ['sub_task',0],
                ['contact_id',$contact->id],
            ])->latest()->first();

            if($task){
                $task->update(['etat_id' => $etat_id ]);
            }
            if($subTask){
                $subTask->update(['etat_id' => $etat_id ]);
            }
            $success = 'download';
        }

        $titre_af = Document::find($request->devis_id)->action->title;
        $motif = $request->motif ;

        $in = array(
            '{titre_af}',
            '{motif_refus}'
        );
        $out = array(
            $titre_af,
            $motif,
        );

        $default_content = str_replace($in,$out, $email->default_content);
        $default_header = $email->default_header;
        $default_footer = $email->default_footer;
        $fullname = $contact->firstname . " " . $contact->lastname;
        $email = $contact->email;

        Mail::send('pages.email.model',
        ['htmlMain' => $default_content, 'htmlHeader' =>$default_header,'htmlFooter' => $default_footer],
        function ($m) use ($motif, $fullname, $email) {
            $m->from(auth()->user()->email);
            $m->bcc(auth()->user()->email);
            $m->to($email, $fullname)->subject($motif);
        });


       return response()->json([
           'success' => $success,
           'msg' => $msg
       ]);
    }

    public function createDepotDevis(Request $request){
        $time=Carbon::now()->format('Y-m-d');
        $contact_id = Auth()->user()->contact_id;

        $file = $request->file('attachments')[0];

        $filename = $file->getClientOriginalName();
        $newFilename = $time . '_' . $filename;
        Storage::disk('public_uploads')->putFileAs('document', $file, $newFilename );

        $task = Task::where([
            ['af_id',$request['af_id']],
            ['contact_id',$contact_id],
            ['type_id',221],
            ['sub_task',1],
        ])->first();

        $resume = "Devis formatuer sur facture envoyé";
        $type= "devis";
        if($request->step == 2){
            $resume = "Contrat prestation service formateur sur facture envoyé";
            $type= "contrat";
        }elseif($request->step == 3){
            $resume = "Facture formateur sur facture envoyé";
            $type= "facture";
        }
        $document = new Document();
        $document->af_id = $request->af_id;
        $document->task_id = $task->id;
        $document->contact_id =$contact_id;
        $document->montant_ht = $request->montant_ht;
        $document->montant_ttc = $request->montant_ttc;
        $document->tva = $request->tva;
        $document->path = $newFilename;
        $document->type = $type;
        $document->save();

        $etat_id = 106;

        if($task){
            $task->update([
                'title' => $resume,
                'etat_id' => $etat_id,
            ]);
        }

        $success = true;
        $message = 'Devis envoyé avec succès.';
        return response()->json([
            'msg' => $message,
            'success' => $success,
        ]);
    }

    public function showDownloadDocument($id,$af_id,$member_id){
        $member= Member::find($member_id);

        $contact_id= $member->contact->id;

        $devis = Document::where([
            ['af_id', $af_id],
            ['contact_id', $contact_id],
            ['state', 'devis']
        ])->latest()->first();

        $contrat = Document::where([
            ['af_id', $af_id],
            ['contact_id', $contact_id],
            ['state', 'contrat']
        ])->latest()->first();

        $facture = Document::where([
            ['af_id', $af_id],
            ['contact_id', $contact_id],
            ['state', 'facture']
        ])->latest()->first();

        return view('pages.formation.devis.document',['id'=>$id,'devis'=>$devis,'contrat'=>$contrat,'facture'=>$facture]);
    }

    public function DownloadDocuments($path){

        $file = public_path('uploads/document/' . $path);

        return Response::download($file);
    }
}
