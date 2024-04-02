<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Group;
use App\Models\Param;
use App\Models\Sheet;
use App\Models\Member;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use Carbon\CarbonPeriod;
use App\Models\Categorie;
use App\Models\Formation;
use App\Models\Enrollment;
use App\Models\Sheetparam;
use Illuminate\Http\Request;
use App\Imports\EntitiesImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Library\Services\DbHelperTools;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    const PATH_TO_JSON_DATA = "custom/demo_datas/";
    public function exportDemoDatas(){
        $DbHelperTools=new DbHelperTools();
        $data = [
            't0' => ['par_pf_categories' => $DbHelperTools->getDatasFromTableToArray('par_pf_categories')],
            't1' => ['par_params' => $DbHelperTools->getDatasFromTableToArray('par_params')], 
            't2' => ['pf_formations' => $DbHelperTools->getDatasFromTableToArray('pf_formations')], 
            't3' => ['pf_formation_param' => $DbHelperTools->getDatasFromTableToArray('pf_formation_param')], 
            't4' => ['en_entities' => $DbHelperTools->getDatasFromTableToArray('en_entities')],
            't5' => ['en_contacts' => $DbHelperTools->getDatasFromTableToArray('en_contacts')],
            't6' => ['en_adresses' => $DbHelperTools->getDatasFromTableToArray('en_adresses')],
            't7' => ['par_usr_profils' => $DbHelperTools->getDatasFromTableToArray('par_usr_profils')], 
            't8' => ['par_usr_roles' => $DbHelperTools->getDatasFromTableToArray('par_usr_roles')], 
            't9' => ['par_usr_permissions' => $DbHelperTools->getDatasFromTableToArray('par_usr_permissions')],
            't10' => ['users' => $DbHelperTools->getDatasFromTableToArray('users')], 
            't11' => ['par_usr_permission_role' => $DbHelperTools->getDatasFromTableToArray('par_usr_permission_role')], 
            't12' => ['par_usr_role_user' => $DbHelperTools->getDatasFromTableToArray('par_usr_role_user')], 
            't13' => ['password_resets' => $DbHelperTools->getDatasFromTableToArray('password_resets')],
            't14' => ['personal_access_tokens' => $DbHelperTools->getDatasFromTableToArray('personal_access_tokens')],
            't15' => ['par_planning_templates' => $DbHelperTools->getDatasFromTableToArray('par_planning_templates')],
            't16' => ['par_template_periods' => $DbHelperTools->getDatasFromTableToArray('par_template_periods')],
            't17' => ['af_actions' => $DbHelperTools->getDatasFromTableToArray('af_actions')],
            't18' => ['pf_sheets' => $DbHelperTools->getDatasFromTableToArray('pf_sheets')], 
            't19' => ['pf_sheet_param' => $DbHelperTools->getDatasFromTableToArray('pf_sheet_param')],
            't20' => ['af_sessions' => $DbHelperTools->getDatasFromTableToArray('af_sessions')],
            't21' => ['af_sessiondates' => $DbHelperTools->getDatasFromTableToArray('af_sessiondates')],
            't22' => ['af_schedules' => $DbHelperTools->getDatasFromTableToArray('af_schedules')],
            't23' => ['pf_prices' => $DbHelperTools->getDatasFromTableToArray('pf_prices')],
            't24' => ['af_enrollments' => $DbHelperTools->getDatasFromTableToArray('af_enrollments')],
            't25' => ['af_members' => $DbHelperTools->getDatasFromTableToArray('af_members')],
            't26' => ['res_ressources' => $DbHelperTools->getDatasFromTableToArray('res_ressources')],
            't27' => ['af_scheduleressources' => $DbHelperTools->getDatasFromTableToArray('af_scheduleressources')],
            't28' => ['en_contracts' => $DbHelperTools->getDatasFromTableToArray('en_contracts')],
            't29' => ['af_schedulecontacts' => $DbHelperTools->getDatasFromTableToArray('af_schedulecontacts')],
            't30' => ['par_document_models' => $DbHelperTools->getDatasFromTableToArray('par_document_models')],
            't31' => ['par_settings' => $DbHelperTools->getDatasFromTableToArray('par_settings')],
            't32' => ['af_rel_price' => $DbHelperTools->getDatasFromTableToArray('af_rel_price')],
            't33' => ['pf_rel_price' => $DbHelperTools->getDatasFromTableToArray('pf_rel_price')],
            't34' => ['dev_estimates' => $DbHelperTools->getDatasFromTableToArray('dev_estimates')],
            't35' => ['dev_estimate_items' => $DbHelperTools->getDatasFromTableToArray('dev_estimate_items')],
            't36' => ['par_taxes' => $DbHelperTools->getDatasFromTableToArray('par_taxes')],
            't37' => ['help_indexes' => $DbHelperTools->getDatasFromTableToArray('help_indexes')],
            't38' => ['af_agreements' => $DbHelperTools->getDatasFromTableToArray('af_agreements')],
            't39' => ['af_agreement_items' => $DbHelperTools->getDatasFromTableToArray('af_agreement_items')],
            't40' => ['af_fundings' => $DbHelperTools->getDatasFromTableToArray('af_fundings')],
            't41' => ['af_funding_payments' => $DbHelperTools->getDatasFromTableToArray('af_funding_payments')],
            't42' => ['inv_invoices' => $DbHelperTools->getDatasFromTableToArray('inv_invoices')],
            't43' => ['inv_invoice_items' => $DbHelperTools->getDatasFromTableToArray('inv_invoice_items')],
            't44' => ['inv_invoice_payments' => $DbHelperTools->getDatasFromTableToArray('inv_invoice_payments')],
            't45' => ['af_convocations' => $DbHelperTools->getDatasFromTableToArray('af_convocations')],
            't46' => ['activity_log' => $DbHelperTools->getDatasFromTableToArray('activity_log')],
            't47' => ['af_groups' => $DbHelperTools->getDatasFromTableToArray('af_groups')],
            't48' => ['pf_timestructurescategories' => $DbHelperTools->getDatasFromTableToArray('pf_timestructurescategories')],
            't49' => ['pf_timestructures' => $DbHelperTools->getDatasFromTableToArray('pf_timestructures')],
         ];
         $data = json_encode($data);
         $backupFileName = time() . '_datas.json';
         $fileNameToDownload = 'datas.json';
        //put backup file
        $path = self::PATH_TO_JSON_DATA.'backup';
        if(!File::exists($path)) {
            File::makeDirectory($path, 0755, true, true);
        } 
        File::put(public_path($path.'/'.$backupFileName),$data);
        //put file 
        File::put(public_path(self::PATH_TO_JSON_DATA.$fileNameToDownload),$data);
        return Response::download(public_path(self::PATH_TO_JSON_DATA.$fileNameToDownload));
    }
    public function importDemoDatas(){
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $path = self::PATH_TO_JSON_DATA."datas.json";
        $json = json_decode(file_get_contents($path), true);
        $chunk_result = array_chunk($json, 1000);
        foreach($json as $key => $datas){
            foreach($datas as $table => $rows){
                echo '------------------'.$table.'--------------------<br>';
                DB::table($table)->insert($rows);
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return true;
    }
    
    public function clearDemoDatas(){
        /* 
            Vider les données de démonstration
        */
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        //t49
        DB::table('pf_timestructures')->truncate();
        //t48
        DB::table('pf_timestructurescategories')->truncate();
        //t47
        DB::table('af_groups')->truncate();
        //t46
        DB::table('activity_log')->truncate();
        //t45
        DB::table('af_convocations')->truncate();
        //t44
        DB::table('inv_invoice_payments')->truncate();
        //t43
        DB::table('inv_invoice_items')->truncate();
        //t42
        DB::table('inv_invoices')->truncate();
        //t41
        DB::table('af_funding_payments')->truncate();
        //t40
        DB::table('af_fundings')->truncate();
        //t39
        DB::table('af_agreement_items')->truncate();
        //t38
        DB::table('af_agreements')->truncate();
        //t37
        DB::table('help_indexes')->truncate();
        //t36
        DB::table('par_taxes')->truncate();
        //t35
        DB::table('dev_estimate_items')->truncate();
        //t34
        DB::table('dev_estimates')->truncate();
        //t33
        DB::table('pf_rel_price')->truncate();
        //t32
        DB::table('af_rel_price')->truncate();
        //t31
        DB::table('par_settings')->truncate();
        //t30
        DB::table('par_document_models')->truncate();
        //t29
        DB::table('af_schedulecontacts')->truncate();
        //t28
        DB::table('en_contracts')->truncate();
        //t27
        DB::table('af_scheduleressources')->truncate();
        //t26
        DB::table('res_ressources')->truncate();
        //t25
        DB::table('af_members')->truncate();
        //t24
        DB::table('af_enrollments')->truncate();
        //t23
        DB::table('pf_prices')->truncate();
        //t22
        DB::table('af_schedules')->truncate();
        //t21
        DB::table('af_sessiondates')->truncate();
        //t20
        DB::table('af_sessions')->truncate();
        //t19
        DB::table('af_actions')->truncate();
        //t18
        DB::table('par_template_periods')->truncate();
        //t17
        DB::table('par_planning_templates')->truncate();
        //t16
        DB::table('personal_access_tokens')->truncate();
        //t15
        DB::table('password_resets')->truncate();
        //t14
        DB::table('par_usr_role_user')->truncate();
        //t13
        DB::table('par_usr_permission_role')->truncate();
        //t12
        DB::table('users')->truncate();
        //t11
        DB::table('par_usr_permissions')->truncate();
        //t10
        DB::table('par_usr_roles')->truncate();
        //t9
        DB::table('par_usr_profils')->truncate();
        //t8
        DB::table('en_adresses')->truncate();
        //t7
        DB::table('en_contacts')->truncate();
        //t6
        DB::table('en_entities')->truncate();
        //t5
        DB::table('pf_sheet_param')->truncate();
        //t4
        DB::table('pf_sheets')->truncate();
        //t3
        DB::table('pf_formation_param')->truncate();
        //t2
        DB::table('pf_formations')->truncate();
        //t1
        DB::table('par_params')->truncate();
        //t0
        DB::table('par_pf_categories')->truncate();
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        exit();
    }
    public function importDemoDatasOLD(){
        /*
            Importer les données de démonstration
            par_pf_categories 
            par_params 
            pf_formations 
            pf_sheets 
        */
        $this->insertCategories();
        echo '*******les catégories ont été insérées avec succès******';
        $this->insertParams();
        echo '*******les paramétres ont été insérées avec succès******';
        $this->insertFormations();
        echo '*******les formations ont été insérées avec succès******';
        $this->insertSheets();
        echo '*******les fiches techniques ont été insérées avec succès******';
        $this->insertEntities();
        echo '*******les entitées ont été insérées avec succès******';
        $this->insertContacts();
        echo '*******les contacts ont été insérées avec succès******';
        $this->insertAdresses();
        echo '*******les adresses ont été insérées avec succès******';
        exit();
    }
    public function insertFormations()
    {
        $path = self::PATH_TO_JSON_DATA."formations.json";
        $json = json_decode(file_get_contents($path), true);
        $datas=$json['formations'];
        $DbHelperTools=new DbHelperTools();
        foreach($datas as $data){
            $DbHelperTools->manageFormation($data);
        }
        return true;
    }
    public function insertSheets()
    {   
        $path = self::PATH_TO_JSON_DATA."sheets.json";
        $json = json_decode(file_get_contents($path), true);
        $dataSheets=$json['sheets'];
        $DbHelperTools=new DbHelperTools();
        foreach($dataSheets as $data){
            $sheet_id=$DbHelperTools->manageSheets($data);
            if($sheet_id>0){
                //Récupérer toutes les lignes pf_sheet_param de la fiche
                //But c'est ignorer la double creation d'un paramétre
                $sheet = Sheet::find($sheet_id);
                $idsSheetparam=$sheet->sheetparams->pluck('id');
                //Récupération des paramétrages concerant la fiche 
                $idsparams = Param::select('id','name')->where([['param_code', 'PF_TYPE_SHEETS'],['is_active',1]])->whereNotIn('id',$idsSheetparam)->get();
                foreach ($idsparams as $p) {
                    $dataSheetparam=array(
                        'id'=>0,
                        'title'=>$p['name'],
                        'content'=>null,
                        'order_show'=>1,
                        'sheet_id'=>$sheet_id,
                        'param_id'=>$p['id'],
                    );
                    $DbHelperTools->manageSheetsParams($dataSheetparam);
                }
            }
        }
        return true;
    }
    /*
        Insérer les paramètres dans la table 'par_pf_params'
     */
    public function insertParams()
    {
        $path = self::PATH_TO_JSON_DATA."params.json";
        $json = json_decode(file_get_contents($path), true);
        $dataParams=$json['params'];
        $DbHelperTools=new DbHelperTools();
        foreach($dataParams as $data){
            $DbHelperTools->manageParams($data);
        }
        return true;
    }
    public function insertCategories(){
        $path = self::PATH_TO_JSON_DATA."categories.json";
        $json = json_decode(file_get_contents($path), true);
        $dataCategories=$json['categories'];
        $DbHelperTools=new DbHelperTools();
        foreach($dataCategories as $data){
            $DbHelperTools->manageCategories($data);
        }
        return true;
    }
    public function insertEntities()
    {
        $path = self::PATH_TO_JSON_DATA."entities.json";
        $json = json_decode(file_get_contents($path), true);
        $datas=$json['entities'];
        $DbHelperTools=new DbHelperTools();
        foreach($datas as $data){
            $DbHelperTools->manageEntitie($data);
        }
        return true;
    }
    public function insertContacts()
    {
        $path = self::PATH_TO_JSON_DATA."contacts.json";
        $json = json_decode(file_get_contents($path), true);
        $datas=$json['contacts'];
        $DbHelperTools=new DbHelperTools();
        foreach($datas as $data){
            $DbHelperTools->manageContact($data);
        }
        return true;
    }
    public function insertAdresses()
    {
        $path = self::PATH_TO_JSON_DATA."adresses.json";
        $json = json_decode(file_get_contents($path), true);
        $datas=$json['adresses'];
        $DbHelperTools=new DbHelperTools();
        foreach($datas as $data){
            $DbHelperTools->manageAdresse($data);
        }
        return true;
    }
    public function test(){
        $typeSession = 'AF_SESSION_TYPE_SCSS';
        //$typeSession = 'AF_SESSION_TYPE_SCAS';
        //$typeSession = 'AF_SESSION_TYPE_DISC';
        $nbdaysToProgram = 8;
        $start_date = '2021-03-09';
        $DbHelperTools=new DbHelperTools();
        $dates = $DbHelperTools->getDatesToProgram($start_date,$nbdaysToProgram,$typeSession);
        dd($dates);
        exit();
    }
    public function importEntities()
    {
        $DbHelperTools=new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/en_entities_societes.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
        //Insert entities
        if(count($array[0])>0){
            foreach($array[0] as $k=>$row){
                //dd($row);
                $rs=Entitie::select('id')->where('ref',$row['ref'])->get();
                //$entity_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
                $entity_id=0;
                //dd($entity_id);
                $data = [
                    "id" => $entity_id,
                    "ref" => $row['ref'],   
                    "entity_type" => $row['entity_type'],
                    "type" => $row['type'],
                    "type_establishment" => $row['type_establishment'],
                    "name" => $row['name'],
                    "siren" => $row['siren'],
                    "siret" => $row['siret'],
                    "acronym" => $row['acronym'],
                    "naf_code" => null,
                    "pro_phone" => $row['pro_phone'],
                    "pro_mobile" => $row['pro_mobile'],
                    "fax" => $row['fax'],
                    "email" => $row['email'],
                    "is_active" => $row['is_active'],
                    "prospecting_area" => $row['prospectiong_area'],
                    "matricule_code" => $row['matricule_code'],
                    "personal_thirdparty_code" => $row['personal_thirdparty_code'],
                    "vendor_code" => '',
                    "is_client" => $row['is_client'],
                    "is_funder" => $row['is_funder'],
                    "is_former" => $row['is_former'],
                    "is_prospect" => $row['is_prospect'],
                    "is_stage_site" => $row['is_stage_site'],
                    "iban" => null,
                    "bic" => null,
                  ];
                  $entity_id=$DbHelperTools->manageEntitie($data);
                  if($entity_id>0){
                  //Insert adresse
                    $rs=Adresse::select('id')->where([
                        ['postal_code',$row['cp']],['city',$row['ville']],['entitie_id',$entity_id]
                    ])->get();
                    $adr_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
                    $line_1=$row['res'].' '.$row['complement_adr'].' '.$row['bp'];
                    $line_2=$row['numero_voie'].' '.$row['indice'].' '.$row['nature'].' '.$row['label'];
                    $dataAdresse = array(
                        "id" => $adr_id,
                        "entitie_id" => $entity_id,
                        "line_1" => $line_1,
                        "line_2" => $line_2,
                        "line_3" => null,
                        "postal_code" => $row['cp'],
                        "city" => $row['ville'],
                        "country" => 'fr',
                        "is_main_entity_address" => 1,
                        "is_billing" => 1,
                        "is_formation_site" => 0,
                        "is_stage_site" => 0,
                    );
                    $adresse_id = $DbHelperTools->manageAdresse($dataAdresse);
                }
            }
        }
        //Insert contacts
        $success=$this->importContacts();
        //Importer les particulier
        //$success=$this->importParticularsEntities();
        return true;
    } 
    public function importContacts()
    {
        $DbHelperTools=new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/en_contacts_societes.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
        if(count($array[0])>0){
            $genderArray=['Madame'=>'Mme','Mademoiselle'=>'Mme','Monsieur'=>'M','Docteur'=>'M'];
            foreach($array[0] as $k=>$row){
                //dump($k);
                //dd($row);
                $rs=Entitie::select('id')->where('ref',$row['entity_ref'])->get();
                $entity_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
                //dd($entity_id);
                if($entity_id>0){
                    if(!empty($row['firstname']) || !empty($row['lastname'])){
                        $email=$row['email'];
                         /*
                        if(!empty($row['email'])){
                            $rsContact=Contact::select('id')->where([['email',$row['email'],['entitie_id',$entity_id]]])->get();
                        }else{
                            if(!empty($row['firstname']) && !empty($row['lastname'])){
                                $rsContact=Contact::select('id')->where([['firstname',$row['firstname']],['lastname',$row['lastname']],['entitie_id',$entity_id]])->get();
                            }elseif(!empty($row['firstname']) && empty($row['lastname'])){
                                $rsContact=Contact::select('id')->where([['firstname',$row['firstname'],['entitie_id',$entity_id]]])->get();
                            }elseif(empty($row['firstname']) && !empty($row['lastname'])){
                                $rsContact=Contact::select('id')->where([['lastname',$row['lastname'],['entitie_id',$entity_id]]])->get();
                            }
                        } */

                        $rsContact=[];
                        /* if(!empty($row['firstname']) && !empty($row['lastname'])){
                            $rsContact=Contact::select('id')->where([['firstname',$row['firstname']],['lastname',$row['lastname']],['entitie_id',$entity_id]])->get();
                        }elseif(!empty($row['firstname']) && empty($row['lastname'])){
                            $rsContact=Contact::select('id')->where([['firstname',$row['firstname'],['entitie_id',$entity_id]]])->get();
                        }elseif(empty($row['firstname']) && !empty($row['lastname'])){
                            $rsContact=Contact::select('id')->where([['lastname',$row['lastname'],['entitie_id',$entity_id]]])->get();
                        } */

                        $contact_id=(count($rsContact)>0 && $rsContact[0]['id'])?$rsContact[0]['id']:0;
                        $gender=(!empty($row['gender']))?$genderArray[$row['gender']]:null;
                        $dataContact=array(
                            "id" => $contact_id,
                            "entitie_id" => $entity_id,
                            "is_main_contact" => $row['is_main_contact'],
                            "is_billing_contact" => $row['is_billing_contact'],
                            "is_order_contact" => $row['is_order_contact'],
                            "is_active" => $row['is_active'],
                            "is_trainee_contact" => $row['is_trainee_contact'],
                            "gender" => $gender,
                            "firstname" => $row['firstname'],
                            "lastname" => $row['lastname'],
                            "email" => $email,
                            "function" => $row['function'],
                            "pro_phone" => $row['pro_phone'],
                            "pro_mobile" => $row['pro_mobile'],
                            "birth_date" => null,
                            "type_former_intervention" => $row['type_former_intervention'],
                            "is_former" => $row['is_former'],
                        );
                        //var_dump($dataContact);
                        //dd($dataContact);
                        $id=$DbHelperTools->manageContact($dataContact);
                        echo 'CREATED ID : '.$id;
                    }
                }else{
                    echo '<p style="color:red;">ENTITY NOT FOUND : '.$row['entity_ref'].' '.$row['firstname'].' '.$row['lastname'].'</p>';
                }
            }
        }
        return true;
    }
    public function importParticularsEntities()
    {
        $DbHelperTools=new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/en_entities_particuliers.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
        //Insert entities
        if(count($array[0])>0){
            foreach($array[0] as $k=>$row){
                //dd($row);
                $rs=Entitie::select('id')->where('ref',$row['ref'])->get();
                //$entity_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
                $entity_id=0;
                $is_prospect=($row['is_prospect']=='Oui')?1:0;
                //dd($entity_id);
                echo 'INDEX : '.$k.' - '.$entity_id.'<br>';
                $data = [
                    "id" => $entity_id,
                    "ref" => $row['ref'],   
                    "entity_type" => $row['entity_type'],
                    "type" => $row['type'],
                    "type_establishment" => $row['type_establishment'],
                    "name" => $row['name'],
                    "siren" => $row['siren'],
                    "siret" => $row['siret'],
                    "acronym" => $row['acronym'],
                    "naf_code" => null,
                    "pro_phone" => $row['pro_phone'],
                    "pro_mobile" => $row['pro_mobile'],
                    "fax" => $row['fax'],
                    "email" => $row['email'],
                    "is_active" => $row['is_active'],
                    "prospecting_area" => $row['prospectiong_area'],
                    "matricule_code" => $row['matricule_code'],
                    "personal_thirdparty_code" => $row['personal_thirdparty_code'],
                    "vendor_code" => '',
                    "is_client" => $row['is_client'],
                    "is_funder" => $row['is_funder'],
                    "is_former" => $row['is_former'],
                    "is_prospect" => $is_prospect,
                    "is_stage_site" => $row['is_stage_site'],
                    "iban" => null,
                    "bic" => null,
                  ];
                  $entity_id=$DbHelperTools->manageEntitie($data);
                  if($entity_id>0){
                  //Insert adresse
                    $rs=Adresse::select('id')->where([
                        ['postal_code',$row['cp']],['city',$row['ville']],['entitie_id',$entity_id]
                    ])->get();
                    $adr_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
                    $line_1=$row['res'].' '.$row['complement_adr'].' '.$row['bp'];
                    $line_2=$row['numero_voie'].' '.$row['indice'].' '.$row['nature'].' '.$row['label'];
                    $dataAdresse = array(
                        "id" => $adr_id,
                        "entitie_id" => $entity_id,
                        "line_1" => $line_1,
                        "line_2" => $line_2,
                        "line_3" => null,
                        "postal_code" => $row['cp'],
                        "city" => $row['ville'],
                        "country" => 'fr',
                        "is_main_entity_address" => 1,
                        "is_billing" => 1,
                        "is_formation_site" => 0,
                        "is_stage_site" => 0,
                    );
                    $adresse_id = $DbHelperTools->manageAdresse($dataAdresse);
                }
            }
        }
        //Insert contacts
        //$success=$this->importParticularsContacts();
        return true;
    }
    public function importParticularsContacts()
    {
        $DbHelperTools=new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/en_contacts_particuliers.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
        //echo 'TOTAL : '.count($array[0]).'<br>';exit();
        $i=0;
        if(count($array[0])>0){
            $genderArray=['Madame'=>'Mme','Mademoiselle'=>'Mme','Monsieur'=>'M','Docteur'=>'M'];
            foreach($array[0] as $k=>$row){
                //dd($row);
                $rs=Entitie::select('id')->where('ref',$row['entity_ref'])->get();
                $entity_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
                //dd($entity_id);
                echo 'INDEX : '.$k.'--------------ID : '.$entity_id.' ---------- '.$row['entity_ref'].'-------'.$row['firstname'].'<br>';
                $idc=0;
                if($entity_id>0){
                    if(!empty($row['firstname']) || !empty($row['lastname'])){
                        $email=$row['email'];
                        /* if(!empty($row['email'])){
                            $rsContact=Contact::select('id')->where([['email',$row['email'],['entitie_id',$entity_id]]])->get();
                        }else{
                            if(!empty($row['firstname']) && !empty($row['lastname'])){
                                $rsContact=Contact::select('id')->where([['firstname',$row['firstname']],['lastname',$row['lastname']],['entitie_id',$entity_id]])->get();
                            }elseif(!empty($row['firstname']) && empty($row['lastname'])){
                                $rsContact=Contact::select('id')->where([['firstname',$row['firstname'],['entitie_id',$entity_id]]])->get();
                            }elseif(empty($row['firstname']) && !empty($row['lastname'])){
                                $rsContact=Contact::select('id')->where([['lastname',$row['lastname'],['entitie_id',$entity_id]]])->get();
                            }
                        } */
                        $rsContact=[];
                        $contact_id=(count($rsContact)>0 && $rsContact[0]['id'])?$rsContact[0]['id']:0;
                        $gender=(!empty($row['gender']))?$genderArray[$row['gender']]:null;

                        $birth_date=($row['birth_date']>0)?\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['birth_date']):null;

                        $dataContact=array(
                            "id" => $contact_id,
                            "entitie_id" => $entity_id,
                            "is_main_contact" => $row['is_main_contact'],
                            "is_billing_contact" => $row['is_billing_contact'],
                            "is_order_contact" => $row['is_order_contact'],
                            "is_active" => $row['is_active'],
                            "is_trainee_contact" => $row['is_trainee_contact'],
                            "gender" => $gender,
                            "firstname" => $row['firstname'],
                            "lastname" => $row['lastname'],
                            "email" => $email,
                            "function" => $row['function'],
                            "pro_phone" => $row['pro_phone'],
                            "pro_mobile" => $row['pro_mobile'],
                            "birth_date" => $birth_date,
                            "type_former_intervention" => $row['type_former_intervention'],
                            "is_former" => $row['is_former'],
                        );
                        $idc=$DbHelperTools->manageContact($dataContact);
                        $i++; 
                    }
                }else{
                    echo '<p style="color:red;">ENTITY NOT FOUND : '.$row['entity_ref'].' '.$row['firstname'].' '.$row['lastname'].'</p>';
                }
                $color=($idc>0)?'green':'red';
                echo '<p style="color:'.$color.';">CONTACT ID : '.$idc.'</p>';
            }
        }
        echo '<p style="color:blue;">TOTAL : '.$i.'</p>';
        return true;
    }
    public function importExternalIntervenants()
    {
        $DbHelperTools=new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/en_intervenants_externe.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
        //dd($array);
        //Insert entities
        if(count($array[0])>0){
            foreach($array[0] as $k=>$row){
                //dd($row);
                $entity_type=$row['entity_type'];
                
                //$rs=Entitie::select('id')->where([['name',$row['name']],['entity_type',$entity_type]])->get();
                //$entity_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;

                $entity_id=0;
                echo 'INDEX : '.$k.' - '.$row['name'].' ID = '.$entity_id.'<br>';
                $flag=true;
                if($flag){                
                    $data = [
                        "id" => $entity_id,
                        "ref" => ($entity_id==0)?$DbHelperTools->generateEntityCode():'',
                        "entity_type" => $row['entity_type'],
                        /* "type" => $row['type'],
                        "type_establishment" => $row['type_establishment'], */
                        "name" => $row['name'],
                        /* "siren" => $row['siren'],*/
                        "siret" => $row['siret'], 
                        "acronym" => $row['acronym'],
                        "naf_code" => null,
                        "pro_phone" => $row['pro_phone'],
                        "pro_mobile" => $row['pro_mobile'],
                        "fax" => $row['fax'],
                        "email" => $row['email'],
                        "is_active" => $row['is_active'],
                        /* "prospecting_area" => $row['prospectiong_area'],
                        "matricule_code" => $row['matricule_code'],
                        "personal_thirdparty_code" => $row['personal_thirdparty_code'],
                        "vendor_code" => '', */
                        "is_client" => $row['is_client'],
                        "is_funder" => $row['is_funder'],
                        "is_former" => $row['is_former'],
                        "is_prospect" => $row['is_prospect'],
                        "is_stage_site" => $row['is_stage_site'],
                        /* "iban" => null,
                        "bic" => null, */
                    ];
                    //dd($data);
                    $entity_id=$DbHelperTools->manageEntitie($data);
                    if($entity_id>0){
                    //Insert contact
                            $genderArray=['Madame'=>'Mme','Mademoiselle'=>'Mme','Monsieur'=>'M','Docteur'=>'M','Organisme'=>'M'];
                            //Sur contrat Interne  Sur facture
                            $typeFormerInterventionArray=['P'=>'Sur contrat','S'=>'Sur facture'];
                            $contact_id=0;
                            $gender=(!empty($row['gender']))?$genderArray[$row['gender']]:null;
                            $birth_date=($row['birth_date']>0)?\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['birth_date']):null;
                            $type_former_intervention=(!empty($row['entity_type']))?$typeFormerInterventionArray[$row['entity_type']]:null;
                            $dataContact=array(
                                "id" => $contact_id,
                                "entitie_id" => $entity_id,
                                "is_main_contact" => 1,
                                "is_billing_contact" => 1,
                                "is_order_contact" => 0,
                                "is_active" => $row['is_active'],
                                "is_trainee_contact" => 0,
                                "gender" => $gender,
                                "firstname" => $row['firstname'],
                                "lastname" => $row['lastname'],
                                "email" => $row['email'],
                                "function" => null,
                                "pro_phone" => $row['pro_phone'],
                                "pro_mobile" => $row['pro_mobile'],
                                "birth_date" => $birth_date,
                                "type_former_intervention" => $type_former_intervention,
                                "is_former" => $row['is_former'],
                            );
                            $idc=$DbHelperTools->manageContact($dataContact);   
                            //Insert adresse
                            $rs=Adresse::select('id')->where([
                                ['postal_code',$row['cp']],['city',$row['ville']],['entitie_id',$entity_id]
                            ])->get();
                            $adr_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
                            $line_1=$row['res'].' '.$row['complement_adr'].' '.$row['bp'];
                            $line_2=$row['numero_voie'].' '.$row['indice'].' '.$row['nature'].' '.$row['label'];
                            $dataAdresse = array(
                                "id" => $adr_id,
                                "entitie_id" => $entity_id,
                                "line_1" => $line_1,
                                "line_2" => $line_2,
                                "line_3" => null,
                                "postal_code" => $row['cp'],
                                "city" => $row['ville'],
                                "country" => 'fr',
                                "is_main_entity_address" => 1,
                                "is_billing" => 1,
                                "is_formation_site" => 0,
                                "is_stage_site" => 0,
                            );
                            $adresse_id = $DbHelperTools->manageAdresse($dataAdresse);
                    }
                }
            }
        }
        return true;
    } 
    public function importInternalIntervenants()
    {
        $DbHelperTools=new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/en_intervenants_interne.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
        //dd($array);
        //Insert 
        if(count($array[0])>0){
            //Creer ou mettre a jour une entités CRFPE
            $rs=Entitie::select('id')->where('ref','CRFPE')->get();
            $entity_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
            if($entity_id==0){
                $data = [
                    "id" => $entity_id,
                    "ref" => 'CRFPE',
                    "entity_type" => 'S',
                    "name" => 'Centre régional de formation des professionnels de l\'enfance',
                    "siret" => '783 707 797 00016', 
                    "acronym" => '',
                    "naf_code" => null,
                    "pro_phone" => '33 (0)3 20 14 93 00',
                    "pro_mobile" => '',
                    "fax" => '33 (0)3 20 14 93 09',
                    "email" => 'accueil@crfpe.fr',
                    "is_active" => 1,
                    "is_client" => 0,
                    "is_funder" => 0,
                    "is_former" => 1,
                    "is_prospect" => 0,
                    "is_stage_site" => 1,
                    "iban" => 'FR76 3000 3011 0600 0502 7028 147',
                    "bic" => 'SOGEFRPP',
                ];
                $entity_id=$DbHelperTools->manageEntitie($data);
                if($entity_id){
                    $rs=Adresse::select('id')->where('entitie_id',$entity_id)->get();
                    $adr_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
                    //Insert adresse
                    $dataAdresse = array(
                        "id" => $adr_id,
                        "entitie_id" => $entity_id,
                        "line_1" => '465 RUE COURTOIS',
                        "line_2" => '',
                        "line_3" => null,
                        "postal_code" => '59042',
                        "city" => 'LILLE',
                        "country" => 'fr',
                        "is_main_entity_address" => 1,
                        "is_billing" => 1,
                        "is_formation_site" => 0,
                        "is_stage_site" => 0,
                    );
                    $adresse_id = $DbHelperTools->manageAdresse($dataAdresse);
                }
            }
            foreach($array[0] as $k=>$row){
                    $contact_id=0;
                    //dd($row['birth_date']);
                    $birth_date=($row['birth_date']>0)?\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['birth_date']):null;
                    $dataContact=array(
                            "id" => $contact_id,
                            "entitie_id" => $entity_id,
                            "is_main_contact" => 1,
                            "is_billing_contact" => 1,
                            "is_order_contact" => 0,
                            "is_active" => 1,
                            "is_trainee_contact" => 0,
                            "gender" => $row['gender'],
                            "firstname" => $row['firstname'],
                            "lastname" => $row['lastname'],
                            "email" => '',
                            "function" => $row['function'],
                            "pro_phone" => '',
                            "pro_mobile" => '',
                            "birth_date" => $birth_date,
                            "type_former_intervention" => $row['type_former_intervention'],
                            "is_former" => $row['is_former'],
                    );
                    $idc=$DbHelperTools->manageContact($dataContact);   
            }
        }
        return true;
    } 
    public function importEntityContactAddressEnrollmentMembers()
    {
        $DbHelperTools=new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/en_entity_contact_enrollment.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
   //     dd($array);
        //Insert entities
        if(count($array[0])>0){
            foreach($array[0] as $k=>$row){
        //        dd($row);
       //         $entity_type=$row['entity_type'];
                if($row['id_entity']>0)
                    continue;
                $nameEntity = $row['nom_de_naissance'].' '.$row['prenom'];
                $rs=Entitie::select('id')->where([['name',$nameEntity],['entity_type','P']])->get();
                $entity_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;

        //        $entity_id=0;
            /*    if($entity_id>0)
                    continue;*/
                echo 'INDEX : '.$k.' - '.$nameEntity.' ID = '.$entity_id.'<br>';
                $flag=true;
                if($flag){                
                    $data = [
                        "id" => $entity_id,
                        "ref" => ($entity_id==0)?$DbHelperTools->generateEntityCode():'',
                        "entity_type" => 'P',//$row['entity_type'],
                        /* "type" => $row['type'],
                        "type_establishment" => $row['type_establishment'], */
                        "name" => $nameEntity,
                        /* "siren" => $row['siren'],*/
                    //    "siret" => $row['siret'], 
                    //    "acronym" => $row['acronym'],
                    //    "naf_code" => null,
                        "pro_phone" => $row['tel_fixe'],
                        "pro_mobile" => $row['tel_portable'],
                    //    "fax" => $row['fax'],
                        "email" => $row['email_perso'],
                        "is_active" => 1,
                        /* "prospecting_area" => $row['prospectiong_area'],
                        "matricule_code" => $row['matricule_code'],
                        "personal_thirdparty_code" => $row['personal_thirdparty_code'],
                        "vendor_code" => '', */
                        "is_client" => 1,
                        "is_funder" => 0,
                        "is_former" => 0,
                        "is_prospect" => 1,
                        "is_stage_site" => 0,
                        /* "iban" => null,
                        "bic" => null, */
                    ];
                    //dd($data);
                    $entity_id=$DbHelperTools->manageEntitie($data);
                    
                    if($entity_id>0){
                    //Insert contact
                        $genderArray=['Femme'=>'Mme','Homme'=>'M','Madame'=>'Mme','Mademoiselle'=>'Mme','Monsieur'=>'M','Docteur'=>'M','Organisme'=>'M'];
                        //Sur contrat Interne  Sur facture
                //        $typeFormerInterventionArray=['P'=>'Sur contrat','S'=>'Sur facture'];
                        $contact_id=$row['id_contact']>0?$row['id_contact']:0;
                        $lastname = $row["nom_dusage"]!=null?$row["nom_dusage"] : $row["nom_de_naissance"];
                        $rsContact=Contact::select('id')->where([['firstname',$row['prenom']],['lastname',$lastname],['email',$row['email_perso']],['entitie_id',$entity_id]])->get();
                        $contact_id=(count($rsContact)>0 && $rsContact[0]['id'])?$rsContact[0]['id']:0;

                        $gender=(!empty($row['genre']))?$genderArray[$row['genre']]:null;
                        $birth_date=($row['date_naissance']>0)?\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date_naissance']):null;
                //          $type_former_intervention=(!empty($row['entity_type']))?$typeFormerInterventionArray[$row['entity_type']]:null;
                        
                        $birthname = $row["nom_dusage"]!=null?$row["nom_de_naissance"] : '';
                        $dataContact=array(
                            "id" => $contact_id,
                            "entitie_id" => $entity_id,
                            "is_main_contact" => 1,
                            "is_billing_contact" => 0,
                            "is_order_contact" => 0,
                            "is_active" => 1,
                            "is_trainee_contact" => 1,
                            "gender" => $gender,
                            "firstname" => $row['prenom'],
                            "lastname" => $lastname,
                            "email" => $row['email_perso'],
                            "function" => null,
                            "pro_phone" => $row['tel_fixe'],
                            "pro_mobile" => $row['tel_portable'],
                            "birth_date" => $birth_date,
                            "birth_name" => $birthname,
                    //        "type_former_intervention" => $type_former_intervention,
                            "is_former" => 0,
                        );
                        $idc=$DbHelperTools->manageContact($dataContact);
                //        dd($nameEntity,$entity_id,$idc);
                        //Insert adresse
                        $rs=Adresse::select('id')->where([
                            ['postal_code',$row['code_postal']],['city',$row['ville']],['entitie_id',$entity_id]
                        ])->get();
                        $adr_id=(count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;
              //          dd($adr_id); 
                        $line_1=$row['adresse_num'].' '.$row['adresse_num2'].' '.$row['adresse_typevoie'].' '.$row['adresse_nomvoie'];
                        $line_2=$row['residence_batiment_escalier_appartement'].' '.$row['adresse2'];
                        $line_3=$row['adresse3'];
                        $dataAdresse = array(
                            "id" => $adr_id,
                            "entitie_id" => $entity_id,
                            "line_1" => $line_1,
                            "line_2" => $line_2,
                            "line_3" => $line_3,
                            "postal_code" => $row['code_postal'],
                            "city" => $row['ville'],
                            "country" => 'fr',
                //             "is_main_entity_address" => 1,
                            "is_main_contact_address" => 1,
                            "is_billing" => 1,
                            "is_formation_site" => 0,
                            "is_stage_site" => 0,
                        );
                        $adresse_id = $DbHelperTools->manageAdresse($dataAdresse);

                        $rs = Enrollment::select('id')->where([['af_id', $row['id_af']], ['entitie_id', $entity_id], ['enrollment_type', 'S']])->get();
                        $idEnrollment = (count($rs)>0 && $rs[0]['id'])?$rs[0]['id']:0;//($rs) ? $rs['id'] : 0;
                        $dataEnrollment = array(
                            'id' => $idEnrollment,
                            'entitie_id' => $entity_id,
                            'nb_participants' => 1,
                            'price' => 0,
                            'price_type' => null,
                            'af_id' => $row['id_af'],
                            'enrollment_type' => 'S',
                        );
                //        dd($dataEnrollment);
                        $enrollment_id = $DbHelperTools->manageEnrollment($dataEnrollment);

                        $rs = Member::select('id')->where([['contact_id', $contact_id], ['enrollment_id', $enrollment_id]])->first();
                        $id = ($rs) ? $rs['id'] : 0;
                        $idGrp = isset($row['id_groupe'])?$row['id_groupe']:null;
                 //       var_dump($idGrp);
                        if($idGrp==null){
                   //         var_dump($row['groupe']);
                            $rs = Group::select('id')->where([['title', $row['groupe']], ['af_id', $row['id_af']]])->first();
                            $idGrp = ($rs) ? $rs['id'] : null;
                        }
                  //      dd($idGrp);
                        $data_member = array(
                            'id' => $id,
                            'unknown_contact_name' => null,
                            'contact_id' => $idc,
                            'enrollment_id' => $enrollment_id,
                            'group_id' => $idGrp,
                        );
                        $member_id = $DbHelperTools->manageMember($data_member);
                        var_dump($member_id,$adresse_id,$enrollment_id,$idc,$entity_id);
                    }
                }
            }
        }
        die('Fin');
        return true;
    }
    public function manageDuplicateEntities($case)
    {
        /*  SELECT ref, COUNT(ref) FROM en_entities GROUP BY ref HAVING COUNT(ref)>1
            $case==1 => faux doublons (meme réf mais différent nom)
            $case==2 ==> vrai doublons (meme ref et mm nom)
        */
        $DbHelperTools=new DbHelperTools();
        if($case==1)
            $path = self::PATH_TO_JSON_DATA."/xlsx/false_duplicate_entities.xlsx";
        if($case==2)
            $path = self::PATH_TO_JSON_DATA."/xlsx/true_duplicate_entities.xlsx";
        
        $array = Excel::toArray(new EntitiesImport, $path);
        //dd($array);
        if(count($array[0])>0){
            foreach($array[0] as $k=>$row){
                //dd($row);
                $nbRows=Entitie::select('id')->where('id',$row['id'])->count();
                dump($nbRows);
                if($nbRows>0)
                    Entitie::where('id', $row['id'])->update(['ref' => $row['ref']]);
                dump($row);
            }
        }
        return true;
    }
    public function updateEntitiesCollectiveAndAuxiliaryCustomerAccountCodes(){
        
        $DbHelperTools = new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/entities_codes_collectif_auxilary.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
        $i=0;
        //dd($array[0]);
        if(count($array[0])>0){
            foreach($array[0] as $k=>$row){
                $auxiliary_customer_account=$row['auxiliary_customer_account'];
                $collective_customer_account=$row['collective_customer_account'];
                Entitie::where('id', $row['id'])->update(['auxiliary_customer_account' => $auxiliary_customer_account,'collective_customer_account' => $collective_customer_account]);
                $i++;
            }
        }
        return $i;


        $DbHelperTools = new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/xlsx_entities_codes.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
        $i=$iFound=$iNotFound=0;
        $iFound=0;
        if(count($array[0])>0){
            foreach($array[0] as $k=>$row){
                $en = Entitie::select('id')->where('name',$row['name'])->first();
                if($en){
                    $id=$en->id;
                    $auxiliary_customer_account=$row['code'];
                    $collective_customer_account=$DbHelperTools->generateCodeCollectifs($id);
                    //Entitie::where('id', $id)->update(['auxiliary_customer_account' => $auxiliary_customer_account,'collective_customer_account' => $collective_customer_account]);
                    Log::debug('N° '.$i.' : '.$row['name'].' was founded with id = '.$id);
                    $iFound++;
                }else{
                    Log::debug('N° '.$i.' : '.$row['name'].' not found ');
                    $iNotFound++;
                }
                //dump($row);
                $i++;
            }
        }
        dd('Found : '.$iFound.' - Not found : '.$iNotFound.' - Total : '.count($array[0]));
        dd(count($array[0]));
        return true;


        
        //$code=$DbHelperTools->formatString('.abc-DEFGH....IG_K LM O%P');
        //dd($code);
        $ids = Entitie::select('id')->whereNull('auxiliary_customer_account')->limit(10)->pluck('id');
        //dd($ids->count());
        if($ids->count()>0){
            foreach($ids as $id){
                $auxiliary_customer_account=$DbHelperTools->generateAuxiliaryAccountForEntity($id);
                $collective_customer_account=$DbHelperTools->generateCodeCollectifs($id);
                //Entitie::where('id', $id)->update(['auxiliary_customer_account' => $auxiliary_customer_account,'collective_customer_account' => $collective_customer_account]);
                //dd($auxiliary_customer_account.$collective_customer_account);
                dump('ID=>'.$id.':'.$auxiliary_customer_account.'==>'.$collective_customer_account);
            }
        }
        return true;
    }
    public function fixContactsFirstnameLastname()
    {
        $DbHelperTools=new DbHelperTools();
        $path = self::PATH_TO_JSON_DATA."/xlsx/correction_nom_prenom.xlsx";
        $array = Excel::toArray(new EntitiesImport, $path);
        //dd($array);
        $i=0;
        if(count($array[0])>0){
            foreach($array[0] as $k=>$row){
                //dd($row);
                $nbRows=Contact::select('id')->where('id',$row['id'])->count();
                //dump($nbRows);
                if($nbRows>0){
                    Contact::where('id', $row['id'])->update(['firstname' => $row['new_firstname'],'lastname' => $row['new_lastname']]);
                    $i++;
                }
                //dd($row);
            }
        }
        return $i;
    }
    public function fixUpdateRefEntities($code){
        $DbHelperTools=new DbHelperTools();
        $rs = Entitie::where('ref',$code)->get();
        $i=0;
        foreach($rs as $k=>$en){
            if($k==0){continue;}
            $newCode=$DbHelperTools->generateEntityCode();
            Entitie::where('id', $en->id)->update(['ref' => $newCode]);
            dump('ID=>'.$en->id.':'.$newCode);
            $i++;
        }
        dump('Total : '.$i);
    }
}
