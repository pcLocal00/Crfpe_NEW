{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

<!--begin::Card-->
<div class="card card-custom">
    <div class="card-header flex-wrap border-0 ">
        <div class="card-title">
            <h3 class="card-label">Gestion des produits de formation
                <!-- <span class="d-block text-muted pt-2 font-size-sm">Sorting &amp; pagination remote datasource</span> -->
            </h3>
        </div>
        <div class="card-toolbar">
            <!--begin::Dropdown-->
            <!-- <div class="dropdown dropdown-inline mr-2">
                <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="la la-download"></i></button>

                <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">

                    <ul class="navi flex-column navi-hover py-2">
                        <li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">Choose
                            an option:</li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-print"></i>
                                </span>
                                <span class="navi-text">Print</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-copy"></i>
                                </span>
                                <span class="navi-text">Copy</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-excel-o"></i>
                                </span>
                                <span class="navi-text">Excel</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-text-o"></i>
                                </span>
                                <span class="navi-text">CSV</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-pdf-o"></i>
                                </span>
                                <span class="navi-text">PDF</span>
                            </a>
                        </li>
                    </ul>

                </div>

            </div> -->
            <!--end::Dropdown-->
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_formation()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                title="Ajouter une nouvelle formation" onclick="_formFormation(0,0,1)"><i class="flaticon2-add-1"></i></button>
            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin: Search Form-->

        <x-modal id="modal_form_formation" content="modal_form_formation_content" />

        <x-filter-form type="Formations" />

        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="kt_dt_formations">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produits de formation</th>
                    <th>Type / Statut / Etat</th>
                    <th>Dates</th>
                    <th>Informations</th>
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

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('custom/plugins/jstree/dist/themes/default/style.min.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>
<script src="{{ asset('custom/plugins/jstree/dist/jstree.min.js') }}"></script>

{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=1') }}"></script>
{{-- <script src="{{ asset('custom/js/list-formation.js?v=3') }}"></script> --}}
<script>
$(document).ready( function () {    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var dt_formation = $('#kt_dt_formations');
    var dtUrl = '/api/sdt/formations';
    // begin first dt_formation
    dt_formation.DataTable({
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
        },{
            targets: -1,
            title: 'Actions',
            orderable: false,
            width: '140px',
        },{
            targets: 2,
            width: '140px',
        },{
            targets: 3,
            width: '130px',
        }
        ],
        lengthMenu: [5, 10, 25, 50],
        pageLength: 10,
    });

    dt_formation.on('change', '.group-checkable', function() {
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

    dt_formation.on('change', 'tbody tr .checkbox', function() {
        $(this).parents('tr').toggleClass('active');
    });
});

var _reload_dt_formation = function(){
    $('#kt_dt_formations').DataTable().ajax.reload();
}

var _viewFormation = function(formation_id) {
    window.location.href = "/view/formation/" + formation_id;
}
var _deleteFormation = function(formation_id) {
    var successMsg="Votre produit de formation a été supprimé.";
    var errorMsg="Votre produit de formation n\'a pas été supprimé.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le produit de formation?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimez-le!",
        cancelButtonText: "Annuler"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/delete/formation/"+ formation_id,
                type: "GET",
                dataType: "JSON",
                success: function(result, status) {
                    if(result.success){
                        _showResponseMessage("success",successMsg);
                    }else{
                        _showResponseMessage("error",errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error",errorMsg);
                },
                complete: function(result, status) {
                    _reload_dt_formation();
                    KTApp.unblockPage();
                }
            });
        }
    });
}
var _archiveFormation = function(formation_id) {
    var successMsg="Votre produit de formation a été archivé.";
    var errorMsg="Votre produit de formation n\'a pas été archivé.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir archiver le produit de formation?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, archivez-le!",
        cancelButtonText: "Annuler"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/archive/formation/"+ formation_id,
                type: "GET",
                dataType: "JSON",
                success: function(result, status) {
                    if(result.success){
                        _showResponseMessage("success",successMsg);
                    }else{
                        _showResponseMessage("error",errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error",errorMsg);
                },
                complete: function(result, status) {
                    _reload_dt_formation();
                    KTApp.unblockPage();
                }
            });
        }
    });
}

    function _formFormation(id_formation,mode,type=0) {
        //mode == 0, ajout ou edit depuis la page view formation produit parent
        //mode == 1, ajout edit

        //type == 0,edit
        //type == 1, ajout
        $('#INPUT_HIDDEN_EDIT_MODE').val(mode);
        var modal_id = 'modal_form_formation';
        var modal_content_id = 'modal_form_formation_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/formation/' + id_formation + '/' + type,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    }
_loadDatasForSelectOptions('statusSelect', 'PF_STATUS_FORMATION',0);
_loadDatasForSelectOptions('typesSelect', 'PF_TYPE_FORMATION',0);
_loadDatasForSelectOptions('statesSelect', 'PF_STATE_FORMATION',0);

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
var categories_tree = 'categories_tree';
$('#'+categories_tree).jstree({
    "core": {
        "multiple": true,
        "themes": {
            "responsive": true
        },
        'data': {
            'url': function(node) {
                return '/api/filter/catalogues';
            },
            'data': function(node) {
                return {
                    'parent': node.id
                };
            }
        },
    },
    "checkbox": {
        "three_state": false,
    },
    "plugins": ["state", "checkbox"]
});
//A la fin du chargement
$('#'+categories_tree).bind("ready.jstree", function() {
    initializeSelections();
}).jstree();

function initializeSelections() {
    var instance = $('#'+categories_tree).jstree(true);
    instance.deselect_all();
}
//submit form
var form_id = 'formFilterFormations';
$("#"+form_id).submit(function(event) {
    event.preventDefault();
    KTApp.blockPage();
    var formData = $(this).serializeArray();
    categories_ids = $("#"+categories_tree).jstree("get_selected");
    if(categories_ids){
        formData = formData.concat([
            {name: "filter_categories_ids", value: categories_ids},
        ]);
    }
    var table = 'kt_dt_formations';
    var dtUrl = '/api/sdt/formations';
    $.ajax({
        type: "POST",
        dataType: 'json',
        data: formData,
        url: dtUrl,
        success: function(response) {
            if (response.data.length == 0) {
                $('#'+table).dataTable().fnClearTable();
                return 0;
            }
            $('#'+table).dataTable().fnClearTable();
            $("#"+table).dataTable().fnAddData(response.data, true);
        },
        error: function() {
            $('#'+table).dataTable().fnClearTable();
        }
    }).done(function(data) {
        KTApp.unblockPage();
    });
    return false;
});
var _reset = function() {
    initializeSelections();
    _reload_dt_formation();
}
</script>
@endsection
