<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Group;
use App\Models\Media;
use App\Models\Param;
use App\Models\Action;
use App\Models\Member;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use App\Models\Session;
use App\Models\Schedule;
use App\Models\Attachment;
use App\Models\Enrollment;
use App\Models\FileContact;
use App\Models\Sessiondate;
use Illuminate\Http\Request;
use App\Models\Attachmentlog;
use App\Imports\ContactsImport;
use App\Imports\ContactsImportProspect;
use App\Library\Helpers\Helper;
use App\Models\Schedulecontact;
use App\Models\Internshiproposal;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use App\Models\Task;
use App\Models\User;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class importController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function import()
    {
        $page_title = 'Importation des fichier XLSX';
        $page_description = '';
        return view('pages.import.main', compact('page_title', 'page_description'));
    }

    public function getformFileUpload($category = 'PARCOURSUP')
    {
        //dd(Attachmentlog::all());
        $row = [];
        $mainContact = [];
        $mainAdresse = [];
        return view('pages.import.forms.form-entity', ['row' => $row, 'mainContact' => $mainContact, 'mainAdresse' => $mainAdresse, 'file_category' => $category]);
    }

    public function postFormFileUpload(Request $request)
    {
        $success = false;
        if ($request->hasFile('file_to_upload')) {
            $attachement = $request->file('file_to_upload');
            $filename = time() . md5(rand(1, 100)) . bin2hex(random_bytes(16)) . '.' . $attachement->getClientOriginalExtension();
            $file_category = $request->input('file_category');
            Storage::disk('public_uploads')->putFileAs('fichier excel/contacts/' . ($file_category == 'PROSPECTS' ? 'prospects/' : ''), $attachement, $filename);
            $Attachment_id = Attachment::create([
                'name' => $attachement->getClientOriginalName(),
                'path' => $filename,
                'type' => $attachement->getClientOriginalExtension(),
                'category' => $file_category
            ])->id;
            # echo public_path().'/uploads/fichier excel/contacts/'.$filename;
            // Excel::import(new ContactsImport, 'http:/crfpe.local/uploads/fichier excel/contacts/16382598199778d5d219c5080b9a6a17bef029331c9eee7a1e4da2920a365ded0fb8a8c4d4.xls');
            $contactsImport = $file_category == 'PROSPECTS' ? new ContactsImportProspect($Attachment_id) : new ContactsImport($Attachment_id);
            Excel::import($contactsImport, public_path() . '/uploads/fichier excel/contacts/' . ($file_category == 'PROSPECTS' ? 'prospects/' : '') . $filename);
            $success = true;
        }
        return response()->json([
            'success' => $success,
            'files' => 'good',
        ]);
    }

    public function getFileImport()
    {
        $result = [];
        $files = Attachment::where('category', 'PARCOURSUP')->get();
        if (count($files) > 0) {
            foreach ($files as $en) {
                $result[] = ['id' => $en['id'], 'name' => $en['name'], 'date' => date('d-m-Y', strtotime($en['created_at']))];
            }
        }
        return response()->json($result);
    }
    public function getContactFileImport($file_id)
    {
        $result = [];
        $contact_ids = FileContact::where('file_id', $file_id)->select('contact_id')->pluck('contact_id')->toArray();
        $entite_ids = Contact::whereIn('id', $contact_ids)->select('entitie_id')->pluck('entitie_id')->toArray();
        $entities = Entitie::whereIn('id', $entite_ids)->get();

        if (count($entities) > 0) {
            foreach ($entities as $en) {
                $result[] = ['id' => $en['id'], 'name' => ($en['name'] . ' - ' . $en['ref'] . ' - ' . $en['entity_type'])];
            }
        }

        // dd($contacts[0]->id);
        return response()->json($result);
    }

    public function listContacts($file_id)
    {
        // dd($attachment_id);
        $entity_id = 0;
        $tools = new PublicTools();
        // $dtRequests = $request->all();
        $data = $meta = [];
        $attachements = FileContact::where('file_id', $file_id)->get();
        foreach ($attachements as $att) {
            $d = Contact::find($att->contact_id);
            $row = array();
            $cssClass = ($d && $d->entitie->entity_type === 'P') ? 'primary' : 'info';

            $line_info = json_decode($att->line_info);

            // $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" value="' . $att->id . '" class="checkable"><span></span></label>';
            //Line number
            $row[] = $line_info->line ?? '';
            /* line Info */
            $spanInfoLine = '';
            if ($line_info) {
                $spanInfoLine = '<div class="text-body mb-2"><i class="far fa-user text-' . $cssClass . '"></i> Contact : ' . $line_info->nom . ' ' . $line_info->prenom . '</div>';
                $spanInfoLine .= isset($line_info->date_de_naissance) ? '<div class="text-body mb-2"><i class="fas fa-birthday-cake text-' . $cssClass . '"></i> Naissance : ' . $line_info->date_de_naissance . '</div>' : '';
                $spanInfoLine .= '<div class="text-body mb-2"><i class="fa fa-phone text-' . $cssClass . '"></i> Téléphone : ' . (isset($line_info->telephone_mobile) ? $line_info->telephone_mobile : $line_info->telephone) . '</div>';
                $spanInfoLine .= '<div class="text-body mb-2"><i class="far fa-envelope text-' . $cssClass . '"></i> Email : ' . $line_info->adresse_mail . '</div>';
            }
            $row[] = $spanInfoLine;
            /* Contact ID */
            $row[] = $att->contact_id;

            $spanName = '';
            if ($d) {
                $spanName = '<div class="text-body mb-2"><i class="far fa-user text-' . $cssClass . '"></i> Contact : ' . $d->firstname . ' ' . $d->lastname . '</div>';
                $spanName .= !$d->birth_date ? '' : '<div class="text-body mb-2"><i class="fas fa-birthday-cake text-' . $cssClass . '"></i> Naissance : ' . date_create_from_format('Y-m-d', $d->birth_date)->format('d.m.Y') . '</div>';
                $spanName .= '<div class="text-body mb-2"><i class="far fa-envelope text-' . $cssClass . '"></i> Email : ' . $d->email . '</div>';
                if ($d && $d->entitie && !$d->entitie->adresses->isEmpty()) {
                    $adresse = $d->entitie->adresses->first();
                    $adresse_line = $adresse->line_1 . ' ' . $adresse->line_2 . ' ' . $adresse->line_3 . ' ' . $adresse->postal_code . ' ' . $adresse->city;
                    $spanName .= '<div class="text-body mb-2"><i class="fas fa-map-marker-alt text-' . $cssClass . '"></i> ' . $adresse_line . '</div>';
                }
            }
            $row[] = $spanName;
            //Infos
            $labelActive = 'Désactivé';
            $cssClassActive = 'danger';
            if ($d && $d->is_active == 1) {
                $labelActive = 'Activé';
                $cssClassActive = 'success';
            } elseif (!$d) {
                $labelActive = 'Mémorisé';
                $cssClassActive = 'warning';
            }
            $spanActive = $tools->constructParagraphLabelDot('xs', $cssClassActive, $labelActive);
            $spanPricipal = '';
            if ($d && $d->is_main_contact) {
                $spanPricipal = $tools->constructParagraphLabelDot('xs', $cssClass, 'Principal');
            }
            $spanStagiaire = '';
            if ($d && $d->is_trainee_contact) {
                $spanStagiaire = $tools->constructParagraphLabelDot('xs', $cssClass, 'Stagiaire');
            }
            $spanFormer = '';
            if ($d && $d->is_former) {
                $spanFormer = $tools->constructParagraphLabelDot('xs', $cssClass, 'Formateur (' . $d->type_former_intervention . ')');
            }
            $spanBilling = '';
            if ($d && $d->is_billing_contact) {
                $spanBilling = $tools->constructParagraphLabelDot('xs', $cssClass, 'Facturation');
            }
            $spanOrder = '';
            if ($d && $d->is_order_contact) {
                $spanOrder = $tools->constructParagraphLabelDot('xs', $cssClass, 'Commande');
            }
            $row[] = $spanActive . $spanPricipal . $spanStagiaire . $spanFormer . $spanBilling . $spanOrder;

            //<th>Client</th>
            if ($entity_id == 0) {
                $spanContactName = '';
                if ($d) {
                    $cssClass = ($d && $d->entitie->entity_type === 'P') ? 'primary' : 'info';
                    $spanEnType = '<div class="symbol symbol-40 symbol-light-' . $cssClass . ' flex-shrink-0"><span class="symbol-label font-size-h4 font-weight-bold">' . $d->entitie->entity_type . '</span>';
                    $spanContactName = $d->entitie->name . ' - ' . $spanEnType;
                }
                $row[] = $spanContactName;
            }

            //<th>Date</th>
            $created_at = !$d ? '' : $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            $updated_at = !$d ? '' : $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at . $updated_at;

            $suggested = json_decode($att->suggested_contacts);
            // if ($att->suggested_contacts) {
            //     dd($att->suggested_contacts);
            // }
            $suggested_tab = '';
            if ($suggested) {
                $suggested_count = count($suggested);
                if ($suggested_count == 1) {
                    $sug_contact = Contact::find($suggested[0]);
                    $suggested_tab = '<div class="text-body mb-2">' . $sug_contact->id . ' - ' . $sug_contact->firstname . ' ' . $sug_contact->lastname . '</div>';
                    $suggested_tab .= '<button class="btn btn-sm btn-edit btn-icon" onclick="_selectSuggestedContactWithConfirm(' . $sug_contact->id . ',' . $att->id . ')" title="Validation du contact proposé"><i class="fas fa-check text-success"></i></button>';
                    $suggested_tab .= '<button class="btn btn-sm btn-edit btn-icon" onclick="_declineSugAndId(' . $att->id . ')" title="Refus du contact proposé et saisi d\'un id"><i class="fas fa-ban text-danger"></i></button>';
                    $suggested_tab .= '<button class="btn btn-sm btn-edit btn-icon" onclick="_declineSugAndNew(' . $att->id . ')" title="Refus et création d\'un nouveau contact"><i class="fas fa-plus text-primary"></i></button>';
                } elseif ($suggested_count > 0) {
                    $suggested_tab = '<button class="btn btn-sm" onclick="_showSuggested(' . $att->id . ')" title="Choisir parmis les propositions"><i class="fas fa-user"></i></button> ' . $suggested_count . ' propositions';
                }
            }

            $row[] = $suggested_tab;

            //<th>Actions</th>
            //Actions
            // $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formContact(' . $d->id . ',' . $d->entitie_id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            // $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewContact(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
            // $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteContact(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
            // $row[] = $btn_edit;

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
    public function constructViewImport($viewtype)
    {
        $datafilter = new \stdClass();
        $datafilter->files = Attachment::where('category', $viewtype == 2 ? 'PROSPECTS' : 'PARCOURSUP')->orderBy('id', 'DESC')->get();
        $import_types = Param::where('param_code', 'TASK_TYPE')->orderBy('order_show')->get();
        $import_rsp_modes = Param::where('param_code', 'TASK_RESPONSE_MODE')->orderBy('order_show')->get();
        $import_repsonsables = Contact::join('users', 'users.contact_id', 'en_contacts.id')
        ->join('par_usr_role_user', 'par_usr_role_user.user_id', 'users.id')
        ->join('par_usr_roles', 'par_usr_roles.id', 'par_usr_role_user.role_id')
        ->join('par_usr_profils', 'par_usr_profils.id', 'par_usr_roles.profil_id')
        ->where('par_usr_profils.code', 'CRFPE')
        ->groupBy('en_contacts.id')
        ->get()
        ;
        return view('pages.import.construct.view', compact('viewtype', 'datafilter', 'import_types', 'import_repsonsables', 'import_rsp_modes'));
    }
    public function viewLogs($file_id)
    {
        $title = "Les erreurs du fichier";
        $logs = null;
        if ($file_id > 0) {
            $file = Attachment::find($file_id);
            $title = 'Les erreurs du fichier n° ' . $file->id . ' "' . $file->name . '"';
            $logs = Attachmentlog::where('attachment_id', $file_id)->orderBy('id', 'desc')->get();
        }
        return view('pages.import.construct.logs', compact('logs', 'title'));
    }
    public function showSuggested($attachment)
    {
        $fileContact = FileContact::find($attachment);
        $c_ids = json_decode($fileContact->suggested_contacts);
        $contacts = Contact::whereIn('id', $c_ids)->get();
        $title = "Choisir parmis les propositions";
        return view('pages.import.construct.suggested', compact('contacts', 'title', 'attachment'));
    }
    public function selectSuggested($contactId, $attachment)
    {
        $DbHelperTools = new DbHelperTools();
        $fileContact = FileContact::find($attachment);
        $contact = Contact::find($contactId);
        $row = json_decode($fileContact->line_info);
        $birth_date = null;

        if (isset($row->date_de_naissance) && date_create_from_format('d/m/Y', $row->date_de_naissance)) {
            $birth_date = date_create_from_format('d/m/Y', $row->date_de_naissance)->format('Y-m-d');
        }

        try {
            /* Entitie save */
            $entity_id = $contact->entitie->id ?? 0;
            $entity_exists = $entity_id > 0;

            $entity_data = array(
                'id' => $entity_id,
                'ref' => $DbHelperTools->generateEntityCode(),
                'entity_type' => 'P',
                'name' => $row->nom . ' ' . $row->prenom,
                'pro_phone' => $row->telephone_mobile,
                'pro_mobile' => $row->telephone_mobile,
                'email' => $row->adresse_mail,
                'is_client' => 1,
                'is_active' => 1,
            );
            $entity_id = $DbHelperTools->manageEntitie($entity_data);
            $entitie = Entitie::find($entity_id);

            /* SAVE CONTACT + FILECONTACT */
            if ($entity_id > 0) {
                $adresse_array = array(
                    'entitie_id' => $entity_id,
                    'line_1' => $row->adresse_1,
                    'postal_code' => $row->code_postal,
                );
                $adrRow = Adresse::select('id')->where($adresse_array)->first();
                $adresse_id = ($adrRow) ? $adrRow->id : 0;
                $dataAdresse = array(
                    "id" => $adresse_id,
                    "entitie_id" => $entity_id,
                    "line_1" => $row->adresse_1,
                    "line_2" => $row->adresse_2,
                    "line_3" => $row->adresse_3,
                    "postal_code" => $row->code_postal,
                    "city" => ($row->commune) ? $row->commune : 'ND',
                    "country" => ($row->pays) ? $row->pays : 'ND',
                    "is_main_entity_address" => 1,
                    "is_billing" => 1,
                    "is_formation_site" => 0,
                    "is_stage_site" => 0,
                );
                $adresse_id = $DbHelperTools->manageAdresse($dataAdresse);
                $dataContact = array(
                    'id' => $contactId,
                    'gender' => $row->civilite,
                    'firstname' => $row->prenom,
                    'lastname' => $row->nom,
                    'pro_phone' => $row->telephone_mobile,
                    'pro_mobile' => $row->telephone_mobile,
                    'nationality' => $row->pays,
                    'email' => $row->adresse_mail,
                    'entitie_id' => $entity_id,
                    'is_active' => 1,
                    'is_main_contact' => 1,
                    'birth_date' => $birth_date,
                );
                $contactId = $DbHelperTools->manageContact($dataContact);

                /* Auxiliary account update */
                $codes_updates = [];

                if (!$entity_exists || !$entitie->auxiliary_customer_accoun) {
                    $auxiliary_customer_account = $DbHelperTools->generateAuxiliaryAccountForEntity($entitie->id);
                    $codes_updates['auxiliary_customer_account'] = $auxiliary_customer_account;
                }
                if (!$entity_exists || !$entitie->collective_customer_account) {
                    $collective_customer_account = $DbHelperTools->generateCodeCollectifs($entitie->id);
                    $codes_updates['collective_customer_account'] = $collective_customer_account;
                }
                if (!empty($codes_updates)) {
                    Entitie::where('id', $entitie->id)->update($codes_updates);
                }
            }

            $fileContact->contact_id = $contactId;
            $fileContact->suggested_contacts = null;
            $fileContact->state = 'old';
            $fileContact->save();
            $success = true;
        } catch (Exception $e) {
            dd($e);
            $success = false;
        }

        return response()->json(['success' => $success]);
    }
    public function selectSuggestedProspect($contactId, $attachment)
    {
        $DbHelperTools = new DbHelperTools();
        $fileContact = FileContact::find($attachment);
        $contact = Contact::find($contactId);
        $row = json_decode($fileContact->line_info);

        try {
            /* Entitie save */
            $entity_id = $contact->entitie->id ?? 0;
            $entity_exists = $entity_id > 0;

            $entity_data = array(
                'id' => $entity_id,
                'ref' => $DbHelperTools->generateEntityCode(),
                'entity_type' => 'P',
                'name' => $row->nom . ' ' . $row->prenom,
                'pro_phone' => $row->telephone,
                'pro_mobile' => $row->telephone,
                'email' => $row->adresse_mail,
                'is_client' => 1,
                'is_active' => 1,
            );
            $entity_id = $DbHelperTools->manageEntitie($entity_data);
            $entitie = Entitie::find($entity_id);

            /* SAVE CONTACT + FILECONTACT */
            if ($entity_id > 0) {
                $adresse_array = array(
                    'entitie_id' => $entity_id,
                    'line_1' => $row->adresse_1,
                    'postal_code' => $row->code_postal,
                );
                $adrRow = Adresse::select('id')->where($adresse_array)->first();
                $adresse_id = ($adrRow) ? $adrRow->id : 0;
                $dataAdresse = array(
                    "id" => $adresse_id,
                    "entitie_id" => $entity_id,
                    "line_1" => $row->adresse_1,
                    "line_2" => $row->adresse_2,
                    "line_3" => $row->adresse_3,
                    "postal_code" => $row->code_postal,
                    "city" => ($row->ville) ? $row->ville : 'ND',
                    "country" => $row->pays,
                    "is_main_entity_address" => 1,
                    "is_billing" => 1,
                    "is_formation_site" => 0,
                    "is_stage_site" => 0,
                );
                $adresse_id = $DbHelperTools->manageAdresse($dataAdresse);
                $dataContact = array(
                    'id' => $contactId,
                    'firstname' => $row->prenom,
                    'lastname' => $row->nom,
                    'pro_phone' => $row->telephone,
                    'pro_mobile' => $row->telephone,
                    'nationality' => $row->pays,
                    'email' => $row->adresse_mail,
                    'entitie_id' => $entity_id,
                    'is_active' => 1,
                    'is_main_contact' => 1,
                );
                $contactId = $DbHelperTools->manageContact($dataContact);

                /* Auxiliary account update */
                $codes_updates = [];

                if (!$entity_exists || !$entitie->auxiliary_customer_accoun) {
                    $auxiliary_customer_account = $DbHelperTools->generateAuxiliaryAccountForEntity($entitie->id);
                    $codes_updates['auxiliary_customer_account'] = $auxiliary_customer_account;
                }
                if (!$entity_exists || !$entitie->collective_customer_account) {
                    $collective_customer_account = $DbHelperTools->generateCodeCollectifs($entitie->id);
                    $codes_updates['collective_customer_account'] = $collective_customer_account;
                }
                if (!empty($codes_updates)) {
                    Entitie::where('id', $entitie->id)->update($codes_updates);
                }
            }

            $fileContact->contact_id = $contactId;
            $fileContact->suggested_contacts = null;
            $fileContact->state = 'old';
            $fileContact->save();
            $success = true;
        } catch (Exception $e) {
            $success = false;
        }

        return response()->json(['success' => $success]);
    }
    public function deleteGedAttachment(Request $request)
    {
        $success = false;
        if ($request->isMethod('delete')) {
            if ($request->has('file_id')) {
                $file_id = $request->file_id;
                // $DbHelperTools = new DbHelperTools();
                if ($file_id > 0) {
                    //ged_attachment_logs
                    $deletedLogsRows = Attachmentlog::where('attachment_id', $file_id)->forceDelete();
                    //ged_attachment_contacts
                    $deletedContactsRows = FileContact::where('file_id', $file_id)->forceDelete();
                    //ged_attachments
                    $deletedRows = Attachment::where('id', $file_id)->forceDelete();
                    if ($deletedRows)
                        $success = true;
                }
            }
        }
        return response()->json(['success' => $success]);
    }

    public function storeImportTask(Request $request)
    {
        $responsable = Contact::find((int) $request->import_responsable);
        $type = Param::find((int) $request->import_type);
        $end_date = DateTime::createFromFormat('d/m/Y', $request->end_date);
        $callback_date = DateTime::createFromFormat('d/m/Y', $request->callback_date);
        $attachments = FileContact::whereIn('id', explode(',', $request->import_attachments))->get();
        $source = Param::where('code', 'IMPORT_PROSPECT')->first();
        $callback_mode = Param::where('code', 'CALLBACK_SOLARIS')->first();
        $rsp_mode = Param::find((int) $request->import_rsp_mode);
        $insert_data = [];

        foreach ($attachments as $att) {
            $contact = Contact::find($att->contact_id);

            if (!$contact) {
                $line = json_decode($att->line_info)->line ?? 0;
                return response()->json([
                    'success' => false,
                    'message' => "Attention, la ligne $line n'est pas encore traitée (sans contact).",
                ]);
            }

            $insert_data[] = [
                "title" => "{$type->name} - {$responsable->firstname} {$responsable->lastname}",
                "description" => "Tâche créé suite à l’import de contact {$responsable->id} du {$att->created_at->format('d/m/Y à H:i')}",
                "apporteur_id" => Auth::user()->contact->id ?? null,
                "priority" => 'normal',
                "responsable_id" => $responsable->id,
                "start_date" => date('Y-m-d H:i:s'),
                "ended_date" => $end_date->format('Y-m-d 00:00:00'),
                "callback_date" => $callback_date ? $callback_date->format('Y-m-d 00:00:00') : null,
                "source_id" => $source->id ?? null,
                "type_id" => $type->id,
                "callback_mode_id" => $callback_mode->id,
                "reponse_mode_id" => $rsp_mode->id,
                "contact_id" => $contact->id ?? null,
                "entite_id" => $contact ? $contact->entitie->id ?? null : null,
            ];
        }

        try {
            if (!empty($insert_data)) {
                Task::insert($insert_data);
            }
            return response()->json([
                'success' => true,
                'message' => 'Tâches ajoutées avec succès.'
            ]);
        } catch (Exception $e) {
            // dd($e);
            return response()->json([
                'success' => false,
                'message' => 'Erreur inconnue, merci de réessayer plus tard.'
            ]);
        }
    }
}
