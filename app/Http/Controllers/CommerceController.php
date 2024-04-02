<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade as PDF;
use finfo;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use Carbon\Carbon;
use App\Models\Taxe;
use App\Models\Media;
use App\Models\Param;
use App\Models\Action;
use App\Models\Member;
use App\Models\Adresse;
use App\Models\Contact;
use App\Models\Entitie;
use App\Models\Funding;
use App\Models\Invoice;
use App\Models\Task;
use App\Models\Session;
use App\Models\Estimate;
use App\Models\Schedule;
use App\Mail\InvoiceMail;
use App\Models\Agreement;
use App\Models\Emailmodel;
use App\Models\Ressource;
use App\Models\Attachment;
use App\Models\Enrollment;
use App\Mail\AgreementMail;
use App\Models\Certificate;
use App\Models\Convocation;
use App\Models\Invoiceitem;
use App\Models\Sessiondate;
use App\Models\Estimateitem;
use Illuminate\Http\Request;
use App\Models\Agreementitem;
use App\Models\Documentmodel;
use App\Models\Fundingpayment;
use App\Models\Invoicepayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Library\Services\PublicTools;
use App\Library\Services\DbHelperTools;
use App\Mail\EstimateMail;
use App\Models\Refund;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Exception;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CommerceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function estimates()
    {
        $page_title = 'Liste des devis';
        $page_description = '';
        return view('pages.commerce.estimate.list', compact('page_title', 'page_description'));
    }

    public function sdtEstimates(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        /* if($af_id>0){
            $datas = Estimate::where('af_id',$af_id)->latest();
        }else{
            $datas = Estimate::latest();
        } */
        $datas = Estimate::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('estimate_number', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('note', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
            } else {
                $datas = Estimate::orderByDesc('id');
            }
        }
        if ($af_id > 0) {
            $datas->where('af_id', $af_id)->latest();
        }

        $recordsTotal=count($datas->get());

         if($request->length>0){
             $start=(int) $request->start;
             $length=(int) $request->length;
             $datas->skip($start)->take($length);
         }

        $udatas = $datas->orderByDesc('id')->get();
        foreach ($udatas as $d) {
            if($d->type == NULL){
                    $row = array();
                    $arr = $DbHelperTools->getAgreementByEstimate($d->id);
                    $agreementNumber = ($arr['number']) ? ('<p>' . $arr['agreement_type'] . ' : ' . $arr['number'] . '</p>') : '';
                    //ID
                    $row[] = $d->id;
                    //<th>Devis</th>
                    $status = $DbHelperTools->getParamByCode($d->status);
                    $pStatus = '<p><span class="label label-sm label-light-' . $status['css_class'] . ' label-inline">' . $status['name'] . '</span></p>';
                    $row[] = '<p class="text-' . $status['css_class'] . '">DEVIS #' . $d->estimate_number . '</p>' . $pStatus . $agreementNumber;
                    //<th>Client</th>
                    $pContact = '<p><span class="label label-sm label-light-info label-inline">' . $d->contact->firstname . ' ' . $d->contact->lastname . '</span></p>';
                    $row[] = '<p>' . $d->entity->name . ' - ' . $d->entity->ref . ' - ' . $d->entity->entity_type . '</p>' . $pContact;
                    //<th>AF</th>
                    if ($af_id == 0) {
                        $row[] = '<a href="/view/af/' . $d->af->id . '">' . $d->af->code . '</a>';
                    }
                    //Date creation
                    $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                    $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                    $row[] = $created_at . $updated_at;
                    //Montant
                    $calcul = $DbHelperTools->getAmountsEstimate($d->id);
                    $row[] = '<p class="text-info">' . $calcul['total'] . ' €</p>';
                    //Actions
                    $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formEstimate(' . $d->id . ',' . $d->af->id . ',' . $d->entity->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
                    $btn_view = '<button class="btn btn-sm btn-clean btn-icon" onclick="_viewEstimate(' . $d->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></button>';
                    $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteEstimate(' . $d->id . ')" title="Suppression"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';


                    $btn_pdf = '<a target="_blank" href="/pdf/estimate/' . $d->id . '/1" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span></a>';
                    $btn_pdf_download = '<a target="_blank" href="/pdf/estimate/' . $d->id . '/2" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span></a>';

                    //bouton generate convention or contract
                    $btn_generate_agreement = '';
                    $nb_agreements = Agreement::select('id')->where('estimate_id', $d->id)->count();
                    if (in_array($d->status, ['SE_SENT', 'SE_ACCEPTED']) && $nb_agreements == 0) {
                        $btn_generate_agreement = '<a href="javascript:void(0)" onclick="_generateAgreementEstimate(' . $d->id . ')" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></span><span class="navi-text">Générer convention/contrat</span></a>';
                    }
                    $btn_send_email = '<a style="cursor: pointer;" onclick="_formSendEstimate(' . $d->id . ')" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('ENVELOPE') . '"></i></span> <span class="navi-text">Envoyer par e-mail</span> </a>';

                    $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                            data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                <ul class="navi flex-column navi-hover py-2">
                                    <li class="navi-item">
                                        ' . $btn_send_email . '
                                        ' . $btn_generate_agreement . '
                                        ' . $btn_pdf . '
                                        ' . $btn_pdf_download . '
                                    </li>
                                </ul>
                            </div>
                        </div>';

                    $row[] = $btn_edit . $btn_more;
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
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=> $recordsTotal,
        ];
        return response()->json($result);
    }


    public function sdtInvoiveFact(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        /* if($af_id>0){
            $datas = Estimate::where('af_id',$af_id)->latest();
        }else{
            $datas = Estimate::latest();
        } */

        if (auth()->user()->roles[0]->code != 'FORMATEUR'){

                $datas = Invoice::latest();
                if ($af_id > 0) {
                    $datas->where('af_id', $af_id)->latest();
                }
                $recordsTotal=count($datas->get());
                $udatas = $datas->orderByDesc('id')->get();
        }
        else{
            $contact_id = auth()->user()->contact_id;

            $datas = Invoice::latest();
            if ($af_id > 0 && $contact_id > 0) {
                $datas->where([['af_id',$af_id],['contact_id',$contact_id]])->latest();
            }
            $recordsTotal=count($datas->get());
            $udatas = $datas->orderByDesc('id')->get();
        }

        foreach ($udatas as $d) {
             if($d->invoice_type == "INT/FAC"){
                    $row = array();
                    //$arr = $DbHelperTools->getAgreementByEstimate($d->id);
                    //$agreementNumber = ($arr['number']) ? ('<p>' . $arr['agreement_type'] . ' : ' . $arr['number'] . '</p>') : '';
                    //ID
                    $row[] = $d->id;

                    //<th>Devis</th>
                    //$status = $DbHelperTools->getParamByCode($d->status);
                    //$pStatus = '<p><span class="label label-sm label-light-' . $status['css_class'] . ' label-inline">' . $status['name'] . '</span></p>';
                    $row[] = '<p class="text-info">Facture #' . $d->number . '</p>';

                    //<th>Client</th>
                    $row[] = '<p><span class="label label-sm label-light-info label-inline" style="font-size:12px;padding: 20px 10px;">' . $d->agreement->estimate->contact->firstname . ' ' . $d->agreement->estimate->contact->lastname . '</span></p>';
                    $row[] = '<p><span class="label label-sm label-light-info label-inline" style="font-size:12px;padding: 20px 10px;">' . $d->status . '</span></p>';
                    //<th>AF</th>
                   
                    
                    //Date creation
                    $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                    $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                    $row[] = $created_at . $updated_at;
                    //Montant
                    $id_invoice_item = Invoiceitem::select('id')->where([['invoice_id',$d->id],['statut','actif']])->pluck('id');
                    if(count($id_invoice_item) != 0){
                        $invoice_item = Invoiceitem::findOrFail(intval($id_invoice_item[0]));
                        $tva = $d->tax_percentage/100;
                        $tauxtva = $invoice_item->total * $tva;
                        $total = $tauxtva + $invoice_item->total;
                    }
                    else{
                        $total = 0.00;
                    }

                    //$calcul = $DbHelperTools->getAmountsAgreement($d->id);
                    $row[] = '<p class="text-info">' . $total . ' €</p>';
                    //Actions
                    if($d->is_clo_btn == 0){
                        $btn_facture = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formInvoiceFact(' . $d->id . ',' . $d->af_id .')" title="Déposer facture"><i class="' . $tools->getIconeByAction('PDF') . '"></i></button>';
                    }
                    else{
                        $btn_facture = '<button disabled class="btn btn-sm btn-clean btn-icon" title="Déposer facture"><i class="' . $tools->getIconeByAction('PDF') . '"></i></button>';
                    }
                    if($d->status == "not_paid"){
                        $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteInvoice(' . $d->id . ',' . $d->af_id .')" title="Supprimer facture"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
                    }
                    else{
                        $btn_delete = '';
                    }

                        $id_attachment = Invoiceitem::select('attachement_id')->where([['invoice_id',intval($d->id)],['statut','actif']])->pluck('attachement_id');

                        if(count($id_attachment) != 0){ 
                            if($id_attachment[0] != NULL){
                                $attachment = Attachment::findOrFail(intval($id_attachment[0]));
                                $btn_pdf = '<a target="_blank" href="/uploads/afs/'.$af_id.'/documents-invoice/Invoice/'.$attachment->path.'" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF facture</span></a>';
                            }
                            else{
                             $btn_pdf = '<a class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF facture</span></a>';
                            }
                        }
                        else{
                            $btn_pdf = '<a class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF facture</span></a>';
                        }

                        if (auth()->user()->roles[0]->code != 'FORMATEUR'){
                            if($d->is_clo_btn == 1){
                                if($d->status == "not_paid"){
                                    $btn_validation = '<button class="btn btn-sm btn-clean btn-icon" onclick="_validationinvoicefact(' . $d->id . ',' . $d->af_id .')" title="Validation/Refus de facture"><i class="' . $tools->getIconeByAction('VALIDATE') . '"></i></button>';
                                }
                                else{
                                    $btn_validation = '';
                                }
                            }
                            else{
                                $btn_validation = '';
                            }
                        }
                        else{
                            $btn_validation = '';
                        }
                    
                    //bouton generate convention or contract
                    // $btn_generate_agreement = '';
                    // $nb_agreements = Agreement::select('id')->where('estimate_id', $d->id)->count();
                    // if (in_array($d->status, ['SE_SENT', 'SE_ACCEPTED']) && $nb_agreements == 0) {
                    //     $btn_generate_agreement = '<a href="javascript:void(0)" onclick="_generateAgreementEstimate(' . $d->id . ')" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></span><span class="navi-text">Générer convention/contrat</span></a>';
                    // }

                    $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                            data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                <ul class="navi flex-column navi-hover py-2">
                                    <li class="navi-item">
                                        ' . $btn_pdf . '
                                    </li>
                                </ul>
                            </div>
                        </div>';

                    $row[] = $btn_facture . $btn_validation. $btn_delete . $btn_more;
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
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=> $recordsTotal,
        ];
        return response()->json($result);
    }


    public function sdtAgreementFact(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        /* if($af_id>0){
            $datas = Estimate::where('af_id',$af_id)->latest();
        }else{
            $datas = Estimate::latest();
        } */

        if (auth()->user()->roles[0]->code != 'FORMATEUR'){

                $datas = Agreement::latest();
                if ($af_id > 0) {
                    $datas->where('af_id', $af_id)->latest();
                }
                $recordsTotal=count($datas->get());
                $udatas = $datas->orderByDesc('id')->get();
        }
        else{
            $contact_id = auth()->user()->contact_id;

            $datas = Agreement::latest();
            if ($af_id > 0 && $contact_id > 0) {
                $datas->where([['af_id',$af_id],['contact_id',$contact_id]])->latest();
            }
            $recordsTotal=count($datas->get());
            $udatas = $datas->orderByDesc('id')->get();
        }

        foreach ($udatas as $d) {
             if($d->agreement_type == "INT/FAC"){
                    $row = array();
                    //$arr = $DbHelperTools->getAgreementByEstimate($d->id);
                    //$agreementNumber = ($arr['number']) ? ('<p>' . $arr['agreement_type'] . ' : ' . $arr['number'] . '</p>') : '';
                    //ID
                    $row[] = $d->id;

                    //<th>Devis</th>
                    //$status = $DbHelperTools->getParamByCode($d->status);
                    //$pStatus = '<p><span class="label label-sm label-light-' . $status['css_class'] . ' label-inline">' . $status['name'] . '</span></p>';
                    $row[] = '<p class="text-info">Contrat #' . $d->number . '</p>';

                    //<th>Client</th>
                    $row[] = '<p><span class="label label-sm label-light-info label-inline" style="font-size:12px;padding: 20px 10px;">' . $d->estimate->contact->firstname . ' ' . $d->estimate->contact->lastname . '</span></p>';
                    $row[] = '<p><span class="label label-sm label-light-info label-inline" style="font-size:12px;padding: 20px 10px;">' . $d->status . '</span></p>';
                    //<th>AF</th>
                   
                    
                    //Date creation
                    $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                    $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                    $row[] = $created_at . $updated_at;
                    //Montant
                    $id_agreement_item = Agreementitem::select('id')->where([['agreement_id',$d->id],['statut','actif']])->pluck('id');
                    if(count($id_agreement_item) != 0){
                        $agreement_item = Agreementitem::findOrFail(intval($id_agreement_item[0]));
                        $tva = $d->tax_percentage/100;
                        $tauxtva = $agreement_item->total * $tva;
                        $total = $tauxtva + $agreement_item->total;
                    }
                    else{
                        $total = 0.00;
                    }

                    //$calcul = $DbHelperTools->getAmountsAgreement($d->id);
                    $row[] = '<p class="text-info">' . $total . ' €</p>';
                    //Actions
                    if($d->is_clo_btn == 0){
                        $btn_contrat = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formAgreementFact(' . $d->id . ',' . $d->af->id .')" title="Déposer Contrat"><i class="' . $tools->getIconeByAction('PDF') . '"></i></button>';
                    }
                    else{
                        $btn_contrat = '<button disabled class="btn btn-sm btn-clean btn-icon" title="Déposer Contrat"><i class="' . $tools->getIconeByAction('PDF') . '"></i></button>';
                    }
                    if($d->status == "deposit"){
                        $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteAgreement(' . $d->id . ',' . $d->af->id .')" title="Supprimer Contrat"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
                    }
                    else{
                        $btn_delete = '';
                    }

                        $id_attachment = Agreementitem::select('attachement_id')->where([['agreement_id',intval($d->id)],['statut','actif']])->pluck('attachement_id');

                        if(count($id_attachment) != 0){ 
                            if($id_attachment[0] != NULL){
                                $attachment = Attachment::findOrFail(intval($id_attachment[0]));
                                $btn_pdf = '<a target="_blank" href="/uploads/afs/'.$af_id.'/documents-agreement/Agreement/'.$attachment->path.'" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF contrat</span></a>';
                            }
                            else{
                             $btn_pdf = '<a class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF contrat</span></a>';
                            }
                        }
                        else{
                            $btn_pdf = '<a class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF contrat</span></a>';
                        }

                        if (auth()->user()->roles[0]->code != 'FORMATEUR'){
                            if($d->is_clo_btn == 1){
                                if($d->status == "deposit"){
                                    $btn_validation = '<button class="btn btn-sm btn-clean btn-icon" onclick="_validationagreementfact(' . $d->id . ',' . $d->af->id .')" title="Validation/Refus du contrat"><i class="' . $tools->getIconeByAction('VALIDATE') . '"></i></button>';
                                }
                                else{
                                    $btn_validation = '';
                                }
                            }
                            else{
                                $btn_validation = '';
                            }
                        }
                        else{
                            $btn_validation = '';
                        }
                    
                    //bouton generate convention or contract
                    // $btn_generate_agreement = '';
                    // $nb_agreements = Agreement::select('id')->where('estimate_id', $d->id)->count();
                    // if (in_array($d->status, ['SE_SENT', 'SE_ACCEPTED']) && $nb_agreements == 0) {
                    //     $btn_generate_agreement = '<a href="javascript:void(0)" onclick="_generateAgreementEstimate(' . $d->id . ')" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></span><span class="navi-text">Générer convention/contrat</span></a>';
                    // }

                    $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                            data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                <ul class="navi flex-column navi-hover py-2">
                                    <li class="navi-item">
                                        ' . $btn_pdf . '
                                    </li>
                                </ul>
                            </div>
                        </div>';

                    $row[] = $btn_contrat . $btn_validation. $btn_delete . $btn_more;
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
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=> $recordsTotal,
        ];
        return response()->json($result);
    }

    public function sdtestimatesfact(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        /* if($af_id>0){
            $datas = Estimate::where('af_id',$af_id)->latest();
        }else{
            $datas = Estimate::latest();
        } */

        if (auth()->user()->roles[0]->code != 'FORMATEUR'){

                $datas = Estimate::latest();
                if ($af_id > 0) {
                    $datas->where('af_id', $af_id)->latest();
                }
                $recordsTotal=count($datas->get());
                $udatas = $datas->orderByDesc('id')->get();
        }
        else{
            $contact_id = auth()->user()->contact_id;

            $datas = Estimate::latest();
            if ($af_id > 0 && $contact_id > 0) {
                $datas->where([['af_id',$af_id],['contact_id',$contact_id]])->latest();
            }
            $recordsTotal=count($datas->get());
            $udatas = $datas->orderByDesc('id')->get();
        }

        foreach ($udatas as $d) {
             if($d->type == "INT/FACT"){
                    $row = array();
                    //$arr = $DbHelperTools->getAgreementByEstimate($d->id);
                    //$agreementNumber = ($arr['number']) ? ('<p>' . $arr['agreement_type'] . ' : ' . $arr['number'] . '</p>') : '';
                    //ID
                    $row[] = $d->id;

                    //<th>Devis</th>
                    //$status = $DbHelperTools->getParamByCode($d->status);
                    //$pStatus = '<p><span class="label label-sm label-light-' . $status['css_class'] . ' label-inline">' . $status['name'] . '</span></p>';
                    $row[] = '<p class="text-info">DEVIS #' . $d->estimate_number . '</p>';
                    //<th>Client</th>
                    $row[] = '<p><span class="label label-sm label-light-info label-inline" style="font-size:12px;padding: 20px 10px;">' . $d->contact->firstname . ' ' . $d->contact->lastname . '</span></p>';
                    $row[] = '<p><span class="label label-sm label-light-info label-inline" style="font-size:12px;padding: 20px 10px;">' . $d->status . '</span></p>';
                    //<th>AF</th>
                    if ($af_id > 0) {
                    $row[] = '<a href="/view/af/' . $d->af->id . '">' . $d->af->code . '</a>';
                    }
                    else{
                    $row[] = 'Aucun Af';
                    }
                    
                    //Date creation
                    $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                    $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                    $row[] = $created_at . $updated_at;
                    //Montant
                    $calcul = $DbHelperTools->getAmountsEstimateFact($d->id);
                    $row[] = '<p class="text-info">' . $calcul['total'] . ' €</p>';
                    //Actions
                    if($d->is_clo_btn == 0){
                        $btn_depot = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formEstimateFact(' . $d->id . ',' . $d->af->id .')" title="Déposer PDF"><i class="' . $tools->getIconeByAction('PDF') . '"></i></button>';
                    }
                    else{
                        $btn_depot = '<button class="btn btn-sm btn-clean btn-icon" disabled title="Déposer PDF"><i class="' . $tools->getIconeByAction('PDF') . '"></i></button>';
                    }
                    if($d->status == "DEPOSE"){
                        $btn_delete = '<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteEstimateFact(' . $d->id . ',' . $d->af->id .')" title="Supprimer PDF"><i class="' . $tools->getIconeByAction('DELETE') . '"></i></button>';
                    }
                    else{
                        $btn_delete = '';
                    }
                    if (auth()->user()->roles[0]->code != 'FORMATEUR'){
                        if($d->is_clo_btn == 1){
                            if($d->status == "DEPOSE"){
                                $btn_validation = '<button class="btn btn-sm btn-clean btn-icon" onclick="_validationEstimateFact(' . $d->id . ',' . $d->af->id .')" title="Validation/Refus de devis"><i class="' . $tools->getIconeByAction('VALIDATE') . '"></i></button>';
                            }
                            else{
                                $btn_validation = '';
                            }
                        }
                        else{
                            $btn_validation = '';
                        }
                    }
                    else{
                        $btn_validation = '';
                    }

                    
                        $id_attachment = Estimateitem::select('attachement_id')->where([['estimate_id',intval($d->id)],['statut','actif']])->pluck('attachement_id');

                        if(count($id_attachment) != 0){ 
                            if($id_attachment[0] != NULL){
                                $attachment = Attachment::findOrFail(intval($id_attachment[0]));
                                $btn_pdf = '<a target="_blank" href="/uploads/afs/'.$af_id.'/documents-estimates/Estimatesfact/'.$attachment->path.'" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF Devis</span></a>';
                            }
                            else{
                             $btn_pdf = '<a class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF Devis</span></a>';
                            }
                        }
                        else{
                            $btn_pdf = '<a class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF Devis</span></a>';
                        }
                    
                    //bouton generate convention or contract
                    // $btn_generate_agreement = '';
                    // $nb_agreements = Agreement::select('id')->where('estimate_id', $d->id)->count();
                    // if (in_array($d->status, ['SE_SENT', 'SE_ACCEPTED']) && $nb_agreements == 0) {
                    //     $btn_generate_agreement = '<a href="javascript:void(0)" onclick="_generateAgreementEstimate(' . $d->id . ')" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></span><span class="navi-text">Générer convention/contrat</span></a>';
                    // }

                    $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                            data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                <ul class="navi flex-column navi-hover py-2">
                                    <li class="navi-item">
                                        ' . $btn_pdf . '
                                    </li>
                                </ul>
                            </div>
                        </div>';

                    $row[] = $btn_depot . $btn_delete . $btn_validation . $btn_more;
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
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=> $recordsTotal,
        ];
        return response()->json($result);
    }

    public function validationInvoice($invoice_id, $af_id)
    {
        return view('pages.commerce.invoice.form.validation-invoice',compact('invoice_id','af_id'));
    }

    public function validationAgreement($agreement_id, $af_id)
    {
        return view('pages.commerce.agreement.form.validation-agreement',compact('agreement_id','af_id'));
    }

    public function validationEstimatesfact($estimate_id, $af_id)
    {
        return view('pages.commerce.estimate.form.validation-estimates',compact('estimate_id','af_id'));
    }

    public function formagreementintfac($agreement_id, $af_id)
    {
        return view('pages.commerce.agreement.form.formagreement',compact('agreement_id','af_id'));
    }

    public function forminvoiceintfac($invoice_id, $af_id)
    {
        return view('pages.commerce.invoice.form.forminvoice',compact('invoice_id','af_id'));
    }

    public function validationformEstimatesfact(Request $request, $estimate_id, $af_id)
    {
        if ($request->isMethod('post')) {

            $data = $request->post();
            $Helper = new DbHelperTools();

            $motif = $data['textarea_motif'];
            $check_validation = $data['yesnovalidation'];

            $id_estimate_item = Estimateitem::select('id')->where([['estimate_id',$estimate_id],['statut','actif']])->pluck('id');
            $estimate = Estimate::findOrFail(intval($estimate_id));
            $estimate_item = Estimateitem::findOrFail(intval($id_estimate_item[0]));

            if($check_validation == 1){

                    if($estimate){
                        $estimate->status = "ACCEPTE";
                        $estimate->save();
                    }

                    $contact_id = $estimate->contact_id;

                    if($estimate_item){
                        $estimate_item->motif = $motif;
                        $estimate_item->is_validation = 1;
                        $estimate_item->save();
                    }

                    $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
                    $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');

                    $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');
                    $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');
                    $entite_id = Contact::select('entitie_id')->where('id',intval($contact_id))->pluck('entitie_id');

                    $task_sub_val = $estimate->estimate_number.'_'.'Validation de devis Formateur / Facture à '.$firstname_former[0].' '.$lastname_former[0];

                    $sub_task_validation_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$task_sub_val],['entite_id',intval($entite_id[0])],['etat_id',$id_etat_en[0]]])->pluck('id');

                    $sub_task_validation = Task::findOrFail(intval($sub_task_validation_id[0]));
                
                    if($sub_task_validation){
                        $sub_task_validation->etat_id = $id_etat_ter[0];
                        $sub_task_validation->save();
                    }

                    $todayDate = Carbon::now();

                    $date_generate = $Helper->generateAgreementNumberByAgreementDate('CONTRACT_FORMATION_PROFESSIONNELLE', $todayDate);

                    $date_number = str_replace('-', '', $date_generate);

                    $number_agre = 'CF'.$date_number;

                    $agreement = Agreement::create([
                        'agreement_type' => 'INT/FAC',
                        'number' => $number_agre,
                        'agreement_date' => $todayDate,
                        'status' => 'sent',
                        'discount_label' => 'Remise',
                        'discount_type' => 'before_tax',
                        'entitie_id' => $entite_id[0],
                        'contact_id' => $contact_id,
                        'af_id' => $af_id,
                        'estimate_id' => $estimate_id,
                    ]);

                    $title_prin = $estimate->estimate_number.'_'.'WORFLOW DEMANDE DEVIS FORMATEUR SUR FACTURE';

                    $task_prin = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',0],['title',$title_prin],['etat_id',$id_etat_en[0]],['entite_id',intval($entite_id[0])]])->pluck('id');
                    $call_backdate = Carbon::now()->addDays(15);

                    $user_id = auth()->user()->contact_id;

                    $sub_task = Task::create([
                        'title' => $estimate->estimate_number.'_'.'Demande de contrat Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0],
                        'description' => $sub_task_validation->description,
                        'etat_id' => $id_etat_en[0],
                        'responsable_id' => $user_id,
                        'apporteur_id' => $user_id,
                        'start_date' => $todayDate,
                        'ended_date' => $sub_task_validation->ended_date,
                        'callback_date' => $call_backdate,
                        'callback_mode' => 'email',
                        'entite_id' => $entite_id[0],
                        'contact_id' => $contact_id,
                        'af_id' => $af_id, 
                        'task_parent_id' => $task_prin[0],
                        'sub_task' => 1,
                    ]);

            }
            else{

                if($estimate_item){
                    $estimate_item->motif = $motif;
                    $estimate_item->is_validation = 0;
                    $estimate_item->statut = 'annule';
                    $estimate_item->save();
                }

                if($estimate){
                    $estimate->is_clo_btn = 0;
                    $estimate->status = 'DEMANDE ENVOYE';
                    $estimate->tax_percentage = NULL;
                    $estimate->save();
                }

                $contact_id = $estimate->contact_id;
                $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');
                $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');
                $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
                $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');

                $task_sub_dem = $estimate->estimate_number.'_'.'Demande de devis Formateur / Facture à '.$firstname_former[0].' '.$lastname_former[0];

                $sub_task_dem_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$task_sub_dem]])->pluck('id');

                $sub_task = Task::findOrFail(intval($sub_task_dem_id[0]));
                
                if($sub_task){
                    $sub_task->etat_id = $id_etat_en[0];
                    $sub_task->save();
                }

                $sub_task_val = $estimate->estimate_number.'_'.'Validation de devis Formateur / Facture à '.$firstname_former[0].' '.$lastname_former[0];
                $sub_task_val_id = Task::select('id')->where([['contact_id',intval($contact_id)],['title',$sub_task_val],['sub_task',1]])->pluck('id');
                $taskvalidationdelete = Task::where('id', $sub_task_val_id[0])->forceDelete();
            }

        }
    }


    public function validationformInvoice(Request $request, $invoice_id, $af_id)
    {
        if ($request->isMethod('post')) {

            $data = $request->post();
            $Helper = new DbHelperTools();

            $motif_fct = $data['textarea_motif_fct'];
            $check_validation_fct = $data['yesnovalidationfct'];

            $id_invoice_item = Invoiceitem::select('id')->where([['invoice_id',$invoice_id],['statut','actif']])->pluck('id');
            $invoice = Invoice::findOrFail(intval($invoice_id));
            $invoice_item = Invoiceitem::findOrFail(intval($id_invoice_item[0]));

            $estimate_id = $invoice->agreement->estimate_id;
            $estimate = Estimate::findOrFail(intval($estimate_id));

            if($check_validation_fct == 1){

                    if($invoice){
                        $invoice->status = "paid";
                        $invoice->save();
                    }

                    $contact_id = $invoice->contact_id;

                    if($invoice_item){
                        $invoice_item->motif = $motif_fct;
                        $invoice_item->is_validation = 1;
                        $invoice_item->save();
                    }

                    $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
                    $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');

                    $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');
                    $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');
                    $entite_id = Contact::select('entitie_id')->where('id',intval($contact_id))->pluck('entitie_id');

                    $task_sub_val = $estimate->estimate_number.'_'.'Validation de facture Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];
                    $task_prin = $estimate->estimate_number.'_'.'WORFLOW DEMANDE DEVIS FORMATEUR SUR FACTURE';

                    $sub_task_validation_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$task_sub_val],['etat_id',$id_etat_en[0]]])->pluck('id');

                    $sub_task_validation = Task::findOrFail(intval($sub_task_validation_id[0]));
                
                    if($sub_task_validation){
                        $sub_task_validation->etat_id = $id_etat_ter[0];
                        $sub_task_validation->save();
                    }

                    $task_prin_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',0],['title',$task_prin],['etat_id',$id_etat_en[0]]])->pluck('id');
                    $task_prin = Task::findOrFail(intval($task_prin_id[0]));

                    if($task_prin){
                        $task_prin->etat_id = $id_etat_ter[0];
                        $task_prin->save();
                    }
            }
            else{

                if($invoice_item){
                    $invoice_item->motif = $motif_fct;
                    $invoice_item->is_validation = 0;
                    $invoice_item->statut = 'annule';
                    $invoice_item->save();
                }

                if($invoice){
                    $invoice->is_clo_btn = 0;
                    $invoice->status = 'cancelled';
                    $invoice->tax_percentage = NULL;
                    $invoice->save();
                }

                $contact_id = $invoice->contact_id;

                $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');
                $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');

                $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
                $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');

                $task_sub_dem = $estimate->estimate_number.'_'.'Demande de facture Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];

                $sub_task_dem_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$task_sub_dem]])->pluck('id');

                $sub_task = Task::findOrFail(intval($sub_task_dem_id[0]));
                
                if($sub_task){
                    $sub_task->etat_id = $id_etat_en[0];
                    $sub_task->save();
                }

                $sub_task_val = $estimate->estimate_number.'_'.'Validation de facture Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];
                $sub_task_val_id = Task::select('id')->where([['contact_id',intval($contact_id)],['title',$sub_task_val],['sub_task',1]])->pluck('id');
                $taskvalidationdelete = Task::where('id', $sub_task_val_id[0])->forceDelete();
            }

        }
    }



    public function validationformAgreement(Request $request, $agreement_id, $af_id)
    {
        if ($request->isMethod('post')) {

            $data = $request->post();
            $Helper = new DbHelperTools();

            $motif_agr = $data['textarea_motif_agr'];
            $check_validation_agr = $data['yesnovalidationagr'];

            $id_agreement_item = Agreementitem::select('id')->where([['agreement_id',$agreement_id],['statut','actif']])->pluck('id');
            $agreement = Agreement::findOrFail(intval($agreement_id));
            $agreement_item = Agreementitem::findOrFail(intval($id_agreement_item[0]));

            $estimate_id = Agreement::select('estimate_id')->where('id',intval($agreement_id))->pluck('estimate_id');
            $estimate = Estimate::findOrFail(intval($estimate_id[0]));

            if($check_validation_agr == 1){

                    if($agreement){
                        $agreement->status = "signed";
                        $agreement->save();
                    }

                    $contact_id = $agreement->contact_id;

                    if($agreement_item){
                        $agreement_item->motif = $motif_agr;
                        $agreement_item->is_validation = 1;
                        $agreement_item->save();
                    }

                    $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
                    $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');

                    $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');
                    $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');
                    $entite_id = Contact::select('entitie_id')->where('id',intval($contact_id))->pluck('entitie_id');

                    $task_sub_val = $estimate->estimate_number.'_'.'Validation de contrat Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];

                    $sub_task_validation_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$task_sub_val],['etat_id',$id_etat_en[0]]])->pluck('id');

                    $sub_task_validation = Task::findOrFail(intval($sub_task_validation_id[0]));
                
                    if($sub_task_validation){
                        $sub_task_validation->etat_id = $id_etat_ter[0];
                        $sub_task_validation->save();
                    }

                    $todayDate = Carbon::now();

                    $date_generate = $Helper->generateInvoiceNumberByBillDate('INVOICE', $todayDate);

                    $date_number = str_replace('-', '', $date_generate);

                    $invoice_num = 'F'.$date_number;

                    $invoice = Invoice::create([
                        'number' => $invoice_num,
                        'bill_date' => $todayDate,
                        'invoice_type' => 'INT/FAC',
                        'status' => 'sent',
                        'entitie_id' => $entite_id[0],
                        'contact_id' => $contact_id,
                        'af_id' => $af_id,
                        'agreement_id' => $agreement_id,
                    ]);

                    $title_prin = $estimate->estimate_number.'_'.'WORFLOW DEMANDE DEVIS FORMATEUR SUR FACTURE';

                    $task_prin = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',0],['title',$title_prin],['etat_id',$id_etat_en[0]]])->pluck('id');
                    $call_backdate = Carbon::now()->addDays(15);

                    $user_id = auth()->user()->contact_id;

                    $sub_task = Task::create([
                        'title' => $estimate->estimate_number.'_'.'Demande de facture Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0],
                        'description' => $sub_task_validation->description,
                        'etat_id' => $id_etat_en[0],
                        'responsable_id' => $user_id,
                        'apporteur_id' => $user_id,
                        'start_date' => $todayDate,
                        'ended_date' => $sub_task_validation->ended_date,
                        'callback_date' => $call_backdate,
                        'callback_mode' => 'email',
                        'entite_id' => $entite_id[0],
                        'contact_id' => $contact_id,
                        'af_id' => $af_id, 
                        'task_parent_id' => $task_prin[0],
                        'sub_task' => 1,
                    ]);

            }
            else{

                if($agreement_item){
                    $agreement_item->motif = $motif_agr;
                    $agreement_item->is_validation = 0;
                    $agreement_item->statut = 'annule';
                    $agreement_item->save();
                }

                if($agreement){
                    $agreement->is_clo_btn = 0;
                    $agreement->status = 'canceled';
                    $agreement->tax_percentage = NULL;
                    $agreement->save();
                }

                $contact_id = $agreement->contact_id;
                $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');
                $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');
                $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
                $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');

                $task_sub_dem = $estimate->estimate_number.'_'.'Demande de contrat Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];

                $sub_task_dem_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$task_sub_dem]])->pluck('id');

                $sub_task = Task::findOrFail(intval($sub_task_dem_id[0]));
                
                if($sub_task){
                    $sub_task->etat_id = $id_etat_en[0];
                    $sub_task->save();
                }

                $sub_task_val = $estimate->estimate_number.'_'.'Validation de contrat Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];
                $sub_task_val_id = Task::select('id')->where([['contact_id',intval($contact_id)],['title',$sub_task_val],['sub_task',1]])->pluck('id');
                $taskvalidationdelete = Task::where('id', $sub_task_val_id[0])->forceDelete();
            }

        }
    }


    public function deleteInvoice(Request $request, $invoice_id, $af_id)
    {
        if ($request->isMethod('delete')) {

            $invoice = Invoice::findOrFail(intval($invoice_id));

            $id_attachement = Invoiceitem::select('attachement_id')->where([['invoice_id',$invoice_id],['statut','actif']])->pluck('attachement_id');
            $id_invoice_item = Invoiceitem::select('id')->where([['invoice_id',$invoice_id],['statut','actif']])->pluck('id');
            $attachment = Attachment::findOrFail(intval($id_attachement[0]));
            $estimate_id = $invoice->agreement->estimate_id;
            $estimate = Estimate::findOrFail(intval($estimate_id));

            $image_path = '/afs/'.$af_id.'/documents-invoice/Invoice/'.$attachment->path;
                
            Storage::disk('public_uploads')->delete($image_path);

            if($invoice){
                $invoice->tax_percentage = NULL;
                $invoice->is_clo_btn = 0;
                $invoice->status = 'draft';
                $invoice->save();
            }

            $invoiceitemdelete = Invoiceitem::where('id', $id_invoice_item[0])->forceDelete();
            $attachmentdelete = Attachment::where('id', $id_attachement[0])->forceDelete();

            $contact_id = $invoice->contact_id;
            $entite_id = Contact::select('entitie_id')->where('id',intval($contact_id))->pluck('entitie_id');

            $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
            $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');

            $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');

            $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');

            $title_sub_val = $estimate->estimate_number.'_'.'Validation de facture Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];
            $title_sub_dem = $estimate->estimate_number.'_'.'Demande de facture Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];

            $sub_task_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$title_sub_dem],['etat_id',$id_etat_ter[0]]])->pluck('id');
            $sub_task = Task::findOrFail(intval($sub_task_id[0]));
        
             if($sub_task){
                $sub_task->etat_id = $id_etat_en[0];
                $sub_task->save();
             }

             $sub_task_validation_id = Task::select('id')->where([['contact_id',intval($contact_id)],['title',$title_sub_val],['sub_task',1],['etat_id',$id_etat_en[0]]])->pluck('id');
             $taskvalidationdelete = Task::where('id', $sub_task_validation_id[0])->forceDelete();

            }
    }



    public function deleteAgreement(Request $request, $agreement_id, $af_id)
    {
        if ($request->isMethod('delete')) {

            $agreement = Agreement::findOrFail(intval($agreement_id));

            $id_attachement = Agreementitem::select('attachement_id')->where([['agreement_id',$agreement_id],['statut','actif']])->pluck('attachement_id');
            $id_agreement_item = Agreementitem::select('id')->where([['agreement_id',$agreement_id],['statut','actif']])->pluck('id');
            $attachment = Attachment::findOrFail(intval($id_attachement[0]));
            $estimate_id = $agreement->estimate_id;
            $estimate = Estimate::findOrFail(intval($estimate_id));

            $image_path = '/afs/'.$af_id.'/documents-agreement/Agreement/'.$attachment->path;
                
            Storage::disk('public_uploads')->delete($image_path);

            if($agreement){
                $agreement->tax_percentage = NULL;
                $agreement->is_clo_btn = 0;
                $agreement->status = 'draft';
                $agreement->save();
            }

            $agreementitemdelete = Agreementitem::where('id', $id_agreement_item[0])->forceDelete();
            $attachmentdelete = Attachment::where('id', $id_attachement[0])->forceDelete();

            $contact_id = $agreement->contact_id;
            $entite_id = Contact::select('entitie_id')->where('id',intval($contact_id))->pluck('entitie_id');

            $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
            $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');

            $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');

            $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');

            $title_sub_val = $estimate->estimate_number.'_'.'Validation de contrat Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];
            $title_sub_dem = $estimate->estimate_number.'_'.'Demande de contrat Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];

            $sub_task_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$title_sub_dem],['etat_id',$id_etat_ter[0]]])->pluck('id');
            $sub_task = Task::findOrFail(intval($sub_task_id[0]));
        
             if($sub_task){
                $sub_task->etat_id = $id_etat_en[0];
                $sub_task->save();
             }

             $sub_task_validation_id = Task::select('id')->where([['contact_id',intval($contact_id)],['title',$title_sub_val],['sub_task',1],['etat_id',$id_etat_en[0]]])->pluck('id');
             $taskvalidationdelete = Task::where('id', $sub_task_validation_id[0])->forceDelete();

            }
    }


    
    public function deleteEstimatesfact(Request $request, $estimate_id, $af_id)
    {
        if ($request->isMethod('delete')) {

            $estimate = Estimate::findOrFail(intval($estimate_id));

            $id_attachement = Estimateitem::select('attachement_id')->where([['estimate_id',$estimate_id],['statut','actif']])->pluck('attachement_id');
            $id_estimate_item = Estimateitem::select('id')->where([['estimate_id',$estimate_id],['statut','actif']])->pluck('id');
            $attachment = Attachment::findOrFail(intval($id_attachement[0]));

            $image_path = '/afs/'.$af_id.'/documents-estimates/Estimatesfact/'.$attachment->path;
                
            Storage::disk('public_uploads')->delete($image_path);

            if($estimate){
                $estimate->tax_percentage = NULL;
                $estimate->is_clo_btn = 0;
                $estimate->status = 'DEMANDE ENVOYE';
                $estimate->save();
            }

            $estimateitemdelete = Estimateitem::where('id', $id_estimate_item[0])->forceDelete();
            $attachmentdelete = Attachment::where('id', $id_attachement[0])->forceDelete();

            $contact_id = $estimate->contact_id;
            $entite_id = Contact::select('entitie_id')->where('id',intval($contact_id))->pluck('entitie_id');

            $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
            $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');

            $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');

            $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');

            $title_sub_val = $estimate->estimate_number.'_'.'Validation de devis Formateur / Facture à '.$firstname_former[0].' '.$lastname_former[0];
            $title_sub_dem = $estimate->estimate_number.'_'.'Demande de devis Formateur / Facture à '.$firstname_former[0].' '.$lastname_former[0];

            $sub_task_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$title_sub_dem],['entite_id',intval($entite_id[0])],['etat_id',$id_etat_ter[0]]])->pluck('id');
            $sub_task = Task::findOrFail(intval($sub_task_id[0]));
        
             if($sub_task){
                $sub_task->etat_id = $id_etat_en[0];
                $sub_task->save();
             }

             $sub_task_validation_id = Task::select('id')->where([['contact_id',intval($contact_id)],['title',$title_sub_val],['sub_task',1],['entite_id',intval($entite_id[0])],['etat_id',$id_etat_en[0]]])->pluck('id');
             $taskvalidationdelete = Task::where('id', $sub_task_validation_id[0])->forceDelete();

            }
    }

    public function formEstimate($estimate_id, $default_af_id, $default_entity_id)
    {
        $row = null;
        if ($estimate_id > 0) {
            $row = Estimate::findOrFail($estimate_id);
        }
        return view('pages.commerce.estimate.form.estimate', compact('row', 'default_af_id', 'default_entity_id'));
    }

    public function storeFormEstimate(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        $estimate_id = $af_id = $entity_id = 0;
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            $estimate_date = Carbon::createFromFormat('d/m/Y', $request->estimate_date);
            $valid_until = Carbon::createFromFormat('d/m/Y', $request->valid_until);
            if ($valid_until > $estimate_date) {
                $data = array(
                    "id" => $request->id,
                    "estimate_date" => $estimate_date,
                    "valid_until" => $valid_until,
                    "af_id" => $request->af_id,
                    "entitie_id" => $request->entitie_id,
                    "contact_id" => $request->contact_id,
                    "tax_percentage" => $request->tax_percentage,
                    "note" => $request->note,
                    "status" => $request->status,
                );
                $estimate_id = $DbHelperTools->manageEstimate($data);
                $entity_id = $request->entitie_id;
                $af_id = $request->af_id;
                //Create default item
                if ($estimate_id > 0 && $request->id == 0) {
                    $af = Action::select('title', 'formation_id')->where('id', $request->af_id)->first();
                    $details = $DbHelperTools->getQuantityUnittypeRate($af_id, $entity_id);
                    $data = array(
                        "id" => 0,
                        "title" => $af->title,
                        "description" => $DbHelperTools->getItemDescription($request->af_id),
                        "quantity" => $details['quantity'],
                        "unit_type" => $details['unit_type'],
                        "rate" => $details['rate'],
                        "total" => $details['quantity'] * $details['rate'],
                        "is_main_item" => 1,
                        "estimate_id" => $estimate_id,
                        "pf_id" => $af->formation_id
                    );
                    $item_id = $DbHelperTools->manageEstimateItem($data);
                }
                $success = true;
                $msg = 'Le devis a été enregistrée avec succès';
            } else {
                $msg = 'Attention, La date de validité est inférieur a la date de devis !!!';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'estimate_id' => $estimate_id,
            'entity_id' => $entity_id,
            'af_id' => $af_id,
        ]);
    }

    public function selectAfsOptions($af_id)
    {
        // dd($af_id);
        $result = [];
        if ($af_id > 0) {
            $rows = Action::select('id', 'code', 'title')->where('id', $af_id)->get();
        } else {
            $rows = Action::select('id', 'code', 'title')->get();
        }
        if (count($rows) > 0) {
            foreach ($rows as $pf) {
                $result[] = ['id' => $pf['id'], 'name' => ($pf['title'] . ' (' . $pf['code'] . ')')];
            }
        }
        return response()->json($result);
    }


    public function selectEntitiesOptions($af_id, $entity_id)
    {
        $result = [];
        $ids_entities = [];
        if ($af_id > 0) {
            if ($entity_id > 0) {
                $ids_entities = Enrollment::select('entitie_id')->where([['af_id', $af_id], ['entitie_id', $entity_id]])->pluck('entitie_id');
            } else {
                $ids_entities = Enrollment::select('entitie_id')->where('af_id', $af_id)->pluck('entitie_id');
            }
        }
        $rows = Entitie::select('id', 'ref', 'entity_type', 'name')->whereIn('id', $ids_entities)
        ->where('is_active',true)
        ->get();
        if (count($rows) > 0) {
            foreach ($rows as $en) {
                $result[] = ['id' => $en['id'], 'name' => ($en['name'] . ' - ' . $en['ref'] . ' - ' . $en['entity_type'])];
            }
        }
        return response()->json($result);
    }

    public function getEstimateItems($estimate_id)
    {
        $items = null;
        $calcul = [];
        $discount_type = 'before_tax';
        $tax_percentage = 0;
        $discount_amount_type = $discount_amount = '';
        if ($estimate_id > 0) {
            $items = Estimateitem::where('estimate_id', $estimate_id)->get();
            $DbHelperTools = new DbHelperTools();
            $calcul = $DbHelperTools->getAmountsEstimate($estimate_id);
            $estimate = Estimate::select('discount_type', 'discount_amount', 'discount_amount_type', 'tax_percentage', 'discount_label')->where('id', $estimate_id)->first();
            $discount_type = $estimate->discount_type;
            $discount_amount = $estimate->discount_amount;
            $discount_label = $estimate->discount_label;
            $tax_percentage = $estimate->tax_percentage;
            $discount_amount_type = $estimate->discount_amount_type;
        }
        return view('pages.commerce.estimate.items', compact('items', 'estimate_id', 'calcul', 'discount_type', 'tax_percentage', 'discount_amount_type', 'discount_amount', 'discount_label'));
    }

    public function getAgreementItems($agreement_id)
    {
        $DbHelperTools = new DbHelperTools();
        $items = null;
        $calcul = [];
        $discount_type = 'before_tax';
        $tax_percentage = 0;
        $discount_amount_type = $discount_amount = '';
        if ($agreement_id > 0) {
            $items = Agreementitem::where('agreement_id', $agreement_id)->get();
            $calcul = $DbHelperTools->getAmountsAgreement($agreement_id);
            $agreement = Agreement::select('discount_type', 'discount_amount', 'discount_amount_type', 'tax_percentage', 'discount_label')->where('id', $agreement_id)->first();
            $discount_type = $agreement->discount_type;
            $discount_amount = $agreement->discount_amount;
            $discount_label = $agreement->discount_label;
            $tax_percentage = $agreement->tax_percentage;
            $discount_amount_type = $agreement->discount_amount_type;
        }
        $agreementHasInvoice=$DbHelperTools->agreementHasInvoice($agreement_id);
        return view('pages.commerce.agreement.items', compact('items', 'agreement_id', 'calcul', 'discount_type', 'tax_percentage', 'discount_amount_type', 'discount_amount', 'discount_label','agreementHasInvoice'));
    }

    public function formEstimateItem($item_id, $estimate_id)
    {
        $row = null;
        if ($item_id > 0) {
            $row = Estimateitem::findOrFail($item_id);
        }
        return view('pages.commerce.estimate.form.item', compact('row', 'estimate_id'));
    }

    public function storeFormEstimateItem(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            $data = array(
                "id" => $request->id,
                "title" => $request->title,
                "description" => $request->description,
                "quantity" => $request->quantity,
                "unit_type" => $request->unit_type,
                "rate" => $request->rate,
                "total" => $request->rate * $request->quantity,
                "is_main_item" => $request->is_main_item,
                "estimate_id" => $request->estimate_id,
                "pf_id" => $request->formation_id
            );
            $row_id = $DbHelperTools->manageEstimateItem($data);
            $success = true;
            $msg = 'Element a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formDiscount($estimate_id)
    {
        $row = null;
        if ($estimate_id > 0) {
            $row = Estimate::select('id', 'discount_type', 'discount_amount', 'discount_amount_type', 'discount_label')->where('id', $estimate_id)->first();
        }
        return view('pages.commerce.estimate.form.discount', compact('row'));
    }

    public function storeFormDiscount(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            if ($request->id > 0) {
                $row = Estimate::find($request->id);
                $row->discount_type = $request->discount_type;
                $row->discount_amount = $request->discount_amount;
                $row->discount_amount_type = $request->discount_amount_type;
                $row->discount_label = $request->discount_label;
                $row->save();
                $id = $row->id;
                $success = true;
                $msg = 'Réduction a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function selectTaxes()
    {
        $result = [];
        $rows = Taxe::select('id', 'title', 'percentage')->get();
        if (count($rows) > 0) {
            foreach ($rows as $tx) {
                $result[] = ['id' => $tx['percentage'], 'name' => $tx['title']];
            }
        }
        return response()->json($result);
    }

    public function createPdfEstimate($estimate_id, $render_type)
    {
        $estimate = null;
        if ($estimate_id > 0) {
            $estimate = Estimate::findOrFail($estimate_id);
            $dn = $estimate->estimate_date ? $estimate->estimate_date : $estimate->created_at;
            $dn = Carbon::createFromFormat('Y-m-d', $dn);

        }else{
            $dn = Carbon::now();
        }
        // dd($dn);
        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', 'DEVIS')->first();
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
        //MAIN
        $keyword = array(
            "{COMPANY_NAME}",
            "{COMPANY_ADDRESS_LINE_1}",
            "{COMPANY_CS}",
            "{COMPANY_ZIPCODE}",
            "{COMPANY_CITY}",
            "{COMPANY_PHONE}",
            "{COMPANY_ESTIMATE_EMAIL}",
            "{COMPANY_SIRET}",
            "{COMPANY_CODE_APE}",
            "{COMPANY_ACTIVITY_DECLARATION_NUMBER}",
            "{ESTIMATE_ENTITY}",
            "{ESTIMATE_ADDRESS_LINE1}",
            "{ESTIMATE_ADDRESS_LINE2}",
            "{ESTIMATE_POSTAL_CODE}",
            "{ESTIMATE_CITY}",
            "{ESTIMATE_CONTACT_FIRSTNAME}",
            "{ESTIMATE_CONTACT_LASTNAME}",
            // //
            "{DATE_CREATION}",
            "{ESTIMATE_WRITER_NAME}",
            "{ESTIMATE_WRITER_EMAIL}",
            "{ESTIMATE_NUMBER}",
            "{HTML_ITEMS}",
            "{ESTIMATE_VALID_UNTIL}",
            "{COMPANY_WEBSITE}",
            "{SIGNATURE}",
            "{COMPANY_DENOMINATION}"
        );
        $entity_adresse = Adresse::where([['entitie_id', $estimate->entity->id], ['is_main_entity_address', 1]])->first();
        // dd($entity_adresse);
        //$entity_contact=Contact::where([['entitie_id',$estimate->entity->id],['is_main_contact',1]])->first();
        $valid_until = Carbon::createFromFormat('Y-m-d', $estimate->valid_until);

        $activity = Activity::where([['log_name', 'estimate_log'], ['subject_type', 'App\Models\Estimate'], ['subject_id', $estimate->id]])->whereIn('description', ['created', 'updated'])->orderByDesc('id')->first();
        $ESTIMATE_WRITER_NAME = ($activity) ? $activity->causer->name . ' ' . $activity->causer->lastname : '';
        $ESTIMATE_WRITER_EMAIL = ($activity) ? $activity->causer->email : '';

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
            $estimate->entity->name,
            (!is_null($entity_adresse)) ? $entity_adresse->line_1 : "",
            (!is_null($entity_adresse)) ? $entity_adresse->line_2 : "",
            (!is_null($entity_adresse)) ? $entity_adresse->postal_code : "",
            (!is_null($entity_adresse)) ? $entity_adresse->city : "",
            $estimate->contact->firstname,
            $estimate->contact->lastname,
            $dn->format('d/m/Y'),
            $ESTIMATE_WRITER_NAME,
            $ESTIMATE_WRITER_EMAIL,
            $estimate->estimate_number,
            $DbHelperTools->getHtmlEstimateItems($estimate->id),
            $valid_until->format('d/m/Y'),
            config('global.company_website'),
            public_path('custom/images/signature.png'),
            config('global.company_denomination'),
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

        // $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlFooter'));
        // $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain'));
        $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));

        $entityname = preg_replace('/\s+/', '', $estimate->entity->name);
        $namePdf = 'DEVIS' . $estimate->estimate_number . '-' . $entityname;
        if ($render_type == 1) {
            return $pdf->stream();
        } elseif ($render_type == 3) {
            $temp = env('TEMP_PDF_FOLDER');
            $temp_directory = public_path() . "/" . $temp;
            $pathToStorage = $temp_directory . '/' . $namePdf;
            if (!File::isDirectory($temp_directory)) {
                File::makeDirectory($temp_directory, 0777, true, true);
            }
            $pdf->save($pathToStorage);
            return $namePdf;
        }
        return $pdf->download($namePdf . '-' . time() . '.pdf');
    }

    public function generateAgreement($estimate_id)
    {
        $success = false;
        if ($estimate_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $success = $DbHelperTools->generateAgreementFromEstimate($estimate_id);
        }
        return response()->json(['success' => $success]);
    }

    public function createPdfAgreement($agreement_id, $render_type)
    {
        $agreement = null;
        $internshiproposal=null;
		$pStags='';

        if ($agreement_id > 0) {
            $agreement = Agreement::findOrFail($agreement_id);
            $dn = $agreement->agreement_date ? $agreement->agreement_date : $agreement->created_at;
            $dn = Carbon::createFromFormat('Y-m-d', $dn);
        }

		$result = $datas = [];
		$fname=[];
		$lname=[];
        if ($agreement->af_id > 0) {
            $datas=DB::table('af_members')->selectRaw('en_contacts.*')
            ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
            ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
            ->join('en_entities', 'en_contacts.entitie_id', '=', 'en_entities.id')
            ->select('af_members.id','en_contacts.firstname','en_contacts.lastname')
            //->where([['af_members.contact_id','>',0], ['af_enrollments.enrollment_type', 'S'],['af_enrollments.af_id', $agreement->af_id],['en_contacts.is_trainee_contact',1]])->get();	
			->where([['af_members.contact_id','>',0], ['af_enrollments.enrollment_type', 'S'],['af_enrollments.af_id', $agreement->af_id],['en_entities.id', $agreement->entitie_id]])
            ->groupBy('en_contacts.id')->get();	
        }
        if (count($datas) > 0) {
            foreach ($datas as $member) {
				$result[] = $member->firstname.' '.$member->lastname;
            }
			// $collection = collect($result)->unique();
            $pStags = implode(' , ', $result);
        }	
        
        if ($agreement->agreement_type == 'convention') {
            $document_type = 'CONVENTION_FORMATION_PROFESSIONNELLE';
        } elseif ($agreement->agreement_type == 'contract') {
            $document_type = 'CONTRACT_FORMATION_PROFESSIONNELLE';
        }
        //HEADER
        $DbHelperTools = new DbHelperTools();

        $dm = Documentmodel::where('code', $document_type)->first();
		$content=$dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        // $dn = Carbon::now();
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
            "{COMPANY_ESTIMATE_EMAIL}",
            "{COMPANY_SIRET}",
            "{COMPANY_CODE_APE}",
            "{COMPANY_ACTIVITY_DECLARATION_NUMBER}",
            //
            "{ENTITY_NAME}",
            "{ENTITY_ADDRESS_LINE1}",
            "{ENTITY_ADDRESS_LINE2}",
            "{ENTITY_POSTAL_CODE}",
            "{ENTITY_CITY}",
            "{CONTACT_NAME}",
            "{CONTACT_FUNCTION}",
            "{AF_TITLE}",
            "{AF_CODE}",
            "{TOTAL}",
            "{HTML_ITEMS}",
            //BANK
            "{COMPANY_BANK_CODE}",
            "{COMPANY_BANK_GUICHET}",
            "{COMPANY_BANK_ACCOUNT_NUMBER}",
            "{COMPANY_BANK_KEY_RIB}",
            "{COMPANY_BANK_IBAN}",
            "{COMPANY_BANK_BIC}",
            //AF PEFAGOGIQUE
            "{AF_OBJECTIFS}",
            "{AF_CONTENT}",
            "{AF_MODALITE_PEDAGOGIQUES}",
            "{AF_MODALITE_ACCES}",
            "{AF_MODALITE_EVALUATION}",
            "{AF_MODALITE_MOYENS_DISPOSITION}",
            "{AF_PRE_REQUIS}",
            "{AF_PROFIL_INTERVENANTS}",
            "{AF_NB_MAX_PARTICIPANTS}",
            "{NB_THEO_DAYS}",
            "{NB_THEO_HOURS}",
            "{NB_PRACTICAL_DAYS}",
            "{NB_PRACTICAL_HOURS}",
            "{HTML_AF_RECAP_SEANCES}",
            "{AF_LIEU_FORMATION}",
            //
            "{HTML_FINANCES}",
            //
            "{DATE_NOW}",
            "{DATE_CREATION}",
            "{AGREEMENT_NUMBER}",
            "{SIGNATURE_CACHET}",
            "{COMPANY_DENOMINATION}",
            //stagiaire info 
            "{MEMBER_CONTACT}"
        );
        $entity_adresse = Adresse::where([['entitie_id', $agreement->entity->id], ['is_main_entity_address', 1]])->first();
        $calcul = $DbHelperTools->getAmountsAgreement($agreement->id);

        //Fiche technique
        $details_af = $DbHelperTools->getAfDefaultSheetDetails($agreement->af->id);
        $AF_OBJECTIFS = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_OBJ'] : '';
        $AF_CONTENT = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_CNT'] : '';
        $AF_MODALITE_PEDAGOGIQUES = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_MP'] : '';
        $AF_MODALITE_ACCES = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_MA'] : '';
        $AF_MODALITE_EVALUATION = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_ME'] : '';
        $AF_MODALITE_MOYENS_DISPOSITION = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_MAD'] : '';
        $AF_PRE_REQUIS = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_PREQ'] : '';
        $AF_PROFIL_INTERVENANTS = (count($details_af) > 0) ? $details_af['PF_TYPE_SHEETS_INT'] : '';

        $type_pf = $DbHelperTools->getParamPFormation($agreement->af->formation_id, 'PF_TYPE_FORMATION');
        if ($type_pf != 'PF_TYPE_DIP') {
            $html_recap_schedules = $DbHelperTools->getHtmlAfRecapSchedules($agreement->af->id);
        } else {
            $html_recap_schedules = "<p>Conformément aux modalités de planning prévues</p>";
        }

        $training_site = ($agreement->af->training_site != 'OTHER') ? $agreement->af->training_site : $agreement->af->other_training_site;

        $formattedDate = today()->format('d/m/Y');
        // dd($formattedDate);

        
        //echo $html_recap_schedules;exit();
        //dd($html_recap_schedules);
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
            //
            $agreement->entity->name,
            $entity_adresse->line_1,
            $entity_adresse->line_2,
            $entity_adresse->postal_code,
            $entity_adresse->city,
            $agreement->contact->firstname . ' ' . $agreement->contact->lastname,
            $agreement->contact->function,

            $agreement->af->title,
            $agreement->af->code,
            number_format($calcul['total'], 2),
            $DbHelperTools->getHtmlAgreementItems($agreement->id),
            //BANK
            config('global.company_bank_code'),
            config('global.company_bank_guichet'),
            config('global.company_bank_account_number'),
            config('global.company_bank_key_rib'),
            config('global.company_bank_iban'),
            config('global.company_bank_bic'),
            //AF PEFAGOGIQUE
            $AF_OBJECTIFS,
            $AF_CONTENT,
            $AF_MODALITE_PEDAGOGIQUES,
            $AF_MODALITE_ACCES,
            $AF_MODALITE_EVALUATION,
            $AF_MODALITE_MOYENS_DISPOSITION,
            $AF_PRE_REQUIS,
            $AF_PROFIL_INTERVENANTS,
            $agreement->af->max_nb_trainees . ' participant(s)',
            $agreement->af->nb_days,
            $agreement->af->nb_hours,
            $agreement->af->nb_pratical_days ?? 0,
            $agreement->af->nb_pratical_hours ?? 0,
            $html_recap_schedules,
            $training_site,
            $DbHelperTools->getHtmlAgreementFundings($agreement_id),
            $formattedDate,
            $dn->format('d/m/Y'),
            $agreement->number,
            public_path('custom/images/signature_cachet.png'),
            config('global.company_denomination'),
			$pStags
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

        // dump($htmlFooter);
        // $htmlMain= preg_replace('/\<\/?(figure( class\=\"[a-z]+\")?|tbody|theader|tfooter)\>/', '', $htmlMain);
        // $htmlHeader= preg_replace('/\<\/?(figure( class\=\"[a-z]+\")?|tbody|theader|tfooter)\>/', '', $htmlHeader);
        // $htmlFooter= preg_replace('/\<\/?(figure( class\=\"[a-z]+\")?|tbody|theader|tfooter)\>/', '', $htmlFooter);
        // dd($htmlFooter);

        $pdf = PDF::loadView('pages.pdf.model', compact('htmlMain', 'htmlHeader', 'htmlFooter'));
        if ($render_type == 1) {
            return $pdf->stream();
        }
        $pdfName = $agreement->number . '-' . time() . '.pdf';
        if ($render_type == 3) {
            $temp = env('TEMP_PDF_FOLDER');
            $pdfName = $document_type.'_'.$agreement->number . '.pdf';
            $temp_directory = public_path() . "/" . $temp;
            $pathToStorage = $temp_directory . '/' . $pdfName;
            if (!File::isDirectory($temp_directory)) {
                File::makeDirectory($temp_directory, 0777, true, true);
            }
            $pdf->save($pathToStorage);
            return true;
        }
        return $pdf->download($pdfName);
        //return $pdf->download($agreement->number . '-' . time() . '.pdf');
    }

    public function sdtAgreements(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Agreement::latest();


        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('estimate_number', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('note', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
            } else {
                $datas = Agreement::orderByDesc('id');
            }
        }
        if ($af_id > 0) {
            $datas->where('af_id', $af_id)->latest();
        }

        $recordsTotal=count($datas->get());

         if($request->length>0){
             $start=(int) $request->start;
             $length=(int) $request->length;
             $datas->skip($start)->take($length);
         }

        $udatas = $datas->orderByDesc('id')->get();
        // dd($udatas);

        $nwdata = [];

        foreach($udatas as $dt)
        {
            $entity = Entitie::where('id',$dt->entitie_id)->first();

            if($entity->entity_type == 'S')
            {
                $nwdata[] = $dt;
            }
        }

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

        // dd($nwdata);

        foreach ($udatas as $d) {

            // if($d->agreement_type != 'INT/FAC'){
                $row = array();
                //ID
                $row[] = $d->id;
                //<th>Type</th>
                $pStatus = '<p><span class="label label-sm label-light-' . $arrayCssLabel[$d->status] . ' label-inline">' . $arrayLabel[$d->status] . '</span></p>';
                $row[] = $arrayLabel[$d->agreement_type] . $pStatus;
                //<th>N°</th>
                $pEstimate = '';
                if ($d->estimate) {
                    $pEstimate = '<p class="text-warning">Devis n° : ' . $d->estimate->estimate_number . '</p>';
                }
                //stats
                $arr = $DbHelperTools->getAgreementStatistics($d->id);
                $fundings = '<p class="text-primary"><strong>' . $arr['fundings'] . '</strong> financeur(s)</p>';
                $deadlines = '<p class="text-primary"><strong>' . $arr['invoices'] . '</strong> facture(s)/<strong>' . $arr['deadlines'] . '</strong> échéance(s)</p>';
                $row[] = '<p class="text-info">#' . $d->number . '</p>' . $pEstimate . $fundings . $deadlines;
                //<th>Client</th>
                $afText = '';
                if ($af_id == 0) {
                    $afText = '<p><a href="/view/af/' . $d->af->id . '">' . $d->af->code . '</a><p>';
                }
                $row[] = $afText . '<p>' . $d->entity->name . ' - ' . $d->entity->ref . ' - ' . $d->entity->entity_type . '</p>';
                //<th>Montant</th>
                $calcul = $DbHelperTools->getAmountsAgreement($d->id);
                $row[] = '<p class="text-info">' . number_format($calcul['total'], 2) . ' €</p>';
                //<th>Dates</th>
                $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
                $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
                $row[] = $created_at . $updated_at;

                //<th>Actions</th>
                $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formAgreement(' . $d->id . ',' . $d->af->id . ',' . $d->entity->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';


                //$btn_pdf='<a class="btn btn-sm btn-clean btn-icon" target="_blank" href="/pdf/agreement/'.$d->id.'/1" title="Pdf"><i class="'.$tools->getIconeByAction('PDF').'"></i></a>';
                //$btn_pdf_download='<a class="btn btn-sm btn-clean btn-icon" href="/pdf/agreement/'.$d->id.'/2" title="Download"><i class="'.$tools->getIconeByAction('DOWNLOAD').'"></i></a>';

                $btn_pdf = '<a target="_blank" href="/pdf/agreement/' . $d->id . '/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>';
                $btn_pdf_download = ' <a target="_blank" href="/pdf/agreement/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>';
                $btn_new_invoice = '';
                if (in_array($d->status,['sent','signed']) && $calcul['total']>0) {
                    $btn_new_invoice = ' <a href="javascript:void(0)" onclick="_createInvoiceFormAgreement(' . $d->id . ')" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Créer facture</span></a>';
                }
                $btn_send_email = '<a style="cursor: pointer;" onclick="_formSendAgreement(' . $d->id . ')" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('ENVELOPE') . '"></i></span> <span class="navi-text">Envoyer par e-mail</span> </a>';
                $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                        data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                            <ul class="navi flex-column navi-hover py-2">
                                <li class="navi-item">
                                    ' . $btn_send_email . '
                                    ' . $btn_new_invoice . '
                                    ' . $btn_pdf . '
                                    ' . $btn_pdf_download . '
                                </li>
                            </ul>
                        </div>
                    </div>';

                $row[] = $btn_edit . $btn_more;
                $data[] = $row;
            // }
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
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=> $recordsTotal,
        ];
        return response()->json($result);
    }

    public function agreements()
    {
        $page_title = 'Liste des conventions & contrats';
        $page_description = '';
        return view('pages.commerce.agreement.list', compact('page_title', 'page_description'));
    }

    public function selectContactsOptions($entity_id)
    {
        $result = [];
        $rows = Contact::select('id', 'firstname', 'lastname')->where('entitie_id', $entity_id)->get();
        if (count($rows) > 0) {
            foreach ($rows as $en) {
                $result[] = ['id' => $en['id'], 'name' => ($en['firstname'] . ' - ' . $en['lastname'])];
            }
        }
        return response()->json($result);
    }

    public function formAgreement($agreement_id, $default_af_id, $default_entity_id)
    {
        $row = null;
        if ($agreement_id > 0) {
            $row = Agreement::findOrFail($agreement_id);
        }
        $DbHelperTools = new DbHelperTools();
        $agreementHasInvoice=$DbHelperTools->agreementHasInvoice($agreement_id);
        return view('pages.commerce.agreement.form.agreement', compact('row', 'default_af_id', 'default_entity_id','agreementHasInvoice'));
    }

    public function storeFormAgreement(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        $agreement_id = $af_id = $entity_id = 0;
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $rs_type = $DbHelperTools->getAgreementTypeByEntity($request->entitie_id);
            //dd($rs_type);
            $prefix = $rs_type['prefix'];
            $typeIndice = $rs_type['typeIndice'];
            $agreement_type = $rs_type['agreement_type'];
            //dd($request->all());
            $agreement_date = Carbon::createFromFormat('d/m/Y', $request->agreement_date);
            $data = array(
                "id" => $request->id,
                //'number' => ($request->id == 0) ? $prefix . $DbHelperTools->generateAgreementNumber($typeIndice) : null,
                'number' => ($request->id == 0) ? $prefix . $DbHelperTools->generateAgreementNumberByAgreementDate($typeIndice,$agreement_date) : null,
                'agreement_date' => $agreement_date,
                'agreement_type' => $agreement_type,
                "af_id" => $request->af_id,
                "entitie_id" => $request->entitie_id,
                "contact_id" => $request->contact_id,
                "tax_percentage" => $request->tax_percentage,
                "status" => $request->status,
            );
            $agreement_id = $DbHelperTools->manageAgreement($data);

            $entity_id = $request->entitie_id;
            $af_id = $request->af_id;
            //Create default item
            if ($agreement_id > 0 && $request->id == 0) {
                $af = Action::select('title', 'formation_id')->where('id', $af_id)->first();
                $details = $DbHelperTools->getQuantityUnittypeRate($af_id, $entity_id);
                $data = array(
                    "id" => 0,
                    "title" => $af->title,
                    "description" => $DbHelperTools->getItemDescription($af_id),
                    "quantity" => $details['quantity'],
                    "unit_type" => $details['unit_type'],
                    "rate" => $details['rate'],
                    "total" => $details['quantity'] * $details['rate'],
                    "is_main_item" => 1,
                    "agreement_id" => $agreement_id,
                    "pf_id" => $af->formation_id
                );
                $item_id = $DbHelperTools->manageAgreementItem($data);
            }
            $success = true;
            $msg = 'Le données ont étés enregistrées avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'agreement_id' => $agreement_id,
            'entity_id' => $entity_id,
            'af_id' => $af_id,
        ]);
    }

    public function formAgreementItem($item_id, $agreement_id)
    {
        $row = null;
        if ($item_id > 0) {
            $row = Agreementitem::findOrFail($item_id);
        }
        return view('pages.commerce.agreement.form.item', compact('row', 'agreement_id'));
    }

    public function getFundings($agreement_id)
    {
        $agreement_amount = $funders_amount = $rest_amount = 0;
        $htmlFinance = $agreement = null;
        $canAddFunder =false;
        if ($agreement_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $htmlFinance = $DbHelperTools->getHtmlAgreementFundingsForForm($agreement_id);
            $agreement = Agreement::select('id', 'agreement_type')->where('id', $agreement_id)->first();
            $calcul = $DbHelperTools->getAmountsAgreement($agreement_id);
            $agreement_amount = $calcul['total'];
            $arrayFunding = $DbHelperTools->getFundingsTotalAmount($agreement_id);
            $funders_amount = $arrayFunding['total'];
            $rest_amount = $arrayFunding['rest'];
            if($rest_amount>0){
                $canAddFunder =true;
            }
        }
        $agreementHasInvoice=$DbHelperTools->agreementHasInvoice($agreement_id);
        return view('pages.commerce.agreement.fundings', compact('agreement', 'agreement_amount', 'funders_amount', 'rest_amount', 'htmlFinance','canAddFunder','agreementHasInvoice'));
    }

    public function storeFormAgreementItem(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            $data = array(
                "id" => $request->id,
                "title" => $request->title,
                "description" => $request->description,
                "quantity" => $request->quantity,
                "unit_type" => $request->unit_type,
                "rate" => $request->rate,
                "total" => $request->rate * $request->quantity,
                "is_main_item" => $request->is_main_item,
                "agreement_id" => $request->agreement_id,
                "pf_id" => $request->formation_id
            );
            $row_id = $DbHelperTools->manageAgreementItem($data);
            $success = true;
            $msg = 'Element a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formAgreementDiscount($agreement_id)
    {
        $row = null;
        if ($agreement_id > 0) {
            $row = Agreement::select('id', 'discount_type', 'discount_amount', 'discount_amount_type', 'discount_label')->where('id', $agreement_id)->first();
        }
        return view('pages.commerce.agreement.form.discount', compact('row'));
    }

    public function storeFormAgreementDiscount(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            if ($request->id > 0) {
                $row = Agreement::find($request->id);
                $row->discount_type = $request->discount_type;
                $row->discount_amount = $request->discount_amount;
                $row->discount_amount_type = $request->discount_amount_type;
                $row->discount_label = $request->discount_label;
                $row->save();
                $id = $row->id;
                $success = true;
                $msg = 'Réduction a été enregistrée avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formFunding($funding_id, $agreement_id)
    {
        $row = null;
        if ($funding_id > 0) {
            $row = Funding::findOrFail($funding_id);
        }
        $agreement_amount = $funders_amount = $rest_amount = 0;
        $remain_percentage = 0;
        if ($agreement_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $calcul = $DbHelperTools->getAmountsAgreement($agreement_id);
            $agreement_amount = $calcul['total'];

            $arrayFunding = $DbHelperTools->getFundingsTotalAmount($agreement_id);
            $funders_amount = $arrayFunding['total'];
            $rest_amount = $arrayFunding['rest'];
            $ar = $DbHelperTools->retrunTotalPercentageFunders($agreement_id);
            $remain_percentage=$ar['remain_percentage'];
        }
        return view('pages.commerce.agreement.form.funding', compact('row', 'agreement_id', 'agreement_amount', 'funders_amount', 'rest_amount','remain_percentage'));
    }

    public function selectFunderEntitiesOptions($agreement_id, $funding_id)
    {
        /*
        $funding_id>0 mode edition
        $funding_id==0 mode add
        */
        $result = [];
        if ($funding_id > 0) {
            $funding = Funding::find($funding_id);
            if (isset($funding->entity)) {
                $result[] = ['id' => $funding->entity->id, 'name' => ($funding->entity->name . ' - ' . $funding->entity->ref . ' - ' . $funding->entity->entity_type)];
            }
        } else {
            $agreement = Agreement::find($agreement_id);
            $ids_added_fundings = Funding::select('entitie_id')->where('agreement_id', $agreement_id)->pluck('entitie_id');
            $entities = Entitie::select('id', 'ref', 'name', 'entity_type')->where([['is_active', 1], ['is_funder', 1], ['id', '!=', $agreement->entity->id]])->whereNotIn('id', $ids_added_fundings)->get();
            if (count($entities) > 0) {
                foreach ($entities as $en) {
                    $result[] = ['id' => $en['id'], 'name' => ($en['name'] . ' - ' . $en['ref'] . ' - ' . $en['entity_type'])];
                }
            }
            //Ajouter aussi l'entité initial comme financeur
            //$entity=Entitie::select('id','ref','name','entity_type')->where('id',$entity_id)->first();
            if (isset($agreement->entity)) {
                $result[] = ['id' => $agreement->entity->id, 'name' => ($agreement->entity->name . ' - ' . $agreement->entity->ref . ' - ' . $agreement->entity->entity_type)];
            }
        }

        return response()->json($result);
    }

    //
    public function storeFormFunding(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            $data = array(
                "id" => $request->id,
                "amount_type" => $request->amount_type,
                "amount" => $request->amount,
                "status" => 'created',
                "agreement_id" => $request->agreement_id,
                "entitie_id" => $request->entitie_id,
                "is_cfa" => $request->is_cfa,
            );
            $row_id = $DbHelperTools->manageFunding($data);
            $success = true;
            $msg = 'Financeur a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formFundingPayment($fundingpayment_id, $funding_id)
    {
        $row = null;
        if ($fundingpayment_id > 0) {
            $row = Fundingpayment::findOrFail($fundingpayment_id);
        }
        $DbHelperTools = new DbHelperTools();
        $funder_amount = $DbHelperTools->calculateAmountFunding($funding_id);
        $arrayEcheanceAmounts = $DbHelperTools->getEcheanceTotalAmount($funding_id);
        $echeance_amount = $arrayEcheanceAmounts['total'];
        $rest_amount = $arrayEcheanceAmounts['rest'];
        $arr=$DbHelperTools->returnTotalPercentageEcheances($funding_id);
        $remain_percentage=$arr['remain_percentage'];
        return view('pages.commerce.agreement.form.fundingpayment', compact('row', 'funder_amount', 'funding_id', 'echeance_amount', 'rest_amount','remain_percentage'));
    }

    public function storeFormFundingPayment(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $due_date = Carbon::createFromFormat('d/m/Y', $request->due_date);
            //dd($request->all());
            $data = array(
                "id" => $request->id,
                "amount_type" => $request->amount_type,
                "amount" => $request->amount,
                "due_date" => $due_date,
                "payment_date" => null,
                "funding_id" => $request->funding_id,
            );
            //dd($data);
            $row_id = $DbHelperTools->manageFundingPayment($data);
            $success = true;
            $msg = 'Echéance a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function deleteFunding(Request $request)
    {
        $success = false;
        if ($request->isMethod('delete')) {
            if ($request->has('funding_id')) {
                $funding_id = $request->funding_id;
                //dd($contract_id);
                $DbHelperTools = new DbHelperTools();
                if ($funding_id > 0) {
                    $deletedRows = $DbHelperTools->massDeletes([$funding_id], 'funding', 1);
                    if ($deletedRows)
                        $success = true;
                }
            }
        }
        return response()->json(['success' => $success]);
    }

    public function deleteFundingPayment(Request $request)
    {
        $success = false;
        if ($request->isMethod('delete')) {
            if ($request->has('fundingpayment_id')) {
                $fundingpayment_id = $request->fundingpayment_id;
                //dd($contract_id);
                $DbHelperTools = new DbHelperTools();
                if ($fundingpayment_id > 0) {
                    $deletedRows = $DbHelperTools->massDeletes([$fundingpayment_id], 'fundingpayment', 1);
                    if ($deletedRows)
                        $success = true;
                }
            }
        }
        return response()->json(['success' => $success]);
    }

    public function sdtEnrollmentsMembersConvocation(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $members = [];
        $query = DB::table('af_schedulecontacts')
                    ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                    ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                    ->select('af_schedulecontacts.member_id')
                    ->where('af_sessions.af_id',$af_id);
                    // dd($dtRequests);
        if(isset($dtRequests['session_id']) && $dtRequests['session_id']){
            $query->where('af_sessions.id',$dtRequests['session_id']);
            $members = $query->pluck('af_schedulecontacts.member_id')->unique()->toArray();
        }
        if(isset($dtRequests['session_date_id']) && $dtRequests['session_date_id']){
            $query->where('af_sessiondates.id', $dtRequests['session_date_id']);
            $members = $query->pluck('af_schedulecontacts.member_id')->unique()->toArray();
        }
        if(isset($dtRequests['seance_id']) && $dtRequests['seance_id']){
            $query->where('af_schedules.id', $dtRequests['seance_id']);
            $members = $query->pluck('af_schedulecontacts.member_id')->unique()->toArray();
        }
        // dd($query->toSql());
        // dd(count($members));
        // dd($members);

        $data = $meta = [];
        $datas = [];
        if ($af_id > 0) {
            /* $ids_enrollments = Enrollment::select('id')->where([['af_id', $af_id], ['enrollment_type', 'S']])->get()->pluck('id');
            if (count($ids_enrollments) > 0) {
                $datas = Member::whereIn('enrollment_id', $ids_enrollments)->get();
            } */
            $datas = Member::where([['af_enrollments.af_id', $af_id],['af_enrollments.enrollment_type', 'S']])->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')->get(['af_members.*'])->unique();

            if(count($members)){
                $datas = Member::where([['af_enrollments.af_id', $af_id],['af_enrollments.enrollment_type', 'S']])->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                ->whereIn('af_members.id', $members)
                ->get(['af_members.*'])->unique();
            }
                // dd(count($datas));
        }

        foreach ($datas as $d) {
            $row = array();
            //th
            //    $row[]=$d->id;
            $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" value="' . $d->id . '" class="checkable"/><span></span></label>';
            //<th>Prénom</th>
            $firstname = ($d->contact) ? $d->contact->firstname : $d->unknown_contact_name;
            $row[] = $firstname;
            //Nom
            $lastname = ($d->contact) ? $d->contact->lastname : '';
            $row[] = $lastname;
            //<th>Type</th>
            $row[] = ($d->contact) ? $d->contact->type_former_intervention : '';
            //<th>Etat Planning</th>
            /* $spanPlanif = '';
            if ($d->contact) {
                if ($d->contact->id > 0) {
                    //$spanPlanif = $DbHelperTools->getPlanifContact($d->id, $d->enrollment->action->id);
                }
            } */
            $btn_planif_details = '<button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_showScheduleDetails(' .$d->enrollment->action->id. ',' .$d->id. ')" title="Détails du planning"><i class="' . $tools->getIconeByAction('INFO') . '"></i></button>';
            $row[] = $btn_planif_details;
            //<th>Nb heure</th>
            $row[] = '';
            //<th>Cout</th>
            $row[] = '';
            //Actions
            $type = "'MEMBER'";
            $btn_delete = '';//<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteEnrollmentMember('.$d->id.','.$type.')" title="Suppression"><i class="'.$tools->getIconeByAction('DELETE').'"></i></button>';
            $row[] = $btn_delete;

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

    public function convocations()
    {
        $page_title = 'Liste des convocations';
        $page_description = '';
        return view('pages.commerce.convocation.list', compact('page_title', 'page_description'));
    }

    public function sdtConvocations(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $contacts_ids = [];
        $data = $meta = [];
        // dd($request->all());
        $datas = Convocation::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($af_id > 0) {
                    $datas->where('af_id', $af_id);
                }   
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    if($af_id > 0){
                        $afs_ids[]=(int) $af_id;
                    }else{
                        $afs_ids=Action::select('id')->where('code','like', '%' . $request->filter_text . '%')->orWhere('title','like', '%' . $request->filter_text . '%')->pluck('id');
                    }
                    //dd($afs_ids);
                    $contacts_ids += Contact::select('id')->orWhere('firstname','like', '%' . $request->filter_text . '%')->orWhere('lastname','like', '%' . $request->filter_text . '%')->pluck('id')->toArray();

                    //  $query =  Member::where('af_schedulecontacts.is_former', 0)
                    //     ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                    //     ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    //     ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                    //     ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                    //     ->where('af_sessions.af_id', '=', $af_id);
                    // $contacts_ids_query = $query;
                    // $contacts_ids =  $contacts_ids_query->Where('af_schedules.code','like', '%' . $request->filter_text . '%')->select('af_members.contact_id')->pluck('af_members.contact_id')->toArray();
                    // $contacts_ids_query = $query;
                    // $contacts_ids = $contacts_ids_query->Where('af_sessiondates.name','like', '%' . $request->filter_text . '%')->select('af_members.contact_id')->pluck('af_members.contact_id')->toArray();
                    // $contacts_ids_query = $query;
                    // $contacts_ids = $contacts_ids_query->Where('af_sessions.title','like', '%' . $request->filter_text . '%')->select('af_members.contact_id')->pluck('af_members.contact_id')->toArray();
                        // ->join('en_contacts', 'en_contacts.id', '=', 'contact_id')->orderBy('en_contacts.firstname','asc')->get(['af_members.*'])->unique();
                    // $datas->whereIn('af_id', $afs_ids)->where('number', 'like', '%' . $request->filter_text . '%');
                   /** session filtre **/ 
                    // $contacts_ids +=  Member::where('af_schedulecontacts.is_former', 0)
                    //     ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                    //     ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    //     ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                    //     ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                    //     ->where('af_sessions.af_id', '=', $af_id)
                    //     ->Where('af_schedules.code','like', '%' . $request->filter_text . '%')->select('af_members.contact_id')->pluck('af_members.contact_id')->toArray();
                    
                    // $contacts_ids +=  Member::where('af_schedulecontacts.is_former', 0)
                    //     ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                    //     ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    //     ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                    //     ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                    //     ->where('af_sessions.af_id', '=', $af_id)
                    //     ->Where('af_sessiondates.name','like', '%' . $request->filter_text . '%')->select('af_members.contact_id')->pluck('af_members.contact_id')->toArray();
                    
                    // $contacts_ids +=  Member::where('af_schedulecontacts.is_former', 0)
                    //     ->join('af_schedulecontacts', 'af_schedulecontacts.member_id', '=', 'af_members.id')
                    //     ->join('af_schedules', 'af_schedules.id', '=', 'af_schedulecontacts.schedule_id')
                    //     ->join('af_sessiondates', 'af_sessiondates.id', '=', 'af_schedules.sessiondate_id')
                    //     ->join('af_sessions', 'af_sessions.id', '=', 'af_sessiondates.session_id')
                    //     ->where('af_sessions.af_id', '=', $af_id)
                    //     ->Where('af_sessions.title','like', '%' . $request->filter_text . '%')->select('af_members.contact_id')->pluck('af_members.contact_id')->toArray();
                   /** /filtre session **/
                    $datas->where('number', 'like', '%' . $request->filter_text . '%');
                    // $datas->orWhere('number', 'like', '%' . $request->filter_text . '%');
                    if(count($contacts_ids)>0){
                        $datas->orWhereIn('contact_id',$contacts_ids);
                        // $datas->whereIn('contact_id',$contacts_ids);
                    }
                    // dd($datas->toSql());
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('created_at', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                if ($request->has('filter_status') && !empty($request->filter_status)) {
                    $datas->where('status', $request->filter_status);
                }
                /* if ($af_id > 0) {
                    $datas->where('af_id', $af_id);
                } */
            }else{
                if ($af_id > 0) {
                    $datas = Convocation::where('af_id', $af_id)->orderByDesc('id');
                } else {
                    $datas = Convocation::orderByDesc('id');
                }
            }
        }        
        $recordsTotal=count($datas->get());

         if($request->length>0){
             $start=(int) $request->start;
             $length=(int) $request->length;
             $datas->skip($start)->take($length);
         }
        $udatas = $datas->orderByDesc('id')->get();
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
        foreach ($udatas as $d) {
            $row = array();
            //ID
            //     $row[]=$d->id;
            $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" value="' . $d->id . '" class="checkable"/><span></span></label>';
            $row[] = $d->number;
            $row[] = $d->contact_id != null ? $d->contact->firstname . ' - ' . $d->contact->lastname : '';
            $afText = '';
            if ($af_id == 0) {
                $afText = '<a href="/view/af/' . $d->af->id . '">' . $d->af->code . '</a>';
            }
            //<th>Client</th>
            $client = '<ul class="list-unstyled"><li>Client : ' . $d->entity->name . ' - ' . $d->entity->ref . ' - ' . $d->entity->entity_type . '</li>';
            $row[] = $afText . $client;
            $pStatus = '<p><span class="label label-sm label-light-' . $arrayCssLabel[$d->status] . ' label-inline">' . $arrayLabel[$d->status] . '</span></p>';
            $row[] = '<p class="text-info">#' . $d->number . $pStatus . '</p>';
            $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formConvocation(' . $d->id . ',' . $d->af->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_pdf = '<a target="_blank" href="/pdf/convocation/' . $d->id . '/1" class="btn btn-sm btn-clean btn-icon" title="PDF"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text"></span></a>';
            $btn_pdf_download = '<a target="_blank" href="/pdf/convocation/' . $d->id . '/2" class="btn btn-sm btn-clean btn-icon" title="Télécharger"><span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text"></span></a>';

            /*    $btn_more='<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                    data-toggle="dropdown"><i class="'.$tools->getIconeByAction('MORE').'"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                '.$btn_pdf.'
                                '.$btn_pdf_download.'
                            </li>
                        </ul>
                    </div>
                </div>';
                $row[]=$btn_edit . $btn_more;*/
            $row[] = $btn_pdf . $btn_pdf_download;
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
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=> $recordsTotal,
        ];
        return response()->json($result);
    }

    public function formConvocation($convocation_id, $default_af_id)
    {
        $session_data = Session::select('id', 'title')->where('af_id', $default_af_id)->get();
        $row = null;
        if ($convocation_id > 0) {
            $row = Convocation::findOrFail($convocation_id);
        }
        return view('pages.commerce.convocation.form.convocation', compact('row', 'default_af_id', 'session_data'));
    }

    public function sdtEnrollmentsGetDatesConvocation($id_session){
        $sessiondates = Sessiondate::select('id', 'planning_date')->where('session_id', $id_session)->get();
        return response()->json([
            'sessiondates' => $sessiondates
        ]);
    }


    public function sdtEnrollmentsGetSeancesConvocation($id_sessiondate){
        $seances = schedule::select('id', 'start_hour', 'end_hour')->where('sessiondate_id', $id_sessiondate)->get();
        return response()->json([
            'seances' => $seances
        ]);
    }

    public function generateConvocation(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        $invoice_id = $af_id = $entity_id = 0;
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $ids = $request->ids_members;
            $af_id = $request->af_id;
            foreach ($ids as $id_m) {
                $member=Member::findOrFail($id_m);
                //$contact = Contact::findOrFail($id_m);
                $convocation = Convocation::select('id')->where([['af_id', $af_id], ['contact_id', $id_m]])->first();
                $numConv = Convocation::orderByDesc('id')->first();
                $numConv = $numConv != null ? $numConv->number : 0;
                $numConv = 'CV' . str_pad(filter_var($numConv, FILTER_SANITIZE_NUMBER_INT) + 1, 5, '0', STR_PAD_LEFT);
                $data = array(
                    'id' => $convocation != null ? $convocation->id : 0,
                    'number' => $convocation != null ? $convocation->number : $numConv,//($request->id==0)?$DbHelperTools->generateInvoiceNumber('INVOICE'):null,
                    'status' => $convocation != null ? $convocation->status : 'draft',
                    'entitie_id' => $member->contact->entitie->id,//$request->entitie_id,
                    'contact_id' => $member->contact->id,
                    'af_id' => $af_id,
                );
                $convocation_id = $DbHelperTools->manageConvocation($data);
            }
            $success = true;
            $msg = 'Convocations générées avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'convocation_id' => $invoice_id,
            'af_id' => $af_id,
        ]);
    }

    public function invoices()
    {
        $page_title = 'Liste des factures';
        $page_description = '';
        return view('pages.commerce.invoice.list', compact('page_title', 'page_description'));
    }

    public function sdtInvoices(Request $request, $af_id)
    {   
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
     /*   if ($af_id > 0) {
            $datas = Invoice::where('af_id', $af_id)->latest();
        } else {*/
            $datas = Invoice::latest();
    //    }
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('number', 'like', '%' . $request->filter_text . '%')
                        ->orWhere('note', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('bill_date', [$start,$end]);
                    }
                }
                if($request->has('filter_entitie_id')){
                    if($request->filter_entitie_id>0)
                        $datas->where('entitie_id', $request->filter_entitie_id);
                }
            } else {
                $datas = Invoice::orderByDesc('id');
            }
        }
        $recordsTotal=count($datas->get());
        
         if($request->length>0){
             $start=(int) $request->start;
             $length=(int) $request->length;
             $datas->skip($start)->take($length);
         }
        $udatas = $datas->orderByDesc('id')->get();
        $arrayLabel = [
            'draft' => 'Brouillon',
            'not_paid' => 'Non payé',
            'partial_paid' => 'Partiellement payée',
            'paid' => 'Payée',
            'cancelled' => 'Annulé',
            'deposit' => 'Deposer',
            'sent' => 'Envoyer',
            //
            'cvts_ctrs' => 'Facture convention/contrat',
            'students' => 'Facture étudiant',
            'INT/FAC' => 'Intervenant sur facture',
        ];
        $arrayCssLabel = [
            'draft' => 'info',
            'not_paid' => 'primary',
            'partial_paid' => 'warning',
            'paid' => 'success',
            'cancelled' => 'danger',
            'deposit' => 'deposer',
            'sent' => 'envoyer',
            //
            'cvts_ctrs' => 'primary',
            'students' => 'info',
            'INT/FAC' => 'Intervenant sur facture',
        ];
        $arrayFundingOptions=[
            'contact_itself' => 'Le contact lui meme',
            'entity_contact' => 'L’entité du contact',
            'cfa_funder' => 'C financeur',
            'other_funders' => 'Autre financeur',
        ];
        foreach ($udatas as $d) {
        //if($d->invoice_type == 'INT/FAC'){
            $inv_af_id=($d->agreement)?$d->agreement->af->id:$d->af_id;
            if ($af_id > 0 && $af_id != $inv_af_id) {
                continue;
            }
            $af=Action::select('id','code','title')->find($inv_af_id);
            $row = array();
            //ID
            //$row[] = $d->id;
            $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" value="' . $d->id . '" class="checkable"/><span></span></label>';
            //<th>N°</th>
            $typeFacture='<p class="text-'.$arrayCssLabel[$d->invoice_type].'">'.$arrayLabel[$d->invoice_type].'</p>';
            $pAgreement = '';
            if ($d->agreement) {
                $id_attachement = Invoiceitem::select('attachement_id')->where('invoice_id',$d->id)->pluck('attachement_id');
                if(count($id_attachement) != 0){ 
                    if($id_attachement[0] != NULL){
                $attachment = Attachment::findOrFail(intval($id_attachement[0]));
                $btn_pdf_agreement = ' <a target="_blank" href="/uploads/afs/'.$d->af_id.'/documents-invoice/Invoice/'.$attachment->path.'" title="Pdf"><i class="fas fa-external-link-alt"></i></a>';
                $pAgreement = '<p class="text-warning">' . $d->agreement->agreement_type . ' n° : ' . $d->agreement->number . $btn_pdf_agreement . '</p>';
                    }
                }
            }
            $pStatus = '<p><span class="label label-sm label-light-' . $arrayCssLabel[$d->status] . ' label-inline">' . $arrayLabel[$d->status] . '</span></p>';
            
            $pfunding_option='<p class="font-size-sm text-warning">Option de financement : '.$arrayFundingOptions[$d->funding_option].'</p>';

            $arrayRefund=$DbHelperTools->getRefundByInvoice($d->id);
            $pRefund='';
            if($arrayRefund['id']>0){
                // dd($arrayRefund['number']);
                $pRefund='<p class="text-danger"><a class="text-danger font-size-sm" target="_blank" href="/pdf/refund/'.$arrayRefund['id'].'/1">AVOIR N° : #'.$arrayRefund['number'].'</a>'.
                ($arrayRefund['is_synched_to_sage'] ? '<br><span class="label label-sm label-light-danger label-inline"><i class="fas fa-xs fa-check"></i> Sage</span>' : '').'</p>';
            }
            $sSage="";
            if($d->is_synced_to_sage==1){
                $sSage='<br><p><span class="text-success font-size-sm"><i class="fas fa-sm fa-check"></i> Sage</span></p>';
            } elseif(!empty($d->sage_errors)){
                $sage_errors = json_decode($d->sage_errors);
                foreach ($sage_errors as $err_field) {
                    $sSage.='<br><p><span class="text-danger font-size-sm"><i class="fas fa-sm fa-times-circle"></i> Sage: '.$err_field.' non saisi</span></p>';
                }
            }
            $row[] = $typeFacture.'<p class="text-info">#' . $d->number . $pStatus . '</p>'.$pRefund.$pfunding_option.$sSage;
            //<th>AF</th>
            $afText = '';
            if ($af_id == 0) {
                $afText = '<a href="/view/af/' . $af->id . '">' . $af->code . '</a>';
            }
            //<th>Client</th>

            $client = '<ul class="list-unstyled"><li>Client : ' . $d->entity->name . ' - ' . $d->entity->ref . ' - ' . $d->entity->entity_type . '</li>';
            $pContact='';
            if($d->contact_id>0){
                $client.='<li>Contact : '.$d->contact->firstname.' '.$d->contact->lastname.'</li>';
            }
            $client.='</ul>';
            $pfinanceur='';
            $funder_infos_array=$DbHelperTools->getFunderByInvoice($d->id);
            $fin=$funder_infos_array['name'].(($funder_infos_array['ref'])?' - '.$funder_infos_array['ref']:'').(($funder_infos_array['entity_type'])?' - '.$funder_infos_array['entity_type']:'');
            $pfinanceur .='<p class="text-info">Financeur : '.$fin.'<p>';
            $pfinanceur .='<p class="text-info">Contact facturation :'.$funder_infos_array['contact_firstname'].' '.$funder_infos_array['contact_lastname'].'<p>';
            $row[] = $afText.$pAgreement.$client.$pfinanceur;
            //<th>Montant</th>
            $calcul = $DbHelperTools->getAmountsInvoice($d->id);
            $row[] = '<p class="text-info">' . number_format($calcul['total'], 2) . ' €</p>';
            //<th>Paiement reçu</th>
            //$pFundings=$DbHelperTools->getFundingPayment($d->agreement_id,$d->entity_funder->id);
            $pFundings='';
            if($d->fundingpayment_id>0){
                $pFundings = $DbHelperTools->getFundingPaymentById($d->fundingpayment_id, $d->entitie_funder_id, $d->agreement_id);
            }
            $row[] = '<p class="text-warning">' . number_format($calcul['total_paid'], 2) . ' €</p>' . $pFundings;
            //Dates
            $dtBillDate = Carbon::createFromFormat('Y-m-d', $d->bill_date);
            $dtIssueDate = Carbon::createFromFormat('Y-m-d', $d->due_date);
            $bill_date = $tools->constructParagraphLabelDot('xs', 'success', 'Date de facturation : ' . $dtBillDate->format('d/m/Y'));
            $due_date = $tools->constructParagraphLabelDot('xs', 'danger', 'Date d\'échéance : ' . $dtIssueDate->format('d/m/Y'));
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $bill_date . $due_date . $created_at . $updated_at;
            //<th>Actions</th>
            $btn_edit = '';
            if (!in_array($d->status, ['paid', 'partial_paid', 'sent','cancelled'])) {
                $btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formInvoice(' . $d->id . ',' . $inv_af_id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            }
            //$btn_pdf='<a class="btn btn-sm btn-clean btn-icon" target="_blank" href="/pdf/invoice/'.$d->id.'/1" title="Pdf"><i class="'.$tools->getIconeByAction('PDF').'"></i></a>';
            //$btn_pdf_download='<a class="btn btn-sm btn-clean btn-icon" href="/pdf/invoice/'.$d->id.'/2" title="Download"><i class="'.$tools->getIconeByAction('DOWNLOAD').'"></i></a>';
            //$btn_add_payment='<a class="btn btn-sm btn-clean btn-icon" href="javascript:void(0)" title="Ajouter payment" onclick="_formPayment(0,'.$d->id.')"><i class="'.$tools->getIconeByAction('DOLLAR').'"></i>'.'</a>';
            $a_add_payment = '';
            if ($d->status != 'paid' && $d->status != 'cancelled') {
                $a_add_payment = '<a style="cursor: pointer;" onclick="_formPayment(0,' . $d->id . ')" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOLLAR') . '"></i></span> <span class="navi-text">Ajouter paiement</span> </a>';
            }
            //Bouton avoir
            $a_add_refund = '';
            if ($d->status != 'cancelled') {
                $a_add_refund = '<a style="cursor: pointer;" onclick="_formRefund(0,' . $d->id . ')" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOLLAR') . '"></i></span> <span class="navi-text">Ajouter avoir</span> </a>';
            }

            $btn_send_email ='';
            if($calcul['total']>0)
                if (in_array($d->status,['draft','not_paid']))
                    $btn_send_email = '<a style="cursor: pointer;" onclick="_formSendInvoice(' . $d->id . ')" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('ENVELOPE') . '"></i></span> <span class="navi-text">Envoyer par e-mail</span> </a>';

            $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                    data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                ' . $btn_send_email . '
                                ' . $a_add_payment . '
                                ' . $a_add_refund . '
                                <a target="_blank" href="/pdf/invoice/' . $d->id . '/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>
                                <a target="_blank" href="/pdf/invoice/' . $d->id . '/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>
                            </li>
                        </ul>
                    </div>
                </div>';

            $row[] = $btn_edit . $btn_more;
            $data[] = $row;
        //}
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
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=> $recordsTotal,
        ];
        return response()->json($result);
    }

    public function formInvoice($invoice_id, $default_af_id)
    {
        $row = null;
        $funder_infos_array=null;
        $user=auth()->user()->email;
        if ($invoice_id > 0) {
            $row = Invoice::findOrFail($invoice_id);
            $DbHelperTools = new DbHelperTools();
            $funder_infos_array=$DbHelperTools->getFunderByInvoice($invoice_id);
        }
        return view('pages.commerce.invoice.form.invoice', compact('row','funder_infos_array','default_af_id','user'));
    }

    public function formMailInvoice($invoice_id)
    {
        $row = null;
        $content = '';
        $user=auth()->user()->email;
        if ($invoice_id > 0) {
            $row = Invoice::findOrFail($invoice_id);
               
            $email_model_id = Emailmodel::select('id')->where('code','ENVOI_FACTURE')->pluck('id');
            $emailmodel = Emailmodel::findOrFail(intval($email_model_id[0])); 
        
            $subject = strip_tags($emailmodel->custom_header);
            $content = $emailmodel->custom_content;

            $Estimate_type = $row->Estimate_type;

        }
        return view('pages.commerce.invoice.form.mail', compact('row', 'content','user'));
    }


    public function storeFormInvoice(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        $invoice_id = $af_id = $entity_id = 0;
        //dd($request->all());
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $bill_date = Carbon::createFromFormat('d/m/Y', $request->bill_date);
            $due_date = Carbon::createFromFormat('d/m/Y', $request->due_date);
            $af_id = $request->af_id;
            $data = array(
                'id' => $request->id,
                'number' => ($request->id == 0) ? $DbHelperTools->generateInvoiceNumberByBillDate('INVOICE',$bill_date) : null,
                'bill_date' => $bill_date,
                'due_date' => $due_date,
                'accounting_code' => $request->accounting_code,
                'analytical_code' => $request->analytical_code,
                'collective_code' => $request->collective_code,
                'note' => $request->note,
                'invoice_type' => $request->invoice_type,
                'tax_percentage' => $request->tax_percentage,
                'discount_label' => $request->discount_label, 
                'discount_amount' => $request->discount_amount,
                'discount_amount_type' => $request->discount_amount_type,
                'discount_type' => $request->discount_type,
                'status' => 'draft',
                'cancelled_at' => null,
                'cancelled_by' => null,
                'created_by' => Auth::user()->id,
                'entitie_id' => $request->entitie_id,
                'contact_id' => $request->contact_id,
                'agreement_id' => $request->agreement_id,
                'entitie_funder_id' => $request->entitie_funder_id,
                'contact_funder_id' => $request->contact_funder_id,
                'fundingpayment_id' => $request->fundingpayment_id,
                'funding_option' => $request->funding_option,
                'af_id' => $af_id,
            );
            //dd($data);
            $invoice_id = $DbHelperTools->manageInvoice($data);
            if ($invoice_id > 0 && $request->id == 0) {
                if ($request->fundingpayment_id > 0) {
                    $fundingpayment_amount = $DbHelperTools->calculateAmountFundingPayment($request->fundingpayment_id);
                    $af = Action::select('title', 'formation_id')->where('id', $af_id)->first();
                    $data = array(
                        "id" => 0,
                        "title" => $af->title,
                        "description" => $DbHelperTools->getItemDescription($af_id),
                        "quantity" => 1,
                        "unit_type" => '',
                        "rate" => $fundingpayment_amount,
                        "total" => $fundingpayment_amount,
                        "sort" => 1,
                        "fundingpayment_id" => $request->fundingpayment_id,
                        "invoice_id" => $invoice_id
                    );
                    $item_id = $DbHelperTools->manageInvoiceItem($data);
                }
            }
            $success = true;
            $msg = 'Le données ont étés enregistrées avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'invoice_id' => $invoice_id,
            'af_id' => $af_id,
        ]);
    }

    public function storeFormMailInvoice(Request $request)
    {
        $success = false;
        $msg = 'Erreur lors d\'envoi';

        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $invoice = Invoice::findOrFail($request->id);
            $to = '';
            if ($request->contact_id) {
                $contact = Contact::find($request->contact_id);
            }
            if (isset($contact) && (
                ($to = $contact->email) 
                || ($contact->entitie && ($to = $contact->entitie->email))
            )) {
                $subject = $request->subject;
                $arrayCc = $arrayBcc = [];
                $strCc = $strBcc = '';
                if (isset($request->cc) && !empty($request->cc)) {
                    $strCc = implode(',', array_column(json_decode($request->cc), 'value'));
                }
                if (isset($request->bcc) && !empty($request->bcc)) {
                    $strBcc = implode(',', array_column(json_decode($request->bcc), 'value'));
                }
                if (isset($strCc) && !empty($strCc)) {
                    $arrayCc = explode(',', $strCc);
                }
                if (isset($strBcc) && !empty($strBcc)) {
                    $arrayBcc = explode(',', $strBcc);
                }
                $myEmail = Mail::to($to);
                if (count($arrayCc))
                    $myEmail->cc($arrayCc);
                    
                if (count($arrayBcc))
                    $myEmail->bcc($arrayBcc);
                    
                //content
                $content = $request->content;
                $this->createPdfInvoice($invoice->id, 4);
                $attachmentFileName = 'FACTURE-' . $invoice->number . '.pdf';
                $temp = env('TEMP_PDF_FOLDER');
                $attachmentFile = public_path() . '/' . $temp . '/' . $attachmentFileName;
                // $attachmentFile = null;

                $attachmentFileName = 'FACTURE-' . $invoice->number . '.pdf';
                // $attachmentFileName = '';

                try {
                    $from = [['address' => Auth::user()->email, 'name' => Auth::user()->name . ' ' . Auth::user()->lastname]]; 
                    $myEmail->send(new InvoiceMail($invoice, $subject, '', null, $content, $from));
                } catch (Exception $e) {
                    $msg = 'Erreur lors d\'envoi du mail.';
                }

                if (File::exists($attachmentFile)) File::delete($attachmentFile);
                //update invoice to not_paid
                Invoice::where('id', $request->id)->update(['status' => 'not_paid']);
                $success = true;
                $msg = 'Le mail a été envoyée avec succès';
            } else {
                $msg = 'Aucun mail renseigné.';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg
        ]);
    }

    public function selectAgreementsOptions($af_id, $entity_id, $agreement_id)
    {
        $result = [];
        if ($agreement_id > 0) {
            $rows = Agreement::select('id', 'agreement_type', 'number')->where('id', $agreement_id)->get();
        } else {
            $rows = Agreement::select('id', 'agreement_type', 'number')->where([['af_id', $af_id], ['entitie_id', $entity_id]])->get();
        }
        if (count($rows) > 0) {
            $DbHelperTools = new DbHelperTools();
            foreach ($rows as $en) {
                $calcul = $DbHelperTools->getAmountsAgreement($en['id']);
                $result[] = ['id' => $en['id'], 'name' => ($en['agreement_type'] . ' : ' . $en['number'] . ' (' . number_format($calcul['total'], 2) . ' € )')];
            }
        }
        return response()->json($result);
    }

    //
    public function selectEntitiesForInvoiceOptions($af_id)
    {
        $result = [];
        $ids = Agreement::select('entitie_id')->where('af_id', $af_id)->pluck('entitie_id');
        $rows = Entitie::whereIn('id', $ids)->get();
        if (count($rows) > 0) {
            foreach ($rows as $a) {
                $result[] = ['id' => $a->id, 'name' => ($a->name . ' - ' . $a->ref . ' - ' . $a->entity_type)];
            }
        }
        return response()->json($result);
    }

    public function selectEntityContactOptions($agreement_id, $type)
    {
        $result = [];
        $rows = Agreement::where('id', $agreement_id)->get();
        if (count($rows) > 0) {
            foreach ($rows as $a) {
                if ($type == 'E') {
                    $result[] = ['id' => $a->entitie_id, 'name' => ($a->entity->name . ' - ' . $a->entity->ref . ' - ' . $a->entity->entity_type)];
                } elseif ($type == 'C') {
                    $result[] = ['id' => $a->contact_id, 'name' => ($a->contact->firstname . ' - ' . $a->contact->lastname)];
                }

            }
        }
        return response()->json($result);
    }

    public function getInvoiceItems($invoice_id)
    {
        $items = null;
        $invoice_type='';
        if ($invoice_id > 0) {
            $items = Invoiceitem::where('invoice_id', $invoice_id)->get();
            $DbHelperTools = new DbHelperTools();
            $calcul = $DbHelperTools->getAmountsInvoice($invoice_id);
            $inv = Invoice::select('id','discount_label','discount_type','discount_amount','discount_amount_type','tax_percentage','invoice_type','agreement_id')->where( 'id',$invoice_id )->first();
            $items1 = Agreementitem::where('agreement_id', $inv->agreement_id)->get();
            
            $discount_label = $inv->discount_label;
            $discount_type = $inv->discount_type;
            $discount_amount = $inv->discount_amount;
            $tax_percentage = $inv->tax_percentage;
            $discount_amount_type = $inv->discount_amount_type;
            $invoice_type= $inv->invoice_type;
        }
        return view('pages.commerce.invoice.items', compact('items','items1', 'calcul','discount_type','discount_amount','tax_percentage','discount_amount_type','discount_label','invoice_type'));
    }

    public function selectFundingsOptions($agreement_id)
    {
        $result = [];
        $rows = Funding::where('agreement_id', $agreement_id)->get();
        if (count($rows) > 0) {
            $DbHelperTools = new DbHelperTools();
            foreach ($rows as $f) {
                $funder_amount = $DbHelperTools->calculateAmountFunding($f->id);
                $pType = 'Montant fixe';
                if ($f->amount_type == 'percentage') {
                    $pType = 'Pourcentage de ' . $f->amount . '%';
                }
                $x = '(' . $pType . ' : ' . number_format($funder_amount, 2) . '€)';
                $result[] = ['id' => $f->entity->id, 'name' => ($f->entity->name . ' - ' . $f->entity->ref . ' - ' . $f->entity->entity_type) . ' ' . $x];
            }
        }
        return response()->json($result);
    }

    public function selectFunderContactsOptions($entity_id)
    {
        $result = [];
        /* $entity_id = 0;
        $rs = Funding::select('entitie_id')->where('id', $funding_id)->first();
        if (isset($rs)) {
            $entity_id = $rs->entitie_id;
        } */
        if ($entity_id > 0) {
            $rows = Contact::select('id', 'firstname', 'lastname')->where('entitie_id', $entity_id)->get();
            if (count($rows) > 0) {
                foreach ($rows as $en) {
                    $result[] = ['id' => $en['id'], 'name' => ($en['firstname'] . ' - ' . $en['lastname'])];
                }
            }
        }
        return response()->json($result);
    }

    public function createPdfInvoice($invoice_id, $render_type)
    {
        $invoice = null;
        if ($invoice_id > 0) {
            $invoice = Invoice::findOrFail($invoice_id);
            $dn = $invoice->bill_date ? $invoice->bill_date : $invoice->created_at;
            $dn = Carbon::createFromFormat('Y-m-d', $dn);
        }
        else{
            $dn = Carbon::now();
        }
        //HEADER
        $DbHelperTools = new DbHelperTools();
        $dm = Documentmodel::where('code', 'INVOICE')->first();
        $content = $dm->custom_content;
        $header = $dm->custom_header;
        $footer = $dm->custom_footer;
        $dtBillDate = Carbon::createFromFormat('Y-m-d', $invoice->bill_date);
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
            "{INVOICE_NOTE}",
            "{HTML_ITEMS}",
            "{COMPANY_WEBSITE}",
            "{COMPANY_DENOMINATION}",
            //bank
            "{company_bank_code}",
            "{company_bank_guichet}",
            "{company_bank_account_number}",
            "{company_bank_key_rib}",
            "{company_bank_iban}",
            "{company_bank_bic}",
        );
        $funder_infos_array=$DbHelperTools->getFunderByInvoice($invoice->id);
        //$entity_adresse = Adresse::where([['entitie_id', $invoice->entity_funder->id], ['is_main_entity_address', 1]])->first();
        //$INVOICE_WRITER_NAME = ($invoice->user_creator) ? $invoice->user_creator->name . ' ' . $invoice->user_creator->lastname : '';
        //$INVOICE_WRITER_EMAIL = ($invoice->user_creator) ? $invoice->user_creator->email : '';
        $INVOICE_WRITER_NAME ='Service Facturation';
        $INVOICE_WRITER_EMAIL =config('global.company_invoice_email');

        //$INVOICE_SIRET_CLIENT=($invoice->entity_funder->siret)?'N° SIRET :'.$invoice->entity_funder->siret:'';
        // dd($DbHelperTools->getHtmlInvoiceItems($invoice->id,1));
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
            $invoice->note,
            $DbHelperTools->getHtmlInvoiceItems($invoice->id,1),
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
        $pdfName = 'FACTURE-' . $invoice->number . '-' . time() . '.pdf';
        if ($render_type == 3 || $render_type == 4) {
            $temp = env('TEMP_PDF_FOLDER');
            if($render_type == 4){
                $pdfName = "FACTURE-" . $invoice->number.'.pdf';
            }
            $temp_directory = public_path() . "/" . $temp;
            $pathToStorage = $temp_directory . '/' . $pdfName;
            if (!File::isDirectory($temp_directory)) {
                File::makeDirectory($temp_directory, 0777, true, true);
            }
            $pdf->save($pathToStorage);
            return $pdfName;
        }
        return $pdf->download($pdfName);
    }


    public function formPayment($payment_id, $invoice_id)
    {
        $payment = null;
        $amount = 0;
        $invoice_type='cvts_ctrs';
        if ($invoice_id > 0) {
            $inv=Invoice::select('invoice_type')->find($invoice_id);
            $DbHelperTools = new DbHelperTools();
            $calcul = $DbHelperTools->getAmountsInvoice($invoice_id);
            $amount = $calcul['total'];
            if ($calcul['total_paid'] > 0) {
                $amount = $amount - $calcul['total_paid'];
            }
            $invoice_type=$inv->invoice_type;
        }
        if ($payment_id > 0) {
            $payment = Invoicepayment::find($payment_id);
        }
        return view('pages.commerce.invoice.form.payment', compact('payment', 'invoice_id', 'amount','invoice_type'));
    }

    public function storeFormPayment(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            $payment_date = Carbon::createFromFormat('d/m/Y', $request->payment_date);
            $data = array(
                'id' => $request->id,
                'amount' => $request->amount,
                'payment_date' => $payment_date,
                'payment_method' => $request->payment_method,
                'reference' => $request->reference,
                'note' => $request->note,
                'invoice_id' => $request->invoice_id,
                'funding_payment_id' => $request->funding_payment_id,
            );
            $payment_id = $DbHelperTools->manageInvoicePayment($data);
            if ($payment_id > 0) {
                $statusInvoice = $DbHelperTools->getStatusInvoice($request->invoice_id);
                Invoice::where('id', $request->invoice_id)->update(['status' => $statusInvoice]);
            }
            $success = true;
            $msg = 'Your payment has been saved successfully';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function selectFundingpaymentsOptions($invoice_id)
    {
        $result = $rows = [];
        $invoice = Invoice::select('id', 'agreement_id', 'entitie_funder_id', 'fundingpayment_id')->where('id', $invoice_id)->first();
        if ($invoice->entitie_funder_id > 0 && $invoice->agreement_id > 0) {
            $DbHelperTools = new DbHelperTools();
            $funding_id = 0;
            $rsF = Funding::select('id', 'amount_type', 'amount')->where([['entitie_id', $invoice->entitie_funder_id], ['agreement_id', $invoice->agreement_id]])->first();
            if (isset($rsF)) {
                /* $calcul=$DbHelperTools->getAmountsAgreement($invoice->agreement_id);
                $agreement_total_amount=$calcul['total'];
                $funding_id=$rsF->id;
                $funder_amount=$rsF->amount;
                if($rsF->amount_type=='percentage'){
                    $funder_amount=($agreement_total_amount*$rsF->amount)/100;
                } */
                $funding_id = $rsF->id;
                $funder_amount = $DbHelperTools->calculateAmountFunding($funding_id);
            }
            $rows = Fundingpayment::where('id', $invoice->fundingpayment_id)->orderBy('due_date')->get();
            //$rows = Fundingpayment::where('funding_id',$funding_id)->orderBy('due_date')->get();
        }
        if (count($rows) > 0) {
            foreach ($rows as $k => $item) {
                $k++;
                $item_amount = $item->amount;
                if ($item->amount_type == 'percentage') {
                    $item_amount = ($funder_amount * $item->amount) / 100;
                }
                $due_date = Carbon::createFromFormat('Y-m-d', $item->due_date);
                $text = 'Echéance ' . $k . ' - ' . $due_date->format('d-m-Y') . ' - ' . number_format($item_amount, 2) . ' €';
                $result[] = ['id' => $item->id, 'name' => $text];
            }
        }
        return response()->json($result);
    }

    public function selectDeadlinesOptions($entity_id,$agreement_id,$mode)
    {
        /* 
            $mode==1 ==> create
            $mode==2 ==> edit
        */
        $result = $rows = [];
        $DbHelperTools = new DbHelperTools();
        $funding=Funding::select('id')->where([['entitie_id',$entity_id],['agreement_id',$agreement_id]])->first();
        $funding_id=($funding)?$funding->id:0;
        
        $funder_amount = $DbHelperTools->calculateAmountFunding($funding_id);
        $rows = Fundingpayment::where('funding_id', $funding_id)->orderBy('due_date')->get();
        //dd($rows);
        if (count($rows) > 0) {
            foreach ($rows as $k => $item) {
                $can=true;
                if ($mode==1) {
                    $can = $DbHelperTools->checkIfCanInvoiceFundingPayment($item->id);
                }
                if ($can) {
                    $k++;
                    $item_amount = $item->amount;
                    if ($item->amount_type == 'percentage') {
                        $item_amount = ($funder_amount * $item->amount) / 100;
                    }
                    $due_date = Carbon::createFromFormat('Y-m-d', $item->due_date);
                    $text = 'Echéance ' . $k . ' - ' . $due_date->format('d-m-Y') . ' - ' . number_format($item_amount, 2) . ' €';
                    $result[] = ['id' => $item->id, 'name' => $text];
                }
            }
        }
        return response()->json($result);
    }

    public function getAmountFundingPayment($funding_payment_id)
    {
        $DbHelperTools = new DbHelperTools();
        $amount = $DbHelperTools->calculateAmountFundingPayment($funding_payment_id);
        return response()->json(['amount' => number_format($amount, 2, '.', '')]);
    }

    public function selectMailContactsOptions($entity_id)
    {
        $result = [];
        $rows = Contact::select('id', 'firstname', 'lastname', 'email')->where('entitie_id', $entity_id)->get();
        if (count($rows) > 0) {
            foreach ($rows as $en) {
                $result[] = ['id' => $en['id'], 'name' => ($en['firstname'] . ' ' . $en['lastname'] . ' - ' . $en['email'])];
            }
        }
        return response()->json($result);
    }

    public function formInvoiceFromAgreement($agreement_id)
    {
        $row = null;
        $DbHelperTools = new DbHelperTools();
        $htmlFinance = $DbHelperTools->getHtmlInvoiceFromAgreement($agreement_id);
        return view('pages.commerce.agreement.form.invoice-from-agreement', compact('agreement_id', 'htmlFinance'));
    }

    public function storeFormInvoiceFromAgreement(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            $agreement_id = $request->agreement_id;
            $dtNow = Carbon::now();
            if ($agreement_id > 0) {
                //$bill_date = $dtNow->format('Y/m/d');
                $bill_date = $dtNow;
                $newDateTime = Carbon::now()->addMonth();
                $due_date = $newDateTime->format('Y/m/d');
                $rsAgreement = Agreement::select('af_id', 'entitie_id', 'contact_id')->where('id', $agreement_id)->first();
                $entitie_id = $rsAgreement->entitie_id;
                $contact_id = $rsAgreement->contact_id;
                $af_id = $rsAgreement->af_id;
                $af = Action::select('id', 'title')->where('id', $af_id)->first();
                $IDS_FUNDING_PAYMENTS = ($request->has('IDS_FUNDING_PAYMENTS'))?$request->IDS_FUNDING_PAYMENTS:[];
                if (count($IDS_FUNDING_PAYMENTS) > 0) {
                    $rsFp = Fundingpayment::whereIn('id', $IDS_FUNDING_PAYMENTS)->get();
                    if ($rsFp) {
                        foreach ($rsFp as $fp) {
                            if ($fp->id > 0) {
                                $fundingpayment_amount = $DbHelperTools->calculateAmountFundingPayment($fp->id);
                                if($fundingpayment_amount>0){
                                    $rsContact = Contact::select('id','entitie_id')->where('entitie_id', $fp->funding->entitie_id)->first();

                                    $is_cfa=($fp->funding && $fp->funding->is_cfa==1)?$fp->funding->is_cfa:0;
                                    /* 
                                    si le financeur = le contact alors “contact_itself”
                                    sinon , si le financeur = l’entité du contact, alors “Entity_contat”
                                    si is_cfa de la convention ou contrat = 1 alors cfa_funder
                                    sinon “other_funders” 
                                    */
                                    $funding_option='other_funders';
                                    if($fp->funding){
                                        if($is_cfa==1){
                                            $funding_option='cfa_funder';
                                        }elseif($fp->funding->id==$rsContact->entitie_id){//si le financeur = l’entité du contact, alors “Entity_contat”
                                            $funding_option='entity_contact';
                                        }elseif($fp->funding->id==$entitie_id){//si le financeur = le contact alors “contact_itself”
                                            $funding_option='contact_itself';
                                        }
                                    }
                                    $data = array(
                                        'id' => 0,
                                        'number' => $DbHelperTools->generateInvoiceNumberByBillDate('INVOICE',$bill_date),
                                        'bill_date' => $bill_date,
                                        'due_date' => $due_date,
                                        'note' => '',
                                        'tax_percentage' => null,
                                        'discount_amount' => null,
                                        'discount_amount_type' => null,
                                        'discount_type' => null,
                                        'status' => 'draft',
                                        'invoice_type' => 'cvts_ctrs',
                                        'funding_option' => $funding_option,
                                        'cancelled_at' => null,
                                        'cancelled_by' => null,
                                        'created_by' => Auth::user()->id,
                                        'entitie_id' => $entitie_id,
                                        'contact_id' => $contact_id,
                                        'agreement_id' => $agreement_id,
                                        'entitie_funder_id' => $fp->funding->entitie_id,
                                        'contact_funder_id' => $rsContact->id,
                                        'fundingpayment_id' => $fp->id,
                                        'af_id' => $af_id,
                                    );
                                    $invoice_id = $DbHelperTools->manageInvoice($data);
                                    if ($invoice_id > 0) {
                                            $data = array(
                                                "id" => 0,
                                                "title" => $af->title,
                                                "description" => $DbHelperTools->getItemDescription($af_id),
                                                "quantity" => 1,
                                                "unit_type" => '',
                                                "rate" => $fundingpayment_amount,
                                                "total" => $fundingpayment_amount,
                                                "sort" => 1,
                                                "fundingpayment_id" => $fp->id,
                                                "invoice_id" => $invoice_id
                                            );
                                            $item_id = $DbHelperTools->manageInvoiceItem($data);
                                            //Lors de la génération de facture sur une AF, passer la convention/contrat correspondante à l'état signé
                                            Agreement::where('id', $agreement_id)->update(['status' => 'signed']);
                                    }
                                }
                            }
                        }
                    }
                    $success = true;
                    $msg = 'les factures ont étés crées avec succès';
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    public function formMailEstimate($estimate_id)
    {

        $row = null;
        $content = $subject = '';
        if ($estimate_id > 0) {
             
            $email_model_id = Emailmodel::select('id')->where('code','ENVOI_DEVIS')->pluck('id');
            $emailmodel = Emailmodel::findOrFail(intval($email_model_id[0])); 
        
            $subject = strip_tags($emailmodel->custom_header);
            $content = $emailmodel->custom_content;

            $row = Estimate::findOrFail($estimate_id);
            $Estimate_type = $row->Estimate_type;
            $email=auth()->user()->email;
        }
        return view('pages.commerce.estimate.form.mail', compact('row', 'content', 'subject', 'email'));
    }

    public function storeFormMailEstimate(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        //dd($request->all());
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $estimate = Estimate::findOrFail($request->id);
            $to = '';
            if ($request->contact_id) {
                $contact = Contact::find($request->contact_id);
            }
            if (isset($contact) && (
                ($to = $contact->email) 
                || ($contact->entitie && ($to = $contact->entitie->email))
            )) {
                $subject = $request->subject;
                $arrayCc = $arrayBcc = [$user=auth()->user()->email];
                $strCc = $strBcc = '';
                if (isset($request->cc) && !empty($request->cc)) {
                    $strCc = implode(',', array_column(json_decode($request->cc), 'value'));
                }
                if (isset($request->bcc) && !empty($request->bcc)) {
                    $strBcc = implode(',', array_column(json_decode($request->bcc), 'value'));
                }
                if (isset($strCc) && !empty($strCc)) {
                    $arrayCc = explode(',', $strCc);
                }
                if (isset($strBcc) && !empty($strBcc)) {
                    $arrayBcc = explode(',', $strBcc);
                }
                $myEmail = Mail::to($to);
                if (count($arrayCc))
                    $myEmail->cc($arrayCc);

                if (count($arrayBcc))
                    $myEmail->bcc($arrayBcc);

                //content
                $content = $request->content;
                $document_type = 'DEVIS_FORMATION_PROFESSIONNELLE';
                $this->createPdfEstimate($estimate->id, 3);
                $entityname = preg_replace('/\s+/', '', $estimate->entity->name);
                $attachmentFileName = 'DEVIS' . $estimate->estimate_number . '-' . $entityname;
                $temp = env('TEMP_PDF_FOLDER');
                $attachmentFile = public_path() . '/' . $temp . '/' . $attachmentFileName;
                $attachmentFileName = $document_type.'_' . $estimate->number . '.pdf';
                try {
                    $from = [['address' => Auth::user()->email, 'name' => Auth::user()->name . ' ' . Auth::user()->lastname]]; 
                    $e = $myEmail->send(new EstimateMail($estimate, $subject, $attachmentFileName, $attachmentFile, $content, $from));
                    if (File::exists($attachmentFile)) File::delete($attachmentFile);
                    $success = true;
                    $msg = 'Le mail a été envoyée avec succès';
                } catch (Exception $e) {
                    dd($e);
                    $msg = 'Erreur lors d\'envoi du mail.';
                }
            } else {
                $msg = 'Aucun mail renseigné.';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg
        ]);
    }

    public function formMailAgreement($agreement_id)
    {
        $row = null;
        $content = $subject = '';
        $user=auth()->user()->email;
        if ($agreement_id > 0) {
            $row = Agreement::findOrFail($agreement_id);
            $agreement_type = $row->agreement_type;
            if ($row->agreement_type == 'convention') {
                $email_model_id = Emailmodel::select('id')->where('code','ENVOI_CONVENTION')->pluck('id');
                $emailmodel = Emailmodel::findOrFail(intval($email_model_id[0])); 
                $subject = strip_tags($emailmodel->custom_header);
                $content = $emailmodel->custom_content;
                $agreement_type = $row->agreement_type;
            } elseif ($row->agreement_type == 'contract') {
                $email_model_id = Emailmodel::select('id')->where('code','ENVOI_CONTRAT_FORMATEUR')->pluck('id');
                $emailmodel = Emailmodel::findOrFail(intval($email_model_id[0])); 
                $subject = strip_tags($emailmodel->custom_header);
                $content = $emailmodel->custom_content;
                $agreement_type = $row->agreement_type;
            }
        }

        return view('pages.commerce.agreement.form.mail', compact('row', 'content', 'agreement_type', 'subject','user'));
    }
    //formStudentsInvoices
    public function formStudentsInvoices($af_id)
    {
        /* acounting & analytical codes heritage : AF */
        $herited_codes = Action::whereRaw('accounting_code is not null or analytical_code is not null')
        ->get(['id', 'accounting_code', 'analytical_code'])
        ->toArray()
        ;
        $h_account_codes = array_column($herited_codes, 'accounting_code', 'id');
        $h_analytic_codes = array_column($herited_codes, 'analytical_code', 'id');

        $params = Param::select('id', 'code', 'name','accounting_code','analytical_code','amount')->where([['param_code', 'INVOICE_ITEMS_TYPES'], ['is_active', 1]])->get();
        return view('pages.commerce.invoice.form.students-invoices', compact('af_id','params', 'h_account_codes', 'h_analytic_codes'));
    }
    public function storeFormStudentsInvoices(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        $invoice_id = $af_id = $entity_id = 0;
        //dd($request->all());
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            if($request->has('contacts_ids')){
                $bill_date = Carbon::createFromFormat('d/m/Y', $request->bill_date);
                $due_date = Carbon::createFromFormat('d/m/Y', $request->due_date);
                $due_date_funder = Carbon::createFromFormat('d/m/Y', $request->due_date_funder);
                $af_id = $request->af_id;
                $contacts=$request->contacts_ids;
                if(count($contacts)>0){
                    foreach($contacts as $contact_id){
                        $ct=Contact::select('entitie_id')->where('id',$contact_id)->first();    
                        $data = array(
                            'id' => 0,
                            'number' => $DbHelperTools->generateInvoiceNumberByBillDate('INVOICE',$bill_date),
                            'bill_date' => $bill_date,
                            'due_date' => $due_date,
                            'note' => $request->note,
                            'accounting_code' => $request->accounting_code,
                            'analytical_code' => $request->analytical_code,
                            'collective_code' => $request->collective_code,
                            'invoice_type' => 'students',
                            'tax_percentage' => $request->tax_percentage,
                            'discount_label' => $request->discount_label,
                            'discount_amount' => $request->discount_amount,
                            'discount_amount_type' => $request->discount_amount_type,
                            'discount_type' => "before_tax",
                            'status' => 'draft',
                            'cancelled_at' => null,
                            'cancelled_by' => null,
                            'created_by' => Auth::user()->id,
                            'entitie_id' => $ct->entitie_id,
                            'contact_id' => $contact_id,
                            'agreement_id' => null,
                            'funding_option' => $request->funding_option,
                            'entitie_funder_id' => ($request->entitie_funder_id)?$request->entitie_funder_id:null,
                            'contact_funder_id' => ($request->contact_funder_id)?$request->contact_funder_id:null,
                            'fundingpayment_id' => null,
                            'af_id' => $af_id,
                        );
                        //dd($data);
                        $invoice_id = $DbHelperTools->manageInvoice($data);
                        if ($invoice_id > 0) {
                            if ($request->items > 0) {
                                foreach($request->items as $i){
                                    if(isset($i['active']) && $i['active']==1){
                                        $data = array(
                                            "id" => 0,
                                            "title" => $i['title'],
                                            "description" => $i['description'],
                                            "accounting_code" => $i['accounting_code'],
                                            "analytical_code" => $i['analytical_code'],
                                            "quantity" => 1,
                                            "unit_type" => '',
                                            "rate" => $i['rate'],
                                            "total" => $i['rate'],
                                            "sort" => 1,
                                            "fundingpayment_id" => null,
                                            "invoice_id" => $invoice_id
                                        );
                                        if($i['rate']>0)
                                            $item_id = $DbHelperTools->manageInvoiceItem($data);
                                    }
                                }
                                //si le code comptable au niveau global n'est' pas renseigner, enregistrer celui du 1er élément de facturation sur la facture
                                if(!$request->accounting_code){
                                    if (array_key_exists(1, $request->items)) {
                                        Invoice::where('id', $invoice_id)->update([
                                            'accounting_code' => $request->items[1]['accounting_code'],
                                            'analytical_code' => $request->items[1]['analytical_code']
                                        ]);
                                    }
                                }
                            }
                            if($request->entitie_funder_id){
                                //Insérer un financeur par defaut 100%
                                $data = array(
                                    "id" => 0,
                                    "amount_type" => 'percentage',
                                    "amount" => 100,
                                    "status" => 'created',
                                    "agreement_id" => null,
                                    "entitie_id" => $request->entitie_funder_id,
                                    "invoice_id" => $invoice_id,
                                    "is_cfa" => 0,
                                );
                                $funding_id = $DbHelperTools->manageFunding($data);
                                if($funding_id>0){
                                    $data = array(
                                        "id" => 0,
                                        "amount_type" => 'percentage',
                                        "amount" => 100,
                                        "due_date" => $due_date_funder,
                                        "payment_date" => null,
                                        "funding_id" => $funding_id,
                                    );
                                    $row_id = $DbHelperTools->manageFundingPayment($data);
                                }
                            }

                        }
                    }
                }
                $success = true;
                $msg = 'Le données ont étés enregistrées avec succès';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'af_id' => $af_id,
        ]);
    }

    /* Download SAGE files : kown by token */
    public function downloadSageFile(Request $request) {
        return Storage::download($request->sageFile);
    }

    /* Generate PNM */
    public function generatePnm(Request $request) {
        if (!$request->ajax()) {
            die('Not authorized!');
        }

        $DbHelperTools = new DbHelperTools();
        $ignoreErrors = true;
        $inv_param = $request->invoices;
        $qb = DB::table('inv_invoices')
        ->select([
            'inv_invoices.id',
            'inv_refunds.id as id_refund',
            'inv_invoices.bill_date',
            'inv_refunds.invoice_id',
            'inv_refunds.refund_date',
            'pf_formations.accounting_code as pf_accounting_code',
            'pf_formations.analytical_codes as pf_analytical_code',
            'af_actions.accounting_code as af_accounting_code',
            'af_actions.analytical_code as af_analytical_code',
            'inv_invoices.accounting_code as inv_accounting_code',
            'inv_invoices.analytical_code as inv_analytical_code',
            'inv_invoices.number as code_facture',
            'inv_refunds.number as code_avoir',
            'inv_invoices.due_date',
            'inv_invoices.discount_amount',
            'inv_invoices.discount_amount_type',
            'inv_invoices.is_synced_to_sage as inv_synced_sage',
            'inv_refunds.is_synched_to_sage as refund_synced_sage',
            'en_entities.name as nom_client',
            'en_entities.collective_customer_account',
            'en_entities.auxiliary_customer_account',
            'en_entities.entity_type',
            'en_entities.is_synced_to_sage as en_synced_sage',
            'en_entities.id as en_id',
            'en_entities.ref as en_ref',
            'en_funder.name as nom_funder',
            'en_funder.collective_customer_account as collective_funder_account',
            'en_funder.auxiliary_customer_account as auxiliary_funder_account',
            'en_funder.entity_type as funder_type',
            'en_funder.is_synced_to_sage as funder_synced_sage',
            'en_funder.id as funder_id',
            'en_funder.ref as funder_ref',
            DB::raw('SUM(inv_invoice_items.total) as montant_ht'),
        ])
        ->leftjoin('inv_refunds', 'inv_refunds.invoice_id', '=', 'inv_invoices.id')
        ->leftjoin('inv_invoice_items', 'inv_invoice_items.invoice_id', '=', 'inv_invoices.id')
        ->leftjoin('en_entities', 'en_entities.id', '=', 'inv_invoices.entitie_id')
        ->leftjoin('en_entities as en_funder', 'en_funder.id', '=', 'inv_invoices.entitie_funder_id')
        ->leftjoin('af_actions', 'af_actions.id', '=', 'inv_invoices.af_id')
        ->leftjoin('pf_formations', 'pf_formations.id', '=', 'af_actions.formation_id')
        // ->where('inv_invoices.is_synced_to_sage', '0')
        ->groupby('inv_invoices.id')
        ->orderBy('inv_invoices.bill_date', 'asc')
        ->orderBy('inv_invoices.number', 'asc')
        ;
        if (!empty($inv_param)) {
            if (preg_match("/^([0-9]+\,)*[0-9]+$/", $inv_param) == 0) {
                return response()->json(['success' => false, 'message' => 'Erreur dans les paramètres', 'facture' => false]);
            }
            $ignoreErrors = false;
            $qb->whereIn('inv_invoices.id', explode(',', $inv_param));
        }
        $invoices = $qb->get();

        $generated = array();
        $generated_refunds = array();
        $pnm_contents = ["0" => str_pad('0', 105) . PHP_EOL];

        $entities = array();

        foreach ($invoices as $index => $inv) {
            $code_comptable = $inv->inv_accounting_code ?? $inv->af_accounting_code ?? $inv->pf_accounting_code;
            $code_analytique = $inv->inv_analytical_code ?? $inv->af_analytical_code ?? $inv->pf_analytical_code;
            $error = array();
            $pnm_content = '';

            /* client/funder columns */
            $nom_client = $inv->nom_funder ?? $inv->nom_client;
            $collective_customer_account = $inv->collective_funder_account ?? $inv->collective_customer_account;
            $auxiliary_customer_account = $inv->auxiliary_funder_account ?? $inv->auxiliary_customer_account;
            $en_synced_sage = $inv->funder_synced_sage ?? $inv->en_synced_sage;
            $en_id = $inv->funder_id ?? $inv->en_id;
            $en_ref = $inv->funder_ref ?? $inv->en_ref;

            /* Traitement saut de numéro */
            if ($index > 0) {
                $prev_index = $index - 1;
                $inv_num = (int) $DbHelperTools->extractInvoiceNum($inv);
                $inv_prev_num = (int) $DbHelperTools->extractInvoiceNum($invoices[$prev_index]);

                if(($inv_num - $inv_prev_num) > 1) {
                    break;
                }
            }
            
            if ($inv->inv_synced_sage && $inv->refund_synced_sage) {
                if ($ignoreErrors) {
                    continue;
                }
                return response()->json(['success' => false, 'message' => false, 'facture' => $inv->code_facture, 'client' => $en_ref, 'already_sync' => true]);
            }

            if (empty($code_comptable)) {
                $error[] = "Code comptable";
            }
            if (empty($code_analytique)) {
                $error[] = "Code analytique";
            }
            if (empty($collective_customer_account)) {
                $error[] = "Code collectif ".($inv->funder_id ? "financeur" : "client");
            }
            if (empty($auxiliary_customer_account)) {
                $error[] = "Code auxiliaire ".($inv->funder_id ? "financeur" : "client");
            }
            if (!empty($error)) {
                Invoice::where('id', $inv->id)->update(['sage_errors' => json_encode($error)]);
                if ($ignoreErrors) {
                    continue;
                }
                return response()->json(['success' => false, 'message' => $error, 'facture' => $inv->code_facture, 'client' => $en_ref]);
            }
                        
            if (!$en_synced_sage && !in_array($en_id, $entities)) {
                $entities[$inv->id] = $en_id;
            }

            $inv_types = $inv->code_avoir ? 2 : 1;

            foreach (range(1, $inv_types) as $line_number) {
                /* 
                    ($line_number = 1) => invoice
                    ($line_number = 2) => refund
                */

                if ($line_number == 1 && $inv->inv_synced_sage) {
                    continue;
                }

                $is_refund = $line_number == 2;
                foreach (['X', ' ', 'A'] as $x_a) {
                    $pnm_content .= 'VEN';
                    $inv_date = $is_refund ? $inv->refund_date : $inv->bill_date;
                    $pnm_content .= !empty($inv_date) ? Carbon::createFromFormat('Y-m-d', $inv_date)->format('dmy') : '000000';
                    /* Type de pièce */
                    $pnm_content .= $is_refund ? 'AC' : 'FC';
                    /* Compte général */
                    switch ($x_a) {
                        case 'X':
                            $account_client = $collective_customer_account;
                            break;
                        default:
                            $account_client = $code_comptable;
                            break;
                    }
                    $account_gen = str_pad($account_client, 13);
                    $pnm_content .= substr($account_gen, 0, 13);
                    /* Type de compte */
                    $pnm_content .= $x_a;
                    /* Compte auxiliaire / analytique */
                    switch ($x_a) {
                        case 'X':
                            $account_a_client = $auxiliary_customer_account ?? $DbHelperTools->generateAuxiliaryAccountForEntity($inv->funder_id ?? $inv->en_id);
                            break;
                        case 'A':
                            $account_a_client = $code_analytique;
                            break;
                        default:
                            $account_a_client = ' ';
                            break;
                    }
                    $account_a_client = str_pad($account_a_client, 13);
                    $pnm_content .= substr($account_a_client, 0, 13);
                    /* N Facture/Avoir */
                    $invoice_number = $is_refund ? $inv->code_avoir : $inv->code_facture;
                    $invoice_number = str_replace('-', '', $invoice_number);
                    if (!$is_refund) {
                        $invoice_number = substr($invoice_number, 1);
                    }
                    $invoice_number_str = str_pad($invoice_number, 13);
                    $pnm_content .= substr($invoice_number_str, 0, 13);
                    /* Libellé pièce = n_inv + nom_client */
                    $nom_client = $DbHelperTools->removeAccentsAndSpecial($nom_client);       
                    $libelle_piece = "{$invoice_number} - {$inv->nom_client}";
                    $libelle_piece = str_pad($libelle_piece, 25);
                    $pnm_content .= substr($libelle_piece, 0, 25);
                    /* Mode Paiement + Echeance */
                    $pnm_content .= 'S';
                    $pnm_content .= Carbon::createFromFormat('Y-m-d', $inv->due_date)->format('dmy');
                    /* Sens */
                    switch ($x_a) {
                        case 'X':
                            $d_c = $is_refund ? 'C' : 'D';
                            break;
                        default:
                            $d_c = $is_refund ? 'D' : 'C';
                            break;
                    }
                    $pnm_content .= $d_c;
                    /* Remise */
                    $discount_amount = $inv->discount_amount;
                    $discount_type = $inv->discount_amount_type;
                    $discount = 0;
                    if ($discount_amount && $discount_type) {
                        switch ($discount_type) {
                            case 'fixed_amount': 
                                $discount = $inv->discount_amount;
                                break;
                            case 'percentage': 
                                $discount = $inv->montant_ht * $discount_amount / 100;
                                break;
                        }
                    }
                    /* Montant HT */
                    $montant_ht = number_format($inv->montant_ht - $discount, 2, '.', '');
                    $montant_ht = str_pad($montant_ht, 20, ' ', STR_PAD_LEFT);
                    $pnm_content .= $montant_ht;
                    /* FIN */
                    $pnm_content .= 'N';
                    $pnm_content .= PHP_EOL;
                }
            }
            
            $generated[] = $inv->id;
            if ($inv->id_refund) {
                $generated_refunds[] = $inv->id_refund;
            }
            $pnm_contents[$inv->id] = $pnm_content;
        }
        
        $tokenPNC = false;
        if (!empty($entities)) {
            $entities_set = array_values($entities);
            $entities_set = array_unique($entities_set);
            $request->request->add(['entities' => implode(',', $entities_set) ]);
            $resp_pnc = $this->generatePnc($request, true, $ignoreErrors)->getData(true);
            if (!$resp_pnc['success']) {
                if (!$ignoreErrors) {
                    $resp_pnc['facture'] = $inv->code_facture;
                    return response()->json($resp_pnc);
                } else {
                    $filter_by_en = function($i) use($resp_pnc) {
                        return $i !== $resp_pnc['en_id'];
                    };
                    $entities = array_filter($entities, $filter_by_en);
                    $entities_inv = array_keys($entities);
                    $filter_by_inv = function($i) use($entities_inv) {
                        return in_array($i, $entities_inv);
                    };
                    $generated = array_filter($generated, $filter_by_inv);
                    $pnm_contents = array_filter($pnm_contents, $filter_by_inv, ARRAY_FILTER_USE_KEY);
                }
            } else {
                $tokenPNC = $resp_pnc['file'];
            }
        }

        Invoice::whereIn('id', $generated)->update(['is_synced_to_sage' => 1, 'sage_errors' => null]);

        if (!empty($generated_refunds)) {
            Refund::whereIn('id', $generated_refunds)->update(['is_synched_to_sage' => 1]);
        }

        $tokenPNM = uniqid('CRFPE_');
        $fileName = "$tokenPNM.PNM";

        Storage::makeDirectory('sage');
        $pnm_contents = implode('', $pnm_contents);
        Storage::disk('sage')->put($fileName, $pnm_contents);

        return response()->json(['success' => true, 'files' => ['PNM' => $tokenPNM, 'PNC' => $tokenPNC]]);
    }

    /* Generate PNC */
    public function generatePnc(Request $request, $ignore_already_sync = false, $ignore_errors_pnm = false) {
        if (!$request->ajax()) {
            die('Not authorized!');
        }

        $ignore_errors = true;

        $DbHelperTools = new DbHelperTools();
        $compte_collectif_frs = '401'; /* Code gen Frs */
        $entities_param = $request->entities;
        $qb = DB::table('en_entities')
        ->select([
            'en_entities.id as en_id',
            'en_entities.ref',
            'en_entities.name as nom_client',
            'en_entities.entity_type',
            'en_entities.is_client',
            'en_entities.is_funder',
            'en_entities.is_former',
            'en_entities.is_prospect',
            'en_entities.collective_customer_account',
            'en_entities.auxiliary_customer_account',
            'en_entities.is_prospect',
            'en_adresses.line_1',
            'en_adresses.line_2',
            'en_adresses.postal_code',
            'en_adresses.city',
            'en_entities.pro_phone',
            'en_contacts.type_former_intervention',
            'en_entities.is_synced_to_sage as en_synced_sage',
        ])
        ->leftjoin('en_contacts', 'en_contacts.entitie_id', '=', 'en_entities.id')
        ->leftjoin('en_adresses', function ($join) {
            $join->on('en_adresses.entitie_id', '=', 'en_entities.id')
            ->orOn('en_adresses.contact_id', '=', 'en_contacts.id')
            ;
        })
        // ->where('en_entities.is_synced_to_sage', '0')
        ->groupBy('en_entities.id')
        ->orderBy('en_entities.name', 'asc')
        ;

        if (!empty($entities_param)) {
            if (preg_match("/^([0-9]+\,)*[0-9]+$/", $entities_param) == 0) {
                return response()->json(['success' => false, 'message' => 'Erreur dans les paramètres', 'client' => false]);
            }
            $ignore_errors = false || $ignore_errors_pnm;
            $qb->whereIn('en_entities.id', explode(',', $entities_param));
        }

        $entities = $qb->get();

        $generated = array();

        $pnm_content = str_pad('A', 141) . PHP_EOL;
        foreach ($entities as $index => $entity) {
            $ent_types = array();

            if ($entity->en_synced_sage) {
                if ($ignore_already_sync || $ignore_errors) {
                    continue;
                }
                return response()->json(['success' => false, 'message' => false, 'client' => $entity->ref, 'already_sync' => true, 'en_id' => $entity->en_id]);
            }

            if (
                $entity->is_client
                || $entity->is_funder
                || $entity->is_prospect
            ) {
                $ent_types[] = "C";
            }
            
            if (
                $entity->is_former
                && $entity->type_former_intervention == "Sur facture"
            ) {
                $ent_types[] = "F";
            }

            $error = array();
            if (empty($entity->auxiliary_customer_account)) {
                $error[] = "Code auxiliaire ".($entity->is_funder ? "financeur" : "client");
            }
            if (empty($entity->collective_customer_account)) {
                $error[] = "Code collectif ".($entity->is_funder ? "financeur" : "client");
            }
            if (!empty($error)) {
                Entitie::where('id', $entity->en_id)->update(['sage_errors' => json_encode($error)]);
                if (!in_array('F', $ent_types)) {
                    if ($ignore_errors) {
                        continue;
                    }
                    return response()->json(['success' => false, 'message' => $error, 'client' => $entity->ref, 'en_id' => $entity->en_id]);
                }
            }

            foreach ($ent_types as $type) {
                if ($type == 'C' && empty($entity->collective_customer_account)) {
                    if ($ignore_errors) {
                        continue;
                    }
                    return response()->json(['success' => false, 'message' => ['Code collectif'], 'client' => $entity->ref, 'en_id' => $entity->en_id]);
                }

                $pnm_content .= 'X';
                /* Code tiers */
                $pnm_content .= str_pad($entity->auxiliary_customer_account ?? $DbHelperTools->generateAuxiliaryAccountForEntity($entity->en_id), 13);
                /* Nom */
                $nom_client = $DbHelperTools->removeAccentsAndSpecial($entity->nom_client);
                $nom_client = str_pad($nom_client, 31);
                $pnm_content .= substr($nom_client, 0, 31);
                /* Type */
                $pnm_content .= $type;
                /* NS+NS (length = 1+2) */
                $pnm_content .= '   ';
                /* Compte Collectif */
                $cmpt_collectif = $type == 'C' ? str_pad($entity->collective_customer_account, 13) : $compte_collectif_frs;
                $pnm_content .= substr($cmpt_collectif, 0, 13);
                /* Adr1 */
                $line_1_adr = $DbHelperTools->removeAccentsAndSpecial($entity->line_1);
                $line_1_adr = str_pad($line_1_adr, 21);
                $pnm_content .= substr($line_1_adr, 0, 21);
                /* Adr1: complément */
                $line_2_adr = $DbHelperTools->removeAccentsAndSpecial($entity->line_2);
                $line_2_adr = str_pad($line_2_adr, 21);
                $pnm_content .= substr($line_2_adr, 0, 21);
                /* Code postal */
                $postal_code = str_pad($entity->postal_code, 5, '0', STR_PAD_LEFT);
                $postal_code = str_pad($postal_code, 6, ' ', STR_PAD_LEFT);
                $pnm_content .= substr($postal_code, 0, 6);
                /* Ville */
                $city = $DbHelperTools->removeAccentsAndSpecial($entity->city);
                $city = str_pad($city, 21);
                $pnm_content .= substr($city, 0, 21);
                /* Tel */
                $pro_phone = preg_replace('/\D/', '', $entity->pro_phone);
                $pro_phone = str_pad($pro_phone, 15);
                $pnm_content .= substr($pro_phone, 0, 15);
                /* Fin */
                $pnm_content .= PHP_EOL;

                $generated[] = $entity->en_id;
            }
        }
        
        Entitie::whereIn('id', $generated)->update(['is_synced_to_sage' => 1, 'sage_errors' => null]);

        $token = uniqid('CRFPE_');
        $fileName = "$token.PNC";

        Storage::makeDirectory('sage');
        Storage::disk('sage')->put($fileName, $pnm_content);

        return response()->json(['success' => true, 'file' => $token]);
    }

    public function getStudentsContactsByAfForSelect($af_id){
        $result=[];
        if($af_id>0){
            $rows=DB::table('af_members')
                ->join('af_enrollments', 'af_enrollments.id', '=', 'af_members.enrollment_id')
                ->join('en_contacts', 'en_contacts.id', '=', 'af_members.contact_id')
                ->select('af_members.id','af_members.contact_id','af_enrollments.enrollment_type','af_enrollments.af_id','en_contacts.firstname','en_contacts.lastname')
                ->where([['af_enrollments.af_id', $af_id],['af_enrollments.enrollment_type', 'S']])->where('af_members.contact_id','>',0)
                ->get()->unique();
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $result[] = ['id' => $row->contact_id, 'name' => ($row->firstname . '  ' . $row->lastname)];
                }
            }
        }
        return response()->json($result);
    }
    public function selectListFundingsOptions()
    {
        $result = [];
        $rows = Entitie::select('id','ref','entity_type','name')->where([['is_funder',1],['is_active',1]])->get();
        if (count($rows) > 0) {
            foreach ($rows as $f) {
                $result[] = ['id' => $f->id, 'name' => ($f->name . ' - ' . $f->ref . ' - ' . $f->entity_type)];
            }
        }
        return response()->json($result);
    }
    public function selectFunderListContactsOptions($entity_id)
    {
        $result = [];
        if ($entity_id > 0) {
            $rows = Contact::select('id', 'firstname', 'lastname')->where('entitie_id', $entity_id)->get();
            if (count($rows) > 0) {
                foreach ($rows as $en) {
                    $result[] = ['id' => $en['id'], 'name' => ($en['firstname'] . ' - ' . $en['lastname'])];
                }
            }
        }
        return response()->json($result);
    }

    public function formInvoiceItem($item_id, $invoice_id)
    {
        $row = null;
        $params = Param::select('id', 'code', 'name','accounting_code','amount')->where([['param_code', 'INVOICE_ITEMS_TYPES'], ['is_active', 1]])->get();
        if ($item_id > 0) {
            $row = Invoiceitem::findOrFail($item_id);
        }
        return view('pages.commerce.invoice.form.item', compact('row', 'invoice_id','params'));
    }

    public function storeFormInvoiceItem(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            if($request->invoice_type=='cvts_ctrs'){
                $row = Invoiceitem::find($request->id);
                $row->description = (isset($request->description)) ? $request->description : null;
                $row->save();
            }else{
                $data = array(
                    "id" => $request->id,
                    "title" => $request->title,
                    "description" => $request->description,
                    "accounting_code" => $request->accounting_code,
                    "analytical_code" => $request->analytical_code,
                    "quantity" => 1,
                    "unit_type" => '',
                    "rate" => $request->rate,
                    "total" => $request->rate,
                    "sort" => 1,
                    "fundingpayment_id" => null,
                    "invoice_id" => $request->invoice_id
                );
                $item_id = $DbHelperTools->manageInvoiceItem($data);
            }
            $success = true;
            $msg = 'Element a été enregistrée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    public function deleteItemInvoice(Request $request)
    {
        $success = false;
        if ($request->isMethod('delete')) {
            if ($request->has('item_id')) {
                $item_id = $request->item_id;
                $DbHelperTools = new DbHelperTools();
                if ($item_id > 0) {
                    $deletedRows = $DbHelperTools->massDeletes([$item_id], 'invoiceitem', 1);
                    if ($deletedRows)
                        $success = true;
                }
            }
        }
        return response()->json(['success' => $success]);
    }
    public function formParamToItem($i)
    {
        $params = Param::select('id', 'code', 'name','accounting_code','analytical_code','amount')->where([['param_code', 'INVOICE_ITEMS_TYPES'], ['is_active', 1]])->get();
        return view('pages.commerce.invoice.form.param-to-item', compact('params','i'));
    }
    public function formRefund($refund_id, $invoice_id)
    {
        $invoice = null;
        if ($invoice_id > 0) {
            $invoice = Invoice::find($invoice_id);
        }
        return view('pages.commerce.invoice.form.refund', compact('refund_id', 'invoice'));
    }
    public function storeFormRefund(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            //dd($request->all());
            $refund_date = Carbon::createFromFormat('d/m/Y', $request->refund_date);
            $attributes= Carbon::createFromFormat('d/m/Y', $request->refund_date)->format('Y-m-d');
            $inv=Invoice::where('id', $request->invoice_id)->first();
            $bill_date = Carbon::createFromFormat('Y-m-d', $attributes);
            $data = array(
                'id' => $request->id,
                'number' => ($request->id == 0) ? $DbHelperTools->generateRefundNumberByBillDate('REFUND',$bill_date) : null,
                'refund_date' => $refund_date,
                'reason' => $request->reason,
                'invoice_id' => $request->invoice_id,
            );
            $refund_id = $DbHelperTools->manageRefund($data);
            if ($refund_id > 0) {
                //'draft','not_paid','partial_paid','paid','cancelled'
                //$statusInvoice = $DbHelperTools->getStatusInvoice($request->invoice_id);
                Invoice::where('id', $request->invoice_id)->update(['status' => 'cancelled']);
            }
            $success = true;
            $msg = 'Votre avoir facture a été crée avec succès';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    //sdtCertificates
    public function sdtCertificates(Request $request, $af_id, $type = 'employer')
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Certificate::where('type', $type)->latest();
        if ($request->isMethod('post')) {
            
        }
        if ($af_id > 0) {
            $datas = $datas->where('af_id', $af_id)->latest();
        }
        $udatas = $datas->orderByDesc('id')->get();
        $arrayLabel = [
            'draft' => 'Brouillon',
            'signed' => 'Signé',
            'canceled' => 'Annulé',
        ];
        $arrayCssLabel = [
            'draft' => 'info',
            'signed' => 'success',
            'canceled' => 'danger',
        ];
        foreach ($udatas as $d) {
            $row = array();
            //ID
            // $row[] = $d->id;
            //<th>N°</th>
            $row[] = $d->number;
            //<th>Type</th>
            $spanName = '';
            if ($type == 'employer') {
                $urlViewEntity = '/view/entity/';
                $entity=($d->enrollment)?$d->enrollment->entity:null;
                $row[] = ($entity)?(($entity->entity_type == 'S') ? 'Société' : 'Particulier'):'';
                //Inscription
                $spanName = ($entity)?'<div class="text-dark-75 mb-2">' . $entity->ref . '</div>':'';
                $spanName .= ($entity)?'<div class="text-dark-75 font-weight-bolder mb-2"><a href="' . $urlViewEntity . $entity->id . '">' . $entity->name . '</a></div>':'';
            } elseif ($type == 'student') {
                $urlViewContact = '/contacts/';
                $row[] = 'Particulier';
                //Inscription
                $spanName = $d->contact ? '<div class="text-dark-75 font-weight-bolder mb-2"><a target="_blank" href="' . $urlViewContact . $d->contact->id . '">' . $d->contact->firstname . ' ' . $d->contact->lastname . '</a></div>' : '';
            }

            $row[] =$spanName;
            //Status
            $pStatus = '<p><span class="label label-sm label-light-' . $arrayCssLabel[$d->status] . ' label-inline">' . $arrayLabel[$d->status] . '</span></p>';
            $row[] = $pStatus;
            //<th>Dates</th>
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at . $updated_at;
            //<th>Actions</th>
            $pdfUrl = '/pdf/certificate/' . $d->id . ($type == 'student' ? '/student' : '');
            $btn_pdf = '<a target="_blank" href="'.$pdfUrl.'/1" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text">PDF</span> </a>';
            $btn_pdf_download = ' <a target="_blank" href="'.$pdfUrl.'/2" class="navi-link"> <span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text">Télécharger</span> </a>';
            $btn_attached_docs = '<a href="javascript:void(0)" onclick="_modalAttachedDocs(' . $d->id . ')" class="navi-link"><span class="navi-icon"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></span><span class="navi-text">Documents attachés</span></a>';
            $btn_more = '<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                    data-toggle="dropdown"><i class="' . $tools->getIconeByAction('MORE') . '"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                ' . $btn_attached_docs . '
                                ' . $btn_pdf . '
                                ' . $btn_pdf_download . '
                            </li>
                        </ul>
                    </div>
                </div>';
            $row[] = $btn_more;
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
    public function getAttachedDocuments($af_id,$certificate_id)
    {
        return view('pages.af.document.certificate.attached-documents',compact('certificate_id','af_id'));
    }

    public function getAttachedDocumentEstimatesFact($estimate_id, $af_id)
    {
        return view('pages.commerce.estimate.form.document-estimates',compact('estimate_id','af_id'));
    }

    public function uploadCertificateAttachedDocuments(Request $request)
    {
        //dd($request->all());
        $success = false;
        $msg = 'Oops !';
        if($request->hasFile('document')){
            $file = $request->file("document");
            $filename = 'af_'.$request->af_id.'_attestation_'.$request->certificate_id.'_'.time () . $file->getClientOriginalName();
            $filePath='afs/'.$request->af_id.'/documents/certificates/';
            $pathToUpload = 'uploads/'.$filePath;
            if(!File::exists($pathToUpload)) {
                File::makeDirectory($pathToUpload, 0755, true, true);
            }
            $docPath=Storage::disk('public_uploads')->putFileAs ( $filePath, $file, $filename );
            if(isset($docPath)){
                $data=array(
                    'id'=>0,
                    'name'=>$filename,
                    'path'=>$docPath,
                );
                $DbHelperTools = new DbHelperTools();
                $attachment_id=$DbHelperTools->manageAttachment($data);
                if($attachment_id>0){
                    Media::create(['attachment_id' => $attachment_id, 'table_id' => $request->certificate_id, 'table_name' => 'af_certificates']);
                    $success = true;
                    $msg = 'Succès';
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    public function sdtCertificateAttachedDocuments(Request $request, $certificate_id)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $sort = !empty($dtRequests['sort']['sort']) ? $dtRequests['sort']['sort'] : 'asc';
        $field = !empty($dtRequests['sort']['field']) ? $dtRequests['sort']['field'] : 'ID';
        $page = !empty($dtRequests['pagination']['page']) ? (int)$dtRequests['pagination']['page'] : 1;
        $perpage = !empty($dtRequests['pagination']['perpage']) ? (int)$dtRequests['pagination']['perpage'] : -1;
        $pages = 1;
        $total = count($data); // total items in array
        $datas=Media::join('ged_attachments', 'ged_attachments.id', '=', 'ged_medias.attachment_id')
        ->where([['ged_medias.table_id',$certificate_id],['ged_medias.table_name','af_certificates']])
        ->orderBy('ged_attachments.id','desc')->get();
        //dd($datas);
        foreach ($datas as $d) {
            $row = array();
            //document
            $btn_view = ' <a class="btn btn-sm btn-clean btn-icon fancybox-file" href="/uploads/'.$d->path.'" title="Visualiser"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></a>';
            $row[] = $d->name.$btn_view;
            //dates
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            //$updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at;
            $data[] = $row;
        }
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


    public function uploadEstimatesFactAttachedDocuments(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if($request->hasFile('document') && $request->isMethod('post')){

            $file = $request->file("document");
            $filename = 'af_'.$request->af_id.'_estimates_fact_'.$request->estimate_id.'_'.time () . $file->getClientOriginalName();
            $filePath='afs/'.$request->af_id.'/documents-estimates/Estimatesfact/';
            $pathToUpload = 'uploads/'.$filePath;
            if(!File::exists($pathToUpload)) {
                File::makeDirectory($pathToUpload, 0755, true, true);
            }
            $docPath=Storage::disk('public_uploads')->putFileAs ( $filePath, $file, $filename );

            $attachement = Attachment::create([
                'path' => $filename,
                'type' => 'pdf',
                'category' => 'DEVIS SUR FACTURE',
            ]);

            $id_attachement = $attachement->id;

            $data = $request->post();

            $montantHTU = $data['montantHT_estimate_fact'];
            $titre = $data['titre_estimate_fact'];
            $qte = $data['quantite_estimates_fact'];
            $tva = $data['TauxTVA_estimate_fact'];
        
            $total = $qte * $montantHTU;
        
            $estimate_items = Estimateitem::create([
                'title' => $titre,
                'quantity' => $qte,
                'unit_type' => 'forfait',
                'rate' => $montantHTU,
                'total' => $total,
                'is_main_item' => 1,
                'estimate_id' => $request->estimate_id,
                'statut' => 'actif',
                'attachement_id' => $id_attachement,
            ]);

            $estimate = Estimate::findOrFail(intval($request->estimate_id));

            if($estimate){
                $estimate->tax_percentage = $tva;
                $estimate->is_clo_btn = 1;
                $estimate->status = 'DEPOSE';
                $estimate->save();
            }

        
            $contact_id = $estimate->contact_id;

            $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
            $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');
        
            $todayDate = Carbon::now();
        
            $entite_id = Contact::select('entitie_id')->where('id',intval($contact_id))->pluck('entitie_id');

            $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');

            $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');

            $title_prin = $estimate->estimate_number.'_'.'WORFLOW DEMANDE DEVIS FORMATEUR SUR FACTURE';

            $title_sub = $estimate->estimate_number.'_'.'Demande de devis Formateur / Facture à '.$firstname_former[0].' '.$lastname_former[0];

            //////////////////////////////////////////////////////////
        
            $task_id = Task::select('id')->where([['contact_id',intval($contact_id)],['title',$title_sub],['sub_task',1],['entite_id',intval($entite_id[0])],['etat_id',$id_etat_en[0]]])->pluck('id');
        
            $task_prin = Task::select('id')->where([['contact_id',intval($contact_id)],['title',$title_prin],['sub_task',0],['etat_id',$id_etat_en[0]],['entite_id',intval($entite_id[0])]])->pluck('id');
        
            $task = Task::findOrFail(intval($task_id[0]));
        
             if($task){
                $task->etat_id = $id_etat_ter[0];
                $task->save();
             }
        
            $call_backdate = Carbon::now()->addDays(15);

            $user_id = auth()->user()->contact_id;
        
            $sub_task = Task::create([
                'title' => $estimate->estimate_number.'_'.'Validation de devis Formateur / Facture à '.$firstname_former[0].' '.$lastname_former[0],
                'description' => $task->description,
                'etat_id' => $id_etat_en[0],
                'responsable_id' => $user_id,
                'apporteur_id' => $user_id,
                'start_date' => $todayDate,
                'ended_date' => $task->ended_date,
                'callback_date' => $call_backdate,
                'callback_mode' => 'email',
                'entite_id' => $entite_id[0],
                'contact_id' => $contact_id,
                'af_id' => $request->af_id, 
                'task_parent_id' => $task_prin[0],
                'sub_task' => 1,
            ]);

            $success = true;
            $msg = 'Succès';
            
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }




    public function uploadAgreementIntfacAttachedDocuments(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if($request->hasFile('document') && $request->isMethod('post')){

            $file = $request->file("document");
            $filename = 'af_'.$request->af_id.'_agreement_intfac_'.$request->agreement_id.'_'.time () . $file->getClientOriginalName();
            $filePath='afs/'.$request->af_id.'/documents-agreement/Agreement/';
            $pathToUpload = 'uploads/'.$filePath;
            if(!File::exists($pathToUpload)) {
                File::makeDirectory($pathToUpload, 0755, true, true);
            }
            $docPath=Storage::disk('public_uploads')->putFileAs ( $filePath, $file, $filename );

            $attachement = Attachment::create([
                'path' => $filename,
                'type' => 'pdf',
                'category' => 'CONTRAT SUR FACTURE',
            ]);

            $id_attachement = $attachement->id;

            $data = $request->post();

            $montantHTU = $data['montantHT_agreement_fact'];
            $titre = $data['titre_agreement_fact'];
            $qte = $data['quantite_agreement_fact'];
            $tva = $data['TauxTVA_agreement_fact'];
        
            $total = $qte * $montantHTU;
        
            $agreement_items = Agreementitem::create([
                'title' => $titre,
                'quantity' => $qte,
                'unit_type' => 'personne',
                'rate' => $montantHTU,
                'total' => $total,
                'is_main_item' => 1,
                'agreement_id' => $request->agreement_id,
                'statut' => 'actif',
                'attachement_id' => $id_attachement,
            ]);

            $agreement = Agreement::findOrFail(intval($request->agreement_id));

            if($agreement){
                $agreement->tax_percentage = $tva;
                $agreement->is_clo_btn = 1;
                $agreement->status = 'deposit';
                $agreement->save();
            }
        
            $contact_id = $agreement->contact_id;
            $estimate_id = $agreement->estimate_id;

            $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
            $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');
        
            $todayDate = Carbon::now();
        
            $entite_id = Contact::select('entitie_id')->where('id',intval($contact_id))->pluck('entitie_id');

            $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');

            $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');

            //////////////////////////////////////////////////////////////////////

            $estimate = Estimate::findOrFail(intval($estimate_id));

            $title_prin = $estimate->estimate_number.'_'.'WORFLOW DEMANDE DEVIS FORMATEUR SUR FACTURE';

            $title_sub = $estimate->estimate_number.'_'.'Demande de contrat Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];

            ///////////////////////////////////////////////////////////////////////////
            
        
            $task_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$title_sub],['etat_id',$id_etat_en[0]]])->pluck('id');
        
            $task_prin = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',0],['title',$title_prin],['etat_id',$id_etat_en[0]]])->pluck('id');
        
            $task = Task::findOrFail(intval($task_id[0]));
        
             if($task){
                $task->etat_id = $id_etat_ter[0];
                $task->save();
             }
        
            $call_backdate = Carbon::now()->addDays(15);
            $user_id = auth()->user()->contact_id;
        
            $sub_task = Task::create([
                'title' => $estimate->estimate_number.'_'.'Validation de contrat Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0],
                'description' => $task->description,
                'etat_id' => $id_etat_en[0],
                'responsable_id' => $user_id,
                'apporteur_id' => $user_id,
                'start_date' => $todayDate,
                'ended_date' => $task->ended_date,
                'callback_date' => $call_backdate,
                'callback_mode' => 'email',
                'entite_id' => $entite_id[0],
                'contact_id' => $contact_id,
                'af_id' => $request->af_id, 
                'task_parent_id' => $task_prin[0],
                'sub_task' => 1,
            ]);

            $success = true;
            $msg = 'Succès';
            
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }



    public function uploadInvoiceIntfacAttachedDocuments(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        if($request->hasFile('document') && $request->isMethod('post')){

            $file = $request->file("document");
            $filename = 'af_'.$request->af_id.'_invoice_intfac_'.$request->invoice_id.'_'.time () . $file->getClientOriginalName();
            $filePath='afs/'.$request->af_id.'/documents-invoice/Invoice/';
            $pathToUpload = 'uploads/'.$filePath;
            if(!File::exists($pathToUpload)) {
                File::makeDirectory($pathToUpload, 0755, true, true);
            }
            $docPath=Storage::disk('public_uploads')->putFileAs ( $filePath, $file, $filename );

            $attachement = Attachment::create([
                'path' => $filename,
                'type' => 'pdf',
                'category' => 'FACTURE SUR FACTURE',
            ]);

            $id_attachement = $attachement->id;

            $data = $request->post();

            $montantHTU = $data['montantHT_invoice_fact'];
            $titre = $data['titre_invoice_fact'];
            $qte = $data['quantite_invoice_fact'];
            $tva = $data['TauxTVA_invoice_fact'];
        
            $total = $qte * $montantHTU;
        
            $invoice_items = Invoiceitem::create([
                'title' => $titre,
                'quantity' => $qte,
                'rate' => $montantHTU,
                'total' => $total,
                'invoice_id' => $request->invoice_id,
                'statut' => 'actif',
                'attachement_id' => $id_attachement,
            ]);

            $invoice = Invoice::findOrFail(intval($request->invoice_id));

            if($invoice){
                $invoice->tax_percentage = $tva;
                $invoice->is_clo_btn = 1;
                $invoice->status = 'not_paid';
                $invoice->save();
            }
        
            $contact_id = $invoice->contact_id;
            $estimate_id = $invoice->agreement->estimate_id;

            $firstname_former = Contact::select('firstname')->where('id',intval($contact_id))->pluck('firstname');
            $lastname_former = Contact::select('lastname')->where('id',intval($contact_id))->pluck('lastname');
        
            $todayDate = Carbon::now();
        
            $entite_id = Contact::select('entitie_id')->where('id',intval($contact_id))->pluck('entitie_id');

            $id_etat_en = Param::select('id')->where([['param_code','Etat'],['code','En cours']])->pluck('id');

            $id_etat_ter = Param::select('id')->where([['param_code','Etat'],['code','Terminée']])->pluck('id');

            //////////////////////////////////////////////////////////////////////

            $estimate = Estimate::findOrFail(intval($estimate_id));

            $title_prin = $estimate->estimate_number.'_'.'WORFLOW DEMANDE DEVIS FORMATEUR SUR FACTURE';

            $title_sub = $estimate->estimate_number.'_'.'Demande de facture Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0];

            ///////////////////////////////////////////////////////////////////////////
            
        
            $task_id = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',1],['title',$title_sub],['etat_id',$id_etat_en[0]]])->pluck('id');
        
            $task_prin = Task::select('id')->where([['contact_id',intval($contact_id)],['sub_task',0],['title',$title_prin],['etat_id',$id_etat_en[0]]])->pluck('id');
        
            $task = Task::findOrFail(intval($task_id[0]));
        
             if($task){
                $task->etat_id = $id_etat_ter[0];
                $task->save();
             }
        
            $call_backdate = Carbon::now()->addDays(15);
            $user_id = auth()->user()->contact_id;
        
            $sub_task = Task::create([
                'title' => $estimate->estimate_number.'_'.'Validation de facture Formateur / Facture à : '.$firstname_former[0].' '.$lastname_former[0],
                'description' => $task->description,
                'etat_id' => $id_etat_en[0],
                'responsable_id' => $user_id,
                'apporteur_id' => $user_id,
                'start_date' => $todayDate,
                'ended_date' => $task->ended_date,
                'callback_date' => $call_backdate,
                'callback_mode' => 'email',
                'entite_id' => $entite_id[0],
                'contact_id' => $contact_id,
                'af_id' => $request->af_id, 
                'task_parent_id' => $task_prin[0],
                'sub_task' => 1,
            ]);

            $success = true;
            $msg = 'Succès';
            
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }




    public function storeFormMailAgreement(Request $request)
    {
        $success = false;
        $msg = 'Oops !';
        //dd($request->all());
        if ($request->isMethod('post')) {
            $DbHelperTools = new DbHelperTools();
            $agreement = Agreement::findOrFail($request->id);
            $to = '';
            if ($request->contact_id) {
                $contact = Contact::find($request->contact_id);
            }
            if (isset($contact) && (
                ($to = $contact->email) 
                || ($contact->entitie && ($to = $contact->entitie->email))
            )) {
                $subject = $request->subject;
                $arrayCc = $arrayBcc = [];
                $strCc = $strBcc = '';
                if (isset($request->cc) && !empty($request->cc)) {
                    $strCc = implode(',', array_column(json_decode($request->cc), 'value'));
                }
                if (isset($request->bcc) && !empty($request->bcc)) {
                    $strBcc = implode(',', array_column(json_decode($request->bcc), 'value'));
                }
                if (isset($strCc) && !empty($strCc)) {
                    $arrayCc = explode(',', $strCc);
                }
                if (isset($strBcc) && !empty($strBcc)) {
                    $arrayBcc = explode(',', $strBcc);
                }
                $myEmail = Mail::to($to);
                if (count($arrayCc))
                    $myEmail->cc($arrayCc);

                if (count($arrayBcc))
                    $myEmail->bcc($arrayBcc);

                //content
                $from = [];
                $content = $request->content;
                if ($agreement->agreement_type == 'convention') {
                    $document_type = 'CONVENTION_FORMATION_PROFESSIONNELLE';
                    $from = [['address' => Auth::user()->email, 'name' => Auth::user()->name . ' ' . Auth::user()->lastname]]; 
                } elseif ($agreement->agreement_type == 'contract') {
                    $document_type = 'CONTRACT_FORMATION_PROFESSIONNELLE';
                    $from = [['address' => 'severinebernaert@crfpe.fr', 'name' => null]]; 
                }
                $this->createPdfAgreement($agreement->id, 3);
                $attachmentFileName = $document_type.'_' . $agreement->number . '.pdf';
                $temp = env('TEMP_PDF_FOLDER');
                $attachmentFile = public_path() . '/' . $temp . '/' . $attachmentFileName;
                $attachmentFileName = $document_type.'_' . $agreement->number . '.pdf';
                try {
                    $myEmail->send(new AgreementMail($agreement, $subject, $attachmentFileName, $attachmentFile, $content, $from));
                } catch (Exception $e) {
                    $msg = 'Erreur lors d\'envoi du mail.';
                }

                if (File::exists($attachmentFile)) File::delete($attachmentFile);
                //update invoice to not_paid
                //Invoice::where('id', $request->id)->update(['status' => 'not_paid']);
                $success = true;
                $msg = 'Le mail a été envoyée avec succès';
            } else {
                $msg = 'Aucun mail renseigné.';
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg
        ]);
    }
    public function getEntityCollectiveCode($entity_id)
    {
        $code=null;
        if ($entity_id > 0) {
            $row = Entitie::select('id', 'collective_customer_account')->where('id', $entity_id)->first();
            if($row)
                $code=$row->collective_customer_account;
        }
        return response()->json(['code'=>$code]);
    }
    public function controleinvoices()
    {
        $page_title = 'Contrôle de factures';
        $page_description = '';
        return view('pages.commerce.invoice.control-invoices', compact('page_title', 'page_description'));
    }
    public function sdtControlInvoices(Request $request)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $datas = Invoice::latest();
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('accounting_code', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('bill_date', [$start, $end]);
                    }
                }
                if($request->has('filter_accounting_codes')){
                    $datas->whereIn('accounting_code', $request->filter_accounting_codes);
                }
                if($request->has('filter_entitie_id')){
                    if($request->filter_entitie_id>0)
                        $datas->where('entitie_id', $request->filter_entitie_id);
                }
            }
        }
        $udatas = $datas->orderByDesc('id')->get();
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
        $arrayFundingOptions=[
            'contact_itself' => 'Le contact lui meme',
            'entity_contact' => 'L’entité du contact',
            'cfa_funder' => 'C financeur',
            'other_funders' => 'Autre financeur',
        ];
        foreach ($udatas as $d) {
            //dd($d->id);
            $af=($d->af_id>0)?Action::select('id','code','title')->find($d->af_id):null;
            $row = array();
            //ID
            $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" value="' . $d->id . '" class="checkable"/><span></span></label>';
            //<th>N°</th>
            $typeFacture='<p class="text-'.$arrayCssLabel[$d->invoice_type].'">'.$arrayLabel[$d->invoice_type].'</p>';
            $pStatus = '<p><span class="label label-sm label-light-' . $arrayCssLabel[$d->status] . ' label-inline">' . $arrayLabel[$d->status] . '</span></p>';
            $arrayRefund=$DbHelperTools->getRefundByInvoice($d->id);
            $pRefund='';
            if($arrayRefund['id']>0){
                $pRefund='<p class="text-danger"><a class="text-danger font-size-sm" target="_blank" href="/pdf/refund/'.$arrayRefund['id'].'/1">AVOIR N° : #'.$arrayRefund['number'].'</a></p>';
            }
            $sSage="";
            if($d->is_synced_to_sage==1){
                $sSage='<br><p><span class="text-success font-size-sm"><i class="fas fa-check"></i> Sage</span></p>';
            } elseif(!empty($d->sage_errors)){
                $sage_errors = json_decode($d->sage_errors);
                foreach ($sage_errors as $err_field) {
                    $sSage.='<br><p><span class="text-danger font-size-sm"><i class="fas fa-times-circle"></i> Sage: '.$err_field.' non saisi</span></p>';
                }
            }
            $spanCodeComptable='<p class="text-info font-size-sm">Code comptable : '.(($d->accounting_code)?$d->accounting_code:'--').'</p>'; 
            $spanCodeAnalytical='<p class="text-info font-size-sm">Code analytique : '.(($d->analytical_code)?$d->analytical_code:'--').'</p>'; 
            $spanCodeCollective='<p class="text-info font-size-sm">Code collectif : '.(($d->collective_code)?$d->collective_code:'--').'</p>'; 
            $row[] = $typeFacture.'<p class="text-info">#' . $d->number . '</p>'.$pStatus.$pRefund.$spanCodeComptable.$spanCodeAnalytical.$spanCodeCollective;
            //Date facture
            $dtBillDate = Carbon::createFromFormat('Y-m-d', $d->bill_date);
            $row[] =$dtBillDate->format('d/m/Y');
            //<th>AF</th>
            $spanAf =($af)?'<a href="/view/af/' . $af->id . '">' . $af->code . '</a>':'';
            //<th>Client</th>
            $client = '<ul class="list-unstyled"><li>Client : ' . $d->entity->name . ' - ' . $d->entity->ref . ' - ' . $d->entity->entity_type . '</li>';
            $pContact='';
            if($d->contact_id>0){
                $client.='<li>Contact : '.$d->contact->firstname.' '.$d->contact->lastname.'</li>';
            }
            $client.='</ul>';
            $spanAuxiliaryCustomerAccount='<p class="text-warning font-size-sm">Code auxiliaire : '.(($d->entity->auxiliary_customer_account)?$d->entity->auxiliary_customer_account:'--').'</p>'; 
            $row[] = $spanAf.$client.$spanAuxiliaryCustomerAccount;
            //Dates
            $dtIssueDate = Carbon::createFromFormat('Y-m-d', $d->due_date);
            $due_date = $tools->constructParagraphLabelDot('xs', 'danger', 'Date d\'échéance : ' . $dtIssueDate->format('d/m/Y'));
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            $updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $due_date . $created_at . $updated_at;
            //<th>Montant</th>
            $calcul = $DbHelperTools->getAmountsInvoice($d->id);
            $row[] = '<p class="text-info">' . number_format($calcul['total'], 2) . ' €</p>';
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

    public function downloadZipInvoices(Request $request)
	{
		    $datas=array();

            $DbHelperTools = new DbHelperTools();
            //les factures
            $ids_invoices=[];
            if($request->has('ids_invoices')){
                $ids_invoices=$request->ids_invoices;
            }
            //$ids_invoices=[239,238,237];
            //dd($ids_invoices);
            $invoices=Invoice::select('id')->whereIn('id',$ids_invoices)->get();
            $pdfs_files=[];
            if(count($invoices)>0){
                foreach($invoices as $invoice){
                    $pdfs_files[]=$this->createPdfInvoice($invoice->id, 3);
                }
            }
            //dd($pdfs_files);
            if(count($pdfs_files)){
                $zip = new \ZipArchive();
                $temp_zip="temp_zip";
                $fileName = $temp_zip.'\invoices'.'-'.time().'.zip';
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

    public function sendEmailsInvoices(Request $request)
	{
        $success=false;
        $DbHelperTools = new DbHelperTools();
        //les factures
        $ids_invoices=[];
        if($request->has('ids_invoices')){
            $ids_invoices=$request->ids_invoices;
        }
        //dd($ids_invoices);
        $htmlMessages='';
        $invoices=Invoice::whereIn('id',$ids_invoices)->get();
        if(count($invoices)>0){
            $pathToTemplate = public_path() . "/emails_templates/invoices.txt";
            $content = File::get($pathToTemplate);
            foreach($invoices as $invoice){
                $calcul = $DbHelperTools->getAmountsInvoice($invoice->id);
                if (in_array($invoice->status,['draft','not_paid']) && $calcul['total']>0){
                    $af_title=($invoice->agreement)?$invoice->agreement->af->title:'';
                    $rsContact = Contact::select('email')->where('id', $invoice->contact_id)->first();
                    $to = $rsContact->email;
                    $subject = "Votre facture concernant la formation ".$af_title;
                    $myEmail = Mail::to($to);
                    
                    $this->createPdfInvoice($invoice->id, 4);
                    $attachmentFileName = 'FACTURE-' . $invoice->number . '.pdf';
                    $temp = env('TEMP_PDF_FOLDER');
                    $attachmentFile = public_path() . '/' . $temp . '/' . $attachmentFileName;
                    $attachmentFileName = 'FACTURE-' . $invoice->number . '.pdf';
                    $myEmail->send(new InvoiceMail($invoice, $subject, $attachmentFileName, $attachmentFile, $content));
                    if (File::exists($attachmentFile)) File::delete($attachmentFile);
                    //update invoice to not_paid
                    Invoice::where('id', $invoice->id)->update(['status' => 'not_paid']);
                    $success = true;
                    $htmlMessages.='<p class="text-success">La facture n° '.$invoice->number.' a été envoyée avec succès</p>';
                }else{
                    $htmlMessages.='<p class="text-danger">La facture n° '.$invoice->number.' n\'a pas été envoyée avec succès</p>';
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $htmlMessages
        ]);
	}
    public function mergePdfsInvoices(Request $request)
	{
		    $datas=array();

            $DbHelperTools = new DbHelperTools();
            //les factures
            $ids_invoices=[];
            if($request->has('ids_invoices')){
                $ids_invoices=$request->ids_invoices;
            }
            //$ids_invoices=[239,238,237];
            //dd($ids_invoices);
            $invoices=Invoice::select('id')->whereIn('id',$ids_invoices)->get();
            $pdfs_files=[];
            if(count($invoices)>0){
                foreach($invoices as $invoice){
                    $pdfs_files[]=$this->createPdfInvoice($invoice->id, 3);
                }
            }
            //dd($pdfs_files);
            if(count($pdfs_files)){
                $pdf = PDFMerger::init();
                foreach($pdfs_files as $fname){
                    $file_path = public_path().'\temp_pdf/'.$fname;
                    $pdf->addPDF($file_path, 'all');
                }
                $pathForTheMergedPdf = public_path().'\temp_pdf\invoices'.'-'.time().'.pdf';
                $pdf->merge();
                $pdf->save($pathForTheMergedPdf);
                return response()->download($pathForTheMergedPdf);
            }
		    return 0;
	}
    public function getAttachedDocumentsContract($af_id,$contract_id)
    {
        return view('pages.af.document.contract.attached-documents',compact('contract_id','af_id'));
    }
    public function uploadContractAttachedDocuments(Request $request)
    {
        //dd($request->all());
        $success = false;
        $msg = 'Oops !';
        if($request->hasFile('document')){
            $file = $request->file("document");
            $filename = 'af_'.$request->af_id.'_contract_'.$request->contract_id.'_'.time () . $file->getClientOriginalName();
            $filePath='afs/'.$request->af_id.'/documents/contracts/';
            $pathToUpload = 'uploads/'.$filePath;
            if(!File::exists($pathToUpload)) {
                File::makeDirectory($pathToUpload, 0755, true, true);
            }
            $docPath=Storage::disk('public_uploads')->putFileAs ( $filePath, $file, $filename );
            if(isset($docPath)){
                $data=array(
                    'id'=>0,
                    'name'=>$filename,
                    'path'=>$docPath,
                );
                $DbHelperTools = new DbHelperTools();
                $attachment_id=$DbHelperTools->manageAttachment($data);
                if($attachment_id>0){
                    Media::create(['attachment_id' => $attachment_id, 'table_id' => $request->contract_id, 'table_name' => 'en_contracts']);
                    $success = true;
                    $msg = 'Succès';
                }
            }
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }
    public function sdtContractAttachedDocuments(Request $request, $contract_id)
    {
        $tools = new PublicTools();
        $dtRequests = $request->all();
        $data = $meta = [];
        $sort = !empty($dtRequests['sort']['sort']) ? $dtRequests['sort']['sort'] : 'asc';
        $field = !empty($dtRequests['sort']['field']) ? $dtRequests['sort']['field'] : 'ID';
        $page = !empty($dtRequests['pagination']['page']) ? (int)$dtRequests['pagination']['page'] : 1;
        $perpage = !empty($dtRequests['pagination']['perpage']) ? (int)$dtRequests['pagination']['perpage'] : -1;
        $pages = 1;
        $total = count($data); // total items in array
        $datas=Media::join('ged_attachments', 'ged_attachments.id', '=', 'ged_medias.attachment_id')
        ->where([['ged_medias.table_id',$contract_id],['ged_medias.table_name','en_contracts']])
        ->orderBy('ged_attachments.id','desc')->get();
        //dd($datas);
        foreach ($datas as $d) {
            $row = array();
            //document
            $btn_view = ' <a class="btn btn-sm btn-clean btn-icon fancybox-file" href="/uploads/'.$d->path.'" title="Visualiser"><i class="' . $tools->getIconeByAction('VIEW') . '"></i></a>';
            $row[] = $d->name.$btn_view;
            //dates
            $created_at = $tools->constructParagraphLabelDot('xs', 'primary', 'C : ' . $d->created_at->format('d/m/Y H:i'));
            //$updated_at = $tools->constructParagraphLabelDot('xs', 'warning', 'M : ' . $d->updated_at->format('d/m/Y H:i'));
            $row[] = $created_at;
            $data[] = $row;
        }
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
    //les avoirs
    public function avoirs()
    {
        $page_title = 'Liste des avoirs';
        $page_description = '';
        return view('pages.commerce.avoir.list', compact('page_title', 'page_description'));
    }
    public function sdtAvoirs(Request $request, $af_id)
    {
        $tools = new PublicTools();
        $DbHelperTools = new DbHelperTools();
        $dtRequests = $request->all();
        $data = $meta = [];
            
        $datas = Refund::latest();
        //filter
        if ($request->isMethod('post')) {
            if ($request->has('filter')) {
                if ($request->has('filter_text') && !empty($request->filter_text)) {
                    $datas->where('number', 'like', '%' . $request->filter_text . '%');
                }
                if ($request->has('filter_start') && $request->has('filter_end')) {
                    if (!empty($request->filter_start) && !empty($request->filter_end)) {
                        $start = Carbon::createFromFormat('d/m/Y', $request->filter_start);
                        $end = Carbon::createFromFormat('d/m/Y', $request->filter_end);
                        $datas->whereBetween('refund_date', [$start . " 00:00:00", $end . " 23:59:59"]);
                    }
                }
                // if($request->has('filter_entitie_id')){
                //     if($request->filter_entitie_id>0)
                //         //dd($request->filter_entitie_id);
                //         $row_invoice_id = $DbHelperTools->manageAvoirsClient($request->filter_entitie_id);
                //         //dd($row_invoice_id);
                //         $datas->where('invoice_id', $row_invoice_id);
                // }
            } else {
                $datas = Refund::orderByDesc('id');
            }
        }
        $recordsTotal=count($datas->get());

         if($request->length>0){
             $start=(int) $request->start;
             $length=(int) $request->length;
             $datas->skip($start)->take($length);
         }
        $udatas = $datas->orderByDesc('id')->get();
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
        
        foreach ($udatas as $d) {
            $row = array();
            //ID
            //$row[] = $d->id;
            $row[] = '<label class="checkbox checkbox-single"><input type="checkbox" value="' . $d->id . '" class="checkable"/><span></span></label>';
           
            $sSage="";
            // dd($d->is_synced_to_sage);
            if($d->is_synched_to_sage == 1){
                $sSage='<br><span class="label label-sm label-light-danger label-inline"><i class="fas fa-xs fa-check"></i> Sage</span>';
                $row[] = '<span class="text-info"><a target="_blank" href="/pdf/refund/'.$d->id.'/1">#' . $d->number . '</a></span>' .$sSage;
            }else{
                $row[] = '<span class="text-info"><a target="_blank" href="/pdf/refund/'.$d->id.'/1">#' . $d->number . '</a></span>' ;
            }
            
            $row[] = $d->refund_date;
            $row[] = $d->invoice->number;
            $afText = '';
            if ($af_id == 0) {
                $inv_af_id = [
                    "id" => $d->invoice->af_id,
                ];
                $row_id = $DbHelperTools->manageAFavoirs($inv_af_id);
                $afText = '<a href="/view/af/' . $d->invoice->af_id . '">' . $row_id . '</a>';
            }
            $inv_id = [ "id" => $d->invoice_id];
            $row_entity = $DbHelperTools->manageEntityAvoirs($inv_id);
            //dd($af_id);
            //<th>Client</th>
            $client = '<ul class="list-unstyled"><li>Client : ' .  $row_entity->name . ' - ' .  $row_entity->ref . ' - ' .  $row_entity->entity_type . '</li>';
            //$client = '<ul class="list-unstyled"><li>Client : ' . $row_entity->name . '</li>';
            $row[] = $afText;
            $row[] = $client;
            //$row[] = $client;
            //$pStatus = '<p><span class="label label-sm label-light-' . $arrayCssLabel[$d->status] . ' label-inline">' . $arrayLabel[$d->status] . '</span></p>';
            //$row[] = '<p class="text-info">#' . $d->number . $pStatus . '</p>';
            //$btn_edit = '<button class="btn btn-sm btn-clean btn-icon" onclick="_formConvocation(' . $d->id . ',' . $d->af->id . ')" title="Edition"><i class="' . $tools->getIconeByAction('EDIT') . '"></i></button>';
            $btn_pdf = '<a target="_blank" href="/pdf/convocation/' . $d->id . '/1" class="btn btn-sm btn-clean btn-icon" title="PDF"><span class="navi-icon"><i class="' . $tools->getIconeByAction('PDF') . '"></i></span> <span class="navi-text"></span></a>';
            $btn_pdf_download = '<a target="_blank" href="/pdf/convocation/' . $d->id . '/2" class="btn btn-sm btn-clean btn-icon" title="Télécharger"><span class="navi-icon"><i class="' . $tools->getIconeByAction('DOWNLOAD') . '"></i></span> <span class="navi-text"></span></a>';

            /*    $btn_more='<div class="dropdown dropdown-inline"> <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2"
                    data-toggle="dropdown"><i class="'.$tools->getIconeByAction('MORE').'"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                '.$btn_pdf.'
                                '.$btn_pdf_download.'
                            </li>
                        </ul>
                    </div>
                </div>';
                $row[]=$btn_edit . $btn_more;*/
            //$row[] = $btn_pdf . $btn_pdf_download;
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
            "recordsTotal"=> $recordsTotal,
            "recordsFiltered"=> $recordsTotal,
        ];
        return response()->json($result);
    }

    public function generatePnmAvoirs(Request $request)
    {
        if (!$request->ajax()) {
            die('Not authorized!');
        }

        $DbHelperTools = new DbHelperTools();
        $ignoreErrors = true;
        $tab_red_params = array();
        $red = array();
        $inv_id = array();
        $red_params = $request->avoirs;
        $tab_red_params = explode(',', $red_params);
        
        for($i = 0;$i < count($tab_red_params) ; $i++)
        {
            $red[] = Refund::find($tab_red_params[$i]);
        }
        for($j = 0;$j < count($red) ; $j++)
        {
            $inv_id[] = $red[$j]->invoice_id;
        }

        $inv_param = implode(",", $inv_id);
       //var_dump($inv_param);die();
        $qb = DB::table('inv_invoices')
        ->select([
            'inv_invoices.id',
            'inv_refunds.id as id_refund',
            'inv_invoices.bill_date',
            'inv_refunds.invoice_id',
            'inv_refunds.refund_date',
            'pf_formations.accounting_code as pf_accounting_code',
            'pf_formations.analytical_codes as pf_analytical_code',
            'af_actions.accounting_code as af_accounting_code',
            'af_actions.analytical_code as af_analytical_code',
            'inv_invoices.accounting_code as inv_accounting_code',
            'inv_invoices.analytical_code as inv_analytical_code',
            'inv_invoices.number as code_facture',
            'inv_refunds.number as code_avoir',
            'inv_invoices.due_date',
            'inv_invoices.discount_amount',
            'inv_invoices.discount_amount_type',
            'inv_invoices.is_synced_to_sage as inv_synced_sage',
            'inv_refunds.is_synched_to_sage as refund_synced_sage',
            'en_entities.name as nom_client',
            'en_entities.collective_customer_account',
            'en_entities.auxiliary_customer_account',
            'en_entities.entity_type',
            'en_entities.is_synced_to_sage as en_synced_sage',
            'en_entities.id as en_id',
            'en_entities.ref as en_ref',
            'en_funder.name as nom_funder',
            'en_funder.collective_customer_account as collective_funder_account',
            'en_funder.auxiliary_customer_account as auxiliary_funder_account',
            'en_funder.entity_type as funder_type',
            'en_funder.is_synced_to_sage as funder_synced_sage',
            'en_funder.id as funder_id',
            'en_funder.ref as funder_ref',
            DB::raw('SUM(inv_invoice_items.total) as montant_ht'),
        ])
        ->leftjoin('inv_refunds', 'inv_refunds.invoice_id', '=', 'inv_invoices.id')
        ->leftjoin('inv_invoice_items', 'inv_invoice_items.invoice_id', '=', 'inv_invoices.id')
        ->leftjoin('en_entities', 'en_entities.id', '=', 'inv_invoices.entitie_id')
        ->leftjoin('en_entities as en_funder', 'en_funder.id', '=', 'inv_invoices.entitie_funder_id')
        ->leftjoin('af_actions', 'af_actions.id', '=', 'inv_invoices.af_id')
        ->leftjoin('pf_formations', 'pf_formations.id', '=', 'af_actions.formation_id')
        // ->where('inv_invoices.is_synced_to_sage', '0')
        ->groupby('inv_invoices.id')
        ->orderBy('inv_invoices.bill_date', 'asc')
        ->orderBy('inv_invoices.number', 'asc')
        ;
        if (!empty($inv_param)) {
            if (preg_match("/^([0-9]+\,)*[0-9]+$/", $inv_param) == 0) {
                return response()->json(['success' => false, 'message' => 'Erreur dans les paramètres', 'facture' => false]);
            }
            $ignoreErrors = false;
            $qb->whereIn('inv_invoices.id', explode(',', $inv_param));
        }
        $invoices = $qb->get();

        $generated = array();
        $generated_refunds = array();
        $pnm_contents = ["0" => str_pad('0', 105) . PHP_EOL];

        $entities = array();

        foreach ($invoices as $index => $inv) {
            $code_comptable = $inv->inv_accounting_code ?? $inv->af_accounting_code ?? $inv->pf_accounting_code;
            $code_analytique = $inv->inv_analytical_code ?? $inv->af_analytical_code ?? $inv->pf_analytical_code;
            $error = array();
            $pnm_content = '';

            /* client/funder columns */
            $nom_client = $inv->nom_funder ?? $inv->nom_client;
            $collective_customer_account = $inv->collective_funder_account ?? $inv->collective_customer_account;
            $auxiliary_customer_account = $inv->auxiliary_funder_account ?? $inv->auxiliary_customer_account;
            $en_synced_sage = $inv->funder_synced_sage ?? $inv->en_synced_sage;
            $en_id = $inv->funder_id ?? $inv->en_id;
            $en_ref = $inv->funder_ref ?? $inv->en_ref;

            /* Traitement saut de numéro */
            if ($index > 0) {
                $prev_index = $index - 1;
                $inv_num = (int) $DbHelperTools->extractInvoiceNum($inv);
                $inv_prev_num = (int) $DbHelperTools->extractInvoiceNum($invoices[$prev_index]);

                if(($inv_num - $inv_prev_num) > 1) {
                    break;
                }
            }
            
            if ($inv->inv_synced_sage && $inv->refund_synced_sage) {
                if ($ignoreErrors) {
                    continue;
                }
                return response()->json(['success' => false, 'message' => false, 'facture' => $inv->code_facture, 'client' => $en_ref, 'already_sync' => true]);
            }

            if (empty($code_comptable)) {
                $error[] = "Code comptable";
            }
            if (empty($code_analytique)) {
                $error[] = "Code analytique";
            }
            if (empty($collective_customer_account)) {
                $error[] = "Code collectif ".($inv->funder_id ? "financeur" : "client");
            }
            if (empty($auxiliary_customer_account)) {
                $error[] = "Code auxiliaire ".($inv->funder_id ? "financeur" : "client");
            }
            if (!empty($error)) {
                Invoice::where('id', $inv->id)->update(['sage_errors' => json_encode($error)]);
                if ($ignoreErrors) {
                    continue;
                }
                return response()->json(['success' => false, 'message' => $error, 'facture' => $inv->code_facture, 'client' => $en_ref]);
            }
                        
            if (!$en_synced_sage && !in_array($en_id, $entities)) {
                $entities[$inv->id] = $en_id;
            }

            $inv_types = $inv->code_avoir ? 2 : 1;

            foreach (range(1, $inv_types) as $line_number) {
                /* 
                    ($line_number = 1) => invoice
                    ($line_number = 2) => refund
                */

                if ($line_number == 1 && $inv->inv_synced_sage) {
                    continue;
                }

                $is_refund = $line_number == 2;
                foreach (['X', ' ', 'A'] as $x_a) {
                    $pnm_content .= 'VEN';
                    $inv_date = $is_refund ? $inv->refund_date : $inv->bill_date;
                    $pnm_content .= !empty($inv_date) ? Carbon::createFromFormat('Y-m-d', $inv_date)->format('dmy') : '000000';
                    /* Type de pièce */
                    $pnm_content .= $is_refund ? 'AC' : 'FC';
                    /* Compte général */
                    switch ($x_a) {
                        case 'X':
                            $account_client = $collective_customer_account;
                            break;
                        default:
                            $account_client = $code_comptable;
                            break;
                    }
                    $account_gen = str_pad($account_client, 13);
                    $pnm_content .= substr($account_gen, 0, 13);
                    /* Type de compte */
                    $pnm_content .= $x_a;
                    /* Compte auxiliaire / analytique */
                    switch ($x_a) {
                        case 'X':
                            $account_a_client = $auxiliary_customer_account ?? $DbHelperTools->generateAuxiliaryAccountForEntity($inv->funder_id ?? $inv->en_id);
                            break;
                        case 'A':
                            $account_a_client = $code_analytique;
                            break;
                        default:
                            $account_a_client = ' ';
                            break;
                    }
                    $account_a_client = str_pad($account_a_client, 13);
                    $pnm_content .= substr($account_a_client, 0, 13);
                    /* N Facture/Avoir */
                    $invoice_number = $is_refund ? $inv->code_avoir : $inv->code_facture;
                    $invoice_number = str_replace('-', '', $invoice_number);
                    if (!$is_refund) {
                        $invoice_number = substr($invoice_number, 1);
                    }
                    $invoice_number_str = str_pad($invoice_number, 13);
                    $pnm_content .= substr($invoice_number_str, 0, 13);
                    /* Libellé pièce = n_inv + nom_client */
                    $nom_client = $DbHelperTools->removeAccentsAndSpecial($nom_client);       
                    $libelle_piece = "{$invoice_number} - {$inv->nom_client}";
                    $libelle_piece = str_pad($libelle_piece, 25);
                    $pnm_content .= substr($libelle_piece, 0, 25);
                    /* Mode Paiement + Echeance */
                    $pnm_content .= 'S';
                    $pnm_content .= Carbon::createFromFormat('Y-m-d', $inv->due_date)->format('dmy');
                    /* Sens */
                    switch ($x_a) {
                        case 'X':
                            $d_c = $is_refund ? 'C' : 'D';
                            break;
                        default:
                            $d_c = $is_refund ? 'D' : 'C';
                            break;
                    }
                    $pnm_content .= $d_c;
                    /* Remise */
                    $discount_amount = $inv->discount_amount;
                    $discount_type = $inv->discount_amount_type;
                    $discount = 0;
                    if ($discount_amount && $discount_type) {
                        switch ($discount_type) {
                            case 'fixed_amount': 
                                $discount = $inv->discount_amount;
                                break;
                            case 'percentage': 
                                $discount = $inv->montant_ht * $discount_amount / 100;
                                break;
                        }
                    }
                    /* Montant HT */
                    $montant_ht = number_format($inv->montant_ht - $discount, 2, '.', '');
                    $montant_ht = str_pad($montant_ht, 20, ' ', STR_PAD_LEFT);
                    $pnm_content .= $montant_ht;
                    /* FIN */
                    $pnm_content .= 'N';
                    $pnm_content .= PHP_EOL;
                }
            }
            
            $generated[] = $inv->id;
            // if ($inv->id_refund) {
            //     $generated_refunds[] = $red_params;
            // }
            $pnm_contents[$inv->id] = $pnm_content;
        }
        
        $tokenPNC = false;
        if (!empty($entities)) {
            $entities_set = array_values($entities);
            $entities_set = array_unique($entities_set);
            $request->request->add(['entities' => implode(',', $entities_set) ]);
            $resp_pnc = $this->generatePnc($request, true, $ignoreErrors)->getData(true);
            if (!$resp_pnc['success']) {
                if (!$ignoreErrors) {
                    $resp_pnc['facture'] = $inv->code_facture;
                    return response()->json($resp_pnc);
                } else {
                    $filter_by_en = function($i) use($resp_pnc) {
                        return $i !== $resp_pnc['en_id'];
                    };
                    $entities = array_filter($entities, $filter_by_en);
                    $entities_inv = array_keys($entities);
                    $filter_by_inv = function($i) use($entities_inv) {
                        return in_array($i, $entities_inv);
                    };
                    $generated = array_filter($generated, $filter_by_inv);
                    $pnm_contents = array_filter($pnm_contents, $filter_by_inv, ARRAY_FILTER_USE_KEY);
                }
            } else {
                $tokenPNC = $resp_pnc['file'];
            }
        }

        Invoice::whereIn('id', $generated)->update(['is_synced_to_sage' => 1, 'sage_errors' => null]);

        if (!empty($tab_red_params)) {
            Refund::whereIn('id', $tab_red_params)->update(['is_synched_to_sage' => 1]);
        }

        $tokenPNM = uniqid('CRFPE_');
        $fileName = "$tokenPNM.PNM";

        Storage::makeDirectory('sage');
        $pnm_contents = implode('', $pnm_contents);
        Storage::disk('sage')->put($fileName, $pnm_contents);

        return response()->json(['success' => true, 'files' => ['PNM' => $tokenPNM, 'PNC' => $tokenPNC]]);
    }
}