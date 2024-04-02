{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!--begin::Card-->
<div class="card card-custom">
    <div class="card-header flex-wrap border-0 ">
        <div class="card-title">
            <h3 class="card-label">Factures
            </h3>
        </div>
        <div class="card-toolbar">

            <!--begin::Dropdown-->
            <div class="dropdown dropdown-inline mr-2">
                <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="la la-download"></i></button>

                <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">

                    <ul class="navi flex-column navi-hover py-2">
                        <li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">Export:</li>
                        <li class="navi-item">
                            <a href="javascript:void(0)" onclick="_exportInvoices(0)" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-excel-o"></i>
                                </span>
                                <span class="navi-text">Excel</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="javascript:void(0)" onclick="_mergeInvoices(0)" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-print"></i>
                                </span>
                                <span class="navi-text">Impression en masse</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="javascript:void(0)" onclick="_downloadInvoices(0)" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-pdf-o"></i>
                                </span>
                                <span class="navi-text">Génération pdf en masse</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="javascript:void(0)" onclick="_sendEmailsInvoices(0)" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-send-o"></i>
                                </span>
                                <span class="navi-text">Envoyer par email</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!--end::Dropdown-->    
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-1" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_invoices()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Ajouter"
                onclick="_formInvoice(0,0)"><i class="flaticon2-add-1"></i></button>
            <button class="btn btn-sm btn-light-primary ml-1" data-toggle="tooltip" title="Ajouter"
                onclick="_formStudentsInvoices(0)"><i class="flaticon2-add-1"></i> Générer des facture étudiants</button>
            <button class="btn btn-sm btn-light-success ml-1" data-toggle="tooltip" title="Générer et télécharger le fichier PNM pour Sage"
                onclick="_generatePnmFile()"><i class="flaticon2-file-1"></i> Générer le fichier PNM</button>
            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin::filter-->
        <x-filter-form type="Invoices" />
        <!--end::filter-->
        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_invoices">
            <thead>
                <tr>
                    <th></th>
                    <th>N°</th>
                    <th>AF</th>
                    <!-- <th>Client</th> -->
                    <th>Montant</th>
                    <th>Paiement</th>
                    <th>Dates</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<!--end::Card-->
<x-modal id="modal_form_invoice" content="modal_form_invoice_content" />
<x-modal id="modal_form_invoiceItem" content="modal_form_invoiceItem_content" />
<x-modal id="modal_form_funding" content="modal_form_funding_content" />
<x-modal id="modal_form_fundingpayment" content="modal_form_fundingpayment_content" />
<x-modal id="modal_form_payment" content="modal_form_payment_content" />
<x-modal id="modal_form_mail" content="modal_form_mail_content" />
<x-modal id="modal_form_refund" content="modal_form_refund_content" />
<x-modal id="modal_form_students_invoices" content="modal_form_students_invoices_content" />

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>


{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=2') }}"></script>
<!-- <script src="{{ asset('custom/js/list-agreements.js?v=1') }}"></script> -->
<script>
$('.select2').select2();
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$('[data-toggle="tooltip"]').tooltip();

var dtUrl = '/api/sdt/invoices/0';
var table = $('#dt_invoices');
// begin first table
table.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    ordering: false,
    serverSide: false,
    ajax: {
        url: dtUrl,
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
        },
    },
    lengthMenu: [5, 10, 25, 50],
    pageLength: 10,
    headerCallback: function(thead, data, start, end, display) {
        thead.getElementsByTagName('th')[0].innerHTML = `
                    <label class="checkbox checkbox-single">
                        <input type="checkbox" value="" class="group-checkable"/>
                        <span></span>
                    </label>`;
    },
    columnDefs: [{
        targets: 0,
        width: '30px',
        className: 'dt-left',
        orderable: false,
        /* render: function(data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        }, */
    }],
});

table.on('change', '.group-checkable', function() {
    var set = $(this).closest('table').find('td:first-child .checkable');
    var checked = $(this).is(':checked');

    $(set).each(function() {
        if (checked) {
            $(this).prop('checked', true);
            $(this).closest('tr').addClass('active');
        } else {
            $(this).prop('checked', false);
            $(this).closest('tr').removeClass('active');
        }
    });
});

