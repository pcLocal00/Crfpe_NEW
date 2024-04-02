@if($block_id==1)
<div class="card card-custom card-fit card-border">
    <div class="card-header">
        <div class="card-toolbar">
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title=""
                onclick="_reload_dt_contracts()" data-original-title="Rafraîchir"><i
                    class="flaticon-refresh"></i></button>
            @if(auth()->user()->roles[0]->code!='FORMATEUR')  
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title=""
                onclick="_formContractFormer(0,{{ $af_id }})" data-original-title="Générer un nouveau contrat"><i
                    class="flaticon2-add-1"></i></button>
            @endif
            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin: Datatable-->
        <table class="table table-sm table-bordered" id="dt_contracts">
            <thead class="thead-light">
                <tr>
                    <th></th>
                    <th>Contrat</th>
                    <th>Coût</th>
                    <th>NB H</th>
                    <th>Etat</th>
                    <th>Infos</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<script>
var af_id = $("input[name='id']").val();
var dtUrlContracts = '/api/sdt/contracts/' + af_id;
var dt_contracts = $('#dt_contracts');
// begin first table
dt_contracts.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    searching: true,
    paging: true,
    ordering: false,
    info: false,
    ajax: {
        url: dtUrlContracts,
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
        },
    },
    lengthMenu: [5, 10, 25, 50],
    pageLength: 25,
    headerCallback: function(thead, data, start, end, display) {
        thead.getElementsByTagName('th')[0].innerHTML = `
                    <label class="checkbox checkbox-single">
                        <input type="checkbox" value="" class="group-checkable"/>
                        <span></span>
                    </label>`;
    },
});
dt_contracts.on('change', '.group-checkable', function() {
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
dt_contracts.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});
var _reload_dt_contracts = function() {
    $('#dt_contracts').DataTable().ajax.reload();
}

var _signContract = function(id) {
    $.ajax({
        url: '/sign/contract/'+id+'/1',
        type: 'GET',
        dataType: 'json',
        success: function(resp) {
            if (resp.success) {
                toastr.success(resp.message);
                _reload_dt_contracts();
            } else {
                toastr.error(resp.message);
            }
        }
    });
}

function _formContractFormer(contract_id, af_id) {
    var modal_id = 'modal_form_contract';
    var modal_content_id = 'modal_form_contract_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/contract/' + contract_id + '/' + af_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        }
    });
}
var _deleteContract = function(contract_id) {
    var successMsg = "Votre contrat a été supprimée.";
    var errorMsg = "Votre contrat n\'a pas été supprimée.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le contrat?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/delete/contract",
                type: "DELETE",
                data: {
                    contract_id: contract_id
                },
                dataType: "JSON",
                success: function(result, status) {
                    if (result.success) {
                        _showResponseMessage("success", successMsg);
                    } else {
                        _showResponseMessage("error", errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error", errorMsg);
                },
                complete: function(result, status) {
                    _reload_dt_contracts();
                    KTApp.unblockPage();
                }
            });
        }
    });
}

