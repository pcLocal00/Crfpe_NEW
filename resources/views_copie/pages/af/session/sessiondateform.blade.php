@php
$modal_title=($row)?'Edition date':'Ajouter une date';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
$key = ($row)?$row->id:0;
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_sessiondate_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="formSingleSessionDate" class="form">
    <div class="modal-body" id="modal_form_sessiondate_body">
        <div data-scroll="true" data-height="350">
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

            <!-- begin::form -->
            <input type="hidden" name="session_id" value="{{ $session_id }}">
            <input type="hidden" name="sessiondates[{{ $key }}][id]" value="{{ ($sd && $sd['id'])?$sd['id']:0 }}">
            <input type="hidden" name="sessiondates[{{ $key }}][session_id]" value="{{ $session_id }}">

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Date </label>
                        <input type="text" class="form-control form-control-sm" id="date_datepicker"
                            name="sessiondates[{{ $key }}][planning_date]"
                            value="{{ ($sd && $sd['planning_date'])?$sd['planning_date']:'' }}" />
                        <span class="form-text text-muted">Laisser le champ vide si "date à programmer"</span>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-3"></div>
                <div class="col-lg-3"><strong>Début (h)</strong></div>
                <div class="col-lg-3"><strong>Fin (h)</strong></div>
                <div class="col-lg-3"><strong>Durée (h)</strong></div>
            </div>
            @if($autorizeS1)            
            <div class="row mb-4">
                <div class="col-lg-3">Séance 1 (Matin):</div>
                <div class="col-lg-3">
                    <input type="hidden" name="sessiondates[{{ $key }}][schedules][M][id]"
                        value="{{ ($sd && $sd['schedules']['M']['id'])?$sd['schedules']['M']['id']:0 }}">
                    <input type="hidden" name="sessiondates[{{ $key }}][schedules][M][sessiondate_id]"
                        value="{{ ($sd && $sd['id'])?$sd['id']:0 }}">
                    <input type="hidden" name="sessiondates[{{ $key }}][schedules][M][type]" value="M">
                    <input type="text" name="sessiondates[{{ $key }}][schedules][M][start_hour]" data-key="{{ $key }}"
                        id="m_start_hour_s1"
                        value="{{ ($sd && $sd['schedules']['M']['start_hour'])?$sd['schedules']['M']['start_hour']:'' }}"
                        class="form-control form-control-sm schedule-datetimepicker-input-m m_start_hour_s1"
                        data-toggle="datetimepicker" />
                </div>
                <div class="col-lg-3">
                    <input type="text" name="sessiondates[{{ $key }}][schedules][M][end_hour]"
                        value="{{ ($sd && $sd['schedules']['M']['end_hour'])?$sd['schedules']['M']['end_hour']:'' }}"
                        data-key="{{ $key }}" id="m_end_hour_s1"
                        class="form-control form-control-sm schedule-datetimepicker-input-m m_end_hour_s1"
                        data-toggle="datetimepicker" />
                </div>
                <div class="col-lg-3">
                    <input type="text" name="sessiondates[{{ $key }}][schedules][M][duration]" data-key="{{ $key }}"
                        id="m_duration_s1"
                        value="{{ ($sd && $sd['schedules']['M']['duration'])?$sd['schedules']['M']['duration']:'' }}"
                        class="form-control form-control-sm form-control-solid m_duration_s1" readonly>
                </div>
            </div>
           @endif
           @if($autorizeS2)             
            <div class="row mb-4">
                <div class="col-lg-3">Séance 2 (Après midi):</div>
                <div class="col-lg-3">
                    <input type="hidden" name="sessiondates[{{ $key }}][schedules][A][id]"
                        value="{{ ($sd && $sd['schedules']['A']['id'])?$sd['schedules']['A']['id']:0 }}">
                    <input type="hidden" name="sessiondates[{{ $key }}][schedules][A][sessiondate_id]"
                        value="{{ ($sd && $sd['id'])?$sd['id']:0 }}">
                    <input type="hidden" name="sessiondates[{{ $key }}][schedules][A][type]" value="A">
                    <input type="text" name="sessiondates[{{ $key }}][schedules][A][start_hour]" data-key="{{ $key }}"
                        id="a_start_hour_s2"
                        value="{{ ($sd && $sd['schedules']['A']['start_hour'])?$sd['schedules']['A']['start_hour']:'' }}"
                        class="form-control form-control-sm schedule-datetimepicker-input-a a_start_hour_s2"
                        data-toggle="datetimepicker" />
                </div>
                <div class="col-lg-3">
                    <input type="text" name="sessiondates[{{ $key }}][schedules][A][end_hour]" data-key="{{ $key }}"
                        id="a_end_hour_s2"
                        value="{{ ($sd && $sd['schedules']['A']['end_hour'])?$sd['schedules']['A']['end_hour']:'' }}"
                        class="form-control form-control-sm schedule-datetimepicker-input-a a_end_hour_s2"
                        data-toggle="datetimepicker" />
                </div>
                <div class="col-lg-3">
                    <input type="text" name="sessiondates[{{ $key }}][schedules][A][duration]" data-key="{{ $key }}"
                        id="a_duration_s2"
                        value="{{ ($sd && $sd['schedules']['A']['duration'])?$sd['schedules']['A']['duration']:'' }}"
                        class="form-control form-control-sm form-control-solid a_duration_s2" readonly>
                </div>
            </div>
            @endif
            <div class="row mb-4">
                <div class="col-lg-3"></div>
                <div class="col-lg-3"></div>
                <div class="col-lg-3"><strong>Durée total (h)</strong></div>
                <div class="col-lg-3">
                    <input type="text" name="sessiondates[{{ $key }}][duration]"
                        value="{{ ($sd && $sd['duration'])?$sd['duration']:'' }}" id="date_duration"
                        class="form-control form-control-sm form-control-solid" readonly>
                </div>
            </div>
            <!--end:: form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE_DATE"></span></button>
    </div>
