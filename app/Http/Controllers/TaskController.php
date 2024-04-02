<?php

namespace App\Http\Controllers;

use PDF;
use Mail;
use Carbon\Carbon;
use App\Models\Param;
use App\Models\Price;
use App\Models\Task;
use App\Models\Comment;
use App\Models\Action;
use App\Models\Contact;
use App\Mail\TaskMail;
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
use App\Models\Document;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function addTask()
    {
        return view('pages.task.addtask');
    }

    public function getTasks()
    {
        return view('pages.task.getTasks');
    }

    public function addComment($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Task::findOrFail($row_id);
        }

        return view('pages.task.addcomment', compact('row'));
    }


    public function addsubTask($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Task::findOrFail($row_id);
        }

        return view('pages.task.addsubtasks', compact('row'));
    }

    public function getSource()
    {
        $data = $meta = [];
        $datas = Param::select('id', 'name')->where([['param_code', 'TASK_SOURCE'], ['is_active', 1]])->get();
        $recordsTotal = count($datas);
        $result = [
            "recordsTotal" => $recordsTotal,
            "datas" => $datas,
        ];
        return response()->json($result);
    }

    public function getType()
    {
        $data = $meta = [];
        $datas = Param::select('id', 'name')->where([['param_code', 'TASK_TYPE'], ['is_active', 1]])->get();
        $recordsTotal = count($datas);
        $result = [
            "recordsTotal" => $recordsTotal,
            "datas" => $datas,
        ];
        return response()->json($result);
    }

    public function getResponseMode()
    {
        $data = $meta = [];
        $datas = Param::select('id', 'name')->where([['param_code', 'TASK_RESPONSE_MODE'], ['is_active', 1]])->get();
        $recordsTotal = count($datas);
        $result = [
            "recordsTotal" => $recordsTotal,
            "datas" => $datas,
        ];
        return response()->json($result);
    }

    public function getObject()
    {
        $data = $meta = [];
        $datas = Param::select('id', 'name')->where([['param_code', 'Object_concerne'], ['is_active', 1]])->get();
        $recordsTotal = count($datas);
        $result = [
            "recordsTotal" => $recordsTotal,
            "datas" => $datas,
        ];
        return response()->json($result);
    }

    public function getEtat()
    {
        $data = $meta = [];
        $datas = Param::select('id', 'name')->where([['param_code', 'Etat'], ['is_active', 1]])->get();
        $recordsTotal = count($datas);
        $result = [
            "recordsTotal" => $recordsTotal,
            "datas" => $datas,
        ];
        return response()->json($result);
    }


    public function validateTask(Request $request)
    {
        $success = false;
        $msg = 'L\'ajout ne marche pas !';
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();

        if (count($dtRequests) > 0) {
            $result = $DbHelperTools->manageTask($dtRequests);
            $success = true;
            $msg = 'La tâche a été ajoutée avec succès';
        } else {
            $success = false;
            $msg = 'Merci de remplir les champs obligatoires';
        }

        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'result' => $result
        ]);
    }
    
    public function createTask(Request $request, ?int $parent_id = null)
    {
        // dd($request);
        $success = false;
        $msg = 'L\'ajout ne marche pas !';
        $DbHelperTools = new DbHelperTools();
        $contact = Contact::find($request->contact);
        $entity_id = $contact->entitie_id ?? null;

        // if ($request->task_object == 'glblaf') {
        //     $request->pflist = null;
        // } elseif ($request->task_object == 'glblpf') {
        //     $request->aflist = null;
        // } else {
        //     $request->aflist = null;
        //     $request->pflist = null;
        // }
        $dtRequests = [
            'contactlistvalue' => $request->contact,
            'entitielistvalue' => $request->entity,
            'resume' => $request->resume,
            'typevalue' => $request->type,
            'sourcevalue' => $request->source,
            'aflistvalue' => $request->aflist,
            'pflistvalue' => $request->pflist,
            'notes' => $request->notes,
            'description' => $request->myTextarea,
            'rapporteurvalue' => $request->rapporteur,
            'responsablevalue' => $request->responsable,
            'etatvalue' => $request->etat,
            'datedebut' => $request->datedebut,
            'dateecheance' => $request->dateecheance,
            'daterappel' => $request->daterappel,
            'moderappel' => $request->rappelmode,
            'reponsevalue' => $request->reponsemode,
            'prioritetext' => $request->priorite,
            'task_parent_id' => $parent_id,
            'isread' => 0,
        ];

        if ($parent_id) {
            $dtRequests['sub_task'] = 1;
        }

        
        if($dtRequests['typevalue'] == 221){
            $result = $DbHelperTools->manageTaskSousTask($dtRequests);
        }else{
            $result = $DbHelperTools->manageTask($dtRequests);
        }
        //ADD Email Logic
            $row = null;
            $content = '';
            $user=auth()->user()->email;
            if ($result['task'] > 0) {
                $row = $result['data'];

                $email_model_id = Emailmodel::select('id')->where('code','ENVOI_DEVIS_INTERVENANT_SUR_FACTURE')->pluck('id');
                $emailmodel = Emailmodel::findOrFail(intval($email_model_id[0]));

                $subject = strip_tags($emailmodel->custom_header);
                $content = $emailmodel->custom_content;
                $content = $this->MailContent($content,$row);
                $Estimate_type = $row->Estimate_type;

            }

        return view('pages.commerce.invoice.form.task', compact('row', 'content','user'));
        //End Email Logic


        if ($result['task']) {
            $task = Task::findOrFail($result['task']);
            $responsable = $task->responsable;

            if ($request->motivation == 'contact' && $contact) {
                $contact->pro_phone = $request->contact_phone;
                $contact->email = $request->contact_email;
                // $contact->entitie_id = $request->entity;
                $contact->save();

                $address = $contact->entitie ? $contact->entitie->adresses->first() : null;
                if ($address) {
                    $adr_lines = preg_split("/\r?\n/", $request->contact_address);
                    if (isset($adr_lines[0])) {
                        $address->line_1 = $adr_lines[0];
                    }
                    if (isset($adr_lines[1])) {
                        $address->line_2 = $adr_lines[1];
                    }
                    if (isset($adr_lines[2])) {
                        $address->line_3 = $adr_lines[2];
                    }
                    $address->postal_code = $request->contact_cp;
                    $address->city = $request->contact_city;

                    $address->save();
                }

                if ($contact->email && !$parent_id && $request->has('send_mail') && $request->send_mail) {
                    /* Mail */
                    $content = $DbHelperTools->prepareMailContent('TACHE_CREE_SUR_CONTACT', $result['task']);
                    $fullname = $contact->firstname . " " . $contact->lastname;
                    Mail::send('pages.email.model', ['htmlMain' => $content['content'], 'htmlHeader' => $content['header'],
                     'htmlFooter' => $content['footer']], function ($m) use ($contact, $fullname, $content,$responsable) {
                        $m->from(auth()->user()->email);
                        $m->bcc(auth()->user()->email);//TODO delete ur email
                        $m->to($contact->email, $fullname)->subject($content['subject']);
                    });
                }
            }

            if ($request->notes) {
                $comment = new Comment();
                $comment->description = $request->notes;
                $comment->date_comment = new DateTime();
                $comment->task_id = $result['task'];
                $comment->contact_id = Auth::user()->contact ? Auth::user()->contact->id : null;

                $comment->save();
            }

            $success = true;
            $msg = 'La tâche a été ajoutée avec succès';
        }
        // } else {
        //     $success = false;
        //     $msg = 'Merci de remplir les champs obligatoires';
        // }

        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'result' => $result
        ]);
    }


    // public function createTask(Request $request, ?int $parent_id = null)
    // {
    //     $success = false;
    //     $msg = 'L\'ajout ne marche pas !';
    //     $DbHelperTools = new DbHelperTools();
    //     $contact = Contact::find($request->contact);
    //     $entity_id = $contact->entitie_id ?? null;

    //     if ($request->task_object == 'glblaf') {
    //         $request->pflist = null;
    //     } elseif ($request->task_object == 'glblpf') {
    //         $request->aflist = null;
    //     } else {
    //         $request->aflist = null;
    //         $request->pflist = null;
    //     }
    //     $dtRequests = [
    //         'contactlistvalue' => $request->contact,
    //         'entitielistvalue' => $entity_id,
    //         'resume' => $request->resume,
    //         'typevalue' => $request->type,
    //         'sourcevalue' => $request->source,
    //         'aflistvalue' => $request->aflist,
    //         'pflistvalue' => $request->pflist,
    //         'notes' => $request->notes,
    //         'description' => $request->myTextarea,
    //         'rapporteurvalue' => $request->rapporteur,
    //         'responsablevalue' => $request->responsable,
    //         'etatvalue' => $request->etat,
    //         'datedebut' => $request->datedebut,
    //         'dateecheance' => $request->dateecheance,
    //         'daterappel' => $request->daterappel,
    //         'moderappel' => $request->rappelmode,
    //         'reponsevalue' => $request->reponsemode,
    //         'prioritetext' => $request->priorite,
    //         'task_parent_id' => $parent_id,
    //         'isread' => 0,
    //     ];

    //     if ($parent_id) {
    //         $dtRequests['sub_task'] = 1;
    //     }

    //     // if (count($dtRequests) > 0) {
    //     $result = $DbHelperTools->manageTask($dtRequests);
    //     if ($result['task']) {      
    //         $task = Task::findOrFail($result['task']);
    //         $responsable = $task->responsable; 

    //         if ($request->motivation == 'contact' && $contact) {
    //             $contact->pro_phone = $request->contact_phone;
    //             $contact->email = $request->contact_email;
    //             // $contact->entitie_id = $request->entity;
    //             $contact->save();

    //             $address = $contact->entitie ? $contact->entitie->adresses->first() : null;
    //             if ($address) {
    //                 $adr_lines = preg_split("/\r?\n/", $request->contact_address);
    //                 if (isset($adr_lines[0])) {
    //                     $address->line_1 = $adr_lines[0];
    //                 }
    //                 if (isset($adr_lines[1])) {
    //                     $address->line_2 = $adr_lines[1];
    //                 }
    //                 if (isset($adr_lines[2])) {
    //                     $address->line_3 = $adr_lines[2];
    //                 }
    //                 $address->postal_code = $request->contact_cp;
    //                 $address->city = $request->contact_city;

    //                 $address->save();
    //             }
                
    //             if ($contact->email && !$parent_id && $request->has('send_mail') && $request->send_mail) {
    //                 /* Mail */
    //                 $content = $DbHelperTools->prepareMailContent('TACHE_CREE_SUR_CONTACT', $result['task']);
    //                 $fullname = $contact->firstname . " " . $contact->lastname;
    //                 Mail::send('pages.email.model', ['htmlMain' => $content['content'], 'htmlHeader' => $content['header'], 'htmlFooter' => $content['footer']], function ($m) use ($contact, $fullname, $content,$responsable) {
    //                     $m->from(auth()->user()->email);
    //                     $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
    //                     $m->to($contact->email, $fullname)->subject($content['subject']);
    //                 });
    //             }
    //         }

    //         if ($request->notes) {
    //             $comment = new Comment();
    //             $comment->description = $request->notes;
    //             $comment->date_comment = new DateTime();
    //             $comment->task_id = $result['task'];
    //             $comment->contact_id = Auth::user()->contact ? Auth::user()->contact->id : null;

    //             $comment->save();
    //         }

    //         $success = true;
    //         $msg = 'La tâche a été ajoutée avec succès';
    //     }
    //     // } else {
    //     //     $success = false;
    //     //     $msg = 'Merci de remplir les champs obligatoires';
    //     // }

    //     return response()->json([
    //         'success' => $success,
    //         'msg' => $msg,
    //         'result' => $result
    //     ]);
    // }

    public function validateComment(Request $request)
    {
        $success = false;
        $msg = 'La modification ne marche pas !';
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();

        if (count($dtRequests) > 0) {
            $result = $DbHelperTools->manageComment($dtRequests);
            $success = true;
            $msg = 'Le commentaire a été modifiée avec succès';
        } else {
            $success = false;
            $msg = 'La modification ne marche pas';
        }

        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'result' => $result
        ]);
    }

    public function upload(Request $request)
    {
        $path = $request->file('file')->store('files');


        dd($path);
    }

    public function sdtTasks(Request $request)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];

        // $userid = Auth::user()->id;
        // $roles = Auth::user()->roles;

        $datas = Task::latest();

        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('title', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('description', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('start_date', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                if ($request->has('debut_end') && $request->has('fin_end')) {
                    if (!empty($request->debut_end) && !empty($request->fin_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->debut_end);
                        $end = Carbon::createFromFormat('d/m/Y', $request->fin_end);
                        $datas->whereBetween('ended_date', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                if ($request->has('filter_rappel') && $request->has('end_rappel')) {
                    if (!empty($request->filter_rappel) && !empty($request->end_rappel)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_rappel);
                        $end = Carbon::createFromFormat('d/m/Y', $request->end_rappel);
                        $datas->whereBetween('callback_date', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                if ($request->has('filter_etat') && !empty($request->filter_etat)) {
                    $datas->where('etat_id', $request->filter_etat);
                }
                if ($request->has('filter_type') && !empty($request->filter_type)) {
                    $datas->where('type_id', $request->filter_type);
                }
                if ($request->has('filter_source') && !empty($request->filter_source)) {
                    $datas->where('source_id', $request->filter_source);
                }
                if ($request->has('filter_responsable') && !empty($request->filter_responsable)) {
                    $datas->where('responsable_id', $request->filter_responsable);
                }
                if ($request->has('filter_objet') && !empty($request->filter_objet)) {
                    if ($request->filter_objet == "af") {
                        $datas->whereNotNull('af_id');
                    } else if ($request->filter_objet == "pf") {
                        $datas->whereNotNull('pf_id');
                    } else {
                        $datas->whereNotNull('af_id')->whereNotNull('pf_id');
                    }
                }
            }
        }

        if ($request->has('only_my_tasks') && $request->only_my_tasks) {
            /* $udatas = $udatas->filter(function ($d) {
                $r_user_id = $d->responsable->user->id ?? 0;
                $s_user_id = $d->apporteur->user->id ?? 0;
                return Cache::has('user-is-online-' . $r_user_id) || Cache::has('user-is-online-' . $s_user_id);
            }); */
            $contact_id = Auth::user()->contact->id ?? 0;
            $udatas = $datas->whereRaw("( contact_id = $contact_id OR responsable_id = $contact_id OR apporteur_id = $contact_id )");
        }

        $recordsTotal = count($datas->get());
        if ($request->length > 0) {
            $start = (int) $request->start;
            $length = (int) $request->length;
            $datas->skip($start)->take($length);
        }
        $udatas = $datas->orderByDesc('id')->get();


        foreach ($udatas as $d) {

            if($d->sub_task == 0){

                    $row = array();

                    //$btn_div = '<div style="width:30px;" class="flaticon-add toogle-click" title="Afficher tous le reste du tableau"></div>';
                    //SUB
                    $row[] = $d->id;
                    // $row[] = '';
                    //ID
                    $row[] = $d->id;

                    if (isset($d->entite_id)) {
                        $entitie_value = Entitie::find($d->entite_id);
                        $row[] = '<p class="text-info font-size-xs">' . ($entitie_value ? $entitie_value->name : '-') . '</p>';
                    } else {
                        $row[] = '--';
                    }

                    if (isset($d->contact_id)) {
                        $contact_value = Contact::find($d->contact_id);
                        $row[] = '<p class="text-info font-size-xs">' . ($contact_value ? $contact_value->firstname . ' ' . $contact_value->lastname : '-')  . '</p>';
                    } else {
                        $row[] = '--';
                    }

                    $started_at = '--';
                    if ($d->start_date != null) {
                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->start_date);
                        $started_at = $dt->format('d/m/Y');
                    }
                    $ended_at = '--';
                    if ($d->ended_date != null) {
                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->ended_date);
                        $ended_at = $dt->format('d/m/Y');
                    }
                    $callback_date = '--';
                    if ($d->callback_date != null) {
                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->callback_date);
                        $callback_date = $dt->format('d/m/Y');
                    }
                    $pInfos = '<p class="font-size-xs"><strong>Date début :</strong> ' . $started_at . '</p>';
                    $pInfos .= '<p class="font-size-xs"><strong> Date fin :</strong> ' . $ended_at . '</p>';
                    $pInfos .= '<p class="font-size-xs"><strong> Date de rappel :</strong> ' . $callback_date . '</p>';

                    $row[] = $pInfos;

                    if ($d->etat_id > 0) {
                        $etat_id_value = Param::findOrFail($d->etat_id);
                        if ($etat_id_value->code == "Annulée") {
                            if($d->is_read == 0){
                            $row[] = '<div style="display: ruby;"><p class="text-info font-size-xs"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAByElEQVQ4jaWTwUsbQRjF38xuJsnqikYxWEUMRVeLF0HUag6KS0D/AL3orUcFr1K8Fo8e9KCXQvs3SIthzSGoPRhvCYu9VKuCIamsm262uy7Ti4bENJriu8037/f4HswALxQpP3ybmprllO4+SXA++3Z//0tVQGJyUpT8/u89Q0M9TeHwP9nbbBZnqdRPms+/Hk6lXACgD5eSKC5LstxWCwaApvZ2BJubW7zW1qWKDZLRaAtraDjrnZiQg7JcAgIrKwAAe2OjNLMLBZwmkyY8LzKqaXkKAH5JWg91dvrK4VoKNDYi1NXFiM/3AQDokaoOcGCxQ1ECz9L36lAUPzhfOIjFBqkgCNuvFIUJjNXLQ2AM4b6+gI+QHQqA87rRMhECUOqSw1isXwRO3kxPB+vdwnNdZDTNguOM0PG9PZ0Dn6503X5sZPPzYHNzVQFXuv6HE/J5JJFIUwBwLGv15vLSLZpmpZPzqoJ2oYCbiwsHrvseAAQA+Hh+br+LRIq2YURD3d2lHl46DS+TqQj4cXz823GctbF4PAGUvcTi3d2mZZq522y2Znfj+hqWYfwSc7mth1nFZzpSVRWcx2smAODAzLimfX3K81/6CxXCniK7MbuvAAAAAElFTkSuQmCC">' . $etat_id_value->code . '</p></div></br><div style="color:#F1260A;font-weight: 700;">Non lu</div>';
                            }
                            else{
                            $row[] = '<div style="display: ruby;"><p class="text-info font-size-xs"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAByElEQVQ4jaWTwUsbQRjF38xuJsnqikYxWEUMRVeLF0HUag6KS0D/AL3orUcFr1K8Fo8e9KCXQvs3SIthzSGoPRhvCYu9VKuCIamsm262uy7Ti4bENJriu8037/f4HswALxQpP3ybmprllO4+SXA++3Z//0tVQGJyUpT8/u89Q0M9TeHwP9nbbBZnqdRPms+/Hk6lXACgD5eSKC5LstxWCwaApvZ2BJubW7zW1qWKDZLRaAtraDjrnZiQg7JcAgIrKwAAe2OjNLMLBZwmkyY8LzKqaXkKAH5JWg91dvrK4VoKNDYi1NXFiM/3AQDokaoOcGCxQ1ECz9L36lAUPzhfOIjFBqkgCNuvFIUJjNXLQ2AM4b6+gI+QHQqA87rRMhECUOqSw1isXwRO3kxPB+vdwnNdZDTNguOM0PG9PZ0Dn6503X5sZPPzYHNzVQFXuv6HE/J5JJFIUwBwLGv15vLSLZpmpZPzqoJ2oYCbiwsHrvseAAQA+Hh+br+LRIq2YURD3d2lHl46DS+TqQj4cXz823GctbF4PAGUvcTi3d2mZZq522y2Znfj+hqWYfwSc7mth1nFZzpSVRWcx2smAODAzLimfX3K81/6CxXCniK7MbuvAAAAAElFTkSuQmCC">' . $etat_id_value->code . '</p></div></br><div style="color:#72E01C;font-weight: 700;">Lu</div>';
                            }
                        } elseif ($etat_id_value->code == "Terminée") {
                            if($d->is_read == 0){
                            $row[] = '<div style="display: ruby;"><p class="text-info font-size-xs"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAA+UlEQVQ4jbXRMS8EURQF4I8gaFalVulZClESiX+wiSglCtU2GpVE42+optIgarOJQqi2UFKIks4WNqPYt/LyzIxVOMlJ5p53z7nv3WGAJnL08IxjTBoRTbzhADvYQgcZVrHyW0AezHCHDTTwik98YLEuoIcFzOIJm0HPUARe1AW8YB2HOMdU0DtRQIHtqoCj0DwXabuJucCjisVO4MzgzVkyeT9wWLfrnrKGk2TqXuCwfsd8bBqPvm/TQ0xjJqobYUgpltBPbnAZGGt9LKfmMdz4ubh7PJToefB8o1XSVOA0sOysFQd0K5rq2GXwC+E6WdYouPpj/z/hC91aYBcjBs67AAAAAElFTkSuQmCC">' . $etat_id_value->code . '</p></div></br><div style="color:#F1260A;font-weight: 700;">Non lu</div>';
                            }
                            else{
                            $row[] = '<div style="display: ruby;"><p class="text-info font-size-xs"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAA+UlEQVQ4jbXRMS8EURQF4I8gaFalVulZClESiX+wiSglCtU2GpVE42+optIgarOJQqi2UFKIks4WNqPYt/LyzIxVOMlJ5p53z7nv3WGAJnL08IxjTBoRTbzhADvYQgcZVrHyW0AezHCHDTTwik98YLEuoIcFzOIJm0HPUARe1AW8YB2HOMdU0DtRQIHtqoCj0DwXabuJucCjisVO4MzgzVkyeT9wWLfrnrKGk2TqXuCwfsd8bBqPvm/TQ0xjJqobYUgpltBPbnAZGGt9LKfmMdz4ubh7PJToefB8o1XSVOA0sOysFQd0K5rq2GXwC+E6WdYouPpj/z/hC91aYBcjBs67AAAAAElFTkSuQmCC">' . $etat_id_value->code . '</p></div></br><div style="color:#72E01C;font-weight: 700;">lu</div>';
                            }
                        } else {
                            if($d->is_read == 0){
                            $row[] = '<p class="text-info font-size-xs">' . $etat_id_value->name . '</p></br><div style="color:#F1260A;font-weight: 700;">Non lu</div>';
                            }
                            else{
                            $row[] = '<p class="text-info font-size-xs">' . $etat_id_value->name . '</p></br><div style="color:#72E01C;font-weight: 700;">Lu</div>';    
                            }
                            
                        }
                    } else {
                        if($d->is_read == 0){
                        $row[] = '--</br><div style="color:#F1260A;font-weight: 700;">Non lu</div>';
                        }
                        else{
                        $row[] = '--</br><div style="color:#72E01C;font-weight: 700;">Lu</div>';
                        }
                    }

                    $row[] = '<p class="text-info font-size-xs">' . $d->title . '</p>';

                    if (isset($d->apporteur_id)) {
                        $apporteur_value = Contact::find($d->apporteur_id);
                        $row[] = '<p class="text-info font-size-xs">' . ($apporteur_value ? $apporteur_value->firstname . ' ' . $apporteur_value->lastname : '-')  . '</p>';
                    } else {
                        $row[] = '--';
                    }


                    if ($d->apporteur_id > 0) {
                        $responsable_value = Contact::find($d->responsable_id);
                        $row[] = '<p class="text-info font-size-xs">' . ($responsable_value ? $responsable_value->firstname . ' ' . $responsable_value->lastname : '-') . '</p>';
                    } else {
                        $row[] = '--';
                    }

                    // if (isset($d->type_id)) {
                    //     $type_id_value = Param::findOrFail($d->type_id);
                    //     $row[] = '<p class="text-info font-size-xs">' . $type_id_value->name . '</p>';
                    // } else {
                    //     $row[] = '--';
                    // }


                    // dd($d->source_id);

                    // if (isset($d->source_id)) {
                    //     $source_id_value = Param::findOrFail($d->source_id);
                    //     $row[] = '<p class="text-info font-size-xs">' . $source_id_value->name . '</p>';
                    // } else {
                    //     $row[] = '--';
                    // }

                    // $row[] = '<p class="text-info font-size-xs">' . $d->description . '</p>';

                    // if (isset($d->callback_mode)) {
                    //     $row[] = '<p class="text-info font-size-xs">' . $d->callback_mode . '</p>';
                    // } else {
                    //     $row[] = '--';
                    // }

                    // if (isset($d->reponse_mode_id)) {
                    //     $response_mode_id_value = Param::find($d->reponse_mode_id);
                    //     $row[] = '<p class="text-info font-size-xs">' . ($response_mode_id_value ? $response_mode_id_value->name : '-') . '</p>';
                    // } else {
                    //     $row[] = '--';
                    // }

                    // $concerne = "";

                    // if (isset($d->af_id)) {
                    //     $af = \DB::table('af_actions')->Where('id', $d->af_id)->pluck('title');
                    //     $concerne .= '<p class="font-size-xs"><strong>AF :</strong> ' . $af[0] . '</p>';
                    // }

                    // if (isset($d->pf_id)) {
                    //     $pf = \DB::table('pf_formations')->Where('id', $d->pf_id)->pluck('title');
                    //     $concerne .= '<p class="font-size-xs"><strong>PF :</strong> ' . $pf[0] . '</p>';
                    // }

                    // if(isset($d->entite_id)){            
                    //     $entitie=\DB::table('en_entities')->Where('id', $d->entite_id)->pluck('name');
                    //     $concerne .= '<p class="font-size-xs"><strong>Entité :</strong> ' . $entitie[0] . '</p>';
                    // }

                    // if(isset($d->contact_id)){            
                    //     $contact=\DB::table('en_contacts')->Where('id', $d->contact_id)->get();
                    //     $concerne .= '<p class="font-size-xs"><strong>Contact :</strong> ' . $contact[0]->firstname.' '.$contact[0]->lastname . '</p>';
                    // }

                    // $row[] = $concerne;

                    // $comment =  Comment::where('task_id', $d->id)->get();
                    // $txtcomment = "";
                    // foreach ($comment as $elem) {
                    //     // $txtcomment .='<br/>'. '<span class="text-dark">#</span> '.'<pre>'.$elem.'</pre>';
                    //     $txtcomment .= '<br><pre class="text-dark">#&nbsp;' . str_replace("\n", '<br/>', $elem->description) . '</pre>';
                    //     $txtcomment .= '<p class="text-primary">Ecrit par :' . str_replace("\n", '<br/>', $elem->contact ? $elem->contact->firstname . ' ' . $elem->contact->lastname : 'Anonym') . '</p>';
                    // }

                    // $row[] = '<p class="text-info font-size-xs">' . $txtcomment . '</p>';

                    // $row[] = '<p class="text-info font-size-xs">' . $d->priority . '</p>';

                    //Actions
                    $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewTask(' . $d->id . ')" title="Détails"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
                    $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formTask(' . $d->id . ')" title="Édition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                    $btn_done = '<button class="btn btn-sm btn-clean btn-icon" onclick="_terminateTask(' . $d->id . ')" title="Terminer"><i class="' . $tools->getIconeByAction('SEND') . '"></i></button>';
                    $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_cancelTask(' . $d->id . ')" title="Annulation"><i class="' . $tools->getIconeByAction('CANCEL') . '"></i></button>';
                    $btn_report = '<button class="btn btn-sm btn-clean btn-icon" onclick="_reportTask(' . $d->id . ')" title="Reporter"><i class="' . $tools->getIconeByAction('REPORT') . '"></i></button>';
                    $btn_transfert = '<button class="btn btn-sm btn-clean btn-icon" onclick="_validateTask(' . $d->id . ')" title="Transférer"><i class="' . $tools->getIconeByAction('VALIDATE') . '"></i></button>';
                    $btn_comment = '<button class="btn btn-sm btn-clean btn-icon" onclick="_generatecomment(' . $d->id . ')" title="Commenter"><i class="' . $tools->getIconeByAction('COMMENT') . '"></i></button>';
                    $btn_subtask = '<button class="btn btn-sm btn-clean btn-icon" onclick="_subTask(' . $d->id . ')" title="Créer sous-tâches"><i class="' . $tools->getIconeByAction('SUBTASK') . '"></i></button>';

                    $btn_send = '';
                    if ($d->contact_id) {
                        $mailto = "mailto:" . (isset($contact_value->email) ? $contact_value->email : "") . "?subject=Tâche {$d->title}&body=Votre Message";
                        $btn_send = '<a class="btn btn-sm btn-clean btn-icon" href="' . $mailto . '" title="Envoi d’email"><i class="' . $tools->getIconeByAction('EMAIL') . '"></i></a>';
                    }

                    $row[] = $btn_view . $btn_edit . $btn_done . $btn_delete . $btn_report . $btn_transfert . $btn_comment . $btn_subtask . $btn_send;

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

    public function viewTask($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Task::findOrFail($row_id);
        }
        $DbHelperTools = new DbHelperTools();
        if (isset($row->source_id)) {
            $source = $datas = Param::find($row->source_id);
            $row['source'] = $source->name ?? null;
        }
        if (isset($row->type_id)) {
            $type = $datas = Param::find($row->type_id);
            $row['type'] = $type->name ?? null;
        }
        if (isset($row->etat_id)) {
            $etat = $datas = Param::find($row->etat_id);
            $row['etat'] = $etat->name ?? null;
        }

        if (isset($row->reponse_mode_id)) {
            $reponse = $datas = Param::find($row->reponse_mode_id);
            $row['reponse'] = $reponse->name ?? null;
        }

        if (isset($row->apporteur_id)) {
            $apporteur = Contact::find($row->apporteur_id);
            $row['apporteur'] = $apporteur->firstname . ' ' . $apporteur->lastname ?? null;
        }

        if (isset($row->responsable_id)) {
            $responsable = Contact::find($row->responsable_id);
            $row['responsable'] = $responsable->firstname . ' ' . $responsable->lastname ?? null;
        }

        if (isset($row->af_id)) {
            $af = Formation::find($row->af_id);
            $row['af'] = $af->code ?? null;
        }

        if (isset($row->pf_id)) {
            $pf = Action::find($row->pf_id);
            $row['pf'] = $pf->code ?? null;
        }

        if (isset($row->entite_id)) {
            $entitie = Entitie::find($row->entite_id);
            $row['entitie'] = $entitie->name ?? null;
        }

        if (isset($row->contact_id)) {
            $contact = Contact::find($row->contact_id);
            $row['contact'] = $contact ?? null;
        }

        if (isset($row->id)) {
            $comment =  Comment::select('description')->where('task_id', $row->id)->pluck('description');
            $row['comment'] = $comment;
        }

        return view('pages.task.gettask', ['row' => $row]);
    }

    public function editTask(int $id)
    {
        $row = null;
        $rightuser = "";

        if ($id > 0) {
            $row = Task::findOrFail($id);
            // $row = Task::where('id', $id)->firstOrFail();
        }

        if ($id > 0) {
            $row = Task::findOrFail($id);
            $task = Task::find($id);

            if(isset($task->responsable_id)){
                if(auth()->user()->contact_id == $task->responsable_id){
                    $row->is_read =  1;
                    $row->save();
                }
            }
        }

        $DbHelperTools = new DbHelperTools();
        if (isset($row->source_id)) {
            $source = $datas = Param::find($row->source_id);
            $row['source'] = $source->name ?? null;
        }

        if (isset($row->type_id)) {
            $type = $datas = Param::find($row->type_id);
            $row['type'] = $type->name ?? null;
        }

        if (isset($row->etat_id)) {
            $etat = $datas = Param::find($row->etat_id);
            $row['etat'] = $etat->name ?? null;
        }

        if (isset($row->reponse_mode_id)) {
            $reponse = $datas = Param::find($row->reponse_mode_id);
            $row['reponse'] = $reponse->name ?? null;
        }

        if (isset($row->apporteur_id)) {
            $apporteur = Contact::find($row->apporteur_id);
            $row['apporteur'] = $apporteur->firstname . ' ' . $apporteur->lastname ?? null;
        }

        if (isset($row->responsable_id)) {
            $responsable = Contact::find($row->responsable_id);
            $row['responsable'] = $responsable->firstname . ' ' . $responsable->lastname ?? null;
        }

        if (isset($row->af_id)) {
            $af = Formation::find($row->af_id);
            $row['af'] = $af->code ?? null;
        }

        if (isset($row->pf_id)) {
            $pf = Action::find($row->pf_id);
            $row['pf'] = $pf->code ?? null;
        }

        if (isset($row->entite_id)) {
            $entitie = Entitie::find($row->entite_id);
            $row['entitie'] = $entitie->name ?? null;
        }

        if (isset($row->contact_id)) {
            $contact = Contact::find($row->contact_id);
            $row['contact'] = $contact ?? null;
        }

        if (isset($row->id)) {
            $comment = Comment::with('Task')->where('task_id', $row->id)->select('id', 'description')->get();
            $row['comment'] = $comment;
        }
        
        if(isset($row->responsable_id)){
            if(auth()->user()->contact_id == $row->responsable_id){
                $rightuser = "temoin";
            }
        }

        return view('pages.task.edittask', compact('row','rightuser'));
    }

    public function ModifyTask(Request $request)
    {
        $success = false;
        $msg = 'Erreur lors de la modification.';
        $DbHelperTools = new DbHelperTools();
        $contact = Contact::find($request->contact);
        $entity_id = $contact->entitie_id ?? null;

        if ($request->task_object == 'glblaf') {
            $request->pflist = null;
        } elseif ($request->task_object == 'glblpf') {
            $request->aflist = null;
        } else {
            $request->aflist = null;
            $request->pflist = null;
        }

        if ($request->motivation == 'intern') {
            $request->contact = $contact = null;
            $entity_id = null;
        }

       if($request->yesnoread == "on"){
            $is_read = 1;
        }
        else{
            $is_read = 0;
        }
        
        $dtRequests = [
            'id' => $request->id,
            'contactlistvalue' => $request->contact,
            'entitielistvalue' => $entity_id,
            'resume' => $request->resume,
            'typevalue' => $request->type,
            'sourcevalue' => $request->source,
            'aflistvalue' => $request->aflist,
            'pflistvalue' => $request->pflist,
            'notes' => '',
            'description' => $request->myTextarea,
            'rapporteurvalue' => $request->rapporteur,
            'responsablevalue' => $request->responsable,
            'etatvalue' => $request->etat,
            'datedebut' => $request->datedebut,
            'dateecheance' => $request->dateecheance,
            'daterappel' => $request->daterappel,
            'moderappel' => $request->rappelmode,
            'reponsevalue' => $request->reponsemode,
            'prioritetext' => $request->priorite,
            'isread' => $is_read,
        ];

        // if (count($dtRequests) > 0) {
        $result = $DbHelperTools->manageTask($dtRequests);

        if ($result['task']) {
            if ($request->motivation == 'contact' && $contact) {
                $contact->pro_phone = $request->contact_phone;
                $contact->email = $request->contact_email;
                // $contact->entitie_id = $request->entity;
                $contact->save();

                $address = $contact->entitie ? $contact->entitie->adresses->first() : null;
                if ($address) {
                    $adr_lines = preg_split("/\r?\n/", $request->contact_address);
                    if (isset($adr_lines[0])) {
                        $address->line_1 = $adr_lines[0];
                    }
                    if (isset($adr_lines[1])) {
                        $address->line_2 = $adr_lines[1];
                    }
                    if (isset($adr_lines[2])) {
                        $address->line_3 = $adr_lines[2];
                    }
                    $address->postal_code = $request->contact_cp;
                    $address->city = $request->contact_city;

                    $address->save();
                }
            }

            $success = true;
            $msg = 'La tâche a été ajoutée avec succès';
        }

        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'result' => $result
        ]);
    }

    public function annulateTask($row_id)
    {
        $success = false;
        $DbHelperTools = new DbHelperTools();

        if ($row_id > 0) {
            $result = $DbHelperTools->manageStateTask($row_id);
            $success = true;
        } else {
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'result' => $result
        ]);
    }

    public function terminateTask($row_id)
    {
        $success = false;
        $DbHelperTools = new DbHelperTools();

        if ($row_id > 0) {
            $result = $DbHelperTools->manageTerminateTask($row_id);
            $success = true;
        } else {
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'result' => $result
        ]);
    }

    public function reportTask(Request $request)
    {
        $success = false;
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();
        if (count($dtRequests) > 0) {
            $result = $DbHelperTools->reportTask($dtRequests);
            $success = true;
        } else {
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'result' => $result
        ]);
    }

    public function transfertTask(Request $request)
    {
        $success = false;
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();
        if (count($dtRequests) > 0) {
            $result = $DbHelperTools->transfertTask($dtRequests);
            $success = true;
        } else {
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'result' => $result
        ]);
    }

    public function sendTaskMail()
    {

        $email = 'hbriere@havetdigital.fr';
        $offer = [
            'title' => 'RAPPEL',
            'url' => 'https://preprod.solaris-crfpe.fr'
        ];

        Mail::to($email)->send(new TaskMail($offer));

        dd("Mail sent!");
    }

    public function formNewEmail()
    {
        return view('pages.email.form.new-email');
    }

    public function storeFormEmail(Request $request)
    {
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();

            $data = array(
                "id" => $request->id,
                "name" => $request->name,
                "default_header" => $request->custom_header,
                "custom_header" => $request->custom_header,
                "custom_content" => $request->custom_content,
                "default_content" => $request->custom_content,
                "custom_footer" => $request->custom_footer,
                "default_footer" => $request->custom_footer,
                "view_table" => $request->view_table,
            );

            if (!$request->id) {
                $codes = Emailmodel::where('code', 'like', $request->code . '%')->pluck('code')->toArray();
                while (in_array($request->code, $codes)) {
                    $parts = explode('_', $request->code);
                    $number = (int) array_pop($parts) + 1;
                    $request->code = ($number > 1 ? implode('_', $parts) : $request->code) . "_$number";
                }
                $data["code"] = $request->code;
            }

            $row_id = $DbHelperTools->manageEmailmodel($data);
            $success = true;
            $msg = 'La modèle d\'email a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formEmail($row_id)
    {
        $row = null;
        if ($row_id > 0) {
            $emailmodel = Emailmodel::findOrFail($row_id);
        }

        $views = DB::select('SHOW FULL TABLES WHERE table_type = "VIEW"');
        foreach ($views as &$v) {
            $v = array_values((array) $v)[0];
        }

        return view('pages.email.form', ['emailmodel' => $emailmodel, 'views' => $views]);
    }

    public function sendTaskFormEmail($row_id)
    {
        if ($row_id > 0) {
            $emailmodel = Emailmodel::findOrFail($row_id);
        }

        $view_vars = [];
        if ($emailmodel->view_table) {
            $view_vars = DB::select('SHOW COLUMNS FROM ' . $emailmodel->view_table);
            $view_vars = array_map(function ($v) {
                return "{{$v->Field}}";
            }, $view_vars);
        }

        return view('pages.email.sendtask_form', ['emailmodel' => $emailmodel, 'view_vars' => $view_vars]);
    }

    public function restoreEmail($row_id)
    {
        $success = false;
        if ($row_id > 0) {
            $emailtmodel = Emailmodel::findOrFail($row_id);
            $emailtmodel->custom_header = $emailtmodel->default_header;
            $emailtmodel->custom_content = $emailtmodel->default_content;
            $emailtmodel->custom_footer = $emailtmodel->default_footer;
            $emailtmodel->save();
            $id = $emailtmodel->id;
            if ($id > 0) {
                $success = true;
            }
        }
        return response()->json([
            'success' => $success,
        ]);
    }

    public function createEmailOverview($documentmodel_id)
    {
        $dm = Emailmodel::find($documentmodel_id);
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $dn = Carbon::now();
        return view('pages.email.model', ['htmlMain' => $content, 'htmlHeader' => $header, 'htmlFooter' => $footer]);
    }

    public function sendMailTask($row_id)
    {
        $task = Task::find($row_id);
        $emailmodels = Emailmodel::select('id', 'code', 'name')->get();

        return view('pages.task.emailmodel', compact('task', 'emailmodels'));
    }

    public function processSendMailTask(Request $request, $task_id)
    {
        $success = false;
        $message = 'Erreur lors d\'envoi';
        $task = Task::find($task_id);

        if ($task->contact_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $contact = Contact::findOrFail($task->contact_id);
            if ($contact->email) {
                /* Mail */
                $content = $DbHelperTools->prepareMailContent($request->documentmodel_id, $task_id, $request);
                $fullname = $contact->firstname . " " . $contact->lastname;

                Mail::send('pages.email.model', ['htmlMain' => $content['content'], 'htmlHeader' => $content['header'], 'htmlFooter' => $content['footer']], function ($m) use ($contact, $fullname, $request) {
                    $m->from(auth()->user()->email);
                    $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
                    $m->to($contact->email, $fullname)->subject($request->subject);
                    if ($request->has('attachments')) {
                        foreach ($request->attachments as $attachment) {
                            $m->attach($attachment->path(), [
                                'as' => $attachment->getClientOriginalName(),
                                'mime' => $attachment->getClientMimeType(),
                            ]);
                        }
                    }
                });

                $success = true;
                $message = 'Mail envoyé avec succès.';
            } else {
                $message = 'Pas de mail pour le contact.';
            }
        }

        return response()->json([
            'msg' => $message,
            'success' => $success,
        ]);
    }

    public function createComment(Request $request)
    {
        $success = false;
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();
        if (count($dtRequests) > 0) {
            $result = $DbHelperTools->manageComment($dtRequests);
            $success = true;
        } else {
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'result' => $result
        ]);
    }


    public function sdtSubTasks(Request $request, $taskid)
    {
        $ids = explode(",", $taskid);

        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();

        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = [];
        if (count($ids) > 0) {
            foreach ($ids as $key => $value) {
                $datas = Task::where('id', $value)->get();

                foreach ($datas as $d) {
                    $row = array();

                    //ID task
                    $row[] = $d->id;
                    $row[] = '<p class="text-info font-size-xs">' . $d->title . '</p>';

                    if (isset($d->type_id)) {
                        $type_id_value = Param::findOrFail($d->type_id);
                        $row[] = '<p class="text-info font-size-xs" style="max-width: 160px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">' . $type_id_value->code . '</p>';
                    } else {
                        $row[] = '--';
                    }

                    if (isset($d->source_id)) {
                        $source_id_value = Param::findOrFail($d->source_id);
                        $row[] = '<p class="text-info font-size-xs">' . $source_id_value->code . '</p>';
                    } else {
                        $row[] = '--';
                    }

                    if ($d->etat_id > 0) {
                        $etat_id_value = Param::findOrFail($d->etat_id);
                        if ($etat_id_value->code == "Annulée") {
                            if($d->is_read == 0){
                                $row[] = '<div style="display: ruby;"><p class="text-info font-size-xs"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAByElEQVQ4jaWTwUsbQRjF38xuJsnqikYxWEUMRVeLF0HUag6KS0D/AL3orUcFr1K8Fo8e9KCXQvs3SIthzSGoPRhvCYu9VKuCIamsm262uy7Ti4bENJriu8037/f4HswALxQpP3ybmprllO4+SXA++3Z//0tVQGJyUpT8/u89Q0M9TeHwP9nbbBZnqdRPms+/Hk6lXACgD5eSKC5LstxWCwaApvZ2BJubW7zW1qWKDZLRaAtraDjrnZiQg7JcAgIrKwAAe2OjNLMLBZwmkyY8LzKqaXkKAH5JWg91dvrK4VoKNDYi1NXFiM/3AQDokaoOcGCxQ1ECz9L36lAUPzhfOIjFBqkgCNuvFIUJjNXLQ2AM4b6+gI+QHQqA87rRMhECUOqSw1isXwRO3kxPB+vdwnNdZDTNguOM0PG9PZ0Dn6503X5sZPPzYHNzVQFXuv6HE/J5JJFIUwBwLGv15vLSLZpmpZPzqoJ2oYCbiwsHrvseAAQA+Hh+br+LRIq2YURD3d2lHl46DS+TqQj4cXz823GctbF4PAGUvcTi3d2mZZq522y2Znfj+hqWYfwSc7mth1nFZzpSVRWcx2smAODAzLimfX3K81/6CxXCniK7MbuvAAAAAElFTkSuQmCC">' . $etat_id_value->code . '</p></div></br><div style="color:#F1260A;font-weight: 700;">Non lu</div>';
                            }
                            else{
                                $row[] = '<div style="display: ruby;"><p class="text-info font-size-xs"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAByElEQVQ4jaWTwUsbQRjF38xuJsnqikYxWEUMRVeLF0HUag6KS0D/AL3orUcFr1K8Fo8e9KCXQvs3SIthzSGoPRhvCYu9VKuCIamsm262uy7Ti4bENJriu8037/f4HswALxQpP3ybmprllO4+SXA++3Z//0tVQGJyUpT8/u89Q0M9TeHwP9nbbBZnqdRPms+/Hk6lXACgD5eSKC5LstxWCwaApvZ2BJubW7zW1qWKDZLRaAtraDjrnZiQg7JcAgIrKwAAe2OjNLMLBZwmkyY8LzKqaXkKAH5JWg91dvrK4VoKNDYi1NXFiM/3AQDokaoOcGCxQ1ECz9L36lAUPzhfOIjFBqkgCNuvFIUJjNXLQ2AM4b6+gI+QHQqA87rRMhECUOqSw1isXwRO3kxPB+vdwnNdZDTNguOM0PG9PZ0Dn6503X5sZPPzYHNzVQFXuv6HE/J5JJFIUwBwLGv15vLSLZpmpZPzqoJ2oYCbiwsHrvseAAQA+Hh+br+LRIq2YURD3d2lHl46DS+TqQj4cXz823GctbF4PAGUvcTi3d2mZZq522y2Znfj+hqWYfwSc7mth1nFZzpSVRWcx2smAODAzLimfX3K81/6CxXCniK7MbuvAAAAAElFTkSuQmCC">' . $etat_id_value->code . '</p></div></br><div style="color:#72E01C;font-weight: 700;">lu</div>'; 
                            }
                        } elseif ($etat_id_value->code == "Terminée") {
                            if($d->is_read == 0){
                                $row[] = '<div style="display: ruby;"><p class="text-info font-size-xs"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAA+UlEQVQ4jbXRMS8EURQF4I8gaFalVulZClESiX+wiSglCtU2GpVE42+optIgarOJQqi2UFKIks4WNqPYt/LyzIxVOMlJ5p53z7nv3WGAJnL08IxjTBoRTbzhADvYQgcZVrHyW0AezHCHDTTwik98YLEuoIcFzOIJm0HPUARe1AW8YB2HOMdU0DtRQIHtqoCj0DwXabuJucCjisVO4MzgzVkyeT9wWLfrnrKGk2TqXuCwfsd8bBqPvm/TQ0xjJqobYUgpltBPbnAZGGt9LKfmMdz4ubh7PJToefB8o1XSVOA0sOysFQd0K5rq2GXwC+E6WdYouPpj/z/hC91aYBcjBs67AAAAAElFTkSuQmCC">' . $etat_id_value->code . '</p></div></br><div style="color:#F1260A;font-weight: 700;">Non lu</div>';
                            }
                            else{
                                $row[] = '<div style="display: ruby;"><p class="text-info font-size-xs"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAA+UlEQVQ4jbXRMS8EURQF4I8gaFalVulZClESiX+wiSglCtU2GpVE42+optIgarOJQqi2UFKIks4WNqPYt/LyzIxVOMlJ5p53z7nv3WGAJnL08IxjTBoRTbzhADvYQgcZVrHyW0AezHCHDTTwik98YLEuoIcFzOIJm0HPUARe1AW8YB2HOMdU0DtRQIHtqoCj0DwXabuJucCjisVO4MzgzVkyeT9wWLfrnrKGk2TqXuCwfsd8bBqPvm/TQ0xjJqobYUgpltBPbnAZGGt9LKfmMdz4ubh7PJToefB8o1XSVOA0sOysFQd0K5rq2GXwC+E6WdYouPpj/z/hC91aYBcjBs67AAAAAElFTkSuQmCC">' . $etat_id_value->code . '</p></div></br><div style="color:#72E01C;font-weight: 700;">lu</div>';
                            }
                        } else {
                            if($d->is_read == 0){
                                $row[] = '<p class="text-info font-size-xs">' . $etat_id_value->code . '</p></br><p style="color:#F1260A;font-weight: 700;">Non lu</p></br>';
                            }
                            else{
                                $row[] = '<p class="text-info font-size-xs">' . $etat_id_value->code . '</p></br><p style="color:#72E01C;font-weight: 700;">lu</p>';
                            }
                        }
                    } else {
                        if($d->is_read == 0){
                            $row[] = '--</br><div style="color:#F1260A;font-weight: 700;">Non lu</div>';
                        }
                        else{
                            $row[] = '--</br><div style="color:#72E01C;font-weight: 700;">lu</div>';
                        }
                    }

                    $row[] = '<p class="text-info font-size-xs">' . $d->description . '</p>';

                    if (isset($d->apporteur_id)) {
                        $apporteur_value = Contact::findOrFail($d->apporteur_id);
                        $row[] = '<p class="text-info font-size-xs">' . $apporteur_value->firstname . ' ' . $apporteur_value->lastname  . '</p>';
                    } else {
                        $row[] = '--';
                    }

                    if ($d->apporteur_id > 0) {
                        $responsable_value = Contact::findOrFail($d->responsable_id);
                        $row[] = '<p class="text-info font-size-xs">' . $responsable_value->firstname . ' ' . $responsable_value->lastname . '</p>';
                    } else {
                        $row[] = '--';
                    }

                    $started_at = '--';
                    if ($d->start_date != null) {
                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->start_date);
                        $started_at = $dt->format('d/m/Y');
                    }
                    $ended_at = '--';
                    if ($d->ended_date != null) {
                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->ended_date);
                        $ended_at = $dt->format('d/m/Y');
                    }
                    $callback_date = '--';
                    if ($d->callback_date != null) {
                        $dt = Carbon::createFromFormat('Y-m-d H:i:s', $d->callback_date);
                        $callback_date = $dt->format('d/m/Y');
                    }
                    $pInfos = '<p class="font-size-xs"><strong>Date début :</strong> ' . $started_at . '</p>';
                    $pInfos .= '<p class="font-size-xs"><strong> Date fin :</strong> ' . $ended_at . '</p>';
                    $pInfos .= '<p class="font-size-xs"><strong> Date de rappel :</strong> ' . $callback_date . '</p>';

                    $row[] = $pInfos;

                    if (isset($d->callback_mode)) {
                        $row[] = '<p class="text-info font-size-xs">' . $d->callback_mode . '</p>';
                    } else {
                        $row[] = '--';
                    }

                    if (isset($d->reponse_mode_id)) {
                        $response_mode_id_value = Param::findOrFail($d->reponse_mode_id);
                        $row[] = '<p class="text-info font-size-xs">' . $response_mode_id_value->code . '</p>';
                    } else {
                        $row[] = '--';
                    }


                    if (isset($d->entite_id)) {
                        $entitie_value = Entitie::findOrFail($d->entite_id);
                        $row[] = '<p class="text-info font-size-xs">' . $entitie_value->ref . '</p>';
                    } else {
                        $row[] = '--';
                    }


                    if (isset($d->contact_id)) {
                        $contact_value = Contact::findOrFail($d->contact_id);
                        $row[] = '<p class="text-info font-size-xs">' . $contact_value->firstname . ' ' . $contact_value->lastname  . '</p>';
                    } else {
                        $row[] = '--';
                    }

                    $concerne = "";

                    if ($d->af_id > 0) {
                        $af = \DB::table('af_actions')->Where('id', $d->af_id)->pluck('title');
                        $concerne .= '<p class="font-size-xs"><strong>AF :</strong> ' . $af[0] . '</p>';
                    }

                    if ($d->pf_id > 0) {
                        $pf = \DB::table('pf_formations')->Where('id', $d->pf_id)->pluck('title');
                        $concerne .= '<p class="font-size-xs"><strong>PF :</strong> ' . $pf[0] . '</p>';
                    }

                    $row[] = $concerne;

                    $comment =  Comment::select('description')->where('task_id', $d->id)->pluck('description');
                    $txtcomment = "";
                    foreach ($comment as $elem) {
                        $txtcomment .= '<br/>' . '<span class="text-dark">#</span> ' . $elem;
                    }

                    $row[] = '<p class="text-info font-size-xs">' . $txtcomment . '</p>';

                    //$row[] = '<p class="text-info font-size-xs">' . $d->priority . '</p>';


                    //Actions
                    $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewTask(' . $d->id . ')" title="Détails"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
                    $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formTask(' . $d->id . ')" title="Édition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                    $btn_done = '<button class="btn btn-sm btn-clean btn-icon" onclick="_terminateTask(' . $d->id . ')" title="Terminer"><i class="' . $tools->getIconeByAction('SEND') . '"></i></button>';
                    $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_cancelTask(' . $d->id . ')" title="Annulation"><i class="' . $tools->getIconeByAction('CANCEL') . '"></i></button>';
                    $btn_report = '<button class="btn btn-sm btn-clean btn-icon" onclick="_reportTask(' . $d->id . ')" title="Reporter"><i class="' . $tools->getIconeByAction('REPORT') . '"></i></button>';
                    $btn_transfert = '<button class="btn btn-sm btn-clean btn-icon" onclick="_validateTask(' . $d->id . ')" title="Transférer"><i class="' . $tools->getIconeByAction('VALIDATE') . '"></i></button>';
                    $btn_comment = '<button class="btn btn-sm btn-clean btn-icon" onclick="_generatecomment(' . $d->id . ')" title="Commenter"><i class="' . $tools->getIconeByAction('COMMENT') . '"></i></button>';
                    $btn_subtask = '<button class="btn btn-sm btn-clean btn-icon" onclick="_subTask(' . $d->id . ')" title="Créer sous-tâches"><i class="' . $tools->getIconeByAction('SUBTASK') . '"></i></button>';

                    $btn_send = '';
                    if ($d->contact_id) {
                        $mailto = "mailto:{$contact_value->email}?subject=Tâche {$d->title}&body=Votre Message";
                        $btn_send = '<a class="btn btn-sm btn-clean btn-icon" href="' . $mailto . '" title="Envoi d’email"><i class="' . $tools->getIconeByAction('EMAIL') . '"></i></a>';
                    }

                    $row[] = $btn_view . $btn_edit . $btn_done . $btn_delete . $btn_report . $btn_transfert . $btn_comment . $btn_subtask . $btn_send;

                    $data[] = $row;
                }
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

    public function getSubTasks($taskid)
    {
        $data = null;

        if ($taskid > 0) {
            $data = Task::where('task_parent_id', $taskid)->where('sub_task', 1)->whereNotNull('task_parent_id')->get();
            if (count($data) > 0) {
                return view('pages.task.subtasks', ['data' => $data, 'temoin' => 1]);
            }
            else{
                return view('pages.task.subtasks', ['data' => $data, 'temoin' => 0]);
            }
        }
    }

    public function getstatustasks()
    {
        $types = Param::select('id', 'name')->where([['param_code', 'Type_tache'], ['is_active', 1]])->get();
        $json_data = Task::all();
        return view('pages.task.getstatustasks', compact('types', 'json_data'));
    }

    public function getTicketsTerminerParContact(Request $request)
    {
        $finito = $request->get('finito');
        $selectType =  $request->get('selecttype');

        //$result = Task::select('id', DB::raw('SUBSTRING(title, 1, 20) as title'))->take(10)->get()->toArray();
        $result = Task::select(DB::raw('CONCAT(en_contacts.firstname," ",en_contacts.lastname)as nom'), DB::raw('COUNT(tasks.id) as value'))
            ->join('en_contacts', 'en_contacts.id', 'tasks.responsable_id')
            ->whereRaw($finito == 1 ? 'Date(tasks.ended_date) <= CURDATE()' : 'Date(tasks.ended_date) > CURDATE()');
        if (!empty($selectType)) {
            $result = $result->where('tasks.type_id', $selectType);
        }
        $result = $result->groupBy('nom')->having('value', '>', 0)->get()->toArray();
        //dd($result);
        return response()->json($result);
    }

    public function getTicketsNonTerminer()
    {

        $result = Task::select('par_params.name', DB::raw('COUNT(tasks.id) as value'))->join('par_params', 'par_params.id', 'tasks.etat_id')->groupBy('par_params.id')->get()->toArray();
        //$result = Task::select('par_params.name',DB::raw('COUNT(tasks.id) as value'))->join('par_params', 'par_params.id', 'tasks.type_id')->whereRaw('Date(tasks.ended_date) >= CURDATE()')->groupBy('par_params.id')->get()->toArray();
        // dd($result);
        return response()->json($result);
    }

    function getViewVars(Request $request)
    {
        $vars = DB::select('SHOW COLUMNS FROM ' . $request->view);
        $vars = array_map(function ($v) {
            return $v->Field;
        }, $vars);

        return response()->json($vars);
    }

    public function Content($content, $data)
    {

        $date_debut = $data->start_date ? Carbon::createFromFormat('Y-m-d H:i:s', $data->start_date)->format('d/m/Y') : null;

        $af_title = null;
        $ap_title= null;

        if(isset($data->af_id))
        {
            $af_title = $data->af->title;
        }

        if(isset($data->pf_id))
        {
            $ap_title = $data->pf->title;
        }

        $mod_rep = Param::where('id',$data->reponse_mode_id)->first();


        $type_task = Param::where('id',$data->type_id)->first();


        $replaceValues = [

            '{date_debut}' => $date_debut,

            '{titre}' => $data->title,

            '{type}' => $data->type->param_code,

            '{nom_sup}' => isset($data->apporteur->firstname) ? $data->apporteur->firstname : Auth::user()->name,

            '{prenom_sup}' => isset($data->apporteur->lastname) ? $data->apporteur->lastname : Auth::user()->name,
            
            '{mod_rep}' => $mod_rep->name,

            // '{mod_rep}' => $data->type->name,

            '{titre_af}' => $af_title,

            '{titre_pf}' => $ap_title,

            '{type}' => $type_task->name

        ];

        // dd($data);

        // dd('db '.$data->af_id,$data->pf_id);

        $out = str_replace(array_keys($replaceValues), array_values($replaceValues), $content);


        if (isset($data->pf_id)) {

            $out = str_replace('<li>Produit de formation : {titre_pf}</li>', '<li>Action de formation :' . $data->pf->code . '</li>', $out);

        }else{
            $out = str_replace('<li>Produit de formation : </li>', '', $out);
        }

        if (isset($data->af_id)) {

            $out = str_replace('<li>Action de formation : {titre_pf}</li>', '<li>Action de formation :' . $data->af->code . '</li>', $out);

        }else{
            $out = str_replace('<li>Action de formation : </li>', '', $out);
        }

        // if (isset($data->af_id) && isset($data->pf_id)) {

        //     $out = str_replace('<ul><li>Produit de formation : {titre_af}</li><li>Action de formation : {titre_pf}</li></ul>', '', $out);

        // }

        if (!isset($data->responsable)) {

            $out = str_replace('et sera traitée par {nom_resp} {prenom_resp} pour le {date_fin}', '', $out);

        } else {

            $out = str_replace(['{nom_resp}', '{prenom_resp}'], [$data->responsable->firstname, $data->responsable->lastname], $out);

        }

        if (!isset($data->dateecheance)) {

            $out = str_replace('pour le {date_fin}', '', $out);

        } else {

            $out = str_replace('{date_fin}', $data->dateecheance ?? '', $out);

        }

        // dd($out);

        return $out;

    }

    public function MailContent($content, $data){
        $af= Action::find($data['af_id']);
        $schedules = DB::table('af_schedules')
            ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
            ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
            ->join('af_actions', 'af_actions.id', '=', 'af_sessions.af_id')
            ->select('af_schedules.start_hour','af_schedules.end_hour','af_schedules.duration','af_sessions.title')
            ->where('af_actions.id', $af->id)
            ->orderBy('af_schedules.start_hour', 'desc')
            ->get();

        $output = '';
        
        $scheduleData = $schedules;
        
        if (!$scheduleData->isEmpty()) {
            foreach ($scheduleData as $schedule) {
                $output .= "- {$schedule->title} qui aura lieu le {$schedule->start_hour} pour une durée de {$schedule->duration} heures . <br>";
            }
        }
        $out = str_replace(['- {Nom_seance} qui aura lieu le {date_seance} pour une durée de {duree_seance} heures.', '{Nom_AF}'], [$output, $af->title], $content);
        return $out;
    }

    public function UpdateSubTask(Request $request){
        
        $DbHelperTools = new DbHelperTools();
        $task = Document::find($request->devis_id)->task;

        $updated_task = Task::find($task->id);
        $updated_task->update(['etat_id'=>181]);

        if($request->step==1){
            $task->title ='Demande de contrat de prestation de service formateur sur facture';
            $email = Emailmodel::where('code','ENVOI_CONTRAT_FORMATEUR_SUR_FACTURE')->first();
        }
        else if($request->step==2){
            $task->title ='Demande de facture';
            $task->etat_id =179;
            $email = Emailmodel::where('code','ENVOI_FACTURE_FORMATEUR_SUR_FACTURE')->first();
        }
        else if($request->step==3){
            $task->title ='Facture formateur sur facture envoyer';
            $task->etat_id =179;
        }

        $result = $DbHelperTools->manageSousTask($task);
        $msg = 'Votre demande a été traitée avec succès';
        $contact_id = $task->contact_id;
        if(isset($email)){
            $this->sendEmailformateur($contact_id,$email->default_content ,$email->default_header,$email->default_footer , $email->name);
        }

        return response()->json([
            'success' => $result,
            'msg' => $msg,
        ]);
    }

	public function sendEmailformateur($contact_id,$content, $header, $footer,$subject){

        $contact =Contact::find($contact_id);
        $fullname = $contact->firstname . " " . $contact->lastname;
        $header = "CRFPE";
        $footer = "L’équipe du CRFPE";

        Mail::send('pages.email.model', ['htmlMain' => $content, 'htmlHeader' => $header,
         'htmlFooter' => $footer], function ($m) use ($contact, $fullname, $subject) {
            $m->from(auth()->user()->email);
            $m->bcc(auth()->user()->email);
            $m->to($contact->email, $fullname)->subject($subject);
        });
        $success = true;
		$msg = 'Le mail a été envoyée avec succès';
        return response()->json([
            'success' => $success,
            'msg' => $msg
        ]);
    }


	public function sendEmail(Request $request){
        $DbHelperTools =new DbHelperTools();

        $content = $DbHelperTools->prepareMailContent('Envoi_TASK', $request->id,null);
        $contact =Contact::find($request->contact_id);
        $fullname = $contact->firstname . " " . $contact->lastname;
        $header = "CRFPE";
        $footer = "L’équipe du CRFPE";

        Mail::send('pages.email.model', ['htmlMain' => $request['content'], 'htmlHeader' => $header,
         'htmlFooter' => $footer], function ($m) use ($contact, $fullname, $content) {
            $m->from(auth()->user()->email);
            $m->bcc(auth()->user()->email);
            $m->to($contact->email, $fullname)->subject($content['subject']);
        });
        $success = true;
		$msg = 'Le mail a été envoyée avec succès';
        return response()->json([
            'success' => $success,
            'msg' => $msg
        ]);
    }
}
