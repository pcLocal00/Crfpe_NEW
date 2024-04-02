@php
$modal_title=($row)?'Edition inscription':'Ajouter une inscription';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_enrollment_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<div class="modal-body" id="modal_form_enrollment_body">
    <div data-scroll="true" data-height="600">

        <form id="formEnrollment" class="form">
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
            <input type="hidden" id="INPUT_HIDDEN_ACTION_TYPE">
            <input type="hidden" name="af_id" id="INPUT_HIDDEN_AF_ID" value="{{ ($row)?$row->af_id:$af_id }}" />
            <input type="hidden" name="id" id="INPUT_ENROLLMENT_ID" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="enrollment_type" value="{{ ($row)?$row->enrollment_type:'S' }}" />
            <!-- begin::form -->
                        
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <input type="hidden" id="selected_entitie_id" value="{{ ($row)?$row->entitie_id:0 }}">
                        <label>Client @if(!$row)<span class="text-danger">*</span>@endif <span
                                id="LOADER_ENTITIES"></span></label>
                        <div @if($row)class="d-none" @endif>
                            <select class="form-control select2" id="entitiesSelect" name="entitie_id" required>
                                <option value="">Sélectionnez un client</option>
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
                    <div class="form-group">
                        <label>Tarif <span class="text-danger">*</span> <span id="LOADER_PRICES"></span></label>
                        <input type="hidden" id="selected_price_id" value="{{ $price_id }}">
                        <select class="form-control select2" id="pricesSelect" name="price_id" required>
                            <option value="">Sélectionnez un tarif</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="nb_participants">Nombre de participants au total</label>
                        <input id="INPUT_NB_PARTICIPANTS" class="form-control " type="number" name="nb_participants"
                            min="1" value="{{ ($row)?$row->nb_participants:1 }}" id="nb_participants" />
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-lg-12">
                    <p>Liste des contacts : <span id="LOADER_CONTACTS"></span></p>
                    <!--begin: card unknown members-->
                    <div id="CARD_UNKNOWN_MEMBERS"
                        class="card card-custom card-border @if($row)@if($row->entity->entity_type=='P')d-none @endif @endif">
                        <div id="CARD_UNKNOWN_MEMBERS_BODY" class="card-body">
                            <div class="form-group m-0">
                                <label for="">Nombre de stagiaires inconues : </label>
                                <input class="form-control " type="number" name="nb_unknown_members" min="0"
                                    value="{{ $nb_unknown_members }}" />
                                <span class="form-text text-muted">Si vous souhaiter ajouter des stagiaires
                                    inconnues</span>
                            </div>
                        </div>
                    </div>
                    <!--end: card unknown members-->

                    <!--begin: Datatable-->
                    <table class="table table-sm table-bordered table-checkable" id="dt_contacts_for_select">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Nom</th>
                                <th>Prénom</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <!--end: Datatable-->
                </div>
            </div>     
        </form>

    </div>
    <!--end:: form-->
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i></button>
    <button type="button" onclick="_submit_form('SAVE');" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Enregistrer <span id="BTN_SAVE_ENROLLMENT"></span></button>
    <button type="button" onclick="_submit_form('SAVE_AND_CONTINUE');" class="btn btn-sm btn-info"><i class="fa fa-check"></i> Enregistrer et continuer <span id="BTN_SAVE_AND_CONTINUE_ENROLLMENT"></span></button>
</div>

<!-- Form  : end -->
<!-- <script src="{{ asset('custom/js/form-enrollment.js?v=1') }}"></script> -->
<script>

function _submit_form(action_type){
    $('#INPUT_HIDDEN_ACTION_TYPE').val(action_type);
    $('#formEnrollment').submit();
}

