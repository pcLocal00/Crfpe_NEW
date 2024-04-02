@php
$modal_title=($row)?'Edition convocation':'Création Convocation';

$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
$dtNow = Carbon\Carbon::now();
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_convocation_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->

<div class="modal-body" id="modal_form_convocation_body">
    <div data-scroll="true" data-height="600">

        @if($row)
        <!-- Infos date : begin --
        <div class="form-group row">
            <div class="col-lg-12">
                @if($createdAt)<span class="label label-inline label-outline-info mr-2">Crée le :
                    {{ $createdAt }}</span>@endif
                @if($updatedAt)<span class="label label-inline label-outline-info mr-2">Modifié le :
                    {{ $updatedAt }}</span>@endif
            </div>
           
        </div>
        <!-- Infos date : end 
        <div class="separator separator-dashed my-5"></div>-->
        @endif
        <form id="formConvocation" class="form">
            @csrf
            <input type="hidden" id="INPUT_HIDDEN_CONVOCATION_ID" name="id" value="{{ ($row)?$row->id:0 }}" />
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>AF <span id="LOADER_AFS"></span><span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_af_id" value="{{ ($row)?$row->af_id:$default_af_id }}">
                        <input type="hidden" id="default_af_id" value="{{ $default_af_id }}">
                        <select id="afsSelectConvocation" name="af_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mb-12">
                <div class="col-lg-6 mb-lg-0 mb-6">
                    <label>Sessions :</label>
                    <select onchange="createConvocationdataTableChangeReset(this)" class="form-control" id="sessionsSelectFilter" name="session_id" style="width:100%;">
                        <option value="0">Tous les sessions</option>
                        @foreach($session_data as $m)
                            <option value="{{ $m->id }}">{{ $m->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 mb-lg-0 mb-3">
                    <label>Session date :</label>
                    <select onchange="createConvocationdataTableChangeReset(this)" class="form-control" id="dateSelectFilter" name="session_date_id" style="width:100%;">
                        <option value="0">Tous les Session date</option> 
                    </select>
                </div> 
                <div class="col-lg-3 mb-lg-0 mb-3">
                    <label>Seances :</label>
                    <select onchange="createConvocationdataTableChangeReset(this)" class="form-control" id="seancesSelectFilter" name="seance_id" style="width:100%;">
                        <option value="0">Tous les Seances</option> 
                    </select>
                </div>
            </div>
    <!--    <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Client <span id="LOADER_CLIENTS"></span> <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_entity_id" value=" ($row)?$row->entitie_id:0 }}">
                        <select id="entitiesSelect" name="entitie_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div> -->
            @if($row)@endif
            <!-- Items -->
            <div class="row" style="">
                <div class="col-lg-12">
                    <p>Liste des stagiaires : <span id="LOADER_CONTACTS"></span></p>
                    <table class="table table-sm table-bordered table-checkable" style="width:100%;"
                        id="dt_intervenants_members">
                        <thead class="thead-light">
                            <tr>
                                <th></th>
                                <th>Prénom</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Etat Planning</th>
                                <th>Nb heure</th>
                                <th>Cout</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formConvocation').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i> Générer <span id="BTN_SAVE_CONVOCATION"></span></button>
</div>

<!-- Form param : end -->
<!-- <script src="{{ asset('custom/js/form-estimate.js?v=1') }}"></script> -->
<script>
$(document).ready(function() {
    $('.select2').select2();
});
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});

var selected_af_id = $('#selected_af_id').val();
var default_af_id = $('#default_af_id').val();
_loadAfsForSelectOptions('afsSelectConvocation', selected_af_id, default_af_id);