function _showFormerScheduleDetails(contract_id) {
    var modal_id = 'modal_schedule_details';
    var modal_content_id = 'modal_schedule_details_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/get/schedule/contract/details/' + contract_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        }
    });
}
function _modalAttachedDocsContract(contract_id){
    var modal_id = 'modal_form_attached_documents';
    var modal_content_id = 'modal_form_attached_documents_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
    $.ajax({
        url: '/get/attached/documents/contract/'+af_id+'/'+contract_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}

function _signContract(contract_id,af_id){
    alert(2)
}
</script>
@endif
@if($block_id==2)
<div class="card card-custom card-fit card-border">
    <div class="card-header">
        <div class="card-toolbar">
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_estimates()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Ajouter un paramétrage"
                onclick="_formEstimate(0,{{ $af_id }},0)"><i class="flaticon2-add-1"></i></button>
        </div>
    </div>
    <div class="card-body">
        <!--begin::filter-->
        <x-filter-form type="Estimates" />
        <!--end::filter-->
        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_estimates">
            <thead>
                <tr>
                    <th></th>
                    <th>Devis</th>
                    <th>Client</th>
                    <th>Dates</th>
                    <th>Montant</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<script src="{{ asset('custom/js/af-estimates.js?v=2') }}"></script>
<script>
function _formSendEstimate(estimate_id) {
    var modal_id = 'modal_form_mail';
    var modal_content_id = 'modal_form_mail_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: "/form/mail/estimate/" + estimate_id,
        type: "GET",
        dataType: "html",
        success: function(html, status) {
            $("#" + modal_content_id).html(html);
        },
    });
};
</script>
@endif
@if($block_id==3)
<div class="card card-custom card-fit card-border">
    <div class="card-header">
        <div class="card-toolbar">
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_agreements()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Ajouter"
                onclick="_formAgreement(0,{{ $af_id }},0)"><i class="flaticon2-add-1"></i></button>
        </div>
    </div>
    <div class="card-body">
        <!--begin::filter-->

        <!--end::filter-->
        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_agreements">
            <thead>
                <tr>
                    <th></th>
                    <th>Type</th>
                    <th>N°</th>
                    <th>Client</th>
                    <th>Montant</th>
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
<script src="{{ asset('custom/js/af-agreements.js?v=2') }}"></script>
<script>
function _createInvoiceFormAgreement(agreement_id) {
    var modal_id = 'modal_form_invoice_from_agreement';
    var modal_content_id = 'modal_form_invoice_from_agreement_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/invoice-from-agreement/' + agreement_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}
function _formSendAgreement(agreement_id) {
    var modal_id = 'modal_form_mail';
    var modal_content_id = 'modal_form_mail_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: "/form/mail/agreement/" + agreement_id,
        type: "GET",
        dataType: "html",
        success: function(html, status) {
            $("#" + modal_content_id).html(html);
        },
    });
};
</script>
@endif
@if($block_id==4)
<div class="card card-custom card-fit card-border">
    <div class="card-header">
        <div class="card-toolbar">
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_convocations()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Ajouter"
                onclick="_formConvocation(0,{{ $af_id }})"><i class="flaticon2-add-1"></i></button>
        </div>
    </div>
    <div class="card-body">
        <!--begin::filter-->
        <x-filter-form type="Convocations" />
        <!--end::filter-->
        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_convocations">
            <thead>
                <tr>
                    <th></th>
                    <th>N°</th>
                    <th>Stagiaire</th>
                    <th>AF</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<script>
var dtUrl = '/api/sdt/convocations/' + af_id;
var table = $('#dt_convocations');
// begin first table
table.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    ordering: false,
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
    pageLength: 25,
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
        /*   render: function(data, type, full, meta) {
                return `
                            <label class="checkbox checkbox-single">
                                <input type="checkbox" value="" class="checkable"/>
                                <span></span>
                            </label>`;
            },*/
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

var _reload_dt_convocations = function() {
    $('#dt_convocations').DataTable().ajax.reload();
}

function _formConvocation(convocation_id, af_id) {
    var modal_id = 'modal_form_convocation';
    var modal_content_id = 'modal_form_convocation_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/convocation/' + convocation_id + '/' + af_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}

function _showScheduleDetails(af_id, member_id) {
    var modal_id = 'modal_schedule_details';
    var modal_content_id = 'modal_schedule_details_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/get/schedule/member/details/' + af_id + '/' + member_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        }
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
var form_id = 'formFilterConvocations';
$("#" + form_id).submit(function(event) {
    event.preventDefault();
    KTApp.blockPage();
    var formData = $(this).serializeArray();
    var table = 'dt_convocations';
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
</script>
@endif
@if($block_id==5)
<div class="card card-custom card-fit card-border">
    <div class="card-header">
        <div class="card-toolbar">
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_invoices()"><i class="flaticon-refresh"></i>
            </button>
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Ajouter"
                onclick="_formInvoice(0)"><i class="flaticon2-add-1"></i></button>
            <button class="btn btn-sm btn-light-primary ml-1" data-toggle="tooltip" title="Ajouter"
                onclick="_formStudentsInvoices({{ $af_id }})"><i class="flaticon2-add-1"></i> Générer des facture étudiants</button>
        </div>
    </div>
    <div class="card-body">

        <table class="table table-bordered table-checkable" id="dt_invoices_af">
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
<script>
var af_id = $("input[name='id']").val();
var dtUrl = '/api/sdt/invoices/' + af_id;
var table_invoices = $('#dt_invoices_af');
// begin first table
table_invoices.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    ordering: false,
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
    pageLength: 25,
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
        render: function(data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        },
    }],
});

