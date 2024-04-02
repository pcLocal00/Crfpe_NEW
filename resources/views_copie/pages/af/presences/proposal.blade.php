@php
$modal_title=($row)?'Edition proposition de stage':'Ajouter une proposition de stage';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_session_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->

    <div class="modal-body" id="modal_form_session_body">
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

            <form id="formStageProposal" class="form">
            @csrf
            <input type="hidden" name="id" id="INPUT_PROPOSITION_ID" value="{{ ($row)?$row->id:0 }}" />
            <!-- begin::form -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>AF <span id="LOADER_AFS"></span><span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_af_id" value="{{ ($row)?$row->af_id:$af_id }}">
                        <input type="hidden" id="default_af_id" value="{{ $af_id }}">
                        <select id="afsSelectStage" name="af_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="form-group">
                        <label>Nom de la période de stage <span id="LOADER_PERIODES"></span><span
                                class="text-danger">*</span></label>
                        <input type="hidden" id="selected_session_id" value="{{ ($row)?$row->session_id:0 }}">
                        <input type="hidden" id="default_session_id" value="{{ ($row)?$row->session_id:0 }}">
                        <select id="sessionsSelectStagePeriods" name="session_id"
                            class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Etat : </label>
                        <select id="state" name="state" class="form-control form-control-sm select2" required>
                            <option value="draft" {{($row)?(($row->state=='draft')?'selected':''):''}}>Brouillon
                            </option>
                            <option value="approuved" {{($row)?(($row->state=='approuved')?'selected':''):''}}>A
                                approuver</option>
                            <option value="invalid" {{($row)?(($row->state=='invalid')?'selected':''):''}}>Stage non
                                validée</option>
                            <option value="validated" {{($row)?(($row->state=='validated')?'selected':''):''}}>Stage
                                validée</option>
                            <option value="imposed" {{($row)?(($row->state=='imposed')?'selected':''):''}}>Stage imposée
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Date début de la période <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $started_at ='';
                            if($row && $row->started_at!=null){
                                $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->started_at);
                                $started_at = $dt->format('d/m/Y');
                            }
                            $defaultStartedAt ='';
                            if($af && $af->started_at!=null){
                                //$dt = Carbon\Carbon::createFromFormat('Y-m-d',$af->started_at);
                                //$defaultStartedAt = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control datepicker" name="started_at"
                                id="started_at_datepicker" placeholder="Sélectionner une date"
                                value="{{ ($started_at)?$started_at:$defaultStartedAt }}" autocomplete="off" required />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label>Date fin de la période <span class="text-danger">*</span></label>
                    <div class="input-group date">
                        @php
                        $ended_at ='';
                        if($row && $row->ended_at!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->ended_at);
                            $ended_at = $dt->format('d/m/Y');
                        }
                        @endphp
                        <input type="text" class="form-control datepicker" name="ended_at" id="ended_at_datepicker"
                            placeholder="Sélectionner une date" value="{{ ($ended_at)?$ended_at:'' }}"
                            autocomplete="off" required />
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="la la-calendar-check-o"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Stagiaire <span id="LOADER_STAGIAIRES"></span><span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_member_id" value="{{ ($row)?$row->member_id:0 }}">
                        <input type="hidden" id="default_member_id" value="{{ ($row)?$row->member_id:0 }}">
                        <select id="membersSelectStagePeriods" name="member_id"
                            class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Organisme d'accueil <span id="LOADER_ENTITIES"></span><span
                                class="text-danger">*</span></label>
                        <input type="hidden" id="selected_entity_id" value="{{ ($row)?$row->entity_id:0 }}">
                        <input type="hidden" id="default_entity_id" value="{{ ($row)?$row->entity_id:0 }}">
                        <select id="entitiesSelectStage" name="entity_id" class="form-control form-control-sm select2"
                            required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>

            

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Représenté par (nom du signataire de la convention) <span
                                id="LOADER_REPS_CONTACTS"></span><span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_representing_contact_id"
                            value="{{ ($row)?$row->representing_contact_id:0 }}">
                        <input type="hidden" id="default_representing_contact_id"
                            value="{{ ($row)?$row->representing_contact_id:0 }}">
                        <select id="representingContactsSelectStage" name="representing_contact_id"
                            class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Référent de stage <span id="LOADER_REFERENT_CONTACTS"></span><span
                                class="text-danger">*</span></label>
                        <input type="hidden" id="selected_internship_referent_contact_id"
                            value="{{ ($row)?$row->internship_referent_contact_id:0 }}">
                        <input type="hidden" id="default_internship_referent_contact_id"
                            value="{{ ($row)?$row->internship_referent_contact_id:0 }}">
                        <select id="referentContactsSelectStage" name="internship_referent_contact_id"
                            class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Formateur référent <span id="LOADER_REFERENT_TRAINER_CONTACTS"></span><span
                                class="text-danger">*</span></label>
                        <input type="hidden" id="selected_trainer_referent_contact_id"
                            value="{{ ($row)?$row->trainer_referent_contact_id:0 }}">
                        <input type="hidden" id="default_trainer_referent_contact_id"
                            value="{{ ($row)?$row->trainer_referent_contact_id:0 }}">
                        <select id="trainerReferentContactsSelectStage" name="trainer_referent_contact_id"
                            class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="service">Service</label>
                        <textarea class="form-control" id="service" name="service"
                            rows="3">{{ ($row)?$row->service:'' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Adresse lieu de stage <span id="LOADER_ADRESSES"></span><span
                                class="text-danger">*</span></label>
                        <input type="hidden" id="selected_adresse_id" value="{{ ($row)?$row->adresse_id:0 }}">
                        <input type="hidden" id="default_adresse_id" value="{{ ($row)?$row->adresse_id:0 }}">
                        <select id="adressesSelectStage" name="adresse_id"
                            class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>

            <!--end:: form-->
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="button" onclick="$('#formStageProposal').submit();" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE_PROPOSAL"></span></button>
    </div>

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
$("#formStageProposal").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_PROPOSAL');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/stage/proposal',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_PROPOSAL');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_stage_proposal').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_PROPOSAL');
                _showResponseMessage('error', 'Ouups...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_PROPOSAL');
                _reload_dt_stage_proposals();
            }
        });
        return false;
    }
});
$('.datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});

_loadAfsForSelectOptions('afsSelectStage');

function _loadAfsForSelectOptions(select_id) {
    var selected_af_id = $('#selected_af_id').val();
    var default_af_id = $('#default_af_id').val();
    _showLoader('LOADER_AFS');
    $('#' + select_id).empty();
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
        _loadSessionsSelectStagePeriodsOptions('sessionsSelectStagePeriods');
        _loadMembersSelectStagePeriodsOptions('membersSelectStagePeriods');
        _loadEntitiesSelectStageOptions('entitiesSelectStage');
    });
}

