@php
$modal_title=($row)?'Edition période':'Ajouter une période de stage';
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
<form id="formStage" class="form">
    <div class="modal-body" id="modal_form_session_body">
        <div data-scroll="true" data-height="600">
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

            <input type="hidden" name="id" id="INPUT_SESSION_ID" value="{{ ($row)?$row->id:0 }}" />
            <!-- begin::form -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        @php
                        $checkedIsActive = ($row && $row->is_active===1)?'checked=checked':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="is_active" {{ $checkedIsActive }}>
                                <span></span>Active</label>
                        </div>
                    </div>
                </div>
            </div>

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
                @if($row)
                <div class="col-lg-6">
                    <div class="form-group">
                        <span class="label label-md label-outline-primary label-pill label-inline">Code
                            :{{ $row->code }}</span>
                    </div>
                </div>
                @endif
            </div>

            <div class="row">
                <div class="col-lg-12">
                    @php
                    $defaultTitle = ($af && $af->title)?$af->title:'';
                    @endphp
                    <div class="form-group">
                        <label for="title">Intitulé de la période : <span class="text-danger">*</span></label>
                        <input class="form-control " type="text" name="title"
                            value="{{ ($row)?$row->title:$defaultTitle }}" id="title" required />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Semestre de rattachement <span class="text-danger">*</span></label>
                        <select name="attachment_semester" class="form-control " required>
                            @foreach ($params_semesters as $s)
                            @php
                            $selected_sm = ($row && $row->attachment_semester==$s["code"])?'selected':'';
                            @endphp
                            <option {{ $selected_sm }} value="{{ $s["code"] }}">{{ $s["name"] }}</option>
                            @endforeach
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
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$row->started_at);
                            $started_at = $dt->format('d/m/Y');
                            }
                            $defaultStartedAt ='';
                            if($af && $af->started_at!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$af->started_at);
                            $defaultStartedAt = $dt->format('d/m/Y');
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
                        $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$row->ended_at);
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

            @php
            $af_nb_days = ($af && $af->nb_days)?$af->nb_days:0;
            $af_nb_hours = ($af && $af->nb_hours)?$af->nb_hours:0;
            @endphp

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_days">Nb de jours <span class="text-danger">*</span></label>
                        <input class="form-control " type="number" min="0" name="nb_days"
                            value="{{ ($row)?$row->nb_days:$af_nb_days }}" id="nb_days" required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_hours">Durée de la période (Nb d'heures) <span
                                class="text-danger">*</span></label>
                        <input class="form-control " type="number" min="0" name="nb_hours"
                            value="{{ ($row)?$row->nb_hours:$af_nb_hours }}" id="nb_hours" required />
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Etat <span class="text-danger">*</span></label>
                        <select name="state" class="form-control " required>
                            <option value="INACTIF_INTERNSHIP_PERIOD"
                                {{(($row && $row->state=='INACTIF_INTERNSHIP_PERIOD')?'selected':'')}}>Inactif</option>
                            <option value="VALID_INTERNSHIP_PERIOD"
                                {{(($row && $row->state=='VALID_INTERNSHIP_PERIOD')?'selected':'')}}>Validé</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Type </label>
                        <select name="session_type" class="form-control " required>
                            <option value="EJE_SANS_GRATIFICATION_INTERNSHIP_PERIOD"
                                {{(($row && $row->session_type=='EJE_SANS_GRATIFICATION_INTERNSHIP_PERIOD')?'selected':'')}}>
                                EJE Sans Gratification</option>
                            <option value="EJE_AVEC_GRATIFICATION_INTERNSHIP_PERIOD"
                                {{(($row && $row->session_type=='EJE_AVEC_GRATIFICATION_INTERNSHIP_PERIOD')?'selected':'')}}>
                                EJE Avec Gratification</option>
                            <option value="CAP_INTERNSHIP_PERIOD"
                                {{(($row && $row->session_type=='CAP_INTERNSHIP_PERIOD')?'selected':'')}}>CAP</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="description">Objectifs</label>
                        <textarea class="form-control" id="description" name="description"
                            rows="3">{{ ($row)?$row->description:'' }}</textarea>
                    </div>
                </div>
            </div>
            <!--end:: form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE_STAGE"></span></button>
    </div>
</form>
<!-- Form  : end -->
<script>
$(document).ready(function() {
    $('.select2').select2();
});
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
$("#formStage").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_STAGE');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/stage',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_STAGE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_stage').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_STAGE');
                _showResponseMessage('error', 'Ouups...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_STAGE');
                _reload_dt_stages();
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
$('#nb_days').on("input", function() {
    nb_days = this.value;
    calculateHours(nb_days, 'nb_hours');
});

_loadAfsForSelectOptions('afsSelectStage');
function _loadAfsForSelectOptions(select_id) {
    var selected_af_id=$('#selected_af_id').val();
    var default_af_id=$('#default_af_id').val();
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
    });
}
</script>