ClassicEditor.create(document.querySelector("#description"))
    .then(editor => {})
    .catch(error => {});
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formEnrollment").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        var action_type=$('#INPUT_HIDDEN_ACTION_TYPE').val();
        _showLoader('BTN_'+action_type+'_ENROLLMENT');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/enrollment',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_'+action_type+'_ENROLLMENT');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    //var action_type=$('#INPUT_HIDDEN_ACTION_TYPE').val();
                    if(action_type=='SAVE'){
                        $('#modal_form_enrollment').modal('hide');
                    }
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_'+action_type+'_ENROLLMENT');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_'+action_type+'_ENROLLMENT');
                if ($.fn.DataTable.isDataTable('#dt_enrollments')) {
                    _reload_dt_enrollments();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
var dt_contacts_for_select = $('#dt_contacts_for_select');
// begin first table
dt_contacts_for_select.DataTable({
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
dt_contacts_for_select.on('change', '.group-checkable', function() {
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
dt_contacts_for_select.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var selected_entitie_id = $('#selected_entitie_id').val();
//_showLoader('LOADER_ENTITIES');

$('#entitiesSelect').select2();

//_loadDatasEntitiesForSelectEnrollmentsOptions('entitiesSelect', 0, entity_type, 0, selected_entitie_id);
_loadDatasEntitiesForSelectUpdateOptions('entitiesSelect',selected_entitie_id);

function _loadDatasEntitiesForSelectUpdateOptions(select_id,selected_value = 0) {
  var entity_type=0;
    if($('#AF_DEVICE_TYPE').val()=='INTRA'){
        entity_type='S';
    }
 _showLoader('LOADER_ENTITIES');
  $.ajax({
      url: '/api/select/options/entities/by_type/'+entity_type+'/0',
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
      if (selected_value != 0 && selected_value != '') {
          $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
      }
      _hideLoader('LOADER_ENTITIES');
  });
}

$('#entitiesSelect').on('change', function() {
    _updateFormOnChangeClient();
    _loadPrices();
    _loadContacts();
});
var _updateFormOnChangeClient = function() {
    var entitie_id = $('#entitiesSelect').val();
    if (entitie_id > 0) {
        $.ajax({
            url: '/get/entitie/' + entitie_id,
            type: "GET",
            dataType: "JSON",
            success: function(res, status) {
                if (res.entity_type == 'P') {
                    $('#INPUT_NB_PARTICIPANTS').val(1);
                    $('#INPUT_NB_PARTICIPANTS').prop('readonly', true);
                    $("#CARD_UNKNOWN_MEMBERS").addClass("d-none");
                } else if (res.entity_type == 'S') {
                    $('#INPUT_NB_PARTICIPANTS').prop('readonly', false);
                    $("#CARD_UNKNOWN_MEMBERS").removeClass("d-none");
                }
            }
        });
    }
}
var _loadPrices = function() {
    $('#LOADER_PRICES,#LOADER_ENTITIES').html('<i class="fa fa-spinner fa-spin text-primary"></div>');
    var af_id = $('#INPUT_HIDDEN_AF_ID').val();
    var entitie_id = $('#entitiesSelect').val();
    var selected_price_id = $('#selected_price_id').val();
    _loadDatasPricesForSelectOptions('pricesSelect', af_id, entitie_id, selected_price_id);
    $('#LOADER_PRICES,#LOADER_ENTITIES').html('');
}
var _loadContacts = function() {
    var dt_contacts_for_select = $('#dt_contacts_for_select');
    var entity_id = ($('#entitiesSelect').val()) ? $('#entitiesSelect').val() : $('#selected_entitie_id').val();
    var enrollment_id = $('#INPUT_ENROLLMENT_ID').val();
    if (entity_id > 0) {
        $('#LOADER_CONTACTS,#LOADER_ENTITIES').html('<i class="fa fa-spinner fa-spin text-primary"></div>');

        var table = 'dt_contacts_for_select';
        $.ajax({
            type: "POST",
            dataType: 'json',
            data: {
                pagination: {
                    perpage: 50,
                }
            },
            url: '/api/sdt/select/contacts/' + entity_id + '/' + enrollment_id + '/0',
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
var af_id = $('#INPUT_HIDDEN_AF_ID').val();
var entitie_id = $('#selected_entitie_id').val();
var selected_price_id = $('#selected_price_id').val();
_loadDatasPricesForSelectOptions('pricesSelect', af_id, entitie_id, selected_price_id);
$('#pricesSelect').select2();
_loadContacts();
</script>