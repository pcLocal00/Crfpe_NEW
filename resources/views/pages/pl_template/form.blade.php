@php
$modal_title=($row)?'Edition modèle de plannification':'Ajouter un modèle de plannification';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_ptemplate_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->
<form id="formPtemplate" class="form">
    <div class="modal-body" id="modal_form_ptemplate_body">
        <div data-scroll="true" data-height="620">
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
            <input type="hidden" name="id" value="{{ ($row)?$row->id:0 }}" />
            <!-- begin::param form -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        @php
                        $checkedIsActive = ($row && $row->is_active===1)?'checked=checked':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="is_active" {{ $checkedIsActive }}>
                                <span></span>Actif</label>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Odre d'affichage <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="order_show" value="{{ ($row)?$row->order_show:0 }}"
                            required />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" value="{{ ($row)?$row->code:'' }}"
                            required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ ($row)?$row->name:'' }}"
                            required />
                    </div>
                </div>
            </div>

            <div class="accordion accordion-solid accordion-toggle-plus mb-4" id="accordionPeriodes">
                <div class="card">
                    <div class="card-header" id="headingOne5">
                        <div class="card-title" data-toggle="collapse" data-target="#collapsePeriodes">
                            <i class="flaticon-event-calendar-symbol"></i> Périodes jour
                        </div>
                    </div>
                    <div id="collapsePeriodes" class="collapse show" data-parent="#accordionPeriodes">
                        <div class="card-body">
                            <!-- Begin::Periodes -->
                            <input type="hidden" name="m_id" value="{{ ($morning_period)?$morning_period->id:0 }}">
                            <input type="hidden" name="m_type" value="{{ ($morning_period)?$morning_period->type:'M' }}">
                            <div class="row">
                                <div class="col-lg-3"><label>Matin:</label></div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>Heure début M:</label>
                                        @php
                                        $m_start_hour ='';
                                        if($morning_period && $morning_period->start_hour!=null){
                                            $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$morning_period->start_hour);
                                            $m_start_hour = $dt->format('H:i');
                                        }
                                        @endphp
                                        <input type="text" name="m_start_hour" id="m_start_hour" value="{{ $m_start_hour }}"
                                            class="form-control datetimepicker-input" placeholder="09:00"
                                            data-toggle="datetimepicker" />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group"><label>Heure fin M:</label>
                                        @php
                                        $m_end_hour ='';
                                        if($morning_period && $morning_period->end_hour!=null){
                                            $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$morning_period->end_hour);
                                            $m_end_hour = $dt->format('H:i');
                                        }
                                        @endphp
                                        <input type="text" name="m_end_hour" id="m_end_hour" value="{{ $m_end_hour }}"
                                            class="form-control datetimepicker-input" placeholder="12:30"
                                            data-toggle="datetimepicker" />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group"><label>Durée M:</label>
                                        <div class="input-group">
                                            <input type="text" name="m_duration" id="m_duration" value="{{ ($morning_period)?$morning_period->duration:'' }}"
                                                class="form-control form-control-solid" placeholder="03:30" readonly>
                                            <div class="input-group-append">
                                                <button type="button" onclick="_calculateDuration('m')"
                                                    data-toggle="tooltip" title="Calculer la durée du matin"
                                                    class="btn btn-icon btn-outline-primary"><span
                                                        id="SPAN_CALCULATE_DurationOfMorning"><i
                                                            class="flaticon2-reload"></i></span></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="separator separator-dashed my-5"></div>
                            <input type="hidden" name="a_id" value="{{ ($afternoon_period)?$afternoon_period->id:0 }}">
                            <input type="hidden" name="a_type" value="{{ ($afternoon_period)?$afternoon_period->type:'A' }}">
                            <div class="row">
                                <div class="col-lg-3"><label>Après midi:</label></div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label>Heure début A:</label>
                                        @php
                                        $a_start_hour ='';
                                        if($afternoon_period && $afternoon_period->start_hour!=null){
                                            $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$afternoon_period->start_hour);
                                            $a_start_hour = $dt->format('H:i');
                                        }
                                        @endphp
                                        <input type="text" name="a_start_hour" id="a_start_hour" value="{{ $a_start_hour }}"
                                            class="form-control datetimepicker-input" placeholder="13:30"
                                            data-toggle="datetimepicker" />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group"><label>Heure fin A:</label>
                                        @php
                                        $a_end_hour ='';
                                        if($afternoon_period && $afternoon_period->end_hour!=null){
                                            $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$afternoon_period->end_hour);
                                            $a_end_hour = $dt->format('H:i');
                                        }
                                        @endphp
                                        <input type="text" name="a_end_hour" id="a_end_hour" value="{{ $a_end_hour }}"
                                            class="form-control datetimepicker-input" placeholder="17:00"
                                            data-toggle="datetimepicker" />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group"><label>Durée A:</label>
                                        <div class="input-group">
                                            <input type="text" name="a_duration" id="a_duration" value="{{ ($afternoon_period)?$afternoon_period->duration:'' }}"
                                                class="form-control form-control-solid" placeholder="03:30" readonly />
                                            <div class="input-group-append">
                                                <button type="button" onclick="_calculateDuration('a')"
                                                    data-toggle="tooltip" title="Calculer la durée d'après midi"
                                                    class="btn btn-icon btn-outline-primary"><span
                                                        id="SPAN_CALCULATE_DurationOfAfternoon"><i
                                                            class="flaticon2-reload"></i></span></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="separator separator-dashed my-5"></div>
                            <div class="row">
                                <div class="col-lg-3"><a href="javascript:;" onclick="_resetTimes()"
                                        class="btn btn-sm font-weight-bolder btn-light-danger"><i
                                            class="la la-trash-o"></i> Réinitialiser</a></div>
                                <div class="col-lg-3">
                                </div>
                                <div class="col-lg-3">
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group"><label>Durée TOTAL JOUR:</label>
                                        <input type="text" name="duration" id="duration" value="{{ ($row)?$row->duration:'' }}"
                                            class="form-control form-control-solid" placeholder="07:00" readonly />
                                    </div>
                                </div>
                            </div>
                            <!-- End::Periodes -->
                        </div>
                    </div>
                </div>
            </div>



        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form : end -->