function _loadAfsForSelectOptions(select_id, selected_af_id, default_af_id) {
    _showLoader('LOADER_AFS');
    $.ajax({
        url: '/api/select/options/afs/' + default_af_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_af_id != 0 && selected_af_id != '') {
            $('#' + select_id + ' option[value="' + selected_af_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_AFS');
    //    refreshAgreementsSelect();
        refreshEntitiesSelect();
    });
}
$('#afsSelectConvocation').on('change', function() {
    af_id = $('#afsSelectConvocation').val();
    if (af_id > 0) {
        
        dtUrlIntervenantsMembers = '/api/sdt/membersconvocation/' + af_id;//alert(dtUrlIntervenantsMembers);
    //    refreshEntitiesSelect();
    //    $('#dt_intervenants_members').DataTable().ajax.reload(true);
    tableIm.DataTable().ajax.url( dtUrlIntervenantsMembers ).load();
    }
});

/*** DATATABLE RESET ***/
 var createConvocationdataTableChangeReset = element =>{
    tableIm = $('#dt_intervenants_members');
    af_id = $('#afsSelectConvocation').val();
    data = {
        session_id: ($('#sessionsSelectFilter').val())?$('#sessionsSelectFilter').val(): 0,
        session_date_id: $('#dateSelectFilter').val(),
        seance_id: $('#seancesSelectFilter').val(),
        pagination: {
            perpage: 50,
        }
    }
    console.log(data);
    // tableIm.DataTable().ajax.url( '/api/sdt/membersconvocation/' + af_id ).load();
    // tableIm.DataTable({
    //             // "processing": true,
    //             // "serverSide": true,  
    //             "ajax": {
    //                 "url" : '/api/sdt/membersconvocation/' + af_id,
    //              "type": "POST",
    //              "data" : data
        
    //             }
    //         });
    // tableIm.load();
    $.ajax({
        type: "POST",
        dataType: 'json',
        data: data,
        url: '/api/sdt/membersconvocation/' + af_id,
        success: function(response) {
            if (response.data.length == 0) {
                tableIm.dataTable().fnClearTable();
                return 0;
            }
            tableIm.dataTable().fnClearTable();
            tableIm.dataTable().fnAddData(response.data, true);
        },
        error: function() {
            tableIm.dataTable().fnClearTable();
        }
    }).done(function(data) {
        // KTApp.unblockPage();
    });
 }
 /*** /DATATABLE RESET ***/

// $('#sessionsSelectFilter').on('change', function() {
//     session_id = $('#sessionsSelectFilter').val();
//     if (session_id > 0) {
        
//         dtUrlIntervenantsMembers = '/api/sdt/seancesconvocation/' + session_id;//alert(dtUrlIntervenantsMembers);
//     //    refreshEntitiesSelect();
//     //    $('#dt_intervenants_members').DataTable().ajax.reload(true);
//     tableIm.DataTable().ajax.url( dtUrlIntervenantsMembers ).load();
//     }
// });
/** GENERATE DATE FILTRE **/
    $('#sessionsSelectFilter').on('change', function() {
        session_id = $('#sessionsSelectFilter').val();
        if (session_id > 0) {
            
        //    refreshEntitiesSelect();
        //    $('#dt_intervenants_members').DataTable().ajax.reload(true);
        
        $.ajax({
            url: '/api/sdt/getdatesconvocation/' + session_id,
            type: 'POST',
            dataType: 'json',
            success: function(data, status) {
                $('#dateSelectFilter').empty();
                $('#seancesSelectFilter').empty();
                $('#seancesSelectFilter').append('<option value="0">Tous les Seances</option> ');
                $('#dateSelectFilter').append('<option value="0">Tous les Session date</option> ');
                data.sessiondates.forEach(sessionDate => {
                    $('#dateSelectFilter').append('<option value="'+sessionDate.id+'">'+sessionDate.planning_date+'</option> ');
                });
            }
        });
        }
    });
/** /GENERATE DATE FILTRE **/

/** GENERATE SEANCE FILTRE **/
    $('#dateSelectFilter').on('change', function() {
        session_date_id = $('#dateSelectFilter').val();
        if (session_id > 0) {
            
        //    refreshEntitiesSelect();
        //    $('#dt_intervenants_members').DataTable().ajax.reload(true);
        
        $.ajax({
            url: '/api/sdt/getseancesconvocation/' + session_date_id,
            type: 'POST',
            dataType: 'json',
            success: function(data, status) {
                $('#seancesSelectFilter').empty();
                $('#seancesSelectFilter').append('<option value="0">Tous les Seances</option> ');
                data.seances.forEach(seance => {
                    $('#seancesSelectFilter').append('<option value="'+seance.id+'">'+seance.start_hour+' - '+seance.end_hour+'</option> ');
                });
            }
        });
        }
    });
/** /GENERATE SEANCE FILTRE **/
var af_id = selected_af_id;//$("input[name='id']").val();
var dtUrlIntervenantsMembers = '/api/sdt/membersconvocation/' + af_id;
    var tableIm = $('#dt_intervenants_members');

    tableIm.DataTable({
        language: {
            url: "/custom/plugins/datatable/fr.json"
        },
        responsive: true,
        processing: true,
        paging: true,
        ordering: false,
        ajax: {
            url: dtUrlIntervenantsMembers,
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
        /*    render: function(data, type, full, meta) {
                return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
            },*/
        }],
    });
    
    tableIm.on('change', '.group-checkable', function() {
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

    tableIm.on('change', 'tbody tr .checkbox', function() {
        $(this).parents('tr').toggleClass('active');
    });
    var _reload_dt_intervenants_members = function() {
        $('#dt_intervenants_members').DataTable().ajax.reload();
    }
    /* END TABLE */
    var _formEnrollmentIntervenants = function(enrollment_id) {
        var af_id = $("input[name='id']").val();
        var modal_id = 'modal_form_enrollment_intervenants';
        var modal_content_id = 'modal_form_enrollment_intervenants_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/enrollmentintervenants/' + af_id + '/' + enrollment_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
    }

function refreshEntitiesSelect() {
    var af_id = $('#afsSelectConvocation').val();
    if (af_id > 0) {
        var selected_entity_id = $('#selected_entity_id').val();
    //    _loadEntitiesByAgreementForSelectOptions('entitiesSelect', af_id, selected_entity_id);
    }
}
$("#formConvocation").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_CONVOCATION');
        var TableauIdProcess = new Array();
        var j = 0;
        $('#dt_intervenants_members input[class="checkable"]').each(function(){
            var checked = jQuery(this).is(":checked");
            if(checked){
                TableauIdProcess[j] = jQuery(this).val();
                j++;
            }
        });
        $.ajax({
            type: 'POST',
            url: '/form/convocation',
            data: {
                af_id: af_id,
                ids_members: TableauIdProcess,
            },
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_CONVOCATION');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_convocation').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_CONVOCATION');
                _showResponseMessage('error', 'Veuillez sélectionner les stagiaires ...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_CONVOCATION');
                if ($.fn.DataTable.isDataTable('#dt_convocations')) {
                    _reload_dt_convocations();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});

</script>