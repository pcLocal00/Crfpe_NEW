<?php

namespace App\Http\Controllers;

use finfo;
use PDF;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use Carbon\Carbon;
use App\Models\Action;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use App\Models\Session;
use App\Models\Member;
use App\Models\Attachment;
use App\Models\Media;
use Illuminate\Http\Request;
use App\Library\Helpers\Helper;
use App\Models\Internshiproposal;
use Illuminate\Support\Facades\DB;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use Illuminate\Support\Facades\Storage;
use App\Models\Documentmodel;
use App\Models\Invoice;
use App\Models\Param;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function stages()
    {
        $page_title = 'Gestion des stages';
        $page_description = '';
        return view('pages.af.stage.list', compact('page_title', 'page_description'));
    }

    public function sdtStages(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $query = DB::table('af_sessions')->where('is_internship_period', 1)->whereNull('deleted_at');
        if ($af_id > 0) {
            $query->where('af_id', $af_id);
        }
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $query->where('code', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('title', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('description', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('nb_days', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('nb_hours', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        //$datas->whereBetween('started_at', [$start->format('Y-m-d') . " 00:00:00", $end->format('Y-m-d') . " 23:59:59"]);
                        $d1 = $start->format('Y-m-d') . " 00:00:00";
                        $d2 = $end->format('Y-m-d') . " 00:00:00";
                        $query->whereBetween('started_at', [$d1, $d2])->whereBetween('ended_at', [$d1, $d2]);
                    }
                }
            }
        }
        $sessions = $query->orderByDesc('id')->get();
        foreach ($sessions as $d) {
            $row = array();
            //ID
            $row[] = $d->id;
            //Formation
            if ($af_id == 0) {
                $af = Action::select('id', 'code', 'title')->where('id', $d->af_id)->first();
                $spanName = '<div class="text-body mb-2"><a href="/view/af/' . $af->id . '">' . $af->code . '</a></div>';
                $spanName .= '<div class="text-body mb-2"><a href="/view/af/' . $af->id . '">' . $af->title . '</a></div>';
                $row[] = $spanName;
            }
            //Nom de période
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
            $spanName .= '<div class="text-body mb-2">
            <span class="label label-outline-' . $stateCssClass . ' label-pill label-inline mr-2 mb-2">' . $state . '</span>
            <span class="label label-outline-' . $cssClassActive . ' label-pill label-inline mr-2 mb-2">' . $labelActive . '</span></div>';
            $date_s = strtotime($d->started_at);
            $date_f = strtotime($d->ended_at);
            $duration = $tools->constructParagraphLabelDot('xs', 'primary', 'Durée de la période : ' . $d->nb_hours . ' heure(s)');
            $started_at = $tools->constructParagraphLabelDot('xs', 'success', 'Date début de la période : ' . date('d/m/Y', $date_s));
            $ended_at = $tools->constructParagraphLabelDot('xs', 'danger', 'Date fin de la période : ' . date('d/m/Y', $date_f));
            $row[] = $spanName . $duration . $started_at . $ended_at;
            //<th>Dates</th>
            $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $d->created_at);
            $updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $d->updated_at);
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'Crée le : ' . $created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'Modifiée le : ' . $updated_at->format('d/m/Y H:i'));
            $row[] = $created_at . $updated_at;
            //<th>Stagiaires</th>
            $rsArray = $DbHelperTools->getStatsProposalsBySession($d->id);
            $pStats = '<p class="text-info font-size-sm">Brouillant : ' . $rsArray['draft'] . '</p>';
            $pStats .= '<p class="text-info font-size-sm">A approuver : ' . $rsArray['approuved'] . '</p>';
            $pStats .= '<p class="text-info font-size-sm">Stage non validée : ' . $rsArray['invalid'] . '</p>';
            $pStats .= '<p class="text-info font-size-sm">Stage validée : ' . $rsArray['validated'] . '</p>';
            $pStats .= '<p class="text-info font-size-sm">Stage imposée : ' . $rsArray['imposed'] . '</p>';
            $row[] = $pStats;
            //<th>Actions</th>
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formStage(' . $d->id . ',' . $d->af_id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_proposals = '<a class="btn btn-sm btn-clean btn-icon" href="/view/af/' . $d->af_id . '?v=p" title="Propositions de stage"><i class="' . $tools->getIconeByAction('EYE') . '"></i></button>';
            $row[] = $btn_edit . $btn_proposals;
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

    public function formStage($session_id, $af_id)
    {
        $row = $af = null;
        if ($session_id > 0) {
            $row = Session::findOrFail($session_id);
        }
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
        }
        $DbHelperTools = new DbHelperTools();
        $params_semesters = $DbHelperTools->getParamsByParamCode('SEMESTERS_ATTACHED_TO_INTERNSHIPPERIOD');
        return view('pages.af.stage.form', compact('row', 'af_id', 'af', 'params_semesters'));
    }
    public function storeFormStage(Request $request)
    {
        $success = false;
        $msg = 'Oups !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $started_at = Carbon::createFromFormat('d/m/Y', $request->started_at);
            $ended_at = Carbon::createFromFormat('d/m/Y', $request->ended_at);
            $dataSession = array(
                'id' => $request->id,
                'code' => $DbHelperTools->generateSessionCode($request->af_id, $request->id),
                'title' => $request->title,
                'description' => $request->description,
                'nb_days' => $request->nb_days,
                'nb_hours' => $request->nb_hours,
                'state' => $request->state,
                'is_active' => $request->is_active,
                'started_at' => $started_at,
                'ended_at' => $ended_at,
                'session_type' => $request->session_type,
                'af_id' => $request->af_id,
                'is_internship_period' => 1,
                'attachment_semester' => $request->attachment_semester,
            );
            //dd($dataSession);
            $session_id = $DbHelperTools->manageSession($dataSession);
            if ($session_id > 0) {
                $success = true;
                $msg = 'La période a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    public function sdtStagesProposals(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];

        //['draft','approuved','invalid','validated','imposed']
        $stateArray = array(
            'draft' => 'Brouillon',
            'approuved' => 'A approuver',
            'invalid' => 'Stage non validée',
            'validated' => 'Stage validée',
            'imposed' => 'Stage imposée'
        );
        $cssArray = array(
            'draft' => 'warning',
            'approuved' => 'info',
            'invalid' => 'danger',
            'validated' => 'success',
            'imposed' => 'primary'
        );

        $datas = Internshiproposal::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_session_id') && !empty($request->filter_session_id)) {
                    $datas->where('session_id', $request->filter_session_id);
                }
                if ($request->has('filter_member_id') && !empty($request->filter_member_id)) {
                    $datas->where('member_id', $request->filter_member_id);
                }
                if ($request->has('filter_state') && !empty($request->filter_state)) {
                    $datas->where('state', $request->filter_state);
                }
            }
        }
        if ($af_id > 0) {
            $datas->where('af_id', $af_id)->latest();
        }
        $udatas = $datas->orderByDesc('id')->get();
        foreach ($udatas as $d) {
            $row = array();
            //ID
            $row[] = $d->id;
            //N°
            $row[] = '<p class="text-' . $cssArray[$d->state] . '">P #' . $d->id . '</p>';
            // <th>Stagiaire</th>
            $row[] = $d->member->contact->firstname . ' ' . $d->member->contact->lastname;
            // <th>L'organisme d'accueil</th>
            $row[] = $d->entity->name . ' (' . $d->entity->ref . ')';
            // <th>Période de stage</th>
            $row[] = $d->session->title;
            // <th>Infos stage</th>
            $started_at = Carbon::createFromFormat('Y-m-d', $d->started_at);
            $ended_at = Carbon::createFromFormat('Y-m-d', $d->ended_at);
            $started_at = $tools->constructParagraphLabelDot('xs', 'info', 'Début : ' . $started_at->format('d/m/Y'));
            $ended_at = $tools->constructParagraphLabelDot('xs', 'info', 'Fin : ' . $ended_at->format('d/m/Y'));
            $row[] = $started_at . $ended_at;
            // <th>Etat</th>
            $pStatus = '<p class="text-' . $cssArray[$d->state] . ' font-size-sm">' . $stateArray[$d->state] . '</p>';
            $row[] =  $pStatus;
            // <th>Dates</th>
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at . $updated_at;
            // <th>Actions</th>
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formStageProposal(' . $d->id . ',' . $d->af->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_pdf = '<a target="_blank" href="/pdf/convention/stage/' . $d->id . '/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>';
            $btn_pdf_download = '<a target="_blank" href="/pdf/convention/stage/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>';
            $btn_pdf_details = '<a class="navi-link" onclick="_formStageProposalDocuments(' . $d->id . ',' . $d->af->id . ')" > <span class="navi-icon"><i class="fas fa-info"></i></span> <span class="navi-text">Detail</span> </a>';

            $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                    data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                ' . $btn_pdf . '
                                ' . $btn_pdf_download . '
                                ' . $btn_pdf_details . '
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
            'data' => $data
        ];
        return response()->json($result);
    }
    public function formStageProposal($internshiproposal_id, $af_id)
    {
        $row = $af = null;
        if ($internshiproposal_id > 0) {
            $row = Internshiproposal::findOrFail($internshiproposal_id);
        }
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
        }
        return view('pages.af.stage.proposal', compact('row', 'af_id', 'af'));
    }

    public function formStageProposalAttachements($internshiproposal_id, $af_id)
    {
        $row = $af = null;
        if ($internshiproposal_id > 0) {
            $row = Internshiproposal::findOrFail($internshiproposal_id);
        }
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
        }
        $medias = Media::where('table_name', 'internshiproposals')->where('table_id', $internshiproposal_id)->get();
        // $medias = json_encode($media);
        // dd($row->state);
        // dd($media[0]->attachment->path);
        return view('pages.af.stage.proposal_attachments', compact('row', 'af_id', 'af', 'medias'));
    }

    public function formStageProposalAttachementsUpload(Request $request, $internshiproposal_id)
    {
        $attachements = $request->file("attachments_stage");
        // foreach ($attachements as $attachement){
        //     echo $attachement->getClientOriginalName().'||';
        // }
        $files = [];
        // dd($attachements);
        if ($attachements) {
            foreach ($attachements as $attachement) {
                $filename = time() . $attachement->getClientOriginalName();
                $fileextention = $attachement->getClientOriginalExtension();
                Storage::disk('public_uploads')->putFileAs('stage/proposal/attachements', $attachement, $filename);
                // Media::where('table_name','schedulecontact')->where('table_id')
                $Attachment_id = Attachment::insertGetId(['name' => 'Proposition de stage', 'path' => $filename]);
                Media::create(['attachment_id' => $Attachment_id, 'table_id' => $internshiproposal_id, 'table_name' => 'internshiproposals']);
                // $attachment_table_row[] = $row;
                // $files['path'] = $filename;
                // $files['id'] = 1;
                $files[] = ['path' => $filename, 'id' => $Attachment_id, 'name' => 'Proposition de stage'];
            }
        }
        return response()->json([
            'success' => true,
            'files' => $files,
        ]);
        // return true;
    }

    // public function formStageProposalAttachementsGet($internshiproposal_id){
    //     dd("ok");
    //     // $media = Media::whereIn('table_name' , 'internshiproposals')->where('table_id' , $internshiproposal_id)->get();
    //     // dd($media);
    //     // return response()->json([
    //     //     'success' => $success,
    //     //     'media' => $media,
    //     // ]);
    // }

    public function formStageProposalAttachementsDelete($media_id)
    {
        Attachment::where('id', $media_id)->delete();
        Media::where('attachment_id', $media_id)->delete();
        return true;
    }

    public function formStageProposalDocuments($internshiproposal_id, $af_id)
    {
        $row = $af = null;
        if ($internshiproposal_id > 0) {
            $row = Internshiproposal::findOrFail($internshiproposal_id);
        }
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
        }
        return view('pages.af.stage.proposal', compact('row', 'af_id', 'af'));
    }
    public function storeFormStageProposal(Request $request)
    {
        $success = false;
        $msg = 'Oups !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            $started_at = Carbon::createFromFormat('d/m/Y', $request->started_at);
            $ended_at = Carbon::createFromFormat('d/m/Y', $request->ended_at);
            if ($started_at > $ended_at) {
                $msg = 'Attention la date début de la période est supérieur a la date fin de la période !';
            } else {
                $data = array(
                    'id' => $request->id,
                    'state' => $request->state,
                    'started_at' => $started_at,
                    'ended_at' => $ended_at,
                    'representing_contact_id' => $request->representing_contact_id,
                    'internship_referent_contact_id' => $request->internship_referent_contact_id,
                    'trainer_referent_contact_id' => $request->trainer_referent_contact_id,
                    'service' => $request->service,
                    'entity_id' => $request->entity_id,
                    'member_id' => $request->member_id,
                    'session_id' => $request->session_id,
                    'af_id' => $request->af_id,
                    'adresse_id' => $request->adresse_id,
                );
                //dd($data);
                $internshiproposal_id = $DbHelperTools->manageInternshiproposal($data);
                if ($internshiproposal_id > 0) {
                    $success = true;
                    $msg = 'La période a été enregistrée avec succès';
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    public function selectSessionsPeriodsOptions($session_id, $af_id)
    {
        $result = [];
        if ($af_id > 0) {
            if ($session_id > 0) {
                $rows = Session::select('id', 'title', 'code')->where([['id', $session_id], ['af_id', $af_id], ['is_internship_period', 1]])->get();
            } else {
                $rows = Session::select('id', 'title', 'code')->where([['af_id', $af_id], ['is_internship_period', 1]])->get();
            }
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $result[] = ['id' => $row['id'], 'name' => $row['title']];
                }
            }
        }
        return response()->json($result);
    }
    public function selectStagiairesMembersOptions($member_id, $af_id)
    {
        $result = $datas = [];
        if ($af_id > 0) {
            $datas = DB::table('af_members')
                ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                ->select('af_members.id', 'en_contacts.firstname', 'en_contacts.lastname')
                ->where([['af_members.contact_id', '>', 0], ['af_enrollments.enrollment_type', 'S'], ['af_enrollments.af_id', $af_id]])->get();
        }
        if (count($datas) > 0) {
            foreach ($datas as $member) {
                $result[] = ['id' => $member->id, 'name' => $member->firstname . ' ' . $member->lastname];
            }
        }
        return response()->json($result);
    }
    public function selectStageEntitiesOptions($entity_id)
    {
        $result = $datas = [];
        // if ($entity_id > 0) {
        //     $datas=Entitie::select('id','ref','name')->where([['entity_type','S'],['is_stage_site',1],['is_active',1]])->where('id',$entity_id)->get();
        // }else{
        //     $datas=Entitie::select('id','ref','name')->where([['entity_type','S'],['is_stage_site',1],['is_active',1]])->get();
        // }
        $datas = Entitie::select('id', 'ref', 'name')->where([['entity_type', 'S'], ['is_stage_site', 1], ['is_active', 1]])->get();
        if (count($datas) > 0) {
            foreach ($datas as $row) {
                $result[] = ['id' => $row['id'], 'name' => $row['name'] . '(' . $row['ref'] . ')'];
            }
        }
        return response()->json($result);
    }
    public function selectStageContactsOptions(Request $request, $contact_id, $entity_id)
    {
        $result = $datas = [];
        if ($request->has('trainers')) {
            $datas = Contact::select('en_contacts.id', 'en_contacts.firstname', 'en_contacts.lastname', 'en_contacts.function')
            ->join('af_members', 'af_members.contact_id', 'en_contacts.id')
            ->join('af_enrollments', 'af_members.enrollment_id', 'af_enrollments.id')
            ->where('en_contacts.is_active', 1)
            ->where('af_enrollments.enrollment_type', 'F')
            ->where('af_enrollments.af_id', $request->af_id)
            ->groupBy('en_contacts.id')
            ->get();
        } elseif ($entity_id > 0) {
            // if($contact_id>0){
            //     $datas=Contact::select('id','firstname','lastname','function')->where('id',$contact_id)->get();
            // }else{
            //     $datas=Contact::select('id','firstname','lastname','function')->where('entitie_id',$entity_id)->where('is_active',1)->get();
            // }
            $datas = Contact::select('id', 'firstname', 'lastname', 'function')->where('entitie_id', $entity_id)->where('is_active', 1)->get();
        } else {
            //formateur référent
            if ($contact_id > 0) {
                $datas = Contact::select('id', 'firstname', 'lastname', 'function')->where('id', $contact_id)->get();
            } else {
                $datas = Contact::select('id', 'firstname', 'lastname', 'function')->where([['is_former', 1], ['is_active', 1]])->get();
            }
        }
        if (count($datas) > 0) {
            foreach ($datas as $row) {
                $result[] = ['id' => $row['id'], 'name' => $row['firstname'] . ' ' . $row['lastname'] . ' (' . $row['function'] . ')'];
            }
        }
        return response()->json($result);
    }
    public function selectStageAdressesOptions($adresse_id, $entity_id)
    {
        $result = $datas = [];
        if ($entity_id > 0) {
            // if($adresse_id>0){
            //     $datas=Adresse::select('id','line_1','line_2','line_3','postal_code','city','country')->where([['entitie_id',$entity_id],['id',$adresse_id]])
            //     //->where('is_stage_site',1)
            //     ->get();
            // }else{
            //     $datas=Adresse::select('id','line_1','line_2','line_3','postal_code','city','country')->where('entitie_id',$entity_id)
            //     //->where('is_stage_site',1)
            //     ->get();
            // }
            $datas = Adresse::select('id', 'line_1', 'line_2', 'line_3', 'postal_code', 'city', 'country')->where('entitie_id', $entity_id)
                //->where('is_stage_site',1)
                ->get();
        }
        if (count($datas) > 0) {
            foreach ($datas as $row) {
                $result[] = ['id' => $row['id'], 'name' => $row['line_1'] . ' ' . $row['line_2'] . ' ' . $row['line_3'] . ' ' . $row['postal_code'] . ' ' . $row['city'] . ' ' . $row['country']];
            }
        }
        return response()->json($result);
    }

    public function selectStageReferanceOptions($member_id)
    {
        $membre = Member::where('id', $member_id)->get()->first();
        return response()->json($membre->group->referance);
    }
    // proposals Export
    public function mergePdfsProposals(Request $request)
    {
        $datas=array();

        $DbHelperTools = new DbHelperTools();
        //les propositions
        $ids_stage_proposals=[];
        if($request->has('ids_stage_proposals')){
            $ids_stage_proposals=$request->ids_stage_proposals;
        }
        //$ids_invoices=[239,238,237];
        //dd($ids_stage_proposals);
        $Internshiproposals=Internshiproposal::select('id')->whereIn('id',$ids_stage_proposals)->get();
        //dd($Internshiproposals);
        $pdfs_files=[];
        if(count($Internshiproposals)>0){
            foreach($Internshiproposals as $Internship){
                $pdfs_files[]=$this->createPdfConventionStage($Internship->id, 3);
            }
        }
        //dd($pdfs_files);
        if(count($pdfs_files)){
            $pdf = PDFMerger::init();
            foreach($pdfs_files as $fname){
                $file_path = public_path().'\temp_pdf/'.$fname;
                $pdf->addPDF($file_path, 'all');
            }
            $pathForTheMergedPdf = public_path().'\temp_pdf\Internshiproposals'.'-'.time().'.pdf';
            $pdf->merge();
            $pdf->save($pathForTheMergedPdf);
            return response()->download($pathForTheMergedPdf);
        }
        return 0;
    }
    public function createPdfConventionStage($internshiproposal_id, $render_type)
    {
        $internshiproposal = null;
        if ($internshiproposal_id > 0) {
            $internshiproposal = Internshiproposal::findOrFail($internshiproposal_id);
        }
        //dd($internshiproposal);
        $document_type = 'CONVENTION_STAGE';
        $document_typewith = 'CONVENTION_STAGEWITH';
        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', $document_type)->first();
        $dmwith = Documentmodel::where('code', $document_typewith)->first();
        $content = $dm->custom_content;
        $contentwith = $dmwith->custom_content;
        //dd($content);
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $headerwith = $dmwith->custom_header;
        $footerwith = $dmwith->custom_footer;
        $dn = Carbon::now();
        $afInfos = $DbHelperTools->getAfInformationswith($internshiproposal->af->id, $internshiproposal->id);
        $keywordHeader = array(
            '{LOGO_HEADER}',
            '{AF_TITLE}',
        );
        $keywordHeaderReplace = array(
            public_path('media/logo/logo-light.png'),
            $internshiproposal->af->title
        );
        $htmlHeader = str_replace($keywordHeader, $keywordHeaderReplace, $header);
        $htmlHeaderwith = str_replace($keywordHeader, $keywordHeaderReplace, $headerwith);
        //MAIN
        $keyword = array(
            "{COMPANY_NAME}",
            "{COMPANY_DENOMINATION}",
            "{COMPANY_ADDRESS}",
            "{COMPANY_PHONE}",
            "{COMPANY_DIRECTOR}",
            //ORGANISME ACCUEIL
            "{ENTITY_NAME}",
            "{ENTITY_ADRESSE}",
            "{REPRESENTING_CONTACT_NAME}",
            "{REPRESENTING_CONTACT_FUNCTION}",
            "{SERVICE}",
            "{ENTITY_PHONE}",
            "{ADRESSE}",
            //Stagiaire
            "{MEMBER_CONTACT_LASTNAME}",
            "{MEMBER_CONTACT_FIRSTNAME}",
            "{MEMBER_CONTACT_BIRTHDAY}",
            "{MEMBER_CONTACT_ADRESSE}",
            "{MEMBER_CONTACT_PHONE}",
            "{MEMBER_CONTACT_EMAIL}",
            "{AF_TITLE}",
            //Sujet de stage
            "{SESSION_TITLE}",
            "{SESSION_NB_HOURS}",
            "{SESSION_ATTACHMENT_SEMESTER}",
            "{SESSION_STARTED_AT}",
            "{SESSION_ENDED_AT}",
            "{SESSION_DESCRIPTION}",
            //Formateur référent
            "{TRAINER_REFERENT_CONTACT_NAME}",
            "{TRAINER_REFERENT_CONTACT_PHONE}",
            "{TRAINER_REFERENT_CONTACT_EMAIL}",
            //Référent de stage
            "{INTERNSHIP_REFERENT_CONTACT_NAME}",
            "{INTERNSHIP_REFERENT_CONTACT_PHONE}",
            "{INTERNSHIP_REFERENT_CONTACT_EMAIL}",
            //Signature
            "{SIGNATURE}",
            "{LIEU}",
            "{DATE_NOW}",
            "{SESSION_TYPE}"
        );
        $entityAdresse = '';
        $entityAdresseId = 0;
        $entity_adresse = Adresse::select('id', 'line_1', 'line_2', 'line_3', 'postal_code', 'city', 'country')->where([['entitie_id', $internshiproposal->entity_id], ['is_main_entity_address', 1]])->first();
        if ($entity_adresse) {
            $entityAdresseId = $entity_adresse->id;
            $entityAdresse = $entity_adresse->line_1 . ' ' . $entity_adresse->line_2 . ' ' . $entity_adresse->line_3 . ' ' . $entity_adresse->postal_code . ' ' . $entity_adresse->city . ' ' . $entity_adresse->country;
        }
        $adressLieu = '';
        $adressLieuId = $internshiproposal->adresse_id;
        //l’adresse lieu de stage est identique à l’adresse de la société signataire ne pas afficher dans le pdf
        if ($internshiproposal->adresse_id > 0 && $entityAdresseId != $adressLieuId) {
            $rs = Adresse::select('id', 'line_1', 'line_2', 'line_3', 'postal_code', 'city', 'country')->find($internshiproposal->adresse_id);
            $adressLieu = $entity_adresse->line_1 . ' ' . $entity_adresse->line_2 . ' ' . $entity_adresse->line_3 . ' ' . $entity_adresse->postal_code . ' ' . $entity_adresse->city . ' ' . $entity_adresse->country;
        }
        //$training_site = ($convocation->af->training_site != 'OTHER') ? $convocation->af->training_site : $convocation->af->other_training_site;
        $contact_firstname = $contact_lastname = $birthday = $contact_phone = $contact_email = $contact_adresse = '';
        if ($internshiproposal->member->contact) {
            $contact_firstname = $internshiproposal->member->contact->firstname;
            $contact_lastname = $internshiproposal->member->contact->lastname;
            $contact_phone = $internshiproposal->member->contact->pro_phone;
            $contact_email = $internshiproposal->member->contact->email;
            if ($internshiproposal->member->contact->birth_date) {
                $bd = Carbon::createFromFormat('Y-m-d', $internshiproposal->member->contact->birth_date);
                $birthday = $bd->format('d/m/Y');
            }
            $contact_adresse = '';
            $e_adresse = Adresse::select('line_1', 'line_2', 'line_3', 'postal_code', 'city', 'country')
                //->where([['entitie_id',$internshiproposal->member->contact->entitie_id],['is_main_entity_address',1]])
                ->where('entitie_id', $internshiproposal->member->contact->entitie_id)
                ->first();
            if ($e_adresse) {
                $contact_adresse = $e_adresse->line_1 . ' ' . $e_adresse->line_2 . ' ' . $e_adresse->line_3 . ' ' . $e_adresse->postal_code . ' ' . $e_adresse->city . ' ' . $e_adresse->country;
            }
        }
        $started_at = Carbon::parse($internshiproposal->started_at)->format('d/m/Y');
        $ended_at = Carbon::parse($internshiproposal->ended_at)->format('d/m/Y');
        $SESSION_ATTACHMENT_SEMESTER = '';
        if ($internshiproposal->session->attachment_semester != 'SEM_AUCUN') {
            $rsP = Param::select('name')->where('code', $internshiproposal->session->attachment_semester)->first();
            if ($rsP) {
                $SESSION_ATTACHMENT_SEMESTER = 'au ' . $rsP->name . ' de formation';
            }
        }
        $keyreplace = array(
            config('global.company_name'),
            config('global.company_denomination'),
            config('global.company_address'),
            config('global.company_phone'),
            config('global.company_director'),
            $internshiproposal->entity->name,
            $entityAdresse,
            $internshiproposal->representing_contact->firstname . ' ' . $internshiproposal->representing_contact->lastname,
            $internshiproposal->representing_contact->function,
            $internshiproposal->service,
            $internshiproposal->entity->pro_phone,
            $adressLieu,
            $contact_lastname,
            $contact_firstname,
            $birthday,
            $contact_adresse,
            $contact_phone,
            $contact_email,
            $internshiproposal->af->title,
            //Sujet de stage
            $internshiproposal->session->title,
            $internshiproposal->session->nb_hours,
            $SESSION_ATTACHMENT_SEMESTER,
            $started_at,
            $ended_at,
            $internshiproposal->session->description,
            //Formateur référent
            $internshiproposal->trainer_referent_contact->firstname . ' ' . $internshiproposal->trainer_referent_contact->lastname,
            $internshiproposal->trainer_referent_contact->pro_phone,
            $internshiproposal->trainer_referent_contact->email,
            //Référent de stage
            $internshiproposal->internship_referent_contact->firstname . ' ' . $internshiproposal->internship_referent_contact->lastname,
            $internshiproposal->internship_referent_contact->pro_phone,
            $internshiproposal->internship_referent_contact->email,
            //Signature
            public_path('custom/images/signature.png'),
            config('global.company_document_location'),
            $dn->format('d/m/Y'),
            $afInfos['session_type']
        );

        $htmlMain = str_replace($keyword, $keyreplace, $content);
        $htmlMainwith = str_replace($keyword, $keyreplace, $contentwith);
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
            config('global.company_address'),
            config('global.company_phone'),
            config('global.company_fax'),
            config('global.company_email'),
            config('global.company_website'),
            config('global.company_siret'),
        );
        $htmlFooter = str_replace($keywordFooter, $keywordFooterReplace, $footer);
        $htmlFooterwith = str_replace($keywordFooter, $keywordFooterReplace, $footerwith);
        // if ($afInfos['session_type'] != null && $afInfos['session_type'] == 'EJE_AVEC_GRATIFICATION_INTERNSHIP_PERIOD') {
        //     $pdf = PDF::loadView('pages.pdf.modelwith', compact('htmlMainwith', 'htmlHeaderwith', 'htmlFooterwith'));
        // } else {
        // }
        $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
        //dd($internshiproposal->member);

        if ($render_type == 1) {
            return $pdf->stream();
        }
        $pdfName = 'PROPOSITION-' . $internshiproposal->id . '-' . time() . '.pdf';

        if ($render_type == 3) {
            $temp = env('TEMP_PDF_FOLDER');
            $temp_directory = public_path() . "/" . $temp;
            $pathToStorage = $temp_directory . '/' . $pdfName;
            if (!File::isDirectory($temp_directory)) {
                File::makeDirectory($temp_directory, 0777, true, true);
            }
            $pdf->save($pathToStorage);
            return $pdfName;
        }
    }
    public function downloadZipProposals(Request $request)
    {
        $datas=array();

        $DbHelperTools = new DbHelperTools();
        //les factures
        $ids_stage_proposals=[];
        if($request->has('ids_stage_proposals')){
            $ids_stage_proposals=$request->ids_stage_proposals;
        }
        //$ids_invoices=[239,238,237];
        //dd($ids_stage_proposals);
        $Internshiproposals=Internshiproposal::select('id')->whereIn('id',$ids_stage_proposals)->get();
        $pdfs_files=[];
        if(count($Internshiproposals)>0){
            foreach($Internshiproposals as $Internship){
                $pdfs_files[]=$this->createPdfConventionStage($Internship->id, 3);
            }
        }
        //dd($pdfs_files);
        if(count($pdfs_files)){
            $zip = new \ZipArchive();
            $temp_zip="temp_zip";
            $fileName = $temp_zip.'\proposals'.'-'.time().'.zip';
            $temp_directory = public_path().'/'.$temp_zip;
            if (!File::isDirectory($temp_directory)) {
                File::makeDirectory($temp_directory, 0777, true, true);
            }
            if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
            {
                foreach($pdfs_files as $fname){
                    $file_path = public_path('temp_pdf/'.$fname);
                    $finfo = new finfo(FILEINFO_MIME_TYPE);

                    $fileObject=new UploadedFile(
                        $file_path,
                        $fname,
                        $finfo->file($file_path),
                        filesize($file_path),
                        0,
                        false
                    );
                    $relativeName = basename($fileObject);
                    $zip->addFile($fileObject, $relativeName);
                }
                $zip->close();
            }
            $rs=response()->download(public_path($fileName));
            return $rs;
        }
        return 0;
    }

}
