@php
$modal_title=($row)?'Edition devis':'Ajouter un devis';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
$dtNow = Carbon\Carbon::now();
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_estimate_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->

<div class="modal-body" id="modal_form_estimate_body">
    <div data-scroll="true" data-height="600">

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
        <form id="formEstimate" class="form">
            @csrf
            <input type="hidden" id="INPUT_HIDDEN_ESTIMATE_ID" name="id" value="{{ ($row)?$row->id:0 }}" />
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>AF <span id="LOADER_AFS"></span><span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_af_id" value="{{ ($row)?$row->af_id:$default_af_id }}">
                        <input type="hidden" id="default_af_id" value="{{ $default_af_id }}">
                        <select id="afsSelectEstimate" name="af_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Client <span id="LOADER_CLIENTS"></span> <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_entity_id" value="{{ ($row)?$row->entitie_id:0 }}">
                        <input type="hidden" id="default_entity_id" value="{{ $default_entity_id }}">
                        <select id="entitiesSelect" name="entitie_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Contact du devis <span id="LOADER_CONTACTS"></span> <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_contact_id" value="{{ ($row)?$row->contact_id:0 }}">
                        <select id="contactsSelect" name="contact_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <!--  <div class="col-lg-12"> -->
                    <div class="form-group">
                        <label>Date de devis <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $estimate_date =$dtNow->format('d/m/Y');
                            if($row && $row->estimate_date!=null){
                                $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->estimate_date);
                                $estimate_date = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="estimate_date"
                                id="estimate_date_datepicker" placeholder="Sélectionner une date"
                                value="{{ $estimate_date }}" autocomplete="off" required />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- </div> -->
                    <!--   <div class="col-lg-12"> -->
                    <div class="form-group">
                        <label>Valable jusqu'au <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $newDateTime = Carbon\Carbon::now()->addMonth();
                            $valid_until =$newDateTime->format('d/m/Y');
                            if($row && $row->valid_until!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->valid_until);
                            $valid_until = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="valid_until"
                                id="valid_until_datepicker" placeholder="Sélectionner une date"
                                value="{{ $valid_until }}" autocomplete="off" required />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- </div> -->
                </div>
                <div class="col-md-6">

                    <!-- <div class="col-lg-12"> -->
                    <div class="form-group">
                        <input type="hidden" id="selected_status" value="{{ ($row)?$row->status:'' }}">
                        <label>Statut <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" data-col-index="2" id="statusSelect" name="status"
                            required></select>
                    </div>
                    <!-- </div> -->
                    <!-- <div class="col-lg-12"> -->
                    <div class="form-group">
                        <input type="hidden" id="selected_tax_1" value="{{ ($row)?$row->tax_percentage:'' }}">
                        <label>Taxe 1</label>
                        <select id="taxesSelect" name="tax_percentage" class="form-control form-control-sm">
                            <option value="">--Pas de taxe----</option>
                        </select>
                    </div>
                    <!-- </div> -->
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea class="form-control form-control-sm" id="note" name="note"
                            rows="3">{{ ($row)?$row->note:'' }}</textarea>
                    </div>
                </div>
            </div>

            @if($row)
            <!-- Items -->
            <div class="form-group row">
                <div class="col-lg-12">
                    <div class="accordion  accordion-toggle-arrow" id="accordionEstimateItems">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseItems">
                                    <i class="flaticon-list"></i> Les élements du devis
                                </div>
                            </div>
                            <div id="collapseItems" class="collapse show" data-parent="#accordionEstimateItems">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">

                                            <button style="float:right;" type="button"
                                                class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                                                title="Remise" onclick="_formDiscount({{$row->id}})"><i
                                                    class="flaticon2-percentage"></i></button>

                                            <button style="float:right;" type="button"
                                                class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                                                title="Ajouter" onclick="_formEstimateItem(0,{{$row->id}})"><i
                                                    class="flaticon2-add-1"></i></button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12" id="BODY_CARD_ESTIMATE_ITEMS"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Items -->
            @endif
        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formEstimate').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_ESTIMATE"></span></button>
</div>

<!-- Form param : end -->
<!-- <script src="{{ asset('custom/js/form-estimate.js?v=1') }}"></script> -->
<script>
$(document).ready(function() {
    $('.select2').select2();
});
var selected_status = $('#selected_status').val();
_loadDatasForSelectOptions('statusSelect', 'ESTIMATE_STATUS', selected_status, 1);

