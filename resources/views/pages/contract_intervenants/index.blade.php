{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!--begin::Card-->
<div class="card card-custom">
    <div class="card-header flex-wrap border-0 ">
        <div class="card-title">
            <h3 class="card-label">Contrats intervenants</h3>
        </div>
        <div class="card-toolbar">
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title=""
                onclick="_reload_dt_contracts()" data-original-title="Rafraîchir"><i
                    class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title=""
                onclick="_formContractFormer(0,0)" data-original-title="Générer un nouveau contrat"><i
                    class="flaticon2-add-1"></i></button>
            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        
        <p><a id="BTN_SCROLL_TO_WARNINGS" class="btn btn-sm btn-icon btn-light-warning" href="javascript:void(0)" data-toggle="tooltip" data-original-title="Cliquer pour consulter la liste des intervenants sans contrat et avec des séances planifiés"><i class="flaticon2-warning"></i></a></p>
        
        <div class="accordion accordion-solid accordion-toggle-plus mb-4 mt-4" id="accordionFormersContracts">
            <div class="card">
                <div class="card-header" id="headingOne4">
                    <div class="card-title" data-toggle="collapse" data-target="#collapseaccordionFormersContracts">
                        <i class="flaticon-list"></i> La liste des contrats intervenants
                    </div>
                </div>
                <div id="collapseaccordionFormersContracts" class="collapse show" data-parent="#collapseaccordionFormersContracts">
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
            </div>
        </div>

        <div class="accordion accordion-solid accordion-toggle-plus mb-4 mt-4" id="accordionFormersWithoutContracts">
            <div class="card">
                <div class="card-header" id="headingOne5">
                    <div class="card-title" data-toggle="collapse" data-target="#collapseFormersWithoutContracts">
                        <i class="flaticon2-warning"></i> La liste des intervenants sans contrat et avec des séances planifiés
                    </div>
                </div>
                <div id="collapseFormersWithoutContracts" class="collapse show" data-parent="#collapseFormersWithoutContracts">
                    <div class="card-body">
                        <!--begin: Datatable-->
                        <table class="table table-sm table-bordered" id="dt_formers_without_contracts">
                            <thead class="thead-light">
                                <tr>
                                    {{-- <th>member_id</th>
                                    <th>contact_id</th> --}}
                                    <th>Intervenant</th>
                                    <th>AF</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <!--end: Datatable-->
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</div>
<!--end::Card-->
<x-modal id="modal_schedule_details" content="modal_schedule_details_content" />
<x-modal id="modal_form_contract" content="modal_form_contract_content" />
<x-modal id="modal_form_pointage" content="modal_form_pointage_content" />
<x-modal id="modal_form_attached_documents" content="modal_form_attached_documents_content" />
@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('custom/plugins/jstree/dist/themes/default/style.min.css') }}" rel="stylesheet" type="text/css" />
<style>
.jstree-anchor>.jstree-checkbox-disabled {
    display: none;
}

.jstree-default .jstree-anchor {
    height: 100% !important;
}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css" integrity="sha512-nNlU0WK2QfKsuEmdcTwkeh+lhGs6uyOxuUs+n+0oXSYDok5qy0EI0lt01ZynHq6+p/tbgpZ7P+yUb+r71wqdXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>
<script src="{{ asset('custom/plugins/jstree/dist/jstree.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js" integrity="sha512-uURl+ZXMBrF4AwGaWmEetzrd+J5/8NRkWAvJx5sbPSSuOb0bZLqf+tOzniObO00BjHa/dD7gub9oCGMLPQHtQA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=2') }}"></script>
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$("#BTN_SCROLL_TO_WARNINGS").click(function() {
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#accordionFormersWithoutContracts").offset().top
    }, 2000);
});
$('[data-toggle="tooltip"]').tooltip();
var dtUrlContracts = '/api/sdt/contracts/0';
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
                toastr.success("Merci de consulter votre boite email afin de signer le contrat");
            } else {
                toastr.error("Erreur");
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

var dtUrlFomrmersWithoutContratcs = '/api/sdt/formerswithoutcontracts';
var dt_formers_without_contracts = $('#dt_formers_without_contracts');
// begin first table
dt_formers_without_contracts.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    searching: true,
    paging: true,
    ordering: false,
    info: true,
    ajax: {
        url: dtUrlFomrmersWithoutContratcs,
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
        },
    },
    lengthMenu: [5, 10, 25, 50],
    pageLength: 25,
});
function _modalAttachedDocsContract(contract_id,af_id){
    var modal_id = 'modal_form_attached_documents';
    var modal_content_id = 'modal_form_attached_documents_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    //var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
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
    // window.location.href="/pdf/contract/" + contract_id + "/2";
    // alert(1)

    $.ajax({
        url: '/sendAgreements',
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
          
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}
</script>
@endsection