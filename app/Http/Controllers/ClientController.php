<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Group;
use App\Models\Action;
use App\Models\Member;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use App\Models\Invoice;
use App\Models\Contract;
use App\Models\Estimate;
use App\Models\Agreement;
use App\Models\Groupment;
use App\Models\Ressource;
use App\Models\Enrollment;
use App\Models\Convocation;
use Illuminate\Http\Request;
use App\Models\Groupmentgroup;
use App\Models\Schedulecontact;
use Illuminate\Support\Facades\DB;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list()
    {
        $page_title = 'Gestion des clients';
        $page_description = '';
        return view('pages.client.list', compact('page_title', 'page_description'));
    }

    public function sdtEntities(Request $request)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        //$datas=Entitie::all();
        $datas = Entitie::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {

                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('ref', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('name', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('acronym', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('naf_code', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('email', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_type') && !empty($request->filter_type)) {
                    $datas->where('entity_type', $request->filter_type);
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
                if ($request->has('filter_is_client') && !empty($request->filter_is_client)) {
                    $datas->where('is_client', $request->filter_is_client);
                }
                if ($request->has('filter_is_funder') && !empty($request->filter_is_funder)) {
                    $datas->where('is_funder', $request->filter_is_funder);
                }
                if ($request->has('filter_is_former') && !empty($request->filter_is_former)) {
                    $datas->where('is_former', $request->filter_is_former);
                }
                if ($request->has('filter_is_stage_site') && !empty($request->filter_is_stage_site)) {
                    $datas->where('is_stage_site', $request->filter_is_stage_site);
                }
                if ($request->has('filter_is_prospect') && !empty($request->filter_is_prospect)) {
                    $datas->where('is_prospect', $request->filter_is_prospect);
                }
            } else {
                $datas = Entitie::orderByDesc('id');
            }
        }
        $recordsTotal = count($datas->get());
        if ($request->length > 0) {
            $start = (int) $request->start;
            $length = (int) $request->length;
            $datas->skip($start)->take($length);
        }
        $fdatas = $datas->orderByDesc('id')->get();
        //dd($fdatas);
        $urlViewEntity = '/view/entity/';
        foreach ($fdatas as $d) {
            $row = array();
            //ID
            // $row[] = $d->id;
            $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" value="' . $d->id . '" class="checkable"/><span></span></label>';
            //Type
            //childs
            $nb_childs = $d->children()->count();
            $spanGroupSociete = '';
            if ($nb_childs > 0) {
                $spanGroupSociete = '<p class="text-primary mt-2">Groupe de ' . $nb_childs . ' société(s)</p>';
            }
            //parents
            $spanSocieteParent = '';
            $parent = ($d->parent) ? ($d->parent->name . ' (' . $d->parent->ref . ')') : '';
            if (isset($parent) && !empty($parent)) {
                $spanSocieteParent = '<p class="text-warning mt-2">Société parente: <a href="' . $urlViewEntity . $d->parent->id . '">' . $parent . '</a></p>';
            }
            $cssClass = ($d->entity_type === 'P') ? 'primary' : 'info';
            $sSage = "";
            if ($d->is_synced_to_sage == 1) {
                $sSage = '<br><p><span class="text-success font-size-sm"><i class="fas fa-check"></i> Sage</span></p>';
            } elseif (!empty($d->sage_errors)) {
                $sage_errors = json_decode($d->sage_errors);
                foreach ($sage_errors as $err_field) {
                    $sSage .= '<br><p><span class="text-danger font-size-sm"><i class="fas fa-times-circle"></i> Sage: ' . $err_field . ' non saisi</span></p>';
                }
            }
            $spanEnType = '<div class="symbol symbol-40 symbol-light-' . $cssClass . ' flex-shrink-0"><span class="symbol-label font-size-h4 font-weight-bold">' . $d->entity_type . '</span>' . $sSage;
            $row[] = $spanEnType . ' ' . $spanGroupSociete . $spanSocieteParent;
            //Name
            $spanRef = '<div class="text-dark-75 mb-2">' . $d->ref . '</div>';
            $spanName = '<div class="text-dark-75 font-weight-bolder mb-2"><a href="' . $urlViewEntity . $d->id . '">' . $d->name . '</a></div>';
            $spanName .= '<div class="text-body mb-2">' . $d->acronym . '</div>';
            $row[] = $spanRef . $spanName;
            //Contact
            $contact = Contact::where([['entitie_id', $d->id], ['is_main_contact', 1]])->first();
            $spanContactPrincipal = $sp = '';
            if ($contact) {
                $spanContactPrincipal = '<div class="text-body mb-2"><i class="far fa-user text-' . $cssClass . '"></i> Contact : ' . $contact->firstname . ' ' . $contact->lastname . '</div>';
                $spanContactPrincipal .= '<div class="text-body mb-2"><i class="far fa-envelope text-' . $cssClass . '"></i> Email : ' . $contact->email . '</div>';

                $spanContactPrincipal .= ($contact->pro_phone) ? '<div class="text-body mb-2"><i class="fas fa-phone-alt text-' . $cssClass . '"></i> Tél : ' . $contact->pro_phone . '</div>' : '';
                $spanContactPrincipal .= ($contact->pro_mobile) ? '<div class="text-body mb-2"><i class="fas fa-phone-alt text-' . $cssClass . '"></i> Portable : ' . $contact->pro_mobile . '</div>' : '';

                $sp = ($d->entity_type === 'S') ? '<div class="separator separator-dashed mb-2"></div>' : '';
            }
            $spanContact = '';
            if ($d->entity_type === 'S') {
                $spanContact = '<div class="text-body mb-2"><i class="far fa-envelope text-' . $cssClass . '"></i> Email général : ' . $d->email . '</div>';
                $spanContact .= ($d->pro_phone) ? '<div class="text-body mb-2"><i class="fas fa-phone-alt text-' . $cssClass . '"></i> Tél pro : ' . $d->pro_phone . '</div>' : '';
                //$spanContact .= '<div class="text-body mb-2"><i class="fas fa-mobile-alt text-'.$cssClass.'"></i> Portable pro : '.$d->pro_mobile.'</div>';
                $spanContact .= ($d->fax) ? '<div class="text-body mb-2"><i class="fas fa-fax text-' . $cssClass . '"></i> Fax : ' . $d->fax . '</div>' : '';
            }


            $row[] = $spanContactPrincipal . $sp . $spanContact;
            //Informations
            $spanInfosSociete = '';
            if ($d->entity_type === 'S') {
                $spanInfosSociete = ($d->type) ? '<div class="text-body mb-2">Forme: ' . $d->type . '</div>' : '';
                $spanInfosSociete .= ($d->siren) ? '<div class="text-body mb-2">Siren: ' . $d->siren . '</div>' : '';
                $spanInfosSociete .= ($d->siret) ? '<div class="text-body mb-2">Siret: ' . $d->siret . '</div>' : '';
                $spanInfosSociete .= ($d->naf_code) ? '<div class="text-body mb-2">Code NAF: ' . $d->naf_code . '</div>' : '';
                $spanInfosSociete .= ($d->tva) ? '<div class="text-body mb-2">Numéro TVA: ' . $d->tva . '</div>' : '';
                $spanInfosSociete .= '<div class="separator separator-dashed mb-2"></div>';
            }
            $mainAdresse = Adresse::where([['entitie_id', $d->id], ['is_main_entity_address', 1]])->first();
            $spanAdresse = '';
            if ($mainAdresse) {
                $strAdresse = $mainAdresse->line_1;
                $strAdresse .= ($mainAdresse->line_2) ? (' ' . $mainAdresse->line_2) : '';
                $strAdresse .= ($mainAdresse->line_3) ? (' ' . $mainAdresse->line_3) : '';
                $strAdresse .= ($mainAdresse->postal_code) ? (' ' . $mainAdresse->postal_code) : '';
                $strAdresse .= ($mainAdresse->city) ? (' ' . $mainAdresse->city) : '';
                $strAdresse .= ($mainAdresse->country) ? (' ' . $mainAdresse->country) : '';
                $spanAdresse = '<div class="text-body mb-2"><i class="fa fa-map-marker-alt text-' . $cssClass . '"></i> ' . $strAdresse . '</div>';
            }
            $row[] = $spanInfosSociete . $spanAdresse;
            //Roles
            $roles = '';
            if ($d->is_client) {
                $roles .= $tools->constructParagraphLabelDot('xs', $cssClass, 'Client');
            }
            if ($d->is_funder) {
                $roles .= $tools->constructParagraphLabelDot('xs', $cssClass, 'Financeur');
            }
            if ($d->is_former) {
                $roles .= $tools->constructParagraphLabelDot('xs', $cssClass, 'Formateur');
            }
            if ($d->is_stage_site) {
                $roles .= $tools->constructParagraphLabelDot('xs', $cssClass, 'Terrain de stage');
            }
            if ($d->is_prospect) {
                $roles .= $tools->constructParagraphLabelDot('xs', $cssClass, 'Prospect');
            }
            $row[] = $roles;
            //Date
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
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formEntity(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewEntity(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteEntity(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            $row[] = $btn_edit . $btn_view;
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

    public function sdtGroups(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        if ($af_id > 0) {
            $datas = Group::where(['af_id' => $af_id]);
        } else {
            $datas = Group::latest();
        }
        $groups = $datas->orderByDesc('id')->get();


        foreach ($groups as $group) {
            $row = array();

            //ID:
            $row[] = $group->id;

            //<th>Groupe</th>
            $row[] = $group->title;


            //<th>Infos</th>
            $nbr_inscrit = $group->members->count();
            $detail = '<span class="row-details row-details-close" dataid="' . $group->id . '" id="' . $group->id . '"></span> ';
            $row[] = $detail . 'Nombre d\'inscrit : ' . $nbr_inscrit;

            //<th>Dates</th>
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $group->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $group->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at . $updated_at;

            //<th>Actions</th>
            $btn_affectation = '<button class="btn btn-sm btn-clean btn-icon" onclick="_affectationFormGroup(' . $group->id . ')" title="Affectation"><i class="' . $tools->getIconeByAction('GROUP') . '"></i></button>';
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formGroup(' . $group->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';

            $row[] = $btn_edit . $btn_affectation;
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
    public function sdtGroupments(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        if ($af_id > 0) {
            $datas = Groupment::where(['af_id' => $af_id]);
        } else {
            $datas = Groupment::latest();
        }
        $groupments = $datas->orderByDesc('id')->get();


        foreach ($groupments as $groupment) {
            $row = array();

            //ID:
            $row[] = $groupment->id;

            //<th>Groupe</th>
            $row[] = $groupment->name;


            //<th>Infos</th>
            $nbr_inscrit = Groupmentgroup::select('id')->where('groupment_id', $groupment->id)->count();
            $detail = '<span class="row-details row-details-close" dataid="' . $groupment->id . '" id="' . $groupment->id . '"></span> ';
            $row[] = $nbr_inscrit . ' groupe(s) affectées';

            //<th>Dates</th>
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $groupment->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $groupment->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at . $updated_at;

            //<th>Actions</th>
            $btn_affectation = '<button class="btn btn-sm btn-clean btn-icon" onclick="_affectationFormGroupsToGroupment(' . $groupment->id . ')" title="Affectation"><i class="' . $tools->getIconeByAction('LIST') . '"></i></button>';
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formGroupment(' . $groupment->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';

            $row[] = $btn_edit . $btn_affectation;
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


    public function formEntitie($row_id)
    {
        $row = $mainContact = $mainAdresse = null;
        if ($row_id > 0) {
            $row = Entitie::findOrFail($row_id);
            //$row = Entitie::withTrashed()->where('id',$row_id )->first();
            if ($row) {
                $mainContact = Contact::where([['entitie_id', $row->id], ['is_main_contact', 1]])->first();
                $mainAdresse = Adresse::where([['entitie_id', $row->id], ['is_main_entity_address', 1]])->first();
            }
        }

        return view('pages.client.forms.form-entity', ['row' => $row, 'mainContact' => $mainContact, 'mainAdresse' => $mainAdresse]);
    }

    public function storeFormEntitie(Request $request)
    {
        $success = false;
        $msg = '';
        $data = $request->all();

        if ($request->entity_type == 'P') {
            $data['email'] = $request->c_email; //utiliser l'adresse email du contact comme email entity
        }

        $rules = [
            'ref' => ($request->id > 0) ? 'required' : 'required|unique:App\Models\Entitie',
            //'auxiliary_customer_account' => 'nullable|unique:App\Models\Entitie',
            // 'email' => ($request->id > 0) ? 'required' : 'required|unique:App\Models\Entitie',
        ];

        $messages = [
            'ref.unique' => 'Ce code référence est déjà utilisé !',
            //'auxiliary_customer_account.unique' => 'Ce compte auxiliaire est déjà utilisé !',
            // 'email.unique' => 'Cette adresse email est déjà utilisé !',
        ];

        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $msg = '<p>Veuillez vérifier les erreurs ci-dessous : </p>';
            foreach ($errors->get('ref') as $message) {
                $msg .= '<p class="text-danger">' . $message . '</p>';
            }
            foreach ($errors->get('email') as $message) {
                $msg .= '<p class="text-danger">' . $message . '</p>';
            }
            foreach ($errors->get('auxiliary_customer_account') as $message) {
                $msg .= '<p class="text-danger">' . $message . '</p>';
            }
            $success = false;
        } else {
            $DbHelperTools = new DbHelperTools();
            if ($request->entity_type == 'P') {
                $data = [
                    "id" => $request->id,
                    "ref" => ($request->id == 0) ? $DbHelperTools->generateEntityCode() : $request->ref,
                    "entity_type" => $request->entity_type,
                    "name" => $request->c_lastname . ' ' . $request->c_firstname,
                    "pro_phone" => $request->c_pro_phone,
                    "pro_mobile" => $request->c_pro_mobile,
                    "email" => $request->c_email,
                    "is_active" => $request->is_active,
                    "prospecting_area" => $request->prospecting_area,
                    //"collective_customer_account" => $request->collective_customer_account,
                    //"auxiliary_customer_account" => $request->auxiliary_customer_account,
                    "matricule_code" => $request->matricule_code,
                    "personal_thirdparty_code" => $request->personal_thirdparty_code,
                    "vendor_code" => $request->vendor_code,
                    "is_client" => $request->is_client,
                    "is_funder" => $request->is_funder,
                    "is_former" => $request->is_former,
                    "is_prospect" => $request->is_prospect,
                    "iban" => $request->iban,
                    "bic" => $request->bic,
                ];
            }
            //dd($request->birth_date);exit();
            $row_id = $DbHelperTools->manageEntitie($data);
            if ($row_id > 0) {
                //code auxil et collectif
                $en = Entitie::select('id', 'auxiliary_customer_account', 'is_synced_to_sage', 'entity_type')->where('id', $row_id)->first();
                $auxiliary_customer_account = null;
                if (isset($request->auxiliary_customer_account)) {
                    if ($en->is_synced_to_sage == 0) {
                        $auxiliary_customer_account = $request->auxiliary_customer_account;
                    } else {
                        $auxiliary_customer_account = null;
                    }
                } else {
                    // if ($en->is_synced_to_sage == 0 && $en->entity_type != 'S') {
                    //     $auxiliary_customer_account = $DbHelperTools->generateAuxiliaryAccountForEntity($en->id);
                    // } else {
                        //     $auxiliary_customer_account = null;
                        // }
                    $auxiliary_customer_account = $DbHelperTools->generateAuxiliaryAccountForEntity($en->id,$request->a_city);
                }
                //Code auxiliaire update
                if (isset($auxiliary_customer_account)) {
                    Entitie::where('id', $en->id)->update(['auxiliary_customer_account' => $auxiliary_customer_account]);
                }
                //Code collectif
                if ($en->collective_customer_account == null) {
                    $collective_customer_account = $DbHelperTools->generateCodeCollectifs($en->id);
                    Entitie::where('id', $en->id)->update(['collective_customer_account' => $collective_customer_account]);
                }

                //dd($en);
                // if(isset($en) && $en->auxiliary_customer_account==null){
                //     if($en->is_synced_to_sage==0){
                //         $auxiliary_customer_account=$DbHelperTools->generateAuxiliaryAccountForEntity($en->id);
                //         $collective_customer_account=$DbHelperTools->generateCodeCollectifs($en->id);
                //         //Entitie::where('id', $en->id)->update(['auxiliary_customer_account' => $auxiliary_customer_account,'collective_customer_account' => $collective_customer_account]);
                //     }
                // }
                //save contact
                $dataContact = array(
                    "id" => $request->c_id,
                    "entitie_id" => $row_id,
                    "is_main_contact" => 1, //par default
                    "is_billing_contact" => $request->c_is_billing_contact,
                    "is_order_contact" => $request->is_order_contact,
                    "is_active" => 1, //par default
                    "is_trainee_contact" => $request->is_trainee_contact,
                    "gender" => $request->c_gender,
                    "firstname" => $request->c_firstname,
                    "lastname" => $request->c_lastname,
                    "email" => $request->c_email,
                    "function" => $request->c_function,
                    "pro_phone" => $request->c_pro_phone,
                    "pro_mobile" => $request->c_pro_mobile,
                    "birth_date" => (isset($request->birth_date) && !empty($request->birth_date)) ? Carbon::createFromFormat('d/m/Y', $request->birth_date) : null,
                    "type_former_intervention" => ($request->has('c_type_former_intervention')) ? $request->c_type_former_intervention : null,
                    "is_former" => ($request->has('is_former')) ? $request->is_former : 0,
                    "student_status" => ($request->has('student_status')) ? $request->student_status : null,
                    "student_status_date" => ($request->has('student_status_date') && !empty($request->student_status_date)) ? Carbon::createFromFormat('d/m/Y', $request->student_status_date) : null,
                );
                //dd($dataContact);exit();
                $contact_id = $DbHelperTools->manageContact($dataContact);
                //Save adresse
                $dataAdresse = array(
                    "id" => $request->a_id,
                    "entitie_id" => $row_id,
                    "line_1" => $request->a_line_1,
                    "line_2" => $request->a_line_2,
                    "line_3" => $request->a_line_3,
                    "postal_code" => $request->a_postal_code,
                    "city" => $request->a_city,
                    "country" => $request->a_country,
                    "is_main_entity_address" => 1,
                    "is_billing" => 1, //facturation par defaut
                    "is_formation_site" => ($request->has('a_is_formation_site')) ? $request->a_is_formation_site : 0,
                    "is_stage_site" => ($request->has('a_is_stage_site')) ? $request->a_is_stage_site : 0,
                    "contact_id" => $contact_id
                );
                $adresse_id = $DbHelperTools->manageAdresse($dataAdresse);
                $success = true;
                $msg = 'Le client a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function constructFormByEntityType($row_id, $entity_type)
    {
        /*
            $entityType == S or P
        */
        $row = $mainContact = $mainAdresse = $ref = null;
        $auxCodes = Entitie::select('auxiliary_customer_account')
            ->where('id', '!=', $row_id)
            ->where('auxiliary_customer_account', '!=', NULL)
            ->pluck('auxiliary_customer_account')->toArray();

        // var_dump(json_encode($auxCodes));exit;

        if ($row_id > 0) {
            $row = Entitie::findOrFail($row_id);
            if ($row) {
                $mainContact = Contact::where([['entitie_id', $row_id], ['is_main_contact', 1]])->first();
                $mainAdresse = Adresse::where([['entitie_id', $row_id], ['is_main_entity_address', 1]])->first();
            }
        }
        if ($row_id == 0) {
            $DbHelperTools = new DbHelperTools();
            $ref = $DbHelperTools->generateEntityCode();
        }
        $countriesDatas = json_decode(file_get_contents('custom/json/countries.json'), true);
        //dd(count($countriesDatas));
        return view('pages.client.construct.form', [
            'row' => $row, 'ref' => $ref, 'mainContact' => $mainContact, 'mainAdresse' => $mainAdresse, 'entityType' => $entity_type, 'countriesDatas' => $countriesDatas, 'auxCodes' => json_encode($auxCodes)
        ]);
    }

    public function viewEntity($row_id)
    {
        $page_title = 'Détails client';
        $page_description = '';
        $row = null;

        if ($row_id > 0) {
            $row = Entitie::findOrFail($row_id);
        }
        return view('pages.client.view', ['entity' => $row], compact('page_title', 'page_description'));
    }

    public function constructViewContentEntity($viewtype, $entity_id)
    {
        /*  $viewtype==overview
            $viewtype==entity
            $viewtype==contacts
            $viewtype==adresses
        */
        $row = null;
        $type_establishment = '';
        if ($entity_id > 0) {
            //if($viewtype=='entity'){
            $row = Entitie::findOrFail($entity_id);
            $DbHelperTools = new DbHelperTools();
            $type_establishment = $DbHelperTools->getNameParamByCode($row->type_establishment);
            //}
        }
        return view('pages.client.construct.viewcontententity', ['row' => $row, 'viewtype' => $viewtype, 'type_establishment' => $type_establishment]);
    }

    public function sdtContactsMember(Request $request, $af_id, $group_id)
    {
        $dtRequests = $request->all();
        $data = $meta = [];

        $ids_enrollments = Enrollment::select('id')->where(['af_id' => $af_id, 'enrollment_type' => 'S'])->pluck('id');

        $idsMembers = Member::select('id')->where('group_id', $group_id)->orWhereNull('group_id')->pluck('id');

        $members = Member::whereIn('enrollment_id', $ids_enrollments)->whereIn('id', $idsMembers)->get();
        $ids_group_members = Member::select('id')->whereIn('enrollment_id', $ids_enrollments)->where('group_id', $group_id)->pluck('id');

        foreach ($members as $member) {
            $row = array();
            $checked = '';
            if (count($ids_group_members) && $ids_group_members->contains($member->id)) {
                $checked = 'checked';
            }
            //ID
            $row[] = '<label class="checkbox checkbox-single">
                    <input type="checkbox" name="members[]" value="' . $member->id . '" class="checkable" ' . $checked . '/>
                    <span></span>
                    </label>';
            //Contact:
            $row[] = ($member->contact) ? ($member->contact->firstname . ' ' . $member->contact->lastname) : $member->unknown_contact_name;

            //Client:
            $cssClass = ($member->contact != null && $member->contact->entitie != null && $member->contact->entitie->entity_type === 'P') ? 'primary' : 'info';
            $spanEnType = '<div class="symbol symbol-40 symbol-light-' . $cssClass . ' flex-shrink-0"><span class="symbol-label font-size-h4 font-weight-bold">' . ($member->contact != null ? $member->contact->entitie->entity_type : '') . '</span>';
            $row[] = ($member->contact != null ? $member->contact->entitie->name : '') . ' - ' . $spanEnType;


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

    public function sdtContacts(Request $request, $entity_id)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];

        $userid = auth()->user()->id;
        $roles = auth()->user()->roles;

        if ($roles[0]->code == 'FORMATEUR') {
            $contactid = DB::table('users')
                ->where('id', $userid)
                ->pluck('contact_id');

            if (isset($contactid)) {
                $datas = Contact::whereIn('id', $contactid);
            } else {
                $datas = Contact::latest();
            }

            if ($request->isMethod('post')) {
                if ($request->has('filter')) {

                    if ($request->has('filter_text') && !empty($request->filter_text)) {
                        $datas->where('firstname', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('lastname', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('email', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('pro_phone', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('pro_mobile', 'like', '%' . $request->filter_text . '%');
                    }
                    if ($request->has('filter_type') && !empty($request->filter_type)) {
                        $data_temp = DB::table('en_contacts')
                            ->join('en_entities', 'en_contacts.entitie_id', '=', 'en_entities.id')
                            ->where('en_entities.entity_type', 'like', $request->filter_type)
                            ->select('en_contacts.*')
                            ->get();
                        $datas = $data_temp;
                        //$datas->where('entitie_id', $request->filter_type);
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
                }
            }

            if ($entity_id > 0) {
                $datas->where('entitie_id', $entity_id);
            }

            $recordsTotal = count($datas->get());
            if ($request->length > 0) {
                $start = (int) $request->start;
                $length = (int) $request->length;
                $datas->skip($start)->take($length);
            }
            $contacts = $datas->orderByDesc('id')->get();
            // $contacts = $datas;

            foreach ($contacts as $d) {
                $row = array();
                //ID
                $row[] = $d->id;
                //<th>Nom</th>

                $cssClass = ($d->entitie->entity_type === 'P') ? 'primary' : 'info';
                $spanName = '<div class="text-body mb-2"><i class="far fa-user text-' . $cssClass . '"></i> Contact : ' . $d->firstname . ' ' . $d->lastname . '</div>';
                $spanName .= '<div class="text-body mb-2"><i class="far fa-envelope text-' . $cssClass . '"></i> Email : ' . $d->email . '</div>';
                $row[] = $spanName;
                //Infos
                $labelActive = 'Désactivé';
                $cssClassActive = 'danger';
                if ($d->is_active == 1) {
                    $labelActive = 'Activé';
                    $cssClassActive = 'success';
                }
                $spanActive = $tools->constructParagraphLabelDot('xs', $cssClassActive, $labelActive);
                $spanPricipal = '';
                if ($d->is_main_contact) {
                    $spanPricipal = $tools->constructParagraphLabelDot('xs', $cssClass, 'Principal');
                }
                $spanStagiaire = '';
                if ($d->is_trainee_contact) {
                    $spanStagiaire = $tools->constructParagraphLabelDot('xs', $cssClass, 'Stagiaire');
                }
                $spanFormer = '';
                if ($d->is_former) {
                    $spanFormer = $tools->constructParagraphLabelDot('xs', $cssClass, 'Formateur (' . $d->type_former_intervention . ')');
                }
                $spanBilling = '';
                if ($d->is_billing_contact) {
                    $spanBilling = $tools->constructParagraphLabelDot('xs', $cssClass, 'Facturation');
                }
                $spanOrder = '';
                if ($d->is_order_contact) {
                    $spanOrder = $tools->constructParagraphLabelDot('xs', $cssClass, 'Commande');
                }
                $row[] = $spanActive . $spanPricipal . $spanStagiaire . $spanFormer . $spanBilling . $spanOrder;

                //<th>Client</th>
                if ($entity_id == 0) {
                    $cssClass = ($d->entitie->entity_type === 'P') ? 'primary' : 'info';
                    $spanEnType = '<div class="symbol symbol-40 symbol-light-' . $cssClass . ' flex-shrink-0"><span class="symbol-label font-size-h4 font-weight-bold">' . $d->entitie->entity_type . '</span>';
                    $row[] = $d->entitie->name . ' - ' . $spanEnType;
                }
                //<th>Date</th>
                $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                $row[] = $created_at . $updated_at;

                //<th>Actions</th>
                //Actions
                $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formContact(' . $d->id . ',' . $d->entitie_id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewContact(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
                $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteContact(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
                $row[] = $btn_edit;

                $data[] = $row;
            }
        } else {
            if ($entity_id > 0) {
                $datas = Contact::where('entitie_id', $entity_id);
            } else {
                $datas = Contact::latest();
            }

            if ($request->isMethod('post')) {
                if ($request->has('filter')) {

                    if ($request->has('filter_text') && !empty($request->filter_text)) {
                        $datas->where('firstname', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('lastname', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('email', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('pro_phone', 'like', '%' . $request->filter_text . '%')
                            ->orWhere('pro_mobile', 'like', '%' . $request->filter_text . '%');
                    }
                    if ($request->has('filter_type') && !empty($request->filter_type)) {
                        $data_temp = DB::table('en_contacts')
                            ->join('en_entities', 'en_contacts.entitie_id', '=', 'en_entities.id')
                            ->where('en_entities.entity_type', 'like', $request->filter_type)
                            ->select('en_contacts.*')
                            ->get();
                        $datas = $data_temp;
                        //$datas->where('entitie_id', $request->filter_type);
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
                }
            }

            if ($entity_id > 0) {
                $datas->where('entitie_id', $entity_id);
            }

            $recordsTotal = count($datas->get());
            if ($request->length > 0) {
                $start = (int) $request->start;
                $length = (int) $request->length;
                $datas->skip($start)->take($length);
            }
            $contacts = $datas->orderByDesc('id')->get();
            // $contacts = $datas;

            foreach ($contacts as $d) {
                $row = array();
                //ID
                $row[] = $d->id;
                //<th>Nom</th>

                $cssClass = ($d->entitie->entity_type === 'P') ? 'primary' : 'info';
                $spanName = '<div class="text-body mb-2"><i class="far fa-user text-' . $cssClass . '"></i> Contact : ' . $d->firstname . ' ' . $d->lastname . '</div>';
                $spanName .= '<div class="text-body mb-2"><i class="far fa-envelope text-' . $cssClass . '"></i> Email : ' . $d->email . '</div>';
                $row[] = $spanName;
                //Infos
                $labelActive = 'Désactivé';
                $cssClassActive = 'danger';
                if ($d->is_active == 1) {
                    $labelActive = 'Activé';
                    $cssClassActive = 'success';
                }
                $spanActive = $tools->constructParagraphLabelDot('xs', $cssClassActive, $labelActive);
                $spanPricipal = '';
                if ($d->is_main_contact) {
                    $spanPricipal = $tools->constructParagraphLabelDot('xs', $cssClass, 'Principal');
                }
                $spanStagiaire = '';
                if ($d->is_trainee_contact) {
                    $spanStagiaire = $tools->constructParagraphLabelDot('xs', $cssClass, 'Stagiaire');
                }
                $spanFormer = '';
                if ($d->is_former) {
                    $spanFormer = $tools->constructParagraphLabelDot('xs', $cssClass, 'Formateur (' . $d->type_former_intervention . ')');
                }
                $spanBilling = '';
                if ($d->is_billing_contact) {
                    $spanBilling = $tools->constructParagraphLabelDot('xs', $cssClass, 'Facturation');
                }
                $spanOrder = '';
                if ($d->is_order_contact) {
                    $spanOrder = $tools->constructParagraphLabelDot('xs', $cssClass, 'Commande');
                }
                $row[] = $spanActive . $spanPricipal . $spanStagiaire . $spanFormer . $spanBilling . $spanOrder;

                //<th>Client</th>
                if ($entity_id == 0) {
                    $cssClass = ($d->entitie->entity_type === 'P') ? 'primary' : 'info';
                    $spanEnType = '<div class="symbol symbol-40 symbol-light-' . $cssClass . ' flex-shrink-0"><span class="symbol-label font-size-h4 font-weight-bold">' . $d->entitie->entity_type . '</span>';
                    $row[] = $d->entitie->name . ' - ' . $spanEnType;
                }
                //<th>Date</th>
                $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                $row[] = $created_at . $updated_at;

                //<th>Actions</th>
                //Actions
                $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formContact(' . $d->id . ',' . $d->entitie_id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewContact(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
                $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteContact(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
                $row[] = $btn_edit;

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

    public function sdtAdresses(Request $request, $entity_id)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        if ($entity_id > 0) {
            $datas = Adresse::where('entitie_id', $entity_id)->get();
        } else {
            $datas = Adresse::all();
        }

        foreach ($datas as $d) {
            $row = array();
            //ID
            $row[] = $d->id;
            //<th>Adresse</th>
            $spanPricipal = '';
            if ($d->is_main_entity_address) {
                $spanPricipal = '<div class="text-body mb-2"><span class="label label-lg  label-light-info label-inline">Principal</span></div>';
            }
            $spanBilling = '';
            if ($d->is_billing) {
                $spanBilling = '<div class="text-body mb-2"><span class="label label-lg  label-light-info label-inline">Adresse de facturation</span></div>';
            }
            $spanAdresse = '<div class="text-body mb-2"><i class="fa fa-map-marker-alt text-info"></i> Line 1 : ' . $d->line_1 . '</div>';
            $spanAdresse .= '<div class="text-body mb-2"><i class="fa fa-map-marker-alt text-info"></i> Line 2 : ' . $d->line_2 . '</div>';
            $spanAdresse .= '<div class="text-body mb-2"><i class="fa fa-map-marker-alt text-info"></i> Line 3 : ' . $d->line_3 . '</div>';
            $spanAdresse .= '<div class="text-body mb-2"><i class="fa fa-map-marker-alt text-info"></i> Code postale : ' . $d->postal_code . '</div>';
            $spanAdresse .= '<div class="text-body mb-2"><i class="fa fa-map-marker-alt text-info"></i> Ville : ' . $d->city . ' - Pays : ' . $d->country . '</div>';
            $row[] = $spanAdresse;
            //<th>Date</th>
            $created_at = '<div class="mb-2"><span class="label label-outline-info label-inline">C : ' . $d->created_at->format('d/m/Y H:i') . '</span></div>';
            $updated_at = '<div class="mb-2"><span class="label label-outline-info label-inline">M : ' . $d->updated_at->format('d/m/Y H:i') . '</span></div>';
            $row[] = $spanPricipal . $spanBilling . $created_at . $updated_at;
            //<th>Actions</th>
            //Actions
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formAdresse(' . $d->id . ',' . $d->entitie_id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewAdresse(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteAdresse(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
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

    public function formGroup($group_id, $af_id)
    {

        $group = $af = null;

        if ($group_id > 0) {
            $group = Group::findOrFail($group_id);
        }
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
        }
        $referances =  Member::where('af_schedulecontacts.is_former', 0)
            ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
            ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
            ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')->orderBy('en_contacts.firstname', 'asc')->get(['af_members.*'])->unique();
        // $referances = Schedulecontact::where('af_schedulecontacts.is_former', 1)
        // ->join('en_contracts', 'en_contracts.id', '=', 'contract_id')
        // // ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')
        // ->get();

        return view('pages.group.forms.form-group', compact('group', 'af', 'referances'));
    }

    public function formAffectationGroup($group_id, $af_id)
    {
        $group = Group::find($group_id);


        return view('pages.group.forms.form-affectation-group', compact('group', 'af_id'));
    }

    public function storeFormAffectationGroup(Request $request)
    {
        $success = false;
        $msg = 'Aucune affectation n\'est faite !';
        //dd($request->all());
        if ($request->isMethod('post')) {
            //Update group id by null
            $nbRows = Member::where('group_id', $request->GROUP_ID)->update(['group_id' => NULL]);
            if ($request->has('members')) {
                //Update group id
                $nbUpdatedRows = Member::whereIn('id', $request->members)->update(['group_id' => $request->GROUP_ID]);
                if ($nbUpdatedRows > 0) {
                    $success = true;
                    $msg = 'Affectation avec succès.';
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function selectRessourcesOptions($ressource_id = 0)
    {
        $result = [];
        $type_ressource = 'RES_TYPE_LIEU';
        $is_internal = 0;

        $rows = Ressource::select('id', 'name')->where(['type' => $type_ressource, 'is_internal' => $is_internal])->get();

        if (count($rows) > 0) {
            foreach ($rows as $pf) {
                $result[] = ['id' => $pf['id'], 'name' => ($pf['name'])];
            }
        }
        return response()->json($result);
    }

    public function formContact(Request $request, $row_id, $entity_id)
    {
        $row = $entity = null;
        if ($row_id > 0) {
            $row = Contact::findOrFail($row_id);
        }
        if ($entity_id > 0) {
            $entity = Entitie::findOrFail($entity_id);
        }
        $withuser = $request->has('withuser');
        $allentities = $request->has('allentities');
        return view('pages.client.forms.form-contact', compact('row', 'entity', 'withuser', 'allentities'));
    }

    public function formContactComponent($row_id, $entity_id)
    {
        $row = $entity = null;
        if ($row_id > 0) {
            $row = Contact::findOrFail($row_id);
        }
        if ($entity_id > 0) {
            $entity = Entitie::findOrFail($entity_id);
        }
        return view('pages.contact.forms.form-contact-component', compact('row', 'entity'));
    }

    public function storeFormGroup(Request $request)
    {
        // dd($request->referance_id);
        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $dataGroup = array(
                'id' => $request->id,
                'title' => $request->title,
                'af_id' => $request->af_id,
                'ref_contact_id' => $request->referance_id,

            );
            $group_id = $DbHelperTools->manageGroups($dataGroup);
            if ($group_id > 0) {
                $success = true;
                $msg = 'Le groupe a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }


    public function storeFormContact(Request $request)
    {
        $success = false;
        $msg = '';
        //dd($request->all());exit();
        //$data=$request->all();
        $dataContact = array(
            "id" => $request->c_id,
            "entitie_id" => $request->entitie_id,
            "is_main_contact" => $request->c_is_main_contact,
            "is_billing_contact" => $request->c_is_billing_contact,
            "is_order_contact" => $request->is_order_contact,
            "is_trainee_contact" => $request->is_trainee_contact,
            "is_active" => $request->c_is_active,
            "is_valid_accounting" => $request->is_valid_accounting,
            "gender" => $request->c_gender,
            "firstname" => $request->c_firstname,
            "lastname" => $request->c_lastname,
            "email" => $request->c_email,
            "function" => $request->c_function,
            "pro_phone" => $request->c_pro_phone,
            "pro_mobile" => $request->c_pro_mobile,
            "birth_date" => ($request->has('birth_date') && $request->birth_date != null) ? Carbon::createFromFormat('d/m/Y', $request->birth_date) : null,
            "type_former_intervention" => ($request->has('c_type_former_intervention')) ? $request->c_type_former_intervention : null,
            "is_former" => ($request->has('is_former')) ? $request->is_former : 0,
            "birth_name" => $request->birth_name,
            //
            "birth_department" => $request->birth_department,
            "birth_city" => $request->birth_city,
            "social_security_number" => $request->social_security_number,
            "nationality" => $request->nationality,
            "registration_code" => $request->registration_code,

            "student_status" => ($request->has('student_status')) ? $request->student_status : null,
            "student_status_date" => ($request->has('student_status_date') && !empty($request->student_status_date)) ? Carbon::createFromFormat('d/m/Y', $request->student_status_date) : null,
        );
        //dd($data);exit();
        $rules = [
            'email' => ($dataContact['id'] > 0) ? 'required' : 'required:App\Models\Contact',
        ];
        $messages = [
            'email.required' => 'Adresse email est obligatoire !',
        ];
        $validator = Validator::make($dataContact, $rules, $messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $msg = '<p>Veuillez vérifier les erreurs ci-dessous : </p>';
            foreach ($errors->get('email') as $message) {
                $msg .= '<p class="text-danger">' . $message . '</p>';
            }
            $success = false;
        } else {
            $DbHelperTools = new DbHelperTools();
            //avant de sauvegarder il faut checker tous les contacts de l'entité
            Contact::where([['entitie_id', $request->entitie_id], ['is_main_contact', 1]])
                ->update(['is_main_contact' => 0]);
            $contact_id = $DbHelperTools->manageContact($dataContact);

            if ($contact_id && $request->withuser) {
                $contact = Contact::find($contact_id);
                $DbHelperTools->storeUserAccountPersonne($contact, 1, $request->root());
            }

            $success = true;
            $msg = 'Le contact a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formAdresse($row_id, $entity_id)
    {
        $row = null;
        if ($row_id > 0) {
            $row = Adresse::findOrFail($row_id);
        }
        $countriesDatas = json_decode(file_get_contents('custom/json/countries.json'), true);
        return view('pages.client.forms.form-adresse', ['row' => $row, 'entity_id' => $entity_id, 'countriesDatas' => $countriesDatas]);
    }

    public function storeFormAdresse(Request $request)
    {
        $data = $request->all();
        $success = false;
        $msg = 'Veuillez vérifier tous les champs';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $dataAdresse = array(
                "id" => $request->a_id,
                "entitie_id" => $request->a_entitie_id,
                "line_1" => $request->a_line_1,
                "line_2" => $request->a_line_2,
                "line_3" => $request->a_line_3,
                "postal_code" => $request->a_postal_code,
                "city" => $request->a_city,
                "country" => $request->a_country,
                "is_main_entity_address" => $request->a_is_main_entity_address,
                "is_billing" => $request->a_is_billing,
                "is_formation_site" => $request->a_is_formation_site,
                "is_stage_site" => $request->a_is_stage_site,
            );
            $adresse_id = $DbHelperTools->manageAdresse($dataAdresse);
            $success = true;
            $msg = 'L\'adresse a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function sdtSelectContacts(Request $request, $entity_id, $enrollment_id, $is_former)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        if ($entity_id > 0) {
            $datas = Contact::where([['entitie_id', $entity_id], ['is_former', $is_former]])->get();
        } else {
            $datas = Contact::where('is_former', $is_former)->get();
        }
        $ids_contacts = [];
        if ($enrollment_id > 0) {
            $ids_contacts = Member::where('enrollment_id', $enrollment_id)->get()->pluck('contact_id');
        }
        //dd($ids_contacts);
        foreach ($datas as $d) {
            $row = array();
            $checked = '';
            if (count($ids_contacts) && $ids_contacts->contains($d->id)) {
                $checked = 'checked';
            }
            //ID
            $row[] = '<label class="checkbox checkbox-single">
                    <input type="checkbox" name="members[]" value="' . $d->id . '" class="checkable" ' . $checked . '/>
                    <span></span>
                    </label>';
            //<th>Prénom</th>
            $row[] = $d->firstname;
            //Nom
            $row[] = $d->lastname;
            //type d'intervention
            if ($is_former == 1) {
                $row[] = $d->type_former_intervention;
            }
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

    public function getJsonEntitie($entity_id)
    {
        $entity_type = '';
        if ($entity_id > 0) {
            $entity = Entitie::select('id', 'entity_type')->where('id', $entity_id)->first();
            //dd($entity);
            if ($entity) {
                $entity_type = $entity['entity_type'];
            }
        }
        return response()->json(['entity_type' => $entity_type]);
    }

    public function listContacts($contact_id = null)
    {
        $page_title = 'Gestion des contacts';
        $page_description = '';
        $entity_id = 0;
        if ($contact_id > 0) {
            $rs = Contact::select('entitie_id')->where('id', $contact_id)->first();
            $entity_id = $rs->entitie_id;
        }
        return view('pages.contact.list', compact('page_title', 'page_description', 'contact_id', 'entity_id'));
    }

    public function formGroupment($group_id, $af_id)
    {

        $groupment = $af = null;

        if ($group_id > 0) {
            $groupment = Groupment::findOrFail($group_id);
        }
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
        }
        $referances =  Member::where('af_schedulecontacts.is_former', 0)
            ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
            ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
            ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')->orderBy('en_contacts.firstname', 'asc')->get(['af_members.*'])->unique();
        return view('pages.group.forms.form-groupment', compact('groupment', 'af', 'referances'));
    }

    public function storeFormGroupment(Request $request)
    {

        $success = false;
        $msg = 'Veuillez vérifier tous les champs du fomulaire !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $dataGroupment = array(
                'id' => $request->id,
                'name' => $request->name,
                'ref_contact_id' => $request->referance_id,
                'af_id' => $request->af_id,

            );
            $group_id = $DbHelperTools->manageGroupment($dataGroupment);
            if ($group_id > 0) {
                $success = true;
                $msg = 'Le groupement a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formAffectationGroupment($groupment_id, $af_id)
    {
        $groupment = Groupment::find($groupment_id);
        return view('pages.group.forms.form-affectation-groupment', compact('groupment', 'af_id'));
    }

    public function storeFormAffectationGroupment(Request $request)
    {
        $success = false;
        $msg = 'Aucune affectation n\'est faite !';
        //dd($request->all());
        if ($request->isMethod('post')) {
            $deletedRows = Groupmentgroup::where('groupment_id', $request->GROUPMENT_ID)->forceDelete();
            if ($request->has('groups')) {
                $DbHelperTools = new DbHelperTools();
                foreach ($request->groups as $group_id) {
                    $dataGroupmentGroup = array(
                        'id' => 0,
                        'groupment_id' => $request->GROUPMENT_ID,
                        'group_id' => $group_id,
                    );
                    $groupment_group_id = $DbHelperTools->manageGroupmentGroup($dataGroupmentGroup);
                }
                $success = true;
                $msg = 'Affectation avec succès.';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function sdtSelectGroupment(Request $request, $af_id, $groupment_id)
    {
        $dtRequests = $request->all();
        $data = $meta = [];

        $goupes = Group::where('af_id', $af_id)->get();

        $ids_selected_groups = Groupmentgroup::select('group_id')->where('groupment_id', $groupment_id)->pluck('group_id');
        //dd($ids_selected_groups);

        foreach ($goupes as $groupe) {
            $row = array();
            $checked = '';
            if (count($ids_selected_groups) && $ids_selected_groups->contains($groupe->id)) {
                $checked = 'checked';
            }
            //ID
            $row[] = '<label class="checkbox checkbox-single">
                    <input type="checkbox" name="groups[]" value="' . $groupe->id . '" class="checkable" ' . $checked . '/>
                    <span></span>
                    </label>';
            //Groupe:
            $row[] = $groupe->title . ' <span class="text-primary">(' . $groupe->members->count() . ' inscrit(s))</span>';
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
    public function formUnknownContact($member_id, $enrollment_id)
    {
        $entity = null;
        $entitie_id = 0;
        if ($enrollment_id > 0) {
            $rs = Enrollment::select('entitie_id')->where('id', $enrollment_id)->first();
            $entitie_id = $rs->entitie_id;
            $entity = Entitie::select('ref', 'entity_type', 'name')->where('id', $entitie_id)->first();
        }
        return view('pages.client.forms.form-unknown-contact', compact('entitie_id', 'entity', 'enrollment_id', 'member_id'));
    }

    public function storeFormUnknownContact(Request $request)
    {
        $success = false;
        $msg = 'Ooops!';
        //dd($request->all());
        $enrollment_id = 0;
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $enrollment_id = $request->enrollment_id;
            $dataContact = array(
                "id" => 0,
                "entitie_id" => $request->entitie_id,
                "is_active" => 1,
                "gender" => $request->gender,
                "firstname" => $request->firstname,
                "lastname" => $request->lastname,
                "email" => $request->email,
            );
            $contact_id = $DbHelperTools->manageContact($dataContact);
            if ($contact_id > 0) {
                $member_id = $request->member_id;
                Member::where('id', $member_id)->update(['contact_id' => $contact_id]);
            }
            $success = true;
            $msg = 'Le contact a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'enrollment_id' => $enrollment_id,
        ]);
    }
    public function fixEntitiesRef()
    {
        $success = true;
        $rs = Entitie::select('id', 'ref')->where('ref', '')->get();
        if (count($rs) > 0) {
            foreach ($rs as $e) {
                $code = 'CLI' . sprintf('%07d', $e->id);
                //dd($code);
                Entitie::where('id', $e->id)->update(['ref' => $code]);
            }
        }
        //dd($rs);
        return response()->json([
            'success' => $success,
        ]);
    }
    public function sdtEntityDocuments(Request $request, $entity_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        if ($request->isMethod('post')) {
        }
        $documentTypes = array(
            0 => 'Contrats intervenants',
            1 => 'Devis',
            2 => 'Conventions & contrats',
            3 => 'Convocations',
            4 => 'Factures',
            5 => 'Attestations'
        );
        foreach ($documentTypes as $k => $type) {
            /* $row = array();
            // <th>Type</th>
            $row[] =$type;
            // <th>Document</th>
            $row[] ='';
            // <th>AF</th>
            $row[] ='';
            // <th>Dates</th>
            $row[] ='';
            // <th>Actions</th>
            $row[] ='';
            $data[] = $row;  */
            if ($k == 0) { //Contrats intervenants
                $ids_contacts = Contact::select('id')->where('entitie_id', $entity_id)->pluck('id');
                $datas = Contract::whereIn('contact_id', $ids_contacts)->orderBy('id', 'desc')->get();
                foreach ($datas as $d) {
                    $row = array();
                    // <th>Type</th>
                    $row[] = $type;
                    // <th>Document</th>
                    $exist = $DbHelperTools->getSchedulecontactsWithoutContracts($d->contact_id);
                    $spanAlert = ($exist) ? '<p class="text-warning font-size-sm"><i class="flaticon-warning-sign text-warning"></i> Il existe des séances non rattachées au contrat</p>' : '';
                    $nameContact = $d->contact != null ? ('<br><span class="font-size-sm">' . $d->contact->gender . ' ' . $d->contact->lastname . ' ' . $d->contact->firstname . '</span>') : '';
                    $totalCost = $DbHelperTools->getTotalPriceContractFormer($d->id);
                    $amount = '<p class="text-info font-size-sm">Montant : ' . number_format($totalCost, 2) . ' €</p>';
                    $row[] = '<span class="text-primary">' . $d->number . '</span> ' . $nameContact . $spanAlert . $amount;
                    // <th>AF</th>
                    $spanState = $tools->constructParagraphLabelDot('xs', 'primary', 'Etat : ' . $DbHelperTools->getNameParamByCode($d->state));
                    $spanStatus = $tools->constructParagraphLabelDot('xs', 'primary', 'Status : ' . $DbHelperTools->getNameParamByCode($d->status));
                    $row[] = $spanState . $spanStatus;
                    // <th>Dates</th>
                    $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                    $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                    $row[] = $created_at . $updated_at;
                    // <th>Actions</th>
                    $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                        data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                            <ul class="navi flex-column navi-hover py-2">
                                <li class="navi-item">
                                    <a target="_blank" href="/pdf/contract/' . $d->id . '/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>
                                    <a target="_blank" href="/pdf/contract/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>
                                </li>
                            </ul>
                        </div>
                    </div>';
                    $row[] = $btn_more;
                    $data[] = $row;
                }
            }
            if ($k == 1) { //devis
                $datas = Estimate::where('entitie_id', $entity_id)->get();
                foreach ($datas as $d) {
                    $row = array();
                    // <th>Type</th>
                    $row[] = $type;
                    // <th>Document</th>
                    $arr = $DbHelperTools->getAgreementByEstimate($d->id);
                    $agreementNumber = ($arr['number']) ? ('<p>' . $arr['agreement_type'] . ' : ' . $arr['number'] . '</p>') : '';
                    $status = $DbHelperTools->getParamByCode($d->status);
                    $pStatus = '<p><span class="label label-sm label-light-' . $status['css_class'] . ' label-inline">' . $status['name'] . '</span></p>';
                    $calcul = $DbHelperTools->getAmountsEstimate($d->id);
                    $amount = '<p class="text-info font-size-sm">Montant : ' . $calcul['total'] . ' €</p>';
                    $row[] = $pStatus . '<p class="text-' . $status['css_class'] . '">DEVIS #' . $d->estimate_number . '</p>' . $amount . $agreementNumber;
                    // <th>AF</th>
                    $row[] = '<a href="/view/af/' . $d->af->id . '">' . $d->af->code . '</a>';
                    // <th>Dates</th>
                    $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                    $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                    $row[] = $created_at . $updated_at;
                    // <th>Actions</th>
                    $btn_pdf = '<a target="_blank" href="/pdf/estimate/' . $d->id . '/1" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span></a>';
                    $btn_pdf_download = '<a target="_blank" href="/pdf/estimate/' . $d->id . '/2" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span></a>';
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
                    $row[] = $btn_more;

                    $data[] = $row;
                }
            }
            if ($k == 2) { //Conventions & contrats
                $datas = Agreement::where('entitie_id', $entity_id)->get();
                $arrayLabel = [
                    'contract' => 'Contrat',
                    'convention' => 'Convention',
                    'draft' => 'Brouillon',
                    'sent' => 'Envoyé',
                    'signed' => 'Signé',
                    'canceled' => 'Annulé',
                ];
                $arrayCssLabel = [
                    'draft' => 'info',
                    'sent' => 'warning',
                    'signed' => 'success',
                    'canceled' => 'danger',
                ];
                foreach ($datas as $d) {
                    $row = array();
                    // <th>Type</th>
                    $row[] = $type;
                    // <th>Document</th>
                    $pStatus = '<p><span class="label label-sm label-light-' . $arrayCssLabel[$d->status] . ' label-inline">' . $arrayLabel[$d->status] . '</span></p>';
                    $tAgreement = $arrayLabel[$d->agreement_type] . $pStatus;
                    $pEstimate = '';
                    if ($d->estimate) {
                        $pEstimate = '<p class="text-warning">Devis n° : ' . $d->estimate->estimate_number . '</p>';
                    }
                    $arr = $DbHelperTools->getAgreementStatistics($d->id);
                    $fundings = '<p class="text-primary"><strong>' . $arr['fundings'] . '</strong> financeur(s)</p>';
                    $deadlines = '<p class="text-primary"><strong>' . $arr['invoices'] . '</strong> facture(s)/<strong>' . $arr['deadlines'] . '</strong> échéance(s)</p>';
                    $tNumber = '<p class="text-info">#' . $d->number . '</p>' . $pEstimate . $fundings . $deadlines;
                    $calcul = $DbHelperTools->getAmountsAgreement($d->id);
                    $amount = '<p class="text-info font-size-sm">Montant : ' . number_format($calcul['total'], 2) . ' €</p>';
                    $row[] = $tAgreement . $tNumber . $amount;
                    // <th>AF</th>
                    $row[] = '<p><a href="/view/af/' . $d->af->id . '">' . $d->af->code . '</a><p>';
                    // <th>Dates</th>
                    $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                    $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                    $row[] = $created_at . $updated_at;
                    // <th>Actions</th>
                    $btn_pdf = '<a target="_blank" href="/pdf/agreement/' . $d->id . '/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>';
                    $btn_pdf_download = ' <a target="_blank" href="/pdf/agreement/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>';
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
                    $row[] = $btn_more;
                    $data[] = $row;
                }
            }
            if ($k == 3) { //Convocations
                $datas = Convocation::where('entitie_id', $entity_id)->get();
                $arrayLabel = [
                    'draft' => 'Brouillon',
                    'not_paid' => 'Non payé',
                    'partial_paid' => 'Partiellement payée',
                    'paid' => 'Payée',
                    'canceled' => 'Annulé',
                ];
                $arrayCssLabel = [
                    'draft' => 'info',
                    'not_paid' => 'primary',
                    'partial_paid' => 'warning',
                    'paid' => 'success',
                    'canceled' => 'danger',
                ];
                foreach ($datas as $d) {
                    $row = array();
                    // <th>Type</th>
                    $row[] = $type;
                    // <th>Document</th>
                    $stagiaire = $d->contact_id != null ? 'Stagiaire : ' . $d->contact->firstname . ' - ' . $d->contact->lastname : '';
                    $pStatus = '<p><span class="label label-sm label-light-' . $arrayCssLabel[$d->status] . ' label-inline">' . $arrayLabel[$d->status] . '</span></p>';
                    $row[] = '<p class="text-info">#' . $d->number . '</p>' . $pStatus . $stagiaire;
                    // <th>AF</th>
                    $row[] = '<a href="/view/af/' . $d->af->id . '">' . $d->af->code . '</a>';
                    // <th>Dates</th>
                    $row[] = '';
                    // <th>Actions</th>
                    $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                        data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                            <ul class="navi flex-column navi-hover py-2">
                                <li class="navi-item">
                                    <a target="_blank" href="/pdf/convocation/' . $d->id . '/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>
                                    <a target="_blank" href="/pdf/convocation/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>
                                </li>
                            </ul>
                        </div>
                    </div>';
                    $row[] = $btn_more;
                    $data[] = $row;
                }
            }
            if ($k == 4) { //factures
                $datas = Invoice::where('entitie_id', $entity_id)->get();
                $arrayLabel = [
                    'draft' => 'Brouillon',
                    'not_paid' => 'Non payé',
                    'partial_paid' => 'Partiellement payée',
                    'paid' => 'Payée',
                    'cancelled' => 'Annulé',
                    //
                    'cvts_ctrs' => 'Facture convention/contrat',
                    'students' => 'Facture étudiant',
                ];
                $arrayCssLabel = [
                    'draft' => 'info',
                    'not_paid' => 'primary',
                    'partial_paid' => 'warning',
                    'paid' => 'success',
                    'cancelled' => 'danger',
                    //
                    'cvts_ctrs' => 'primary',
                    'students' => 'info',
                ];
                $arrayFundingOptions = [
                    'contact_itself' => 'Le contact lui meme',
                    'entity_contact' => 'L’entité du contact',
                    'cfa_funder' => 'C financeur',
                    'other_funders' => 'Autre financeur',
                ];
                foreach ($datas as $d) {
                    $inv_af_id = ($d->agreement) ? $d->agreement->af->id : $d->af_id;
                    $af = Action::select('id', 'code', 'title')->find($inv_af_id);
                    $row = array();
                    // <th>Type</th>
                    $row[] = $type;
                    // <th>Document</th>
                    $typeFacture = '<p class="text-' . $arrayCssLabel[$d->invoice_type] . '">' . $arrayLabel[$d->invoice_type] . '</p>';
                    $pAgreement = '';
                    if ($d->agreement) {
                        $btn_pdf_agreement = ' <a target="_blank" href="/pdf/agreement/' . $d->agreement->id . '/1" title="Pdf"><i class="fas fa-external-link-alt"></i></a>';
                        $pAgreement = '<p class="text-warning">' . $d->agreement->agreement_type . ' n° : ' . $d->agreement->number . $btn_pdf_agreement . '</p>';
                    }
                    $pStatus = '<p><span class="label label-sm label-light-' . $arrayCssLabel[$d->status] . ' label-inline">' . $arrayLabel[$d->status] . '</span></p>';

                    $pfunding_option = '<p class="font-size-sm text-warning">Option de financement : ' . $arrayFundingOptions[$d->funding_option] . '</p>';

                    $arrayRefund = $DbHelperTools->getRefundByInvoice($d->id);
                    $pRefund = '';
                    if ($arrayRefund['id'] > 0) {
                        $pRefund = '<p class="text-danger"><a class="text-danger font-size-sm" target="_blank" href="/pdf/refund/' . $arrayRefund['id'] . '/1">AVOIR N° : #' . $arrayRefund['number'] . '</a></p>';
                    }
                    $sSage = "";
                    if ($d->is_synced_to_sage == 1) {
                        $sSage = '<br><p><span class="text-success font-size-sm"><i class="fas fa-check"></i> Sage</span></p>';
                    } elseif (!empty($d->sage_errors)) {
                        $sage_errors = json_decode($d->sage_errors);
                        foreach ($sage_errors as $err_field) {
                            $sSage .= '<br><p><span class="text-danger font-size-sm"><i class="fas fa-times-circle"></i> Sage: ' . $err_field . ' non saisi</span></p>';
                        }
                    }
                    $calcul = $DbHelperTools->getAmountsInvoice($d->id);
                    $amount = '<p class="text-info font-size-sm">Montant : ' . number_format($calcul['total'], 2) . ' €</p>';
                    $row[] = $typeFacture . '<p class="text-info">#' . $d->number . $pStatus . '</p>' . $pRefund . $pfunding_option . $sSage . $amount;
                    // <th>AF</th>
                    $row[] = '<a href="/view/af/' . $af->id . '">' . $af->code . '</a>';
                    // <th>Dates</th>
                    $dtBillDate = Carbon::createFromFormat('Y-m-d', $d->bill_date);
                    $dtIssueDate = Carbon::createFromFormat('Y-m-d', $d->due_date);
                    $bill_date = $tools->constructParagraphLabelDot('xs', 'success', 'Date de facturation : ' . $dtBillDate->format('d/m/Y'));
                    $due_date = $tools->constructParagraphLabelDot('xs', 'danger', 'Date d\'échéance : ' . $dtIssueDate->format('d/m/Y'));
                    $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                    $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                    $row[] = $bill_date . $due_date . $created_at . $updated_at;
                    // <th>Actions</th>
                    $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                        data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                            <ul class="navi flex-column navi-hover py-2">
                                <li class="navi-item">
                                    <a target="_blank" href="/pdf/invoice/' . $d->id . '/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>
                                    <a target="_blank" href="/pdf/invoice/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>
                                </li>
                            </ul>
                        </div>
                    </div>';
                    $row[] = $btn_more;
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
}