<!-- <script src="{{ asset('custom/js/form-ptemplate.js?v=1') }}"></script> -->
<script>
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formPtemplate").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        //Calculate duration when validate
        var m_startTime = $('#m_start_hour').val();
        var m_endTime = $('#m_end_hour').val();
        if (!(m_startTime == '' && m_endTime == '')) {
            _hideLoader('BTN_SAVE');
            rs = _calculateDuration('m');
            if(rs==false){
                return false;
            }
        }
        var a_startTime = $('#a_start_hour').val();
        var a_endTime = $('#a_end_hour').val();
        if (!(a_startTime == '' && a_endTime == '')) {
            _hideLoader('BTN_SAVE');
            rs = _calculateDuration('a');
            if(rs==false){
                return false;
            }
        }
        
        _showLoader('BTN_SAVE');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/ptemplate',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_ptemplate').modal('hide');
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
                if ($.fn.DataTable.isDataTable('#dt_ptemplates')) {
                    _reload_dt_ptemplates();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
/* console.log(moment({h:8}));
$('.datetimepicker-input').datetimepicker({
    locale: 'fr',
    format: 'LT',
    minDate: moment({h:8}),
    maxDate: moment({h:12})
}); */
//Matin
$('#m_start_hour').datetimepicker({
    locale: 'fr',
    format: 'LT',
    /* minDate: moment({
        h: 8
    }),
    maxDate: moment({
        h: 14
    }) */
});
$('#m_end_hour').datetimepicker({
    locale: 'fr',
    format: 'LT',
    /* minDate: moment({
        h: 8
    }),
    maxDate: moment({
        h: 14
    }) */
});
//Après midi
$('#a_start_hour').datetimepicker({
    locale: 'fr',
    format: 'LT',
    /* minDate: moment({
        h: 13
    }),
    maxDate: moment({
        h: 20
    }) */
});
$('#a_end_hour').datetimepicker({
    locale: 'fr',
    format: 'LT',
    /* minDate: moment({
        h: 13
    }),
    maxDate: moment({
        h: 20
    }) */
});

var _resetTimes = function() {
    $('#m_start_hour,#m_end_hour,#a_start_hour,#a_end_hour,#m_duration,#a_duration,#duration').val('');
}
/* var _calculateDuration= function(startTime,endTime){
    var hours = moment.duration(moment(endTime, 'HH:mm').diff(moment(startTime, 'HH:mm'))).asHours();
    return hours;
} */
var _calculateTotalDuration = function() {
    var d1 = $('#m_duration').val();
    var d2 = $('#a_duration').val();
    $('#duration').val(Number(d1)+Number(d2));
}
var _calculateDuration = function(type) {
    if (type == 'm') {
        var loader = 'SPAN_CALCULATE_DurationOfMorning';
        var startTime = $('#m_start_hour').val();
        var endTime = $('#m_end_hour').val();
        var input_duration = 'm_duration';
    } else if (type == 'a') {
        var loader = 'SPAN_CALCULATE_DurationOfAfternoon';
        var startTime = $('#a_start_hour').val();
        var endTime = $('#a_end_hour').val();
        var input_duration = 'a_duration';
    }
    return _calculateDurationWithValidation(startTime, endTime, loader, input_duration);
}
var _calculateDurationWithValidation = function(startTime, endTime, loader, input_duration) {
    _showLoader(loader);
    //Cas champ vide
    if (startTime == '' || endTime == '') {
        _showResponseMessage('error',
            "Veuillez saisir une heure de début et de fin pour pouvoir calculer la durée.");
        $('#' + loader).html('<i class="flaticon2-reload"></i>');
        return false;
    }
    //Cas heure fin inférieur heure début
    if (endTime <= startTime) {
        _showResponseMessage('error',
            'L\'heure de fin doit être supérieure à l\'heure de début pour pouvoir calculer la durée.');
        $('#' + loader).html('<i class="flaticon2-reload"></i>');
        return false;
    }

    if (startTime && endTime) {
        var hours = moment.duration(moment(endTime, 'HH:mm').diff(moment(startTime, 'HH:mm'))).asHours();
        $('#' + input_duration).val(hours);
        _calculateTotalDuration();
    }
    $('#' + loader).html('<i class="flaticon2-reload"></i>');
    return true;
}
</script>