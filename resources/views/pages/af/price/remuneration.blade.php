@php
$modal_title='Rémunération intervenants';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_remuneration"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="formRemuneration" class="form">
    <div class="modal-body" id="modal_form_remuneration_body">
        <div data-scroll="true" data-height="650">
            @csrf

            <input type="hidden" name="af_id" id="INPUT_HIDDEN_AF_ID" value="{{ $af_id }}" />
            <input type="hidden" id="INPUT_HIDDEN_MEMBER_ID" value="{{ $member_id }}" />
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Intervenant <span class="text-danger">*</span> <span id="LOADER_MEMBERS"></span></label>
                        <div class="@if($member_id>0) d-none @endif">
                            <select class="form-control select2" id="membersSelect" name="member_id" required>
                                <option value="">Sélectionnez un intervenant</option>
                            </select>
                        </div>
                        @if($member_id>0)
                        <p class="text-primary">{{$contactFormer}}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <!--begin::Card-->
                    <div class="card card-custom card-border">
                        <div class="card-body p-2">
                            <div id="BLOCK_FORM_TYPE_INTERVENTION"></div>    
                            <div class="form-group">
                                <label>Type d'intervention :<span class="text-danger">*</span></label>
                                <select class="form-control" name="type_of_intervention" required>
                                    @if($types)    
                                        @foreach ($types as $t)
                                        <option value="{{ $t['code'] }}">{{ $t['name'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <!--end::Card-->
                </div>
                <div class="col-lg-8">
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
                id="BTN_SAVE_REMUNERATION"></span></button>
    </div>
</form>
<!-- Form  : end -->
<!-- <script src="{{ asset('custom/js/form-remuneratio.js?v=1') }}"></script> -->
<script>
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
var af_id = $('#INPUT_HIDDEN_AF_ID').val();
selected_member_id = $('#INPUT_HIDDEN_MEMBER_ID').val();
$('#membersSelect').select2();
var member_id = ($('#membersSelect').val() > 0) ? $('#membersSelect').val() : 0;
if (member_id == 0) {
    member_id = $('#INPUT_HIDDEN_MEMBER_ID').val();
}
_loadDatasIntervenantsForSelectOptions('membersSelect', af_id, selected_member_id);

var tree_scheduleformers = 'tree_scheduleformers';
$('#' + tree_scheduleformers).jstree({
    "core": {
        "multiple": true,
        "themes": {
            "responsive": true
        },
        'data': {
            'url': function(node) {
                return '/api/tree/schedules/formers/' + af_id + '/' + member_id;
            },
            'data': function(node) {
                return {
                    'parent': node.id
                };
            }
        },
    },
    "checkbox": {
        "three_state": true,
    },
    "plugins": ["state", "types", "wholerow", "checkbox"]
});

function _resfreshJSTreeScheduleFormers() {
    var af_id = $('#INPUT_HIDDEN_AF_ID').val();
    var member_id = $('#membersSelect').val();
    if (member_id == 0) {
        member_id = $('#INPUT_HIDDEN_MEMBER_ID').val();
    }
    $('#tree_scheduleformers').jstree(true).settings.core.data.url = '/api/tree/schedules/formers/' + af_id + '/' +
        member_id;
    $('#tree_scheduleformers').jstree(true).refresh();
}
$('#tree_scheduleformers').bind("ready.jstree", function() {
    initializeSelections();
}).jstree();

function initializeSelections() {
    var instance = $('#tree_scheduleformers').jstree(true);
    instance.deselect_all();
}

$('#membersSelect').on('change', function() {
    _resfreshJSTreeScheduleFormers();
    _loadFormByContractType();
});
var _loadFormByContractType = function() {
    var member_id = $('#membersSelect').val();
    if (member_id == 0) {
        member_id = $('#INPUT_HIDDEN_MEMBER_ID').val();
    }
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#BLOCK_FORM_TYPE_INTERVENTION').html(spinner);
    $.ajax({
        url: '/form/formerpricebytypeintervention/' + member_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#BLOCK_FORM_TYPE_INTERVENTION').html(html);
        }
    });
}
if (selected_member_id > 0) {
    _loadFormByContractType();
}
$("#formRemuneration").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_REMUNERATION');
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
            url: '/form/remuneration',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_REMUNERATION');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_remuneration').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_REMUNERATION');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_REMUNERATION');
                resfreshJSTreeSchedulecontacts();
            }
        });
        return false;
    }
});
</script>