@php
$modal_title=($contract)?'Avenant Contrat':'Contrat';
$createdAt = $updatedAt = $deletedAt = '';
if($contract){
$createdAt = ($contract->created_at)?$contract->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($contract->updated_at)?$contract->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($contract->deleted_at)?$contract->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_contract"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="formContract" class="form">
    <div class="modal-body" id="modal_form_contract_body">
        <div data-scroll="true" data-height="650">
            @csrf

            @if($contract)
            <!-- Infos date : begin -->
            <div class="form-group row">
                <div class="col-lg-12">
                    <span class="label label-inline label-outline-success mr-2">Contrat n° :
                        {{ $contract->number }}</span>
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

            <input type="hidden" name="id" value="{{ ($contract)?$contract->id:0 }}" />
            <input type="hidden" id="INPUT_HIDDEN_CONTACT_ID" value="{{ ($contract)?$contract->contact_id:0 }}" />
            <input type="hidden" id="INPUT_HIDDEN_AF_ID" value="{{ $af_id }}" />

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Etat <span class="text-danger">*</span></label>
                        <select name="state" class="form-control " required>
                            <option value="">Sélectionnez</option>
                            @foreach ($states as $s)
                            @php
                            $selected_state = ($contract && $contract->state==$s["code"])?'selected':'';
                            @endphp
                            <option {{ $selected_state }} value="{{ $s['code'] }}">{{ $s["name"] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Statut <span class="text-danger">*</span></label>
                        <select name="status" class="form-control " required>
                            <option value="">Sélectionnez</option>
                            @foreach ($status as $status)
                            @php
                            $selected_status = ($contract && $contract->status == $status["code"])?'selected':'';
                            @endphp
                            <option {{ $selected_status }} value="{{ $status["code"] }}">
                                {{ $status["name"] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="accounting_code">Code comptable </label>
                        <input class="form-control " type="text" name="accounting_code"
                            value="{{ ($contract)?$contract->accounting_code:'' }}" id="accounting_code" />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Intervenant <span id="LOADER_FORMERS"></span><span class="text-danger">*</span></label>
                        <select class="form-control select2" id="contactsListSelect" name="contact_id" required></select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <!--begin::Card-->
                    <div class="card card-custom card-border">
                        <div class="card-body p-2">
                            <!--begin: jstree-->
                            <div id="tree_scheduleformers" class="tree-demo font-size-sm"></div>
                            <!--end: jstree-->
                        </div>
                    </div>
                    <!--end::Card-->
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE_CONTRACT"></span></button>
    </div>
</form>
<!-- Form  : end -->
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

function _init_select_intervenants() {
    //var af_id = $('#afsSelect').val();
    var af_id = $('#INPUT_HIDDEN_AF_ID').val();
    //selected_member_id = $('#INPUT_HIDDEN_MEMBER_ID_FOR_CONTRACT').val();
    var member_id = ($('#contactsListSelect').val() > 0) ? $('#contactsListSelect').val() : 0;
    /* if (member_id == 0) {
        member_id = $('#INPUT_HIDDEN_MEMBER_ID_FOR_CONTRACT').val();
    } */
    selected_member_id= ($('#contactsListSelect').val() > 0) ? $('#contactsListSelect').val() : 0;
    _loadDatasIntervenantsContractForSelectOptions1('contactsListSelect', af_id, 2, selected_member_id);
}

function _loadDatasIntervenantsContractForSelectOptions1(select_id, af_id, type_former_intervention, selected_value) {
    _showLoader('LOADER_FORMERS');
    $('#' + select_id).empty();
    //console.log(selected_value);
    /* 
            type_former_intervention 1 == Sur facture
            type_former_intervention 2 == Sur contrat
    */
    var contact_id=$('#INPUT_HIDDEN_CONTACT_ID').val();
    $.ajax({
        url: '/api/select/options/formers/contacts/' + af_id + '/'+contact_id+'/2',
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    //console.log(array[i].id);
                    $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
                    /* if (selected_value == 0) {
                        $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
                    } else {
                        if (selected_value == array[i].id) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name + "</option>");
                        }
                    } */
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_value != 0 && selected_value != '') {
            $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
        }
        _resfreshJSTreeScheduleFormers();
        _hideLoader('LOADER_FORMERS');
    });
}
//start jstree
$('#tree_scheduleformers').jstree({
    "core": {
        "multiple": true,
        "themes": {
            "responsive": true
        },
        data: [{
            "id": 1,
            "text": '<span class="text-warning">Pas de données</span>',
            "state": {
                'opened': true,
                'checkbox_disabled': true
            },
            "icon": "fa fa-info text-warning",
            "parent": '#'
        }]
    },
    "checkbox": {
        "three_state": true,
    },
    "plugins": ["state", "types", "wholerow", "checkbox"]
});
//end jstree
function _resfreshJSTreeScheduleFormers() {
    //var af_id = $('#afsSelect').val();
    var af_id = $('#INPUT_HIDDEN_AF_ID').val();
    var contact_id = $('#contactsListSelect').val();
    //console.log(af_id);
    $('#tree_scheduleformers').jstree(true).settings.core.data.url = '/api/tree/schedules/contactformers/contract/' + af_id +'/' + contact_id;
    $('#tree_scheduleformers').jstree(true).refresh();
}
$('#tree_scheduleformers').bind("ready.jstree", function() {
    initializeSelections();
}).jstree();

function initializeSelections() {
    var instance = $('#tree_scheduleformers').jstree(true);
    instance.deselect_all();
}
$('#contactsListSelect').on('change', function() {
    _resfreshJSTreeScheduleFormers();
});

$("#formContract").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_CONTRACT');
        var formData = $(form).serializeArray();
        var schedulescontacts_ids = $("#tree_scheduleformers").jstree("get_selected");
        if (schedulescontacts_ids) {
            formData = formData.concat([{
                name: "schedulescontacts_ids",
                value: schedulescontacts_ids
            }, ]);
        }
        $.ajax({
            type: 'POST',
            url: '/form/contract',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_CONTRACT');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_contract').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_CONTRACT');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_CONTRACT');
                _reload_dt_contracts();
            }
        });
        return false;
    }
});

//_loadAfsForSelectOptions('afsSelect');

function _loadAfsForSelectOptions(select_id) {
    _showLoader('LOADER_AFS');
    var selected_af_id = $('#INPUT_HIDDEN_AF_ID').val();
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/afs/' + selected_af_id,
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
        _init_select_intervenants();
    });
}
/* $('#afsSelect').on('change', function() {
    _init_select_intervenants();
}); */

_init_select_intervenants();
</script>