</form>
<!-- Form  : end -->
<!-- <script src="{{ asset('custom/js/form-sessiondateform.js?v=1') }}"></script> -->
<script>
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$('#date_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});

$('.schedule-datetimepicker-input-m').datetimepicker({
        locale: 'fr',
        format: 'LT',
        //minDate : moment('07:00','H:i'),
        //maxDate : moment('12:30','H:i')
});
$('.schedule-datetimepicker-input-a').datetimepicker({
        locale: 'fr',
        format: 'LT',
        //minDate : moment('13:00','H:i'),
        //maxDate : moment('20:00','H:i')
});

$("#formSingleSessionDate").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_DATE');


            var iError = 0;
            var msg = '';
            m_start_hour = $('#m_start_hour_s1').val();
            m_end_hour = $('#m_end_hour_s1').val();
            if(m_end_hour<m_start_hour){
                iError ++;
                msg = msg + '<p>La date de début : <strong>'+m_start_hour+'</strong> est supérieur a la date de fin : <strong>'+m_end_hour+'</strong></p>';
            }
            a_start_hour = $('#a_start_hour_s2').val();
            a_end_hour = $('#a_end_hour_s2').val();
            if(a_end_hour<a_start_hour){
                iError ++;
                msg = msg + '<p>La date de début : <strong>'+a_start_hour+'</strong> est supérieur a la date de fin : <strong>'+a_end_hour+'</strong></p>';
            }
           
            if(iError>0){
                swal.fire({
                    html: msg,
                    icon: 'error',
                    buttonsStyling: false,
                    confirmButtonText: '<i class="far fa-times-circle"></i> Fermer',
                    customClass: {
                        confirmButton: "btn btn-light-primary"
                    },
                }).then(function() {});
                _hideLoader('BTN_SAVE_DATE');
                return false;
            }

        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/sessiondate',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_DATE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_sessiondate').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_DATE');
                _showResponseMessage('error',
                    'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_DATE');
                _loadPlanningsDates();
            }
        });
        return false;
    }
});
$('#m_start_hour_s1').on("input", function() {
    key_input = 1;
    m_start_hour = $(this).val();
    m_end_hour = $('#m_end_hour_s1').val();
    updateTotalDuration(m_start_hour, m_end_hour, key_input, 'm_duration_s');
    _calculateTotalDateDuration();
});
$('#m_end_hour_s1').on("input", function() {
    key_m_input = 1;
    m_end_hour_1 = $(this).val();
    m_start_hour_1 = $('#m_start_hour_s1').val();
    updateTotalDuration(m_start_hour_1, m_end_hour_1, key_m_input, 'm_duration_s');
    _calculateTotalDateDuration();
});
$('#a_start_hour_s2').on("input", function() {
    key_input = 2;
    m_start_hour = $(this).val();
    m_end_hour = $('#a_end_hour_s2').val();
    updateTotalDuration(m_start_hour, m_end_hour, key_input, 'a_duration_s');
    _calculateTotalDateDuration();
});
$('#a_end_hour_s2').on("input", function() {
    key_m_input = 2;
    m_end_hour_1 = $(this).val();
    m_start_hour_1 = $('#a_start_hour_s2').val();
    updateTotalDuration(m_start_hour_1, m_end_hour_1, key_m_input, 'a_duration_s');
    _calculateTotalDateDuration();
});
var _calculateTotalDateDuration = function() {
    var m_duration_s1 = parseFloat($('#m_duration_s1').val());
    var a_duration_s2 = parseFloat($('#a_duration_s2').val());
    var total_duration = ( ((m_duration_s1)?m_duration_s1:0) + ((a_duration_s2)?a_duration_s2:0)).toFixed(2);
    $('#date_duration').val(total_duration);
}
</script>