@php
$modal_title=($row)?'Edition inscription':'Ajouter une inscription formateurs';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_enrollment_intervenants_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="formEnrollmentIntervenants" class="form">
    <div class="modal-body" id="modal_form_enrollment_intervenants_body">
        <div data-scroll="true" data-height="400">
            @csrf
            @if($row)
            <!-- Infos date : begin -->
            <div class="form-group row">
                <div class="col-lg-12">
                    @if($createdAt)<span class="label label-inline label-outline-info mr-2">Crée le :
                        {{ $createdAt }}</span>@endif
                    @if($updatedAt)<span class="label label-inline label-outline-info mr-2">Modifié le :
                        {{ $updatedAt }}</span>@endif
                </div>
                @if($deletedAt)
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-custom alert-outline-info fade show mb-0" role="alert">
                        <div class="alert-icon"><i class="flaticon-warning"></i></div>
                        <div class="alert-text">Archivé le : {{ $deletedAt }}</div>
                    </div>
                </div>
                @endif
            </div>
            <!-- Infos date : end -->
            <div class="separator separator-dashed my-5"></div>
            @endif
            <input type="hidden" name="af_id" id="INPUT_HIDDEN_AF_ID" value="{{ ($row)?$row->af_id:$af_id }}" />
            <input type="hidden" name="id" id="INPUT_ENROLLMENT_ID" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="enrollment_type" value="{{ ($row)?$row->enrollment_type:'F' }}" />
            <!-- begin::form -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <input type="hidden" id="selected_entitie_id" value="{{ ($row)?$row->entitie_id:0 }}">
                        <label>Formateur @if(!$row)<span class="text-danger">*</span>@endif <span id="LOADER_ENTITIES"></span></label>
                        <div @if($row)class="d-none" @endif>
                            <select class="form-control select2" id="entitiesFormersSelect" name="entitie_id" required>
                                <option value="">Sélectionnez un formateur</option>
                            </select>
                        </div>

                        @if($row)
                        <p class="text-primary">
                            {{ $row->entity->name.' - '.$row->entity->ref.' - '.$row->entity->entity_type }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <p>Liste des intervenants : <span id="LOADER_CONTACTS"></span></p>

                    <!--begin: Datatable-->
                    <table class="table table-sm table-bordered table-checkable" id="dt_intervenants_for_select">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Type d'intervention</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <!--end: Datatable-->
                </div>
            </div>

            <!--end:: form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form  : end -->
<!-- <script src="{{ asset('custom/js/form-enrollment.js?v=1') }}"></script> -->
<script>
$(document).ready(function() {
    $('.select2').select2();
});
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formEnrollmentIntervenants").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        var formData = $(form).serializeArray();
        console.log(formData);
        console.log(form);

        $.ajax({
            type: 'POST',
            url: '/form/enrollment/intervenants',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_enrollment_intervenants').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE');
                if ($.fn.DataTable.isDataTable('#dt_intervenants_members')) {
                    _reload_dt_intervenants_members();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
var dt_intervenants_for_select = $('#dt_intervenants_for_select');
// begin first table
dt_intervenants_for_select.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    searching: false,
    paging: false,
    ordering: false,
    info: false,
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
dt_intervenants_for_select.on('change', '.group-checkable', function() {
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
dt_intervenants_for_select.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var selected_entitie_id = $('#selected_entitie_id').val();
_loadDatasEntitiesForSelectEnrollmentsOptions('entitiesFormersSelect',0,0,1, selected_entitie_id);
//$('#entitiesFormersSelect').select2();

$('#entitiesFormersSelect').on('change', function() {
    _loadContacts();
});

var _loadContacts = function() {
    var entity_id = $('#entitiesFormersSelect').val();
    var enrollment_id = $('#INPUT_ENROLLMENT_ID').val();
    if (entity_id > 0) {
        $('#LOADER_CONTACTS,#LOADER_ENTITIES').html('<i class="fa fa-spinner fa-spin text-primary"></div>');

        var table = 'dt_intervenants_for_select';
        $.ajax({
            type: "POST",
            dataType: 'json',
            data: {
                pagination: {
                    perpage: 50,
                }
            },
            url: '/api/sdt/select/contacts/' + entity_id + '/' + enrollment_id+'/1',
            success: function(response) {
                if (response.data.length == 0) {
                    $('#' + table).dataTable().fnClearTable();
                    $('#LOADER_CONTACTS,#LOADER_ENTITIES').html('');
                    return 0;
                }
                $('#' + table).dataTable().fnClearTable();
                $("#" + table).dataTable().fnAddData(response.data, true);
                $('#LOADER_CONTACTS,#LOADER_ENTITIES').html('');
            },
            error: function() {
                $('#' + table).dataTable().fnClearTable();
            }
        }).done(function(data) {

        });

    }
    return false;
}
</script>