function _loadSessionsSelectStagePeriodsOptions(select_id) {
    var af_id = $('#selected_af_id').val();
    var selected_session_id = $('#selected_session_id').val();
    var default_session_id = $('#default_session_id').val();
    _showLoader('LOADER_PERIODES');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/sessions/periods/' + default_session_id + '/' + af_id,
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
        if (selected_session_id != 0 && selected_session_id != '') {
            $('#' + select_id + ' option[value="' + selected_session_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_PERIODES');
    });
}

function _loadMembersSelectStagePeriodsOptions(select_id) {
    var af_id = $('#selected_af_id').val();
    var selected_member_id = $('#selected_member_id').val();
    var default_member_id = $('#default_member_id').val();
    _showLoader('LOADER_STAGIAIRES');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/stagiaires/members/' + default_member_id + '/' + af_id,
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
        if (selected_member_id != 0 && selected_member_id != '') {
            $('#' + select_id + ' option[value="' + selected_member_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_STAGIAIRES');
    });
}

function _loadEntitiesSelectStageOptions(select_id) {
    var selected_entity_id = $('#selected_entity_id').val();
    var default_entity_id = $('#default_entity_id').val();
    _showLoader('LOADER_ENTITIES');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/stage/entities/' + default_entity_id,
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
        _hideLoader('LOADER_ENTITIES');
        _loadContactsSelectStageOptions('representingContactsSelectStage', 1);
        _loadContactsSelectStageOptions('referentContactsSelectStage', 2);
        _loadContactsSelectStageOptions('trainerReferentContactsSelectStage', 3);
        _loadAdressesSelectStageOptions('adressesSelectStage');
    });
}
$('#entitiesSelectStage').on('change', function() {
    _loadContactsSelectStageOptions('representingContactsSelectStage', 1);
    _loadContactsSelectStageOptions('referentContactsSelectStage', 2);
    // _loadContactsSelectStageOptions('trainerReferentContactsSelectStage', 3);
    _loadAdressesSelectStageOptions('adressesSelectStage');
});

function _loadContactsSelectStageOptions(select_id, type) {
    /* 
    type==1 => Representant
    type==2 => Referent
    type==3 => formateur référent
     */
    if (type == 1) {
        var entity_id = $('#entitiesSelectStage').val();
        var selected_contact_id = $('#selected_representing_contact_id').val();
        var default_contact_id = $('#default_representing_contact_id').val();
        var loader_id = 'LOADER_REPS_CONTACTS';
    } else if (type == 2) {
        var entity_id = $('#entitiesSelectStage').val();
        var selected_contact_id = $('#selected_internship_referent_contact_id').val();
        var default_contact_id = $('#default_internship_referent_contact_id').val();
        var loader_id = 'LOADER_REFERENT_CONTACTS';
    } else if (type == 3) {
        var entity_id = $('#entitiesSelectStage').val();
        var selected_contact_id = $('#selected_trainer_referent_contact_id').val();
        var default_contact_id = $('#default_trainer_referent_contact_id').val();
        var loader_id = 'LOADER_REFERENT_TRAINER_CONTACTS';
    }
    _showLoader(loader_id);
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/stage/contacts/' + default_contact_id + '/' + entity_id,
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
        if (selected_contact_id != 0 && selected_contact_id != '') {
            $('#' + select_id + ' option[value="' + selected_contact_id + '"]').attr('selected', 'selected');
        }
        _hideLoader(loader_id);
    });
}
function _loadAdressesSelectStageOptions(select_id, type = 1) {
    var entity_id = $('#entitiesSelectStage').val();
    var selected_adresse_id = $('#selected_adresse_id').val();
    var default_adresse_id = $('#default_adresse_id').val();
    var loader_id = 'LOADER_ADRESSES';
    _showLoader(loader_id);
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/stage/contacts/' + default_contact_id + '/' + entity_id + (type == 3 ? '?trainers=1&af_id='+$('#afsSelectStage').val() : ''),
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
        if (selected_adresse_id != 0 && selected_adresse_id != '') {
            $('#' + select_id + ' option[value="' + selected_adresse_id + '"]').attr('selected', 'selected');
        }
        _hideLoader(loader_id);
    });
}
</script>