$('#estimate_date_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
$('#valid_until_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});

var selected_af_id = $('#selected_af_id').val();
var default_af_id = $('#default_af_id').val();
_loadAfEstimateForSelectOptions('afsSelectEstimate', selected_af_id, default_af_id);

function _loadAfEstimateForSelectOptions(select_id, selected_af_id, default_af_id) {
    _showLoader('LOADER_AFS');
    $('#'+select_id).empty();     
    $.ajax({
        url: '/api/select/options/afs/' + default_af_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +"</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_af_id != 0 && selected_af_id != '') {
            $('#' + select_id + ' option[value="' + selected_af_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_AFS');
        refreshEntitySelect();
    });
}

function _loadEntitiesEstimateForSelectOptions(select_id, af_id, selected_entity_id, default_entity_id) {
    _showLoader('LOADER_CLIENTS');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/afentities/' + af_id + '/' + default_entity_id,
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
        if (selected_entity_id != 0 && selected_entity_id != '') {
            $('#' + select_id + ' option[value="' + selected_entity_id + '"]').attr('selected', 'selected');
        }
        _refreshSelectContacts();
        _hideLoader('LOADER_CLIENTS');
    });
}

$('#entitiesSelect').on('change', function() {
    _refreshSelectContacts();
});
function _refreshSelectContacts(){
    var entity_id=$('#entitiesSelect').val();
    var selected_contact_id=$('#selected_contact_id').val();
    _loadContactsSelectOptions('contactsSelect', entity_id, selected_contact_id);
}

$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formEstimate").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_ESTIMATE');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/estimate',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_ESTIMATE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    //$('#modal_form_estimate').modal('hide');
                    _formEstimate(result.estimate_id,result.af_id,result.entity_id);
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_ESTIMATE');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_ESTIMATE');
                if ($.fn.DataTable.isDataTable('#dt_estimates')) {
                    _reload_dt_estimates();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
$('#afsSelectEstimate').on('change', function() {
    var af_id = $('#afsSelectEstimate').val();
    if (af_id > 0) {
        _showLoader('LOADER_CLIENTS');
        var selected_entity_id = $('#selected_entity_id').val();
        var default_entity_id = $('#default_entity_id').val();
        _loadEntitiesEstimateForSelectOptions('entitiesSelect', af_id, selected_entity_id, default_entity_id);
    }
});



function refreshEntitySelect(){
    var estimate_id = $('#INPUT_HIDDEN_ESTIMATE_ID').val();
    var action_f_id= $('#selected_af_id').val();
    var selected_entity_id = $('#selected_entity_id').val();
    var default_entity_id = $('#default_entity_id').val();
    //console.log(estimate_id+action_f_id+selected_entity_id+default_entity_id);
    _loadEntitiesEstimateForSelectOptions('entitiesSelect', action_f_id, selected_entity_id, default_entity_id);
}


_loadItems();
function _loadItems() {
    var estimate_id = $('#INPUT_HIDDEN_ESTIMATE_ID').val();
    if (estimate_id > 0) {
        $('#BODY_CARD_ESTIMATE_ITEMS').html('<div class="spinner spinner-primary spinner-lg"></div>');
        $.ajax({
            url: '/get/estimate/items/' + estimate_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#BODY_CARD_ESTIMATE_ITEMS').html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    }
}

function _formEstimateItem(item_id, estimate_id) {
    var modal_id = 'modal_form_estimateItem';
    var modal_content_id = 'modal_form_estimateItem_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/estimate-item/' + item_id + '/' + estimate_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {

        },
        complete: function(result, status) {

        }
    });
}

function _formDiscount(estimate_id) {
    $('#BLOCK_DISCOUNT').html('<div class="spinner spinner-primary spinner-lg"></div>');
    $.ajax({
        url: '/form/discount/' + estimate_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#BLOCK_DISCOUNT').html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}
//
var selected_taxe = $('#selected_tax_1').val();
_loadTaxesOptions(selected_taxe);

function _loadTaxesOptions(selected_taxe) {
    var select_id = 'taxesSelect';
    $.ajax({
        url: '/api/select/taxes',
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
        if (selected_taxe != 0 && selected_taxe != '') {
            $('#' + select_id + ' option[value="' + selected_taxe + '"]').attr('selected', 'selected');
        }
    });
}

function _loadContactsSelectOptions(select_id, entity_id, selected_contact_id) {
    _showLoader('LOADER_CONTACTS');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/contacts/' + entity_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +"</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_contact_id != 0 && selected_contact_id != '') {
            $('#' + select_id + ' option[value="' + selected_contact_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_CONTACTS');
    });
}
function annulateDiscount(){
    $('#BLOCK_DISCOUNT').html('');
}
</script>