table.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_invoices = function() {
    $('#dt_invoices').DataTable().ajax.reload();
}
function _formInvoice(invoice_id, af_id) {
    var modal_id = 'modal_form_invoice';
    var modal_content_id = 'modal_form_invoice_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/invoice/' + invoice_id + '/' + af_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}

$('#filter_datepicker').datepicker({
    language: 'fr',
    rtl: KTUtil.isRTL(),
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
var form_id = 'formFilterInvoices';
$("#" + form_id).submit(function(event) {
    event.preventDefault();
    KTApp.blockPage();
    var formData = $(this).serializeArray();
    var table = 'dt_invoices';
    $.ajax({
        type: "POST",
        dataType: 'json',
        data: formData,
        url: dtUrl,
        success: function(response) {
            if (response.data.length == 0) {
                $('#' + table).dataTable().fnClearTable();
                return 0;
            }
            $('#' + table).dataTable().fnClearTable();
            $("#" + table).dataTable().fnAddData(response.data, true);
        },
        error: function() {
            $('#' + table).dataTable().fnClearTable();
        }
    }).done(function(data) {
        KTApp.unblockPage();
    });
    return false;
});
var _reset = function() {
    _reload_dt_invoices();
}


function _formPayment(payment_id, invoice_id) {
    var modal_id = 'modal_form_payment';
    var modal_content_id = 'modal_form_payment_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: "/form/payment/" + payment_id + '/' + invoice_id,
        type: "GET",
        dataType: "html",
        success: function(html, status) {
            $("#" + modal_content_id).html(html);
        },
    });
};
function _formSendInvoice(invoice_id) {
    var modal_id = 'modal_form_mail';
    var modal_content_id = 'modal_form_mail_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: "/form/mail/invoice/" + invoice_id,
        type: "GET",
        dataType: "html",
        success: function(html, status) {
            $("#" + modal_content_id).html(html);
        },
    });
};

function _formStudentsInvoices(af_id) {
    var modal_id = 'modal_form_students_invoices';
    var modal_content_id = 'modal_form_students_invoices_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/students/invoices/'+ af_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}

function _generatePnmFile() {
    var inv_params = '?invoices=';
    var selections = $('table').find('td:first-child .checkable:checked');
    $(selections).each(function() {
        var inv = $(this);
        inv_params += (inv.val() + ',');
    });
    inv_params = inv_params.slice(0, -1);

    $.ajax({
        url: '/form/generate/pnm/'+ inv_params,
        type: 'GET',
        dataType: 'JSON',
        success: function(resp) {
            if (!resp.success) {
                var error;
                if (!resp.message && resp.already_sync) {
                    error = ': Déjà synchronisée avec Sage.';
                } else {
                    error = 'Informations non saisies: <br/>' + resp.message.join('<br/>');
                }
                _showResponseMessage("error", '['+resp.facture+']['+resp.client+'] ' + error, 0);
            } else {
                if (resp.files.PNC) {
                    window.open('/form/downloadsagefile/?sageFile=' + resp.files.PNC + '.PNC', '_blank');
                }
                setTimeout(() => window.open('/form/downloadsagefile/?sageFile=' + resp.files.PNM + '.PNM', '_blank'), 1000);
            }
            _reload_dt_invoices();
        }
    });
}