table_invoices.on('change', '.group-checkable', function() {
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

table_invoices.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_invoices = function() {
    $('#dt_invoices_af').DataTable().ajax.reload();
}

function _formInvoice(invoice_id) {
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
</script>
@endif
@if($block_id==6 || $block_id==7)
<div class="card card-custom card-fit card-border">
    <div class="card-header">
        <div class="card-toolbar">
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_certificates()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Générer les attestations de suivi de formation"
                onclick="_generateCertificate()"><i class="flaticon-add-circular-button"></i></button>
        </div>
    </div>
    <div class="card-body">
        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_certificates{{$block_id == 7 ? '_students' : ''}}">
            <thead>
                <tr>
                    {{-- <th></th> --}}
                    <th>N°</th>
                    <th>Type</th>
                    <th>Membre</th>
                    <th>Statut</th>
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
<script>
var block = {{$block_id}};
var af_id = $("input[name='id']").val();
var dtUrl = '/api/sdt/certificates/' + af_id + (block == 7 ? '/student' : '');
var table_certificates = $("#dt_certificates"+(block == 7 ? '_students' : ''));
// begin first table
table_certificates.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    ordering: false,
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
    pageLength: 25,
    /* headerCallback: function(thead, data, start, end, display) {
        if (block == 6) {
            thead.getElementsByTagName('th')[0].innerHTML = `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="group-checkable"/>
                            <span></span>
                        </label>`;
        }
    },
    columnDefs: [{
        targets: 0,
        width: '30px',
        className: 'dt-left',
        orderable: false,
        render: function(data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        },
    }], */
});

table_certificates.on('change', '.group-checkable', function() {
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

table_certificates.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

function _reload_dt_certificates() {
    table_certificates.DataTable().ajax.reload();
}
function _generateCertificate() {
        var block_id = 'tab_doc_{{$block_id}}';
        var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
        KTApp.block('#' + block_id, {
            overlayColor: '#000000',
            state: 'danger',
            message: 'Veuillez patienter svp...'
        });
        $.ajax({
            url: '/api/generate/certificates/' + af_id + (block == 7 ? '/student' : ''),
            type: 'GET',
            dataType: 'json',
            success: function(result, status) {
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                } else {
                    _showResponseMessage('error', result.msg);
                }
                _reload_dt_certificates();
            },
            error: function(result, status, error) {},
            complete: function(result, status) {
                KTApp.unblock('#' + block_id);
            }
        });
}
function _modalAttachedDocs(certificate_id){
    var modal_id = 'modal_form_attached_documents';
    var modal_content_id = 'modal_form_attached_documents_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
    $.ajax({
        url: '/get/attached/documents/'+af_id+'/'+certificate_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}
</script>
@endif
@if($block_id==8)
<div class="card card-custom card-fit card-border">
    <div class="card-header">
        <div class="card-toolbar">
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" data-original-title="Rafraîchir" onclick="_reload_dt_estimates_fact()"><i
                    class="flaticon-refresh"></i></button>
                    <input type="hidden" id="hid_af_id" value="<?= $af_id ?>">

            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin: Datatable-->
        <table class="table table-sm table-bordered" id="dt_estimates_fact">
            <thead class="thead-light">
                <tr>
                    <th></th>
                    <th>N# devis</th>
                    <th>Intervenant sur facture</th>
                    <th>Status</th>
                    <th>Af</th>
                    <th>Dates</th>
                    <th>Montant</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<x-modal id="modal_form_documents_estimates_fact" content="modal_form_documents_estimates_fact_content" />
<x-modal id="modal_form_documents_validation_estimates_fact" content="modal_form_documents_validation_estimates_fact_content" />
<script>
var af_id = $("#hid_af_id").val();
var dtUrl = '/api/sdt/estimates_fact/' + af_id; 
var dt_estimates_fact = $('#dt_estimates_fact');
// begin first table
dt_estimates_fact.DataTable({
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
        type: 'GET',
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
        render: function(data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        },
    }],
});

dt_estimates_fact.on('change', '.group-checkable', function() {
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

dt_estimates_fact.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_estimates_fact = function() {
    $('#dt_estimates_fact').DataTable().ajax.reload();
}


function _formEstimateFact(estimate_id, af_id) {
        var modal_id = 'modal_form_documents_estimates_fact';
        var modal_content_id = 'modal_form_documents_estimates_fact_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/attached/documents/estimatesfact/' + estimate_id + '/' + af_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
}


function _deleteEstimateFact(estimate_id, af_id) {

    var successMsg = "Votre document a été supprimée.";
    var errorMsg = "Votre document n\'a pas été supprimée.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le document ?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";

    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui"
    }).then(function(result) {
        if (result.value) {

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    
                    $.ajax({
                        url: '/api/delete/docs/estimatesfact/' + estimate_id + '/' + af_id,
                        type: 'DELETE',
                        dataType: 'html',
                        success: function(html, status) {
                            alert("Document supprimé avec succés");
                            $('#dt_estimates_fact').DataTable().ajax.reload();
                        },
                        error: function(result, status, error) {
                            alert("Désolé votre document n'a pas été supprimé !!");
                        }
                    });
            }
    });
}

function _validationEstimateFact(estimate_id, af_id) {
        var modal_id = 'modal_form_documents_validation_estimates_fact';
        var modal_content_id = 'modal_form_documents_validation_estimates_fact_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);

        $.ajax({
            url: '/form/validation/estimatesfact/' + estimate_id + '/' + af_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
}
</script>
@endif
@if($block_id==9)
<div class="card card-custom card-fit card-border">
    <div class="card-header">
        <div class="card-toolbar">
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" data-original-title="Rafraîchir" onclick="_reload_dt_agreement_fact()"><i
                    class="flaticon-refresh"></i></button>
                    <input type="hidden" id="hid_af_id" value="<?= $af_id ?>">

            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin: Datatable-->
        <table class="table table-sm table-bordered" id="dt_agreement_fact">
            <thead class="thead-light">
                <tr>
                    <th></th>
                    <th>N# contrat</th>
                    <th>Intervenant sur facture</th>
                    <th>Status</th>
                    <th>Dates</th>
                    <th>Cout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<x-modal id="modal_form_attached_documents_agreement_fact" content="modal_form_attached_documents_agreement_fact_content" />
<x-modal id="modal_form_documents_validation_agreement_fact" content="modal_form_documents_validation_agreement_fact_content" />
<script>
    var af_id = $("#hid_af_id").val();
    var dtUrl = '/api/sdt/agreement_fact/' + af_id; 
    var dt_agreement_fact = $('#dt_agreement_fact');
    // begin first table
    dt_agreement_fact.DataTable({
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
            type: 'GET',
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
            render: function(data, type, full, meta) {
                return `
                            <label class="checkbox checkbox-single">
                                <input type="checkbox" value="" class="checkable"/>
                                <span></span>
                            </label>`;
            },
        }],
    });
    
    dt_agreement_fact.on('change', '.group-checkable', function() {
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
    
    dt_agreement_fact.on('change', 'tbody tr .checkbox', function() {
        $(this).parents('tr').toggleClass('active');
    });
    
    var _reload_dt_agreement_fact = function() {
        $('#dt_agreement_fact').DataTable().ajax.reload();
    }
function _formAgreementFact(agreement_id, af_id) {
        var modal_id = 'modal_form_attached_documents_agreement_fact';
        var modal_content_id = 'modal_form_attached_documents_agreement_fact_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);

        $.ajax({
            url: '/form/upload/agreement/attached/documents/' + agreement_id + '/' + af_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
}



function _validationagreementfact(agreement_id, af_id) {
        var modal_id = 'modal_form_documents_validation_agreement_fact';
        var modal_content_id = 'modal_form_documents_validation_agreement_fact_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);

        $.ajax({
            url: '/form/validation/agreement/' + agreement_id + '/' + af_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
}

function _deleteAgreement(agreement_id, af_id) {

var successMsg = "Votre document a été supprimée.";
var errorMsg = "Votre document n\'a pas été supprimée.";
var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le document ?";
var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";

Swal.fire({
    title: swalConfirmTitle,
    text: swalConfirmText,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Oui"
}).then(function(result) {  
    if (result.value) {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/api/delete/docs/agreement/' + agreement_id + '/' + af_id,
                    type: 'DELETE',
                    dataType: 'html',
                    success: function(html, status) {
                        alert("Document supprimé avec succés");
                        $('#dt_agreement_fact').DataTable().ajax.reload();
                    },
                    error: function(result, status, error) {
                        alert("Désolé votre document n'a pas été supprimé !!");
                    }
                });
            }
    });
}
</script>
@endif
@if($block_id==10)
<div class="card card-custom card-fit card-border">
    <div class="card-header">
        <div class="card-toolbar">
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" data-original-title="Rafraîchir" onclick="_reload_dt_invoice_fact()"><i
                    class="flaticon-refresh"></i></button>
                    <input type="hidden" id="hid_af_id" value="<?= $af_id ?>">

            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin: Datatable-->
        <table class="table table-sm table-bordered" id="dt_invoice_fact">
            <thead class="thead-light">
                <tr>
                    <th></th>
                    <th>N# facture</th>
                    <th>Intervenant sur facture</th>
                    <th>Status</th>
                    <th>Dates</th>
                    <th>Montant</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<x-modal id="modal_form_attached_documents_invoice_fact" content="modal_form_attached_documents_invoice_fact_content" />
<x-modal id="modal_form_documents_validation_invoice_fact" content="modal_form_documents_validation_invoice_fact_content" />
<script>
    var af_id = $("#hid_af_id").val();
    var dtUrl = '/api/sdt/invoice_fact/' + af_id; 
    var dt_invoice_fact = $('#dt_invoice_fact');
    // begin first table
    dt_invoice_fact.DataTable({
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
            type: 'GET',
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
            render: function(data, type, full, meta) {
                return `
                            <label class="checkbox checkbox-single">
                                <input type="checkbox" value="" class="checkable"/>
                                <span></span>
                            </label>`;
            },
        }],
    });
    
    dt_invoice_fact.on('change', '.group-checkable', function() {
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
    
    dt_invoice_fact.on('change', 'tbody tr .checkbox', function() {
        $(this).parents('tr').toggleClass('active');
    });
    
    var _reload_dt_invoice_fact = function() {
        $('#dt_invoice_fact').DataTable().ajax.reload();
    }


    
function _formInvoiceFact(invoice_id, af_id) {
        var modal_id = 'modal_form_attached_documents_invoice_fact';
        var modal_content_id = 'modal_form_attached_documents_invoice_fact_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);

        $.ajax({
            url: '/form/upload/invoice/attached/documents/' + invoice_id + '/' + af_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
}



function _validationinvoicefact(invoice_id, af_id) {
        var modal_id = 'modal_form_documents_validation_invoice_fact';
        var modal_content_id = 'modal_form_documents_validation_invoice_fact_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);

        $.ajax({
            url: '/form/validation/invoice/' + invoice_id + '/' + af_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
}

function _deleteInvoice(invoice_id, af_id) {

var successMsg = "Votre document a été supprimée.";
var errorMsg = "Votre document n\'a pas été supprimée.";
var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le document ?";
var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";

Swal.fire({
    title: swalConfirmTitle,
    text: swalConfirmText,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Oui"
}).then(function(result) {  
    if (result.value) {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/api/delete/docs/invoice/' + invoice_id + '/' + af_id,
                    type: 'DELETE',
                    dataType: 'html',
                    success: function(html, status) {
                        alert("Document supprimé avec succés");
                        $('#dt_invoice_fact').DataTable().ajax.reload();
                    },
                    error: function(result, status, error) {
                        alert("Désolé votre document n'a pas été supprimé !!");
                    }
                });
            }
    });
}
</script>
@endif