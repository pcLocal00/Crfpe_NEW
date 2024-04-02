{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!--begin::Card-->
<div class="card card-custom">
    <div class="card-header flex-wrap border-0 ">
        <div class="card-title">
            <h3 class="card-label">Avoirs
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
                            <a href="javascript:void(0)" onclick="_exportavoirs(0)" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-excel-o"></i>
                                </span>
                                <span class="navi-text">Excel</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_avoirs()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-light-success ml-1" data-toggle="tooltip" title="Générer et télécharger le fichier PNM pour Sage"
                onclick="_generatePnmFile()"><i class="flaticon2-file-1"></i> Générer le fichier PNM</button>
            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin::filter-->
        <x-filter-form type="Avoirs" />
        <!--end::filter-->
        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_avoirs">
            <thead>
                <tr>
                    <th></th>
                    <th>N°</th>
                    <th>Date</th>
                    <th>Facture</th>
                    <th>AF</th>
                    <th>Client</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<!--end::Card-->
{{-- <x-modal id="modal_form_avoir" content="modal_form_avoir_content" /> --}}
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
    
    var dtUrl = '/api/sdt/avoirs/0';
    var table = $('#dt_avoirs');
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

    var _reload_dt_avoirs = function() {
        $('#dt_avoirs').DataTable().ajax.reload();
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
    var form_id = 'formFilterAvoirs';
    $("#" + form_id).submit(function(event) {
        event.preventDefault();
        KTApp.blockPage();
        var formData = $(this).serializeArray();
        var table = 'dt_avoirs';
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
    _reload_dt_avoirs();
}

function _generatePnmFile() {
    var avoir_params = '?avoirs=';
    var selections = $('table').find('td:first-child .checkable:checked');
    $(selections).each(function() {
        var avoir = $(this);
        avoir_params += (avoir.val() + ',');
    });
    
    avoir_params = avoir_params.slice(0, -1);
    console.log(avoir_params);
    $.ajax({
        url: '/form/generate/avoirs/pnm/'+ avoir_params,
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
            _reload_dt_avoirs();
        }
    });
}

function _exportavoirs(id){
		var TableauIdProcess = new Array();
		var j = 0;
		if(id>0){
			TableauIdProcess[0]=id;
		}else{	
            $('#dt_avoirs input[class="checkable"]').each(function(){
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
                text: 'Veuillez sélectionner un ou plusieurs avoirs!',
            })
    	}else{
            KTApp.blockPage();
            $.ajax({
                type: 'POST',
                url: '/api/export/avoirs',
                data: {
                    ids_avoirs: TableauIdProcess,
                },
                cache: false,
                xhrFields:{
                    responseType: 'blob'
                },
                success: function(data) {
                    const time = Date.now();
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(data);
                    link.download = 'avoirs-'+time+'.xlsx';
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
{{-- end Scripts Section --}}
@endsection