function _formRefund(refund_id, invoice_id) {
    var modal_id = 'modal_form_refund';
    var modal_content_id = 'modal_form_refund_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: "/form/refund/" + refund_id + '/' + invoice_id,
        type: "GET",
        dataType: "html",
        success: function(html, status) {
            $("#" + modal_content_id).html(html);
        },
    });
};
function _exportInvoices(id){
		var TableauIdProcess = new Array();
		var j = 0;
		if(id>0){
			TableauIdProcess[0]=id;
		}else{	
            $('#dt_invoices input[class="checkable"]').each(function(){
   			    var checked = jQuery(this).is(":checked");
                if(checked){
                    TableauIdProcess[j] = jQuery(this).val();
                    j++;
                }
   			});
		}
    	if(TableauIdProcess.length<1){
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Veuillez sélectionner une ou plusieurs factures!',
            })
    	}else{
            KTApp.blockPage();
            $.ajax({
                type: 'POST',
                url: '/api/export/invoices',
                data: {
                    ids_invoices: TableauIdProcess,
                },
                cache: false,
                xhrFields:{
                    responseType: 'blob'
                },
                success: function(data) {
                    const time = Date.now();
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(data);
                    link.download = 'factures-'+time+'.xlsx';
                    link.click();
                },
                error: function(error) {},
                complete: function(resultat, statut) {
                    KTApp.unblockPage();
                }
            });
            return false; 	
    	}
}
function _downloadInvoices(id){
		var TableauIdProcess = new Array();
		var j = 0;
		if(id>0){
			TableauIdProcess[0]=id;
		}else{	
            $('#dt_invoices input[class="checkable"]').each(function(){
   			    var checked = jQuery(this).is(":checked");
                if(checked){
                    TableauIdProcess[j] = jQuery(this).val();
                    j++;
                }
   			});
		}
    	if(TableauIdProcess.length<1){
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Veuillez sélectionner une ou plusieurs factures!',
            })
    	}else{
            KTApp.blockPage();
            $.ajax({
                type: 'get',
                url: '/api/download/zip/invoices',
                data: {
                    ids_invoices: TableauIdProcess,
                },
                cache: false,
                xhrFields:{
                    responseType: 'blob'
                },
                success: function(data) {
                    const time = Date.now();
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(data);
                    link.download = 'factures-'+time+'.zip';
                    link.click();
                },
                error: function(error) {},
                complete: function(resultat, statut) {
                    KTApp.unblockPage();
                }
            });
            return false; 	
    	}
}
function _sendEmailsInvoices(id){
		var TableauIdProcess = new Array();
		var j = 0;
		if(id>0){
			TableauIdProcess[0]=id;
		}else{	
            $('#dt_invoices input[class="checkable"]').each(function(){
   			    var checked = jQuery(this).is(":checked");
                if(checked){
                    TableauIdProcess[j] = jQuery(this).val();
                    j++;
                }
   			});
		}
    	if(TableauIdProcess.length<1){
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Veuillez sélectionner une ou plusieurs factures!',
            })
    	}else{
            KTApp.blockPage();
            $.ajax({
                type: 'get',
                url: '/api/send/emails/invoices',
                data: {
                    ids_invoices: TableauIdProcess,
                },
                success: function(result) {
                    //console.log(result.msg);
                    if (result.success) {
                        swal.fire({
                            html: result.msg,
                            icon: 'success',
                            buttonsStyling: false,
                            confirmButtonText: '<i class="far fa-times-circle"></i> Fermer',
                            customClass: {
                                confirmButton: "btn btn-light-primary"
                            },
                        }).then(function() {});
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                },
                error: function(error) {
                    _showResponseMessage('error', 'Ooops...');
                },
                complete: function(resultat, statut) {
                    KTApp.unblockPage();
                }
            });
            return false; 	
    	}
}
function _mergeInvoices(id){
		var TableauIdProcess = new Array();
		var j = 0;
		if(id>0){
			TableauIdProcess[0]=id;
		}else{	
            $('#dt_invoices input[class="checkable"]').each(function(){
   			    var checked = jQuery(this).is(":checked");
                if(checked){
                    TableauIdProcess[j] = jQuery(this).val();
                    j++;
                }
   			});
		}
    	if(TableauIdProcess.length<1){
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Veuillez sélectionner une ou plusieurs factures!',
            })
    	}else{
            KTApp.blockPage();
            $.ajax({
                type: 'get',
                url: '/api/merge/pdf/invoices',
                data: {
                    ids_invoices: TableauIdProcess,
                },
                cache: false,
                xhrFields:{
                    responseType: 'blob'
                },
                success: function(data) {
                    const time = Date.now();
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(data);
                    link.download = 'factures-'+time+'.pdf';
                    link.click();
                },
                error: function(error) {},
                complete: function(resultat, statut) {
                    KTApp.unblockPage();
                }
            });
            return false; 	
    	}
}
</script>
@endsection