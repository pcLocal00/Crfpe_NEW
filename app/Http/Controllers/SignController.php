<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Psr7;

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
use App\Models\Schedule;
use App\Models\Formation;
use App\Models\Groupment;
use App\Models\Ressource;
use App\Models\Enrollment;
use App\Models\Sheetparam;
use App\Models\Certificate;
use App\Models\FileContact;
use App\Models\Sessiondate;
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
use App\Models\GedSignature;
use App\Models\Task;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Validator;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    function download_remote_file($file_url, $save_to)
    {
        $content = file_get_contents($file_url);
        file_put_contents($save_to, $content);
    }

    public function getDocument($contract_id, $render_type)
    {
        //pdf part
        $contract = Contract::findOrFail($contract_id);
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', 'CONTRAT_TRAVAIL_FORMATEUR')->first();
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $dn = Carbon::now();

        if ($contract->state != 'SC_GENERATED') {
            // die('Contrat déjà envoyé.');
            return response()->json([
                'success' => false,
                'message' => "Contrat déjà envoyé"
            ]);
        }

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

        // save file into virtual folder
        $dir = "file_sign";
        $filename = "CONTRAT_{$contract->number}.pdf";
        $file = "$dir/$filename";

        $content_to_write = $pdf->download()->getOriginalContent();

        $filetoput = fopen($file, "w");

        fwrite($filetoput, $content_to_write);

        fclose($filetoput);

        define("AUTH_URL", env('URL_AGREMENT'));

        $url = 'https://api.eu1.adobesign.com/api/rest/v6/transientDocuments';

        $header = array(
            "Authorization: Bearer 3AAABLblqZhCQg4TNOQYVNf2f-SoTn7R4c6d5z7pgo2a7QxPoAoRsrrAAJ06Ca4TglI4mZWQLV3NidbSKcz9mUAjSAAShCZSZ",
            "cache-control: no-cache",
            "content-type: multipart/form-data",
            "Content-Disposition: form-data; name='File'; filename='$filename'",
        );
        // $file = __DIR__ . DIRECTORY_SEPARATOR . "document.pdf";

        $filePath = '@' . file_get_contents($file);
        // dd($filePath);
        $fields = array('File' => $filePath, 'Mime-Type' => 'application/pdf', 'File-Name' => $filename);

        $resource = curl_init();
        // dd($header,$fields,$file,$url);

        curl_setopt_array($resource, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => $header
        ));

        $res = curl_exec($resource);
        $result = json_decode($res, true);

        curl_close($resource);

        if (!$res) {
            return response()->json([
                'success' => false,
                'message' => "Erreur",
                'res'=> $res
            ]);
        }

        $transientDocumentId = $result['transientDocumentId'];

        $url0 = 'https://api.eu1.adobesign.com:443/api/rest/v6/agreements';

        $curl = curl_init($url0);
        curl_setopt($curl, CURLOPT_URL, $url0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Cookie: name=value; name2=value2",
            "Authorization:  Bearer 3AAABLblqZhCQg4TNOQYVNf2f-SoTn7R4c6d5z7pgo2a7QxPoAoRsrrAAJ06Ca4TglI4mZWQLV3NidbSKcz9mUAjSAAShCZSZ",
            "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = '{
        "fileInfos": [
            {
            "transientDocumentId": "' . $transientDocumentId . '"
            }
        ],
        "name": "contrat",
        "participantSetsInfo": [
            {
            "order": 1,
            "role": "SIGNER",
            "memberInfos": [
                {
                    "name": "' . ucfirst($cnt->firstname ?? '') . ' ' . ucfirst($cnt->lastname ?? '') . '",
                    "email": "' . $cnt->email . '"
                }
            ]
            }
        ],
        "signatureType": "ESIGN",
        "state": "IN_PROCESS"
        }';

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);

        $agreement = json_decode($resp, true);

        if (!$agreement) {
            return response()->json([
                'success' => false,
                'message' => "Erreur",
                'res'=>'no agreement'
            ]);
        }

        // $enrollments_ids = [];
        // $id = 0;
        $row = Contract::find($contract_id);
        $row->state = 'SC_SENT';
        $row->save();
        $id = $row->id;

        /* Send Mail */
        // $fullname = ucfirst($cnt->firstname ?? '') . ' ' . ucfirst($cnt->lastname ?? '');
        // $content = "Bonjour $fullname,<br/>Un nouveau contrat vous a été envoyé via Adobe pour le signer.<br/><br/>";
        // // $content .= "<b>ID du document: $transientDocumentId</b><br/>";
        // $content .= "<b>Nom du document: document.pdf</b><br/>";
        // $header = "Environnement de formation pour CRFPE";
        // $footer = "Plateforme de formation SOLARIS";

        try {
            // Mail::send('pages.email.model', ['htmlMain' => $content, 'htmlHeader' => $header, 'htmlFooter' => $footer], function ($m) use ($cnt, $fullname) {
            //     $m->from('support@havetdigital.fr');
            //     $m->bcc('hbriere@havetdigital.fr');
            //     $m->to($cnt->email, $fullname)->subject('SOLARIS : Nouveau Contrat à Signer');
            // });

            /* Create Task */
            $params = Param::where('param_code', 'like', 'TASK_%')->get();
            $responsable_id = Contact::where('email', 'severinebernaert@crfpe.fr')->first()->id ?? null;
            $en_cours = Param::where([['param_code', 'Etat'], ['code', 'En cours'], ['is_active', 1]])->pluck('id')->first();
            $type = Param::where([['param_code', 'Etat'], ['code', 'En cours'], ['is_active', 1]])->pluck('id')->first();

            $first_schedule_date = Schedule::selectRaw('MIN(af_schedules.start_hour) as date')
                ->join('af_schedulecontacts', 'af_schedulecontacts.schedule_id', 'af_schedules.id')
                ->where('af_schedulecontacts.contract_id', $contract->id)
                ->pluck('date')->first();

            $end_date = $first_schedule_date ? (new DateTime($first_schedule_date))->modify("-5 day") : null;
            $callback_date = $first_schedule_date ? (new DateTime($first_schedule_date))->modify("-10 day") : null;

            $task = Task::create([
                'title' => "Signature Contrat ({$contract->number})",
                'description' => "Envoi du contrat {$contract->number} pour la signature par {$cnt->firstname} {$cnt->lastname}",
                'apporteur_id' => $responsable_id,
                'priority' => 'normal',
                'responsable_id' => $responsable_id,
                'type_id' => Param::where('code', 'SC_SENT')->first()->id ??  null,
                'start_date' => new DateTime(),
                'ended_date' => $end_date,
                'callback_date' => $callback_date,
                'source_id' => $params->where('code', 'COMMITTEE')->first()->id ?? null,
                'callback_mode_id' => $params->where('code', 'CALLBACK_SOLARIS')->first()->id ?? null,
                'reponse_mode_id' => $params->where('code', 'RESPONSE_SOLARIS')->first()->id ?? null,
                'entite_id' => $cnt->entitie->id ?? null,
                'contact_id' => $cnt->id,
                'af_id' => null,
                'pf_id' => null,
                'etat_id' => $en_cours ?? null,
            ]);

            /* GED SIGNATURE */
            GedSignature::create([
                'type' => 'contract',
                'contract_id' => $contract->id,
                'ged_doc_id' => $agreement['id'],
                'ged_doc_state' => null,
                'task_id' => $task->id,
            ]);

                /* redirect('contrats-intervenants')->with('popup', 'open') */;
            return response()->json([
                'success' => true,
                'message' => "Merci de consulter votre boite email afin de signer le contrat"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur",
                'res'=>"catch error"
            ]);
        }
    }
}
