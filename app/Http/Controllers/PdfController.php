<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Param;
use App\Models\Action;
use App\Models\Member;
use App\Models\Refund;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use App\Models\Invoice;
use App\Models\Session;
use App\Models\Ressource;
use App\Models\Enrollment;
use App\Models\Certificate;
use App\Models\Convocation;
use App\Models\Sessiondate;
use Illuminate\Http\Request;
use App\Models\Documentmodel;
use App\Models\Internshiproposal;
use App\Library\Services\DbHelperTools;
use App\Models\Group;
use App\Models\Groupment;
use App\Models\Prepla_scheduledate_groups;
use App\Models\Prepla_scheduledate_intervenants;
use App\Models\Prepla_schedules;
use App\Models\Preplanning;
use App\Models\Schedulecontact;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;

class PdfController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function createPdfConvocation($convocation_id, $render_type)
    {
        $convocation = null;
        if ($convocation_id > 0) {
            $convocation = Convocation::findOrFail($convocation_id);
        }
        $document_type = 'CONVOCATION';
        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', $document_type)->first();
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $dn = Carbon::now();
        $keywordHeader = array(
            '{LOGO_HEADER}',
        );
        $keywordHeaderReplace = array(
            public_path('media/logo/logo-light.png'),
        );
        $htmlHeader = str_replace($keywordHeader, $keywordHeaderReplace, $header);
        //MAIN
        $keyword = array(
            "{COMPANY_NAME}",
            "{ENTITY_NAME}",
            "{CONTACT_NAME}",
            "{CONTACT_FUNCTION}",
            "{AF_TITLE}",
            "{AF_CODE}",
            "{FORMER_NAME}",
            "{AF_LIEU_FORMATION}",
            "{STARTED_AT}",
            "{ENDED_AT}",
            "{FIRST_SCHEDULE_HOUR}",
            "{LAST_SCHEDULE_HOUR}",
            "{AF_ADRESSE_LIEU_FORMATION}",
            "{DATE_NOW}",
            "{AGREEMENT_NUMBER}",
            "{SIGNATURE_CACHET}",
            "{COMPANY_DENOMINATION}",
            "{SIGNATURE}",
        );
        $entity_adresse = Adresse::where([['entitie_id', $convocation->entity->id], ['is_main_entity_address', 1]])->first();
        $afInfos = $DbHelperTools->getAfInformations($convocation->af->id);

        $training_site = ($convocation->af->training_site != 'OTHER') ? $convocation->af->training_site : $convocation->af->other_training_site;

        $keyreplace = array(
            config('global.company_name'),
            $convocation->entity->name,
            $convocation->contact->firstname . ' ' . $convocation->contact->lastname,
            $convocation->contact->function,
            $convocation->af->title,
            $convocation->af->code,
            $afInfos['mainFormer'],
            $training_site,
            $afInfos['started_at'],
            $afInfos['ended_at'],
            (($afInfos['FIRST_SCHEDULE_HOUR']) ? $afInfos['FIRST_SCHEDULE_HOUR'] : '--'),
            (($afInfos['LAST_SCHEDULE_HOUR']) ? $afInfos['LAST_SCHEDULE_HOUR'] : '--'),
            $afInfos['address_training_location'],
            $dn->format('d/m/Y'),
            $convocation->number,
            public_path('custom/images/signature_cachet.png'),
            config('global.company_denomination'),
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
            config('global.company_address'),
            config('global.company_phone'),
            config('global.company_fax'),
            config('global.company_email'),
            config('global.company_website'),
            config('global.company_siret'),
        );
        $htmlFooter = str_replace($keywordFooter, $keywordFooterReplace, $footer);

        $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
        if ($render_type == 1) {
            return $pdf->stream();
        }
        return $pdf->download($convocation->number . '-' . time() . '.pdf');
    }
    public function generatePdfAttendanceAbsenceSheetFromFilter(Request $request)
    {
        $render_type = 1;
        $iconesArray = [1 => "far fa-file-pdf", 2 => "flaticon-download"];
        $links = [];
        $htmlLinks = '';
        if ($request->isMethod('post')) {
            //dd($request->all());
            $af_id = 0;
            if ($request->has('af_id')) {
                $af_id = $request->af_id;
            }
            $group_id = 0;
            if ($request->has('group_id')) {
                $group_id = $request->group_id;
            }
            $session_id = 0;
            if ($request->has('session_id')) {
                $session_id = $request->session_id;
            }
            $member_id = 0;
            if ($request->has('member_id')) {
                $member_id = $request->member_id;
            }
            $training_site = '';
            if ($request->has('training_site')) {
                $training_site = $request->training_site;
            }
            $start = $end = null;
            if ($request->has('start_date') && $request->has('end_date')) {
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $start = Carbon::createFromFormat('d/m/Y', $request->start_date);
                    $end = Carbon::createFromFormat('d/m/Y', $request->end_date);
                    //$datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                }
            }
            if ($af_id > 0) {
                $href = '/pdf/attendance-absence-sheet/' . $af_id . '/' . $render_type . '/' . $group_id . '/0/0/' . $session_id . '/' . $member_id;
                if (isset($start) && isset($end)) {
                    $href = '/pdf/attendance-absence-sheet/' . $af_id . '/' . $render_type . '/' . $group_id . '/' . $start->format('Y-m-d') . '/' . $end->format('Y-m-d') . '/' . $session_id . '/' . $member_id;
                }
                if (isset($training_site) && !empty($training_site)) {
                    $href .= '/' . $training_site;
                }
                $htmlLinks .= '<ul><li><a href="' . $href . '" class="mb-2 text-success" target="_blank"><i class="' . $iconesArray[$render_type] . ' text-success"></i> Pdf</a></li></ul>';
            }
        }
        //return $htmlLinks;
        //dd($htmlLinks);
        return response()->json(['links' => $htmlLinks]);
    }
    public function createPdfAttendanceAbsenceSheet($af_id, $render_type, $group_id, $start_date = '', $end_date = '', $session_id = '', $member_id = '', $training_site = '')
    {
        if ($af_id > 0) {
            $af = Action::select('id', 'device_type')->where('id', $af_id)->first();
            $old_limit = ini_set("memory_limit", "512M");
            //$session=Session::find($session_id);
            //if($session){
            $DbHelperTools = new DbHelperTools();
            $arrayRs = $DbHelperTools->generateHtmlPdfAttendanceAbsenceSheet($af_id, $render_type, $group_id, $start_date, $end_date, $session_id, $member_id, $training_site);
            $htmlMain = $arrayRs['htmlMain'];
            // dd($htmlMain);
            $htmlHeader = $arrayRs['htmlHeader'];
            $htmlFooter = $arrayRs['htmlFooter'];
            if (!empty($htmlMain) && !empty($htmlHeader) && !empty($htmlFooter)) {
                $type_af = $af->device_type;
                $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
                /* if($type_af=='INTER'){
                        $pdf->setPaper('a4', 'landscape');
                    } */
                if ($render_type == 1) {
                    return $pdf->stream();
                }
                return $pdf->download('fiche-' . time() . '.pdf');
            }
            //}

        }
        abort(403, 'Unauthorized action.');
        exit();
    }
    public function createPdfConventionStage($internshiproposal_id, $render_type)
    {
        $internshiproposal = null;
        if ($internshiproposal_id > 0) {
            $internshiproposal = Internshiproposal::findOrFail($internshiproposal_id);
        }
        $document_type = 'CONVENTION_STAGE';
        $document_typewith = 'CONVENTION_STAGEWITH';
        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', $document_type)->first();
        $dmwith = Documentmodel::where('code', $document_typewith)->first();
        $content = $dm->custom_content;
        $contentwith = $dmwith->custom_content;
        // dd($content);
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
            $adressLieu = $rs->line_1 . ' ' . $rs->line_2 . ' ' . $rs->line_3 . ' ' . $rs->postal_code . ' ' . $rs->city . ' ' . $rs->country;
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
        if ($afInfos['session_type'] != null && $afInfos['session_type'] == 'EJE_AVEC_GRATIFICATION_INTERNSHIP_PERIOD') {
            $pdf = PDF::loadView('pages.pdf.modelwith', compact('htmlMainwith', 'htmlHeaderwith', 'htmlFooterwith'));
        } else {
            $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
        }

        if ($render_type == 1) {
            return $pdf->stream();
        }

        return $pdf->download('convention_stage_' . $internshiproposal->id . '-' . time() . '.pdf');
    }
    public function createPdfRefund($refund_id, $render_type)
    {
        //dd($refund_id);
        $invoice_id = 0;
        $refund = null;
        if ($refund_id > 0) {
            $refund = Refund::findOrFail($refund_id);
            $dn = $refund->refund_date? $refund->refund_date: $refund->created_at; 
            $dn = Carbon::createFromFormat('Y-m-d', $dn);
            if ($refund)
                $invoice_id = $refund->invoice_id;
        }
        $invoice = null;
        if ($invoice_id > 0) {
            $invoice = Invoice::findOrFail($invoice_id);
        }
        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', 'REFUND')->first();
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $dn = Carbon::now();

        // $dtBillDate = Carbon::createFromFormat('Y-m-d', $invoice->bill_date);
        $dtBillDate = Carbon::createFromFormat('Y-m-d', $refund->refund_date);
        $keywordHeader = array(
            '{LOGO_HEADER}',
        );
        $keywordHeaderReplace = array(
            public_path('media/logo/logo-light.png'),
        );
        $htmlHeader = str_replace($keywordHeader, $keywordHeaderReplace, $header);

        //MAIN
        $keyword = array(
            "{COMPANY_NAME}",
            "{COMPANY_ADDRESS_LINE_1}",
            "{COMPANY_CS}",
            "{COMPANY_ZIPCODE}",
            "{COMPANY_CITY}",
            "{COMPANY_PHONE}",
            "{COMPANY_INVOICE_EMAIL}",
            "{COMPANY_SIRET}",
            "{COMPANY_CODE_APE}",
            "{COMPANY_ACTIVITY_DECLARATION_NUMBER}",
            "{INVOICE_ENTITY}",
            "{INVOICE_ADDRESS_LINE1}",
            "{INVOICE_ADDRESS_LINE2}",
            "{INVOICE_POSTAL_CODE}",
            "{INVOICE_CITY}",
            "{INVOICE_SIRET_CLIENT}",
            "{INVOICE_CONTACT_FIRSTNAME}",
            "{INVOICE_CONTACT_LASTNAME}",
            "{DATE_CREATION}",
            "{INVOICE_WRITER_NAME}",
            "{INVOICE_WRITER_EMAIL}",
            "{INVOICE_NUMBER}",
            "{REFUND_NUMBER}",
            "{REFUND_NOTE}",
            "{HTML_ITEMS}",
            "{COMPANY_WEBSITE}",
            "{COMPANY_DENOMINATION}",
            //bank
            "{company_bank_code}",
            "{company_bank_guichet}",
            "{company_bank_account_number}",
            "{company_bank_key_rib}",
            "{company_bank_iban}",
            "{company_bank_bic}"
        );
        $funder_infos_array = $DbHelperTools->getFunderByInvoice($invoice->id);
        $INVOICE_WRITER_NAME = 'Service Facturation';
        $INVOICE_WRITER_EMAIL = config('global.company_invoice_email');
        $keyreplace = array(
            config('global.company_name'),
            config('global.company_address_line_1'),
            config('global.company_cs'),
            config('global.company_zipcode'),
            config('global.company_city'),
            config('global.company_phone'),
            config('global.company_estimate_email'),
            config('global.company_siret'),
            config('global.company_code_ape'),
            config('global.company_activity_declaration_number'),
            //contact devis
            $funder_infos_array['name'],
            $funder_infos_array['line_1'],
            $funder_infos_array['line_2'],
            $funder_infos_array['postal_code'],
            $funder_infos_array['city'],
            $funder_infos_array['siret'],
            $funder_infos_array['contact_firstname'],
            $funder_infos_array['contact_lastname'],
            $dtBillDate->format('d/m/Y'),
            $INVOICE_WRITER_NAME,
            $INVOICE_WRITER_EMAIL,
            $invoice->number,
            $refund->number,
            $refund->reason,
            $DbHelperTools->getHtmlInvoiceItems($invoice->id, 2),
            config('global.company_website'),
            config('global.company_denomination'),
            config('global.company_bank_code'),
            config('global.company_bank_guichet'),
            config('global.company_bank_account_number'),
            config('global.company_bank_key_rib'),
            config('global.company_bank_iban'),
            config('global.company_bank_bic'),
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
            config('global.company_address'),
            config('global.company_phone'),
            config('global.company_fax'),
            config('global.company_email'),
            config('global.company_website'),
            config('global.company_siret'),
        );
        $htmlFooter = str_replace($keywordFooter, $keywordFooterReplace, $footer);

        $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
        if ($render_type == 1) {
            return $pdf->stream();
        }
        $pdfName = 'AVOIR-' . $invoice->number . '-' . time() . '.pdf';

        if ($render_type == 3) {
            $temp = env('TEMP_PDF_FOLDER');
            $pdfName = "AVOIR-" . $invoice->number . '.pdf';
            $temp_directory = public_path() . "/" . $temp;
            $pathToStorage = $temp_directory . '/' . $pdfName;
            if (!File::isDirectory($temp_directory)) {
                File::makeDirectory($temp_directory, 0777, true, true);
            }
            $pdf->save($pathToStorage);
            return true;
        }

        return $pdf->download($pdfName);
    }
    public function createPdfCertificate($certificate_id, $render_type)
    {
        $certificate = null;
        if ($certificate_id > 0) {
            $certificate = Certificate::findOrFail($certificate_id);
        }
        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', 'ATTESTATION_SUIVI_FORMATION')->first();
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $dn = Carbon::now();
        $keywordHeader = array(
            '{LOGO_HEADER}',
        );
        $keywordHeaderReplace = array(
            public_path('media/logo/logo-light.png'),
        );
        $htmlHeader = str_replace($keywordHeader, $keywordHeaderReplace, $header);
        $keyword = array(
            "{COMPANY_NAME}",
            "{STUDENTS}",
            "{AF_TITLE}",
            "{NB_THEO_HOURS}",
            "{NB_PRACTICAL_HOURS}",
            "{AF_LIEU_FORMATION}",
            "{HTML_AF_RECAP_SEANCES}",
            "{HTML_EXTRA_1_STAGIAIRE}",
            "{HTML_EXTRA_2_STAGIAIRE}",
            "{HTML_EXTRA_3_STAGIAIRE}",
            "{AF_OBJECTIFS}",
            "{DATE_NOW}",
            "{SIGNATURE}",
        );
        $HTML_EXTRA_1_STAGIAIRE = $HTML_EXTRA_2_STAGIAIRE = $HTML_EXTRA_3_STAGIAIRE = $html_recap_schedules = '';
        if ($certificate->enrollment->entity->entity_type == "P") {
            $HTML_EXTRA_1_STAGIAIRE = 'Le ou la stagiaire a participé à : 0 heures de formation théorique et 0 heures de formation pratique le cas échéant';
            $HTML_EXTRA_2_STAGIAIRE = 'Résultat de l’évaluation des acquis de la formation :';
            $HTML_EXTRA_3_STAGIAIRE = '<img src="' . public_path('custom/images/checkbox.png') . '" height="15px"> Acquises		<img src="' . public_path('custom/images/checkbox.png') . '" height="15px"> En cours d’acquisition		<img src="' . public_path('custom/images/checkbox.png') . '" height="15px"> Non acquises';
            $member = Member::select('id')->where('enrollment_id', $certificate->enrollment->id)->first();
            $html_recap_schedules = $DbHelperTools->getHtmlAfRecapSchedulesForMember($certificate->af->id, $member->id);
            //dd($html_recap_schedules);
        }
        // show Date(s) et horaire(s) de la formation
        else{
            $member = Member::select('id')->where('enrollment_id', $certificate->enrollment->id)->first();
            $html_recap_schedules = $DbHelperTools->getHtmlAfRecapSchedulesForMember($certificate->af->id, $member->id);
            //dd($html_recap_schedules);
        }
        $details_af = $DbHelperTools->getAfDefaultSheetDetails($certificate->af->id);
        $training_site = ($certificate->af->training_site != 'OTHER') ? $certificate->af->training_site : $certificate->af->other_training_site;
        $AF_OBJECTIFS = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_OBJ'] : '';

        $contacts = Contact::select('en_contacts.id', 'en_contacts.firstname', 'en_contacts.lastname')
            ->join('af_members', 'af_members.contact_id', 'en_contacts.id')
            ->join('af_enrollments', 'af_enrollments.id', 'af_members.enrollment_id')
            ->where('af_enrollments.af_id', $certificate->af_id)
            ->where('en_contacts.entitie_id', $certificate->enrollment->entity->id)
            ->groupBy('en_contacts.id')
            ->get();
        $listContacts = '';
        foreach ($contacts as $c) {
            $listContacts .= '<p style="font-family:Gill Sans, Gill Sans MT, Calibri, Trebuchet MS, sans-serif;font-size:12px;text-align: center;">' . $c->firstname . ' ' . $c->lastname . '</p>';
        }
        $keyreplace = array(
            config('global.company_name'),
            $listContacts,
            $certificate->af->title,
            $certificate->af->nb_hours ?? 0,
            $certificate->af->nb_pratical_hours ?? 0,
            $training_site,
            $html_recap_schedules,
            $HTML_EXTRA_1_STAGIAIRE,
            $HTML_EXTRA_2_STAGIAIRE,
            $HTML_EXTRA_3_STAGIAIRE,
            $AF_OBJECTIFS,
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
            config('global.company_address'),
            config('global.company_phone'),
            config('global.company_fax'),
            config('global.company_email'),
            config('global.company_website'),
            config('global.company_siret'),
        );
        $htmlFooter = str_replace($keywordFooter, $keywordFooterReplace, $footer);

        $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
        if ($render_type == 1) {
            return $pdf->stream();
        }
        return $pdf->download($certificate->number . '-' . time() . '.pdf');
    }

    public function createStudentsPdfCertificate($certificate_id, $render_type)
    {
        $certificate = null;
        if ($certificate_id > 0) {
            $certificate = Certificate::findOrFail($certificate_id);
        }
        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', 'ATTESTATION_ETUDIANT')->first();
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
        $keyword = array(
            "{COMPANY_NAME}",
            "{STUDENT}",
            "{AF_TITLE}",
            "{NB_THEO_HOURS}",
            "{NB_PRACTICAL_HOURS}",
            "{AF_LIEU_FORMATION}",
            "{HTML_AF_RECAP_SEANCES}",
            "{HTML_EXTRA_1_STAGIAIRE}",
            "{HTML_EXTRA_2_STAGIAIRE}",
            "{HTML_EXTRA_3_STAGIAIRE}",
            "{AF_OBJECTIFS}",
            "{DATE_NOW}",
            "{SIGNATURE}",
        );
        $HTML_EXTRA_1_STAGIAIRE = $HTML_EXTRA_2_STAGIAIRE = $HTML_EXTRA_3_STAGIAIRE = $html_recap_schedules = '';

        $c = $certificate->contact;
        $c_nb_hours = 0;
        $c_nb_pratical_hours = 0;

        $c_nb_pratical_hours = Schedulecontact::selectRaw('SUM(af_schedules.duration) as nb_hours')
            ->leftjoin('af_schedules', 'af_schedules.id', 'af_schedulecontacts.schedule_id')
            ->leftjoin('af_members', 'af_members.id', 'af_schedulecontacts.member_id')
            ->leftjoin('af_enrollments', 'af_enrollments.id', 'af_members.enrollment_id')
            ->where('af_enrollments.af_id', $certificate->af->id)
            ->where('af_members.contact_id', $c->id)
            ->where('af_schedulecontacts.pointing', 'present')
            ->pluck('nb_hours')->first() ?? 0;

        $c_nb_hours = Schedulecontact::selectRaw('SUM(af_schedules.duration) as nb_hours')
            ->leftjoin('af_schedules', 'af_schedules.id', 'af_schedulecontacts.schedule_id')
            ->leftjoin('af_members', 'af_members.id', 'af_schedulecontacts.member_id')
            ->leftjoin('af_enrollments', 'af_enrollments.id', 'af_members.enrollment_id')
            ->where('af_enrollments.af_id', $certificate->af->id)
            ->where('af_members.contact_id', $c->id)
            ->pluck('nb_hours')->first() ?? 0;

        $last_date = Schedulecontact::selectRaw('MAX(af_schedules.end_hour) as last_date')
            ->leftjoin('af_schedules', 'af_schedules.id', 'af_schedulecontacts.schedule_id')
            ->leftjoin('af_members', 'af_members.id', 'af_schedulecontacts.member_id')
            ->leftjoin('af_enrollments', 'af_enrollments.id', 'af_members.enrollment_id')
            ->where('af_enrollments.af_id', $certificate->af->id)
            ->where('af_members.contact_id', $c->id)
            ->pluck('last_date')->first() ?? date('Y-m-d H:i:s');
        
        
        $dn = Carbon::createFromFormat('Y-m-d H:i:s', $last_date);

        $HTML_EXTRA_1_STAGIAIRE = 'Le ou la stagiaire a participé à : ' . ((int) $c_nb_hours) . ' heures de formation théorique et ' . ((int) $c_nb_pratical_hours) . ' heures de formation pratique le cas échéant';
        $HTML_EXTRA_2_STAGIAIRE = 'Résultat de l’évaluation des acquis de la formation :';

        $HTML_EXTRA_3_STAGIAIRE = '<div style="width: 30%; vertical-align: top;display: inline-block;">'
            . '<img src="' . public_path('custom/images/checkbox.png') . '" style="height:20px;"> Acquises'
            . '</div>'
            . '<div style="width: 30%; vertical-align: top;display: inline-block;">'
            . '<img src="' . public_path('custom/images/checkbox.png') . '" style="height:20px;"> En cours d’acquisition'
            . '</div>'
            . '<div style="width: 30%; vertical-align: top;display: inline-block;">'
            . '<img src="' . public_path('custom/images/checkbox.png') . '" style="height:20px;"> Non acquises'
            . '</div>';
        $member = Member::select('af_members.*')->leftjoin('af_enrollments', 'af_enrollments.id', 'af_members.enrollment_id')
            ->where('af_enrollments.af_id', $certificate->af->id)
            ->where('af_members.contact_id', $c->id)
            ->first();
        $html_recap_schedules = $DbHelperTools->getHtmlAfRecapSchedulesForMember($certificate->af->id, $member->id);

        $details_af = $DbHelperTools->getAfDefaultSheetDetails($certificate->af->id);
        $training_site = ($certificate->af->training_site != 'OTHER') ? $certificate->af->training_site : $certificate->af->other_training_site;
        $AF_OBJECTIFS = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_OBJ'] : '';

        $contact_name = '<p style="font-family:Gill Sans, Gill Sans MT, Calibri, Trebuchet MS, sans-serif;font-size:12px;text-align: center;">' . $c->firstname . ' ' . $c->lastname . '</p>';
        $keyreplace = array(
            config('global.company_name'),
            $contact_name,
            $certificate->af->title,
            $certificate->af->nb_hours ?? 0,
            $certificate->af->nb_pratical_hours ?? 0,
            $training_site,
            $html_recap_schedules,
            $HTML_EXTRA_1_STAGIAIRE,
            $HTML_EXTRA_2_STAGIAIRE,
            $HTML_EXTRA_3_STAGIAIRE,
            $AF_OBJECTIFS,
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
            config('global.company_address'),
            config('global.company_phone'),
            config('global.company_fax'),
            config('global.company_email'),
            config('global.company_website'),
            config('global.company_siret'),
        );
        $htmlFooter = str_replace($keywordFooter, $keywordFooterReplace, $footer);

        $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
        if ($render_type == 1) {
            return $pdf->stream();
        }
        return $pdf->download($certificate->number . '-' . time() . '.pdf');
    }

    public function createPdfAfTechnicalSheet($af_id, $render_type)
    {
        $af = null;
        if ($af_id > 0) {
            $af = Action::findOrFail($af_id);
        }
        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', 'AF_TECHNICAL_SHEET')->first();
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $dn = Carbon::now();
        $keywordHeader = array(
            '{LOGO_HEADER}',
        );
        $keywordHeaderReplace = array(
            public_path('media/logo/logo-light.png'),
        );
        $htmlHeader = str_replace($keywordHeader, $keywordHeaderReplace, $header);
        $keyword = array(
            "{DOCUMENT_TITLE}",
            "{AF_TITLE}",
            "{AF_OBJECTIFS}",
            "{AF_CONTENT}",
            "{AF_MODALITE_PEDAGOGIQUES}",
            "{AF_MODALITE_ACCES}",
            "{AF_MODALITE_EVALUATION}",
            "{AF_MODALITE_MOYENS_DISPOSITION}",
            "{AF_PRE_REQUIS}",
            "{NB_THEO_DAYS}",
            "{NB_THEO_HOURS}",
            "{NB_PRACTICAL_DAYS}",
            "{NB_PRACTICAL_HOURS}",
            "{AF_PROFIL_INTERVENANTS}",
            "{AF_CODE}",
        );
        //Fiche technique
        $DOCUMENT_TITLE = 'PROGRAMME DE FORMATION';
        $details_af = $DbHelperTools->getAfDefaultSheetDetails($af->id);
        $AF_OBJECTIFS = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_OBJ'] : '';
        $AF_CONTENT = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_CNT'] : '';
        $AF_MODALITE_PEDAGOGIQUES = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_MP'] : '';
        $AF_MODALITE_ACCES = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_MA'] : '';
        $AF_MODALITE_EVALUATION = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_ME'] : '';
        $AF_MODALITE_MOYENS_DISPOSITION = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_MAD'] : '';
        $AF_PRE_REQUIS = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_PREQ'] : '';
        $AF_PROFIL_INTERVENANTS = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_INT'] : '';
        $keyreplace = array(
            $DOCUMENT_TITLE,
            $af->title,
            $AF_OBJECTIFS,
            $AF_CONTENT,
            $AF_MODALITE_PEDAGOGIQUES,
            $AF_MODALITE_ACCES,
            $AF_MODALITE_EVALUATION,
            $AF_MODALITE_MOYENS_DISPOSITION,
            $AF_PRE_REQUIS,
            $af->nb_days,
            $af->nb_hours,
            $af->nb_pratical_days ?? 0,
            $af->nb_pratical_hours ?? 0,
            $AF_PROFIL_INTERVENANTS,
            $af->code,
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
            config('global.company_address'),
            config('global.company_phone'),
            config('global.company_fax'),
            config('global.company_email'),
            config('global.company_website'),
            config('global.company_siret'),
        );
        $htmlFooter = str_replace($keywordFooter, $keywordFooterReplace, $footer);

        $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
        if ($render_type == 1) {
            return $pdf->stream();
        }
        return $pdf->download($af->code . '-' . time() . '.pdf');
    }
    // PDF transfer calendar
    public function createPdfTransferCalendar(Request $request)
    {
        // dd($request->Ppreplanning_id);
        $Ppreplanning_id = $request->Ppreplanning_id;
        $start = $request->preplannings_start_date;
        $end = $request->preplannings_end_date;
        
        $from = Carbon::createFromFormat('d-m-Y', $start)->format('Y-m-d');
        $to = Carbon::createFromFormat('d-m-Y', $end)->format('Y-m-d');

        $datas = Prepla_schedules::where('Pp_id',$Ppreplanning_id)
            ->whereDate('date_start', '>=', $from)
            ->whereDate('date_start', '<=', $to)
            ->orderBy('date_start', 'asc')
            ->get();
        $preplanning = Preplanning::find($Ppreplanning_id);
        foreach($datas as $data){
                $id = $data->id;
                $scheduledate_groups = Prepla_scheduledate_groups::where(['Pp_schedule_id' => $id])->first();
                if(!empty($scheduledate_groups->Groupe)){
                    $group = Group::find($scheduledate_groups->Groupe);
                    $data->group = $group->title;
                }
                if(!empty($scheduledate_groups->Regroupement)){
                    $group = Groupment::find($scheduledate_groups->Regroupement);
                    $data->group = $group->name;
                }
                $scheduledate_intervenants = Prepla_scheduledate_intervenants::where(['Pp_schedule_id' => $id])->orderBy('price', 'asc')->orderBy('type', 'desc')->first();
                if(!empty($scheduledate_intervenants->Contact_id)){
                    $interv_name = Contact::find($scheduledate_intervenants->Contact_id);
                    $data->formateur =  $interv_name->firstname . " " . $interv_name->lastname ;
                    $data->formateur_type = $scheduledate_intervenants->type;
                    $data->price = $scheduledate_intervenants->price;
                }              
        }
        // $data = json_decode($request->tableData, true);
        

        view()->share('tableData', $datas);
        view()->share('preplanning', $preplanning);
        $pdf = PDF::loadView('pages.pdf.transferCalendar', $datas)->setPaper('a4', 'landscape')->setWarnings(false);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="transferCalendar.pdf"');
        return $pdf->download('transferCalendar.pdf');
    }

}
