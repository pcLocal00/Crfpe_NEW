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
use App\Models\Invoice;
use App\Models\Session;
use App\Models\Schedule;
use App\Models\Attachment;
use App\Models\Enrollment;
use App\Models\FileContact;
use App\Models\Sessiondate;
use Illuminate\Http\Request;
use App\Exports\GlobalExport;
use App\Exports\ClientsExport;
use App\Imports\ContactsImport;
use App\Library\Helpers\Helper;
use App\Models\Schedulecontact;
use App\Exports\PersonnesExport;
use App\Models\Internshiproposal;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Library\Services\PublicTools;
use App\Exports\ClientsContactsExport;
use App\Exports\EvolutionTasksExport;
use App\Exports\TasksExport;
use App\Exports\TDBActivitesExport;
use App\Exports\TDBEtudiantsExport;
use App\Exports\TDBIntervenantsExport;
use App\Library\Services\DbHelperTools;
use App\Models\Task;
use App\Models\Refund;
use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Storage;

class exportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function exportClients()
	{
		    $datas=array();
            //les clients
            // $clients=Entitie::all();
            $clients=DB::table('en_entities')
                        ->select('en_entities.*','en_adresses.line_1','en_adresses.line_2','en_adresses.line_3','en_adresses.postal_code','en_adresses.city','en_adresses.country')
                        ->join('en_adresses','en_adresses.entitie_id','=','en_entities.id')
                        ->get();

			$arrayHeader=array(
					'ID',
					'Référence',
					'Type client',
					'Nom',
					'Type d\'etablisement',
					'SIREN',
					'SIRET',
					'Acronyme',
					'Code NAF',
					'TVA',
					'Téléphone pro',
					'Portable pro',
					'Fax',
					'Email',

					'prospecting_area',
					'is_client',
					'is_funder',
					'is_former',
					'is_stage_site',
					'is_synced_to_sage',
					'is_prospect',
					'matricule_code',
					'personal_thirdparty_code',
					'vendor_code',
					'iban',
					'bic',
					'collective_customer_account',
					'auxiliary_customer_account',
					'is_active',
					// 'parent',
                    'line1',
					'line2',
					'line3',
					'Postal_Code',
					'city',
					'country',
					'created_at',
					'updated_at',
			);
			$datas['clients'][0]=$arrayHeader;
            $arrayValues=array();
			if (count ( $clients ) > 0) {
				foreach ( $clients as $d ) {
                    // dd($d);
                    $array=array();
                    // 'ID',
                    $array [] = $d->id;
					// 'Référence',
                    $array [] = $d->ref;
					// 'Type client',
                    $array [] = $d->entity_type;
					// 'Nom',
                    $array [] = $d->name;
					// 'Type d\'etablisement',
                    $array [] = $d->type_establishment;
					// 'SIREN',
                    $array [] = $d->siren;
					// 'SIRET',
                    $array [] = $d->siret;
					// 'Acronyme',
                    $array [] = $d->acronym;
					// 'Code NAF',
                    $array [] = $d->naf_code;
					// 'TVA',
                    $array [] = $d->tva;
					// 'Téléphone pro',
                    $array [] = $d->pro_phone;
					// 'Portable pro',
                    $array [] = $d->pro_mobile;
					// 'Fax',
                    $array [] = $d->fax;
					// 'Email',
                    $array [] = $d->email;
                    // 'prospecting_area',
                    $array [] = $d->prospecting_area;
					// 'is_client',
                    $array [] = $d->is_client;
					// 'is_funder',
                    $array [] = $d->is_funder;
					// 'is_former',
                    $array [] = $d->is_former;
					// 'is_stage_site',
                    $array [] = $d->is_stage_site;
					// 'is_synced_to_sage',
                    $array [] = $d->is_synced_to_sage;
					// 'is_prospect',
                    $array [] = $d->is_prospect;
					// 'matricule_code',
                    $array [] = $d->matricule_code;
					// 'personal_thirdparty_code',
                    $array [] = $d->personal_thirdparty_code;
					// 'vendor_code',
                    $array [] = $d->vendor_code;
					// 'iban',
                    $array [] = $d->iban;
					// 'bic',
                    $array [] = $d->bic;
					// 'collective_customer_account',
                    $array [] = $d->collective_customer_account;
					// 'auxiliary_customer_account',
                    $array [] = $d->auxiliary_customer_account;
					// 'is_active',
                    $array [] = $d->is_active;
					// 'parent',

                    // 'line1',
                    $array [] = ($d->line_1)?$d->line_1:'';					
                    // 'line_2',
                    $array [] = ($d->line_2)?$d->line_2:'';
					// 'line_3',
                    $array [] = ($d->line_3)?$d->line_3:'';
                    // 'postal_code',
                    $array [] = ($d->postal_code)?$d->postal_code:'';
                    // 'city',
                    $array [] = ($d->city)?$d->city:'';
                    // 'country',
                    $array [] = ($d->country)?$d->country:'';
					// 'created_at',
                    $array [] = date("Y-m-d H:i:s", strtotime($d->created_at));
                    // 'updated_at',  
                    $array [] = date("Y-m-d H:i:s", strtotime($d->updated_at));

                    $arrayValues[]=$array;
                }
            }
			$datas['clients'][1]=$arrayValues;
			$datas['clients'][2]='clients';
            //les contacts
            $contacts=Contact::all();
            $arrayHeaderContact=array(
                'Référence client',
                'Nom client',
                'ID',
                'Civilité',
                'Prénom',
                'Nom',
                'Email',
                'pro_phone',
                'pro_mobile',
                'function',
                'birth_date',
                'birth_name',
                'birth_department',
                'birth_city',
                'social_security_number',
                'registration_code',
                'nationality',
                'student_status',
                'student_status_date',
                'is_main_contact',
                'is_billing_contact',
                'is_order_contact',
                'is_trainee_contact',
                'is_former',
                'type_former_intervention',
                'is_active',
                'is_valid_accounting',
                'created_at',
                'updated_at',
            );
            $datas['contacts'][0]=$arrayHeaderContact;
            $arrayValues=array();
            if (count ( $contacts ) > 0) {
				foreach ( $contacts as $d ) {
                    $array=array();
                    //'Référence client',
                    $array [] = $d->entitie->ref;
                    //'Nom client',
                    $array [] = $d->entitie->name;
                    // 'ID',
                    $array [] = $d->id;
                    // 'gender',
                    $array [] = $d->gender;
                    // 'firstname',
                    $array [] = $d->firstname;
                    // 'lastname',
                    $array [] = $d->lastname;
                    // 'email',
                    $array [] = $d->email;
                    // 'pro_phone',
                    $array [] = $d->pro_phone;
                    // 'pro_mobile',
                    $array [] = $d->pro_mobile;
                    // 'function',
                    $array [] = $d->function;
                    // 'birth_date',
                    $dtBirthDate = ($d->birth_date)?Carbon::createFromFormat('Y-m-d',$d->birth_date):null;
                    $array [] = ($dtBirthDate)?$dtBirthDate->format('d-m-Y'):'';
                    // 'birth_name',
                    $array [] = $d->birth_name;
                    // 'birth_department',
                    $array [] = $d->birth_department;
                    // 'birth_city',
                    $array [] = $d->birth_city;
                    // 'social_security_number',
                    $array [] = $d->social_security_number;
                    // 'registration_code',
                    $array [] = $d->registration_code;
                    // 'nationality',
                    $array [] = $d->nationality;
                    // 'student_status',
                    $array [] = $d->student_status;
                    // 'student_status_date',
                    $dtStudentStatusDate = ($d->student_status_date)?Carbon::createFromFormat('Y-m-d',$d->student_status_date):null;
                    $array [] = ($dtStudentStatusDate)?$dtStudentStatusDate->format('d-m-Y'):'';
                    // 'is_main_contact',
                    $array [] = $d->is_main_contact;
                    // 'is_billing_contact',
                    $array [] = $d->is_billing_contact;
                    // 'is_order_contact',
                    $array [] = $d->is_order_contact;
                    // 'is_trainee_contact',
                    $array [] = $d->is_trainee_contact;
                    // 'is_former',
                    $array [] = $d->is_former;
                    // 'type_former_intervention',
                    $array [] = $d->type_former_intervention;
                    // 'is_active',
                    $array [] = $d->is_active;
                    // 'is_valid_accounting',
                    $array [] = $d->is_valid_accounting;
                    // 'created_at',
                    $array [] = $d->created_at->format('d-m-Y H:i:s');
                    // 'updated_at',  
                    $array [] = $d->updated_at->format('d-m-Y H:i:s');                 
                    
                    $arrayValues[]=$array;
                }
            }
            $datas['contacts'][1]=$arrayValues;
			$datas['contacts'][2]='contacts';

			$xlsNameFile='clients-contacts-'.time().'.xlsx';
            if (count($datas)>0){
                $export = new ClientsContactsExport($datas);
                return Excel::download($export, $xlsNameFile);
            }
		return 0;
	}

    // export tasks // tickets non terminés par statuts et types
    public function exportTasks(){
        $datas=array();
        $tasks = Task::select('par_params.name', DB::raw('COUNT(tasks.id) as value'))->join('par_params', 'par_params.id', 'tasks.etat_id')->groupBy('par_params.id')->get();
        
        //dd($tasks);
        $total = count($tasks);

        $arrayHeader=array(
            'Nom Des Types',
            'Pourcentage non terminés',
        );
        $datas['tasks'][0]=$arrayHeader;
        $arrayValues=array();
        if (count ( $tasks ) > 0) {
            foreach ( $tasks as $d ) {
                //dd($d);
                $array=array();
                // 'name',
                $array [] = $d->name;
                // 'value',
                
                $array [] = $d->value / $total * 60 .'%';
                //dd($array);

                $arrayValues[]=$array;
            }
        }
        $datas['tasks'][1]=$arrayValues;
		$datas['tasks'][2]='tasks';

        $xlsNameFile='tickets-non-termines'.time().'.xlsx';
            if (count($datas)>0){
                $export = new TasksExport($datas);
                return Excel::download($export, $xlsNameFile);
            }
		return 0;
    
    }
    // export tasks // évolution des tickets terminés/non terminés en focntion des types
    public function exportTasksEvolution(Request $request){
        $datas=array();
        $reqParams = $request->all();
        $TasksEvolution = json_decode($reqParams['exportData'], true) ?? [];
        //dd($TasksEvolution);

        //dd($results);
        //$total = count($TasksEvolution);

        $arrayHeader=array(
            'Nom des contacts',
            'nombre des tickets',
        );
        $datas['TasksEvolution'][0]=$arrayHeader;
        $arrayValues=array();
        if (count ( $TasksEvolution ) > 0) {
            foreach ( $TasksEvolution as $d ) {
                //dd($d);
                $array=array();
                // 'name',
                $array [] = $d['nom'];
                // 'value',
                
                $array [] = $d['value'];
                //dd($array);

                $arrayValues[]=$array;
            }
        }

        $datas['TasksEvolution'][1]=$arrayValues;
		$datas['TasksEvolution'][2]='TasksEvolution';
        $xlsNameFile='Evolution-des-tickets-termines-non-termines'.time().'.xlsx';
            if (count($datas)>0){
                $export = new EvolutionTasksExport($datas);
                return Excel::download($export, $xlsNameFile);
            }
		return 0;
    }
    
    
    public function invoicesExport(Request $request)
	{
		    $datas=array();
            $DbHelperTools = new DbHelperTools();
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
            //les factures
            $ids_invoices=[];
            if($request->has('ids_invoices')){
                $ids_invoices=$request->ids_invoices;
            }
            //dd($ids_invoices);
            $invoices=Invoice::whereIn('id',$ids_invoices)->get();
            //dd(count ( $invoices ));
            $arrayHeader=array(
					'ID',
					'Type',
					'N°',
					'Date de facturation',
					'Date d\'échéance',
					'Référence client',
					'Nom client',
					'Type client',
					'Montant (€)',
			);
			$datas[0]=$arrayHeader;
            $arrayValues=array();
			if (count ( $invoices ) > 0) {
				foreach ( $invoices as $d ) {
                    $array=array();
                    $dtBillDate = Carbon::createFromFormat('Y-m-d', $d->bill_date);
                    $dtIssueDate = Carbon::createFromFormat('Y-m-d', $d->due_date);
                    $typeFacture=$arrayLabel[$d->invoice_type];
                    // 'ID',
                    $array [] = $d->id;
                    //type
                    $array [] = $typeFacture;
					// 'N°',
                    $array [] = $d->number;
					// 'Date de facturation',
                    $array [] = $dtBillDate->format('d-m-Y');
                    // 'Date d\'échéance',  
                    $array [] = $dtIssueDate->format('d-m-Y');
                    //référence client
                    $array [] = $d->entity->ref;
                    //Nom client
                    $array [] = $d->entity->name;
                    //Type client
                    $array [] = $d->entity->entity_type;
                    //Montant
                    $calcul = $DbHelperTools->getAmountsInvoice($d->id);
                    $array[] = (float) $calcul['total'];
                    
                    $arrayValues[]=$array;
                }
            }
			$datas[1]=$arrayValues;

			$xlsNameFile='factures-'.time().'.xlsx';
            if (count($datas)>0){
                $export = new GlobalExport($datas);
                return Excel::download($export, $xlsNameFile);
            }
		return 0;
	}

    public function avoirsExport(Request $request)
	{
		    $datas=array();
            $DbHelperTools = new DbHelperTools();
            
            $ids_avoirs=[];
            if($request->has('ids_avoirs')){
                $ids_avoirs=$request->ids_avoirs;
            }
            
            $invoices=Refund::whereIn('id',$ids_avoirs)->get();
            
            $arrayHeader=array(
					'N°',
					'Date',
					'Facture',
					'AF',
                    'Intitilé',
					'Client'
			);
			$datas[0]=$arrayHeader;
            $arrayValues=array();
			if (count ( $invoices ) > 0) {
				foreach ( $invoices as $d ) {
                    $array=array();
                    $date = Carbon::createFromFormat('Y-m-d', $d->refund_date);
					// 'N°',
                    $array [] = $d->number;
					// 'Date',
                    $array [] = $date->format('d-m-Y');
                    //Facture
                    $array [] = $d->invoice->number;
                    //AF
                    $array [] = $DbHelperTools->manageAFavoirs($d->invoice->af_id);
                    //Intitilé
                    $array [] = $DbHelperTools->manageAFavoirsTitle($d->invoice->af_id);
                    //Client
                    $row_entity = $DbHelperTools->manageEntityAvoirs($d->invoice_id);

                    $array [] = 'Client : ' .  $row_entity->name . ' - ' .  $row_entity->ref . ' - ' .  $row_entity->entity_type ;

                    $arrayValues[]=$array;
                }
            }
			$datas[1]=$arrayValues;

			$xlsNameFile='avoirs-'.time().'.xlsx';
            if (count($datas)>0){
                $export = new GlobalExport($datas);
                return Excel::download($export, $xlsNameFile);
            }
		return 0;
	}
    /*
    public function clientsExport(){
        // ClientsExport
        return Excel::download(new ClientsExport, 'clients.xlsx');
        // return 'okey';
    }
    */
    public function personnesExport(){
        // ClientsExport
        return Excel::download(new PersonnesExport, 'personnes.xlsx');
        // return 'okey';
    }
    // TDB Export brut
    public function getTDB()
    {
        return view('pages.tdb.gettdb');
    }
    // TDB Export Intervenants
    // public function tdbExportIntervenants(){
    //     $datas=array();
    //     //$TDBIntervenants = DB::table('vw_tdb_intrevenants')->get();
    //     $TDBIntervenants = DB::table('vw_tdb_intrevenants')
    //                         ->select('titre','code','status','date_debut','date_fin','nb_heures_af','Nom','type','nom_formateur','Nb_heures','Nb_heures_present','Nb_heures_absent','Remu','Remu_N_passé','Remu_N_avenir','Nb_seances_sans_remu','Remu_Avant_N','Remu_Apres_N','current_date')->get();
    //     //dd($TDBIntervenants);
    //     $arrayHeader=array(
    //         'titre','code','status','date_debut','date_fin','nb_heures_af','Nom','type','nom_formateur','Nb_heures','Nb_heures_present','Nb_heures_absent','Remu','Remu_N_passé','Remu_N_avenir','Nb_seances_sans_remu','Remu_Avant_N','Remu_Apres_N','current_date'
    //     );
    //     $datas['TDBIntervenants'][0]=$arrayHeader;
    //     $arrayValues=array();
    //     if (count ( $TDBIntervenants ) > 0) {
    //         foreach ( $TDBIntervenants as $d ) {
    //             $array=array();
    //             $array [] = ($d->titre == null) ? "0" : $d->titre;
    //             $array [] = ($d->code == null) ? "0" : $d->code;
    //             $array [] = ($d->status == null) ? "0" : $d->status;
    //             $array [] = ($d->date_debut == null) ? date('00/00/00') :  $d->date_debut;
    //             $array [] = ($d->date_fin == null) ? date('00/00/00') : $d->date_fin;
    //             $array [] = ($d->nb_heures_af == null) ? "0" : (float)$d->nb_heures_af;
    //             $array [] = ($d->Nom == null) ? "0" : $d->Nom;
    //             $array [] = ($d->type == null) ? "0" : $d->type;
    //             $array [] = ($d->nom_formateur == null) ? "0" : $d->nom_formateur;
    //             $array [] = ($d->Nb_heures == null)? "0" : (float)$d->Nb_heures;
    //             $array [] = ($d->Nb_heures_present == null)? "0" : (float)$d->Nb_heures_present;
    //             $array [] = ($d->Nb_heures_absent == null)? "0" : (float)$d->Nb_heures_absent;
    //             $array [] = ($d->Remu == null || $d->Remu == 0)? (float)0.00 : (double)$d->Remu;
    //             $array [] = ($d->Remu_N_passé == null)? "0" : (float)$d->Remu_N_passé;
    //             $array [] = ($d->Remu_N_avenir == null)? "0" : (float)$d->Remu_N_avenir;
    //             $array [] = ($d->Nb_seances_sans_remu == null || $d->Nb_seances_sans_remu == 0)? "0" : $d->Nb_seances_sans_remu;
    //             $array [] = ($d->Remu_Avant_N == null)? "0" : (float)$d->Remu_Avant_N;
    //             $array [] = ($d->Remu_Apres_N == null)? "0" : (float)$d->Remu_Apres_N;
    //             $array [] = ($d->current_date == null)? date('00/00/00') : $d->current_date;

    //             $arrayValues[]=$array;
    //         }
    //         //dd($arrayValues);
    //     }
    //     $datas['TDBIntervenants'][1]=$arrayValues;
	// 	$datas['TDBIntervenants'][2]='TDBIntervenants';

    //     $xlsNameFile='TDB-Intervenants'.time().'.xlsx';
    //         if (count($datas)>0){
    //             $export = new TDBIntervenantsExport($datas);
    //             return Excel::download($export, $xlsNameFile);
    //         }
	// 	return 0;
    // }

    public function tdbExportIntervenants(Request $request){
        // dd($request);
    //     $query = "
    // SELECT
    //     `af`.`id` AS `id`,
    //     `af`.`title` AS `titre`,
    //     `af`.`code` AS `code`,
    //     `af`.`status` AS `status`,
    //     date_format(`af`.`started_at`, '%d/%m/%Y') AS `date_debut`,
    //     date_format(`af`.`ended_at`, '%d/%m/%Y') AS `date_fin`,
    //     `af`.`nb_hours` AS `nb_heures_af`,
    //     concat(co.gender, ' ', co.firstname, ' ', co.lastname) AS Etudiant,
    //     gr.title AS Groupe,
    //     st.student_status AS Statut_etudiant,
    //     st.start_date AS Date_debut_statut,
    //     st.end_date AS Date_fin_statut,
    //     mem.stop_reason AS Evenement,
    //     mem.effective_date AS Date_evenement,
    //     sum(`sc`.`duration`) AS `Nb_heures_planifiées`,
    //     `af`.`nb_hours` - sum(`sc`.`duration`) AS Nb_heures_restant,
    //     sum(`sc2`.`duration`) AS `Nb_heures_present`,
    //     sum(`sc3`.`duration`) AS `Nb_heures_absent`,
    //     sum(`sc4`.`duration`) AS `Nb_heures_non_renseigné`
    // FROM
    //     (
    //         (
    //             (
    //                 (
    //                     (
    //                         (
    //                             (
    //                                 (
    //                                     (
    //                                         (`af_actions` `af` 
    //                                         left join `af_enrollments` `enr` on(`enr`.`af_id` = `af`.`id`)
    //                                         )
    //                                         left join `af_members` `mem` on(`mem`.`enrollment_id` = `enr`.`id`)
    //                                     )
    //                                     left join `en_contacts` `co` on(`co`.`id` = `mem`.`contact_id`)
    //                                 )
    //                                 left join `af_groups` `gr` on(`gr`.`id` = `mem`.`group_id`)
    //                             )
    //                             left join `af_student_status` `st` on (mem.id = st.member_id)
    //                         )
    //                         left join `af_schedulecontacts` `sca` on(`sca`.`member_id` = `mem`.`id`)
    //                     )
    //                     left join `af_schedules` `sc` on(`sc`.`id` = `sca`.`schedule_id`)
    //                 )
    //                 left join `af_schedules` `sc2` on(`sc2`.`id` = `sca`.`schedule_id` and `sca`.`pointing` = 'present')
    //             )
    //             left join `af_schedules` `sc3` on(`sc3`.`id` = `sca`.`schedule_id` and `sca`.`pointing` = 'absent')
    //         )
    //         left join `af_schedules` `sc4` on(`sc4`.`id` = `sca`.`schedule_id` and `sca`.`pointing` is null)
    //     )
    // where 
    //     enr.enrollment_type = 'F' group by `af`.`id`, mem.id";
        
    //     $datas=array();
    //     $TDBIntervenants = DB::select(DB::raw($query));
    $date_debut = null;
    $date_fin = null;

        $TDBIntervenants = DB::table('vw_tdb_intervenants')
                            ->select('titre',
                            'code',
                            'etat',
                            'date_debut',
                            'date_fin',
                            'nb_heures_af',
                            'Nom',
                            'type',
                            'nom_formateur',
                            'Nb_heures',
                            'Nb_heures_present',
                            'Nb_heures_absent',
                            'Renu',
                            'Renu_avant',
                            'Renu_apres',
                            'Nb_seances_sans_remu',
                            'current_date');

                            // if(isset($request->date_debut_int) && isset($request->date_debut_int))
                            // {
                            //     $date_debut = DateTime::createFromFormat('d/m/Y', $request->date_debut_int)->format('Y-m-d');
                            //     $date_fin = DateTime::createFromFormat('d/m/Y', $request->date_fin_int)->format('Y-m-d');
                            //     dd($date_debut,$date_fin);
                            //     $TDBIntervenants->whereRaw('STR_TO_DATE(planning, "%d/%m/%Y") BETWEEN ? AND ?', [$date_debut, $date_fin]);
                            // }


                            if (isset($request->date_debut_int)) {
                                $date_debut = DateTime::createFromFormat('d/m/Y', $request->date_debut_int)->format('Y-m-d');
                                $TDBIntervenants->whereRaw('planning >= ?', [$date_debut]);
                            }
                    
                            
                            if (isset($request->date_fin_int)) {
                                $date_fin = DateTime::createFromFormat('d/m/Y', $request->date_fin_int)->format('Y-m-d');
                                $TDBIntervenants->whereRaw('planning <= ?', [$date_fin]);
                            }

                            $TDBIntervenants = $TDBIntervenants->get();

        $arrayHeader=array(
            'titre','code','etat','date_debut','date_fin','nb_heures_af','Nom','type','nom_formateur','Nb_heures','Nb_heures_present','Nb_heures_absent','Remu','Renu_avant','Renu_apres','Nb_seances_sans_remu','current_date'
        );
        $datas['TDBIntervenants'][0]=$arrayHeader;
        $arrayValues=array();
        if (count ( $TDBIntervenants ) > 0) {
            foreach ( $TDBIntervenants as $d ) {
                $array=array();
                $array [] = ($d->titre == null) ? "0" : $d->titre;
                $array [] = ($d->code == null) ? "0" : $d->code;
                $array [] = ($d->etat == null) ? "0" : $d->etat;
                $array [] = ($d->date_debut == null) ? date('00/00/00') :  $d->date_debut;
                $array [] = ($d->date_fin == null) ? date('00/00/00') : $d->date_fin;
                $array [] = ($d->nb_heures_af == null) ? "0" : (float)$d->nb_heures_af;
                $array [] = ($d->Nom == null) ? "0" : $d->Nom;
                $array [] = ($d->type == null) ? "0" : $d->type;
                $array [] = ($d->nom_formateur == null) ? "0" : $d->nom_formateur;
                $array [] = ($d->Nb_heures == null)? "0" : (float)$d->Nb_heures;
                $array [] = ($d->Nb_heures_present == null)? "0" : (float)$d->Nb_heures_present;
                $array [] = ($d->Nb_heures_absent == null)? "0" : (float)$d->Nb_heures_absent;
                $array [] = ($d->Renu == null || $d->Renu == 0)? (float)0.00 : (double)$d->Renu;
                $array [] = ($d->Renu_avant == null)? "0" : (float)$d->Renu_avant;
                $array [] = ($d->Renu_apres == null)? "0" : (float)$d->Renu_apres;
                $array [] = ($d->Nb_seances_sans_remu == null || $d->Nb_seances_sans_remu == 0)? "0" : $d->Nb_seances_sans_remu;
                $array [] = ($d->current_date == null)? date('00/00/00') : $d->current_date;

                $arrayValues[]=$array;
            }
            //dd($arrayValues);
        }
        $datas['TDBIntervenants'][1]=$arrayValues;
		$datas['TDBIntervenants'][2]='TDBIntervenants';

        $xlsNameFile='TDB-Intervenants'.time().'.xlsx';
            if (count($datas)>0){
                $export = new TDBIntervenantsExport($datas);
                return Excel::download($export, $xlsNameFile);
            }
		return 0;
    }




    // TDB Export Activites
    public function tdbExportActivites(){
        $datas=array();
       

        // dd($TDBActivites);
        $TDBActivites = DB::table('vw_db_activities')
                            ->select('titre',
                            'code',
                            'type_inter',
                            'etat',
                            'date_debut',
                            'date_fin',
                            'nb_heures_af',
                            'Nom','entitie_id',
                            'type_entité',
                            'Nb_participants',
                            'Stop',
                            'nb_heures_totales',
                                'nb_heures_restant',
                                'num_devis',
                                'Createur_devis',
                                'Date_devis',
                                'Statut_devis','mtt_devis','num_conv','Createur_conv','Date_conv','Statut_conv','mtt_conv',
                                'num_fact','Createur_fact','Date_fact','Statut_fact','mtt_fact','Num_avoir','Createur_avoir','Date_avoir','Statut_avoir','mtt_avoir','MTT_Fact_Net',
                                'nb_heures_an','annee','CA_CONV_AN','CA_FACT_AN','CA_CONV_APROG_AN','CA_FACT_APROG_AN','current_date')->get();
        //dd($TDBActivites);
        $arrayHeader=array(
            'titre','code','type_inter','etat','date_debut','date_fin','nb_heures_af','Nom','entitie_id','type_entité','Nb_participants','Stop','nb_heures_totales',
            'nb_heures_restant','num_devis','Createur_devis','Date_devis','Statut_devis','mtt_devis','num_conv','Createur_conv','Date_conv','Statut_conv','mtt_conv',
            'num_fact','Createur_fact','Date_fact','Statut_fact','mtt_fact','Num_avoir','Createur_avoir','Date_avoir','Statut_avoir','mtt_avoir','MTT_Fact_Net',
            'nb_heures_an','annee','CA_CONV_AN','CA_FACT_AN','CA_CONV_APROG_AN','CA_FACT_APROG_AN','current_date'
            // 'num_fact','Createur_fact','Date_fact','Statut_fact','mtt_fact'
        );
        $datas['TDBActivites'][0]=$arrayHeader;
        $arrayValues=array();
        if (count ( $TDBActivites ) > 0) {
            foreach ( $TDBActivites as $d ) {
                $array=array();
                $array [] = ($d->titre == null) ? "0" : $d->titre;
                $array [] = ($d->code == null) ? "0" : $d->code;
                $array [] = ($d->type_inter == null) ? "0" : $d->type_inter;
                $array [] = ($d->etat == null) ? "0" : $d->etat;
                $array [] = ($d->date_debut == null) ? date('00/00/00') : $d->date_debut;
                $array [] = ($d->date_fin == null) ? date('00/00/00') : $d->date_fin;
                $array [] = ($d->nb_heures_af == null) ? "0" : $d->nb_heures_af;
                $array [] = ($d->Nom == null) ? "0" : $d->Nom;
                $array [] = ($d->entitie_id == null) ? "0" : $d->entitie_id;
                $array [] = ($d->type_entité == null) ? "0" : $d->type_entité;
                $array [] = ($d->Nb_participants == null) ? "0" : $d->Nb_participants;
                // $array [] = $d->Stop;
                $array [] = ($d->Stop == null) ? "0" : $d->Stop;
                $array [] = ($d->nb_heures_totales == null)? "0" : (float)$d->nb_heures_totales;
                $array [] = ($d->nb_heures_restant == null)? "0" : (float)$d->nb_heures_restant;
                //les devis
                $array [] = ($d->num_devis == null) ? "0" : $d->num_devis;
                $array [] = ($d->Createur_devis == null) ? "0" : $d->Createur_devis;
                $array [] = ($d->Date_devis == null) ? date('00/00/00') : $d->Date_devis;
                $array [] = ($d->Statut_devis == null) ? "0" : $d->Statut_devis;
                $array [] = ($d->mtt_devis == null)? "0" : (float)$d->mtt_devis;
                //les conv
                $array [] = ($d->num_conv == null) ? "0" : $d->num_conv;
                $array [] = ($d->Createur_conv == null) ? "0" : $d->Createur_conv;
                $array [] = ($d->Date_conv == null) ? date('00/00/00') : $d->Date_conv;
                $array [] = ($d->Statut_conv == null) ? "0" : $d->Statut_conv;
                $array [] = ($d->mtt_conv == null)? "0" : (float)$d->mtt_conv;
                //les factures
                $array [] = ($d->num_fact == null) ? "0" : $d->num_fact;
                $array [] = ($d->Createur_fact == null) ? "0" : $d->Createur_fact;
                $array [] = ($d->Date_fact == null) ? date('00/00/00') : $d->Date_fact;
                $array [] = ($d->Statut_fact == null) ? "0" : $d->Statut_fact;
                $array [] = ($d->mtt_fact == null)? "0" : (float)$d->mtt_fact;
                //les avoir
                $array [] = ($d->Num_avoir == null) ? "0" : $d->Num_avoir;
                $array [] = ($d->Createur_avoir == null) ? "0" : $d->Createur_avoir;
                $array [] = ($d->Date_avoir == null) ? date('00/00/00') : $d->Date_avoir;
                $array [] = ($d->Statut_avoir == null) ? "0" : $d->Statut_avoir;
                $array [] = ($d->mtt_avoir == null)? "0" : (float)$d->mtt_avoir;
                //MTT_Fact_Net
                $array [] = ($d->MTT_Fact_Net == 0)? "0" : (float)$d->MTT_Fact_Net;
                $array [] = ($d->nb_heures_an == null)? "0" : (float)$d->nb_heures_an;
                $array [] = ($d->annee == null) ? "0" : $d->annee;
                $array [] = ($d->CA_CONV_AN == null)? "0" : (float)$d->CA_CONV_AN;
                $array [] = ($d->CA_FACT_AN == null)? "0" : (float)$d->CA_FACT_AN;
                $array [] = ($d->CA_CONV_APROG_AN == null)? "0" : (float)$d->CA_CONV_APROG_AN;
                $array [] = ($d->CA_FACT_APROG_AN == null)? "0" : (float)$d->CA_FACT_APROG_AN;
                $array [] = ($d->current_date == null)? date('00/00/00') : $d->current_date;
                $arrayValues[]=$array;
            }
        }
        $datas['TDBActivites'][1]=$arrayValues;
		$datas['TDBActivites'][2]='TDBActivites';

        $xlsNameFile='TDB-Activites'.time().'.xlsx';
            if (count($datas)>0){
                $export = new TDBActivitesExport($datas);
                return Excel::download($export, $xlsNameFile);
            }
		return 0;
    }




    // TDB Export Etudiants
    public function tdbExportEtudiants(Request $request){
        // dd($request);

        $date_debut = null;
        $date_fin = null;

        $datas=array();
        $TDBEtudiants = DB::table('vw_tdb_etudiants')
                            ->select('titre','code','status','date_debut','date_fin','nb_heures_af','Etudiant','Groupe',
                                    'Statut_etudiant','Date_debut_statut','Date_fin_statut','Evenement','Date_evenement',
                                    'Nb_heures_planifiées','Nb_heures_restant','Nb_heures_present','Nb_heures_absent','Nb_heures_non_renseigné');
        
                                    if (isset($request->date_debut)) {
                                        $date_debut = DateTime::createFromFormat('d/m/Y', $request->date_debut)->format('Y-m-d');
                                        $TDBEtudiants->whereRaw('STR_TO_DATE(date_debut, "%d/%m/%Y") >= ?', [$date_debut]);
                                    }
                            
                                    if (isset($request->date_fin)) {
                                        $date_fin = DateTime::createFromFormat('d/m/Y', $request->date_fin)->format('Y-m-d');
                                        $TDBEtudiants->whereRaw('STR_TO_DATE(date_fin, "%d/%m/%Y") <= ?', [$date_fin]);
                                    }
                            
                                    $TDBEtudiants = $TDBEtudiants->get();


    //     $query = "
    // SELECT
    //     `af`.`id` AS `id`,
    //     `af`.`title` AS `titre`,
    //     `af`.`code` AS `code`,
    //     `af`.`status` AS `status`,
    //     date_format(`af`.`started_at`, '%d/%m/%Y') AS `date_debut`,
    //     date_format(`af`.`ended_at`, '%d/%m/%Y') AS `date_fin`,
    //     `af`.`nb_hours` AS `nb_heures_af`,
    //     concat(co.gender, ' ', co.firstname, ' ', co.lastname) AS Etudiant,
    //     gr.title AS Groupe,
    //     st.student_status AS Statut_etudiant,
    //     st.start_date AS Date_debut_statut,
    //     st.end_date AS Date_fin_statut,
    //     mem.stop_reason AS Evenement,
    //     mem.effective_date AS Date_evenement,
    //     sum(`sc`.`duration`) AS `Nb_heures_planifiées`,
    //     `af`.`nb_hours` - sum(`sc`.`duration`) AS Nb_heures_restant,
    //     sum(`sc2`.`duration`) AS `Nb_heures_present`,
    //     sum(`sc3`.`duration`) AS `Nb_heures_absent`,
    //     sum(`sc4`.`duration`) AS `Nb_heures_non_renseigné`
    // FROM
    //     (
    //         (
    //             (
    //                 (
    //                     (
    //                         (
    //                             (
    //                                 (
    //                                     (
    //                                         (`af_actions` `af` 
    //                                         left join `af_enrollments` `enr` on(`enr`.`af_id` = `af`.`id`)
    //                                         )
    //                                         left join `af_members` `mem` on(`mem`.`enrollment_id` = `enr`.`id`)
    //                                     )
    //                                     left join `en_contacts` `co` on(`co`.`id` = `mem`.`contact_id`)
    //                                 )
    //                                 left join `af_groups` `gr` on(`gr`.`id` = `mem`.`group_id`)
    //                             )
    //                             left join `af_student_status` `st` on (mem.id = st.member_id)
    //                         )
    //                         left join `af_schedulecontacts` `sca` on(`sca`.`member_id` = `mem`.`id`)
    //                     )
    //                     left join `af_schedules` `sc` on(`sc`.`id` = `sca`.`schedule_id`)
    //                 )
    //                 left join `af_schedules` `sc2` on(`sc2`.`id` = `sca`.`schedule_id` and `sca`.`pointing` = 'present')
    //             )
    //             left join `af_schedules` `sc3` on(`sc3`.`id` = `sca`.`schedule_id` and `sca`.`pointing` = 'absent')
    //         )
    //         left join `af_schedules` `sc4` on(`sc4`.`id` = `sca`.`schedule_id` and `sca`.`pointing` is null)
    //     )
    // where 
    //     enr.enrollment_type = 'S'";
        
        // dd($request->date_debut,$request->date_fin);

        // if(isset($request->date_debut))
        // {

        //     $date_start = new DateTime($request->date_debut);
        //     // Format the date in 'Y-m-d' format
        //     $date_debut = $date_start->format('Y-m-d');

        //     $query .= " and DATE(af.started_at) >= '".$date_debut."'";
        // }
        // if(isset($request->date_fin)){

        //     $date = DateTime::createFromFormat('d/m/Y', $request->date_fin);

        //     if ($date !== false) {
        //         $formattedDate = $date->format('Y-m-d');
        //     }
        // // dd($formattedDate,$request->date_fin);

        //     $query .= " and DATE(af.ended_at) <= '".$formattedDate."'";
        // }

        // $query .= " group by `af`.`id`, mem.id";

        // dd($query);

        // $TDBEtudiants = DB::select(DB::raw($query));

        // dd(count($TDBEtudiants),$query);
        // dd($TDBEtudiants);

        $arrayHeader=array( 'titre','code','etat','date_debut','date_fin','nb_heures_af','Etudiant','Groupe',
                            'Statut_etudiant','Date_debut_statut','Date_fin_statut','Evenement','Date_evenement',
                            'Nb_heures_planifiées','Nb_heures_restant','Nb_heures_present','Nb_heures_absent','Nb_heures_non_renseigné');

        $datas['TDBEtudiants'][0]=$arrayHeader;
        $arrayValues=array();
        
        if (count ( $TDBEtudiants ) > 0) {
            foreach ( $TDBEtudiants as $d ) {
                $array=array();
                $array [] = ($d->titre == null) ? "0" : $d->titre;
                $array [] = ($d->code == null) ? "0" : $d->code;
                $array [] = ($d->status == null) ? "0" : $d->status;
                $array [] = ($d->date_debut == null) ? date('00/00/00') : $d->date_debut;
                $array [] = ($d->date_fin == null) ? date('00/00/00') : $d->date_fin;
                $array [] = ($d->nb_heures_af == null) ? "0" : (float)$d->nb_heures_af;
                $array [] = ($d->Etudiant == null) ? "0" : $d->Etudiant;
                $array [] = ($d->Groupe == null) ? "0" : $d->Groupe;
                $array [] = ($d->Statut_etudiant == null) ? "0" : $d->Statut_etudiant;
                $array [] = ($d->Date_debut_statut == null) ? date('00/00/00') : $d->Date_debut_statut;
                $array [] = ($d->Date_fin_statut == null) ? date('00/00/00') : $d->Date_fin_statut;
                $array [] = ($d->Evenement == null) ? "0" : $d->Evenement;
                $array [] = ($d->Date_evenement == null) ? date('00/00/00') : $d->Date_evenement;
                // $array [] = ($d->Nb_heures_planifiées == null)? "0": number_format($d->Nb_heures_planifiées, 2, '.', '');
                $array [] = ($d->Nb_heures_planifiées == null)? "0": (float)$d->Nb_heures_planifiées;
                $array [] = ($d->Nb_heures_restant == null)? "0" : (float)$d->Nb_heures_restant;
                $array [] = ($d->Nb_heures_present == null)? "0"  : (float)$d->Nb_heures_present;
                $array [] = ($d->Nb_heures_absent == null)? "0" : (float)$d->Nb_heures_absent;
                $array [] = ($d->Nb_heures_non_renseigné == null)? "0" : (float)$d->Nb_heures_non_renseigné;

                $arrayValues[]=$array;
            }
        }
        $datas['TDBEtudiants'][1]=$arrayValues;
		$datas['TDBEtudiants'][2]='TDBEtudiants';

        $xlsNameFile='TDB-Etudiants'.time().'.xlsx';
            if (count($datas)>0){
                $export = new TDBEtudiantsExport($datas);
                return Excel::download($export, $xlsNameFile);
            }
		return 0;
    }
}
