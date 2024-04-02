@if($session)
@php
$started_at =$ended_at = '';
if($session && $session->started_at!=null){
$dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$session->started_at);
$started_at = $dt->format('d/m/Y');
}
if($session && $session->ended_at!=null){
$dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$session->ended_at);
$ended_at = $dt->format('d/m/Y');
}
$cssClassDates=\App\Library\Helpers\Helper::getCssClassForDatesPlanned($number_of_dates_planned,$session->nb_total_dates_to_program);
$cssClassHours=\App\Library\Helpers\Helper::getCssClassForHoursPlanned($number_of_hours_planned,$session->nb_hours);
@endphp
    <form id="formSessiondates">
        <!-- BEGIN:DATES -->
        <div class="row">
            <div class="col-lg-12">

                <div class="d-flex flex-row mb-2">
                    <div class="p-2">
                        <p>Session : <strong>{{ $session->code }}</strong></p>
                        <p class="mb-0">{{ $session->title }}</p>
                    </div>
                </div>
                <div class="d-flex flex-row mb-2">
                    <div class="p-2">
                        <p>Durées : <strong class="text-primary">{{ $session->nb_days }}</strong> jours / <strong
                                class="text-primary">{{ $session->nb_hours }}</strong> h
                        </p>
                    </div>
                    <div class="p-2">
                        <p>Date de début : <strong class="text-primary">{{ $started_at }}</strong> - Date de fin :
                            <strong class="text-primary">{{ $ended_at }}</strong>
                        </p>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-6">
                        <div class="p-2">
                            <p class="text-{{ $cssClassDates }}"><strong class="font-weight-bolder">{{ $number_of_dates_planned }}</strong> dates planifiées / <strong class="font-weight-bolder">{{ $session->nb_total_dates_to_program }}</strong> dates totales à programmer</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="p-2">
                            <p class="text-{{ $cssClassHours }} float-right"><strong class="font-weight-bolder">{{ \App\Library\Helpers\Helper::convertTime($number_of_hours_planned) }}</strong> planifiées / <strong class="font-weight-bolder">{{ $session->nb_hours }}h00</strong> programmées</p>
                        </div>
                    </div>
                </div>



            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th></th>
                            <th id="th_s1" colspan="3">Séance 1</th>
                            <th id="th_s2" colspan="3">Séance 2</th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th>Dates</th>
                            <th id="th_start_s1">Début (h)</th>
                            <th id="th_end_s1">Fin (h)</th>
                            <th id="th_duration_s1">Durée (h)</th>
                            <th id="th_start_s2">Début (h)</th>
                            <th id="th_end_s2">Fin (h)</th>
                            <th id="th_duration_s2">Durée (h)</th>
                            <th>Total (h)</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                @if(count($sessiondatesArray)>0)
                @foreach($sessiondatesArray as $key=>$sd)
                <x-scheduleform :key="$key" :sd="$sd" :sessionid="$session->id" />
                @endforeach
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <td>Total des heures planifiées : </td>
                            <td>
                                <input type="text" name="session_duration" id="total_duration"
                                    value="{{ $number_of_hours_planned }}"
                                    class="form-control form-control-sm form-control-solid" placeholder="07:00"
                                    readonly>
                            </td>
                            <td>
                                <span id="SPAN_TOTAL_FORMATED" class="text-info"></span>
                            </td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <button type="button" class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                    title="Ajouter une date" onclick="_formAddSessionDate({{ $session->id }},0)"><i
                        class="flaticon2-add-1"></i></button>
                <button type="submit" class="btn btn-sm btn-outline-primary float-right"><i class="fa fa-check"></i>
                    Enregistrer <span id="BTN_SAVE"></span></button>
            </div>
        </div>

    </form>
    
    <!-- END:DATES -->
    <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('.date_datepicker').datepicker({
        language: 'fr',
        format: 'dd/mm/yyyy',
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
    });
    $('.datetimepicker-input-m').datetimepicker({
        locale: 'fr',
        format: 'LT',
        //minDate : moment('07:00','H:i'),
        //maxDate : moment('12:30','H:i')
    });
    $('.datetimepicker-input-a').datetimepicker({
        locale: 'fr',
        format: 'LT',
        //minDate : moment('13:00','H:i'),
        //maxDate : moment('20:00','H:i')
    });
    $("#formSessiondates").validate({
        rules: {},
        messages: {},
        submitHandler: function(form) {
            _showLoader('BTN_SAVE');
            var formData = $(form).serializeArray(); // convert form to array
            //console.log(formData);
            //_checkEndDateGreaterThanStartDate();
            //
            
            var iError = 0;
            var msg = '';
            $(".m_start_hour").each(function(index) {
                key = $(this).data("key");
                m_start_hour = $('#m_start_hour_' + key).val();
                m_end_hour = $('#m_end_hour_' + key).val();
                if(m_end_hour<m_start_hour){
                    iError ++;
                    msg = msg + '<p>La date de début : <strong>'+m_start_hour+'</strong> est supérieur a la date de fin : <strong>'+m_end_hour+'</strong></p>';
                }
            });
            $(".a_start_hour").each(function(index) {
                key = $(this).data("key");
                a_start_hour = $('#a_start_hour_' + key).val();  
                a_end_hour = $('#a_end_hour_' + key).val();
                if(a_end_hour<a_start_hour){
                    iError ++;
                    msg = msg + '<p>La date de début : <strong>'+a_start_hour+'</strong> est supérieur a la date de fin : <strong>'+a_end_hour+'</strong></p>';
                }
            });
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
                _hideLoader('BTN_SAVE');
                return false;
            }

            $.ajax({
                type: 'POST',
                url: '/form/sessiondate',
                data: formData,
                dataType: 'JSON',
                success: function(result) {
                    _hideLoader('BTN_SAVE');
                    if (result.success) {
                        _showResponseMessage('success', result.msg);
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                },
                error: function(error) {
                    _hideLoader('BTN_SAVE');
                    _showResponseMessage('error',
                        'Veuillez vérifier les champs du formulaire...');
                },
                complete: function(resultat, statut) {
                    _hideLoader('BTN_SAVE');
                    _loadPlanningsDates();
                }
            });
            return false;
        }
    });

    function _checkEndDateGreaterThanStartDate(){
        var iError = 0;
        var msg = '';
        $(".m_start_hour").each(function(index) {
            key = $(this).data("key");
            m_start_hour = $('#m_start_hour_' + key).val();
            m_end_hour = $('#m_end_hour_' + key).val();
            if(m_end_hour<m_start_hour){
                iError ++;
                msg = msg + '<p>La date de début : <strong>'+m_start_hour+'</strong> est supérieur a la date de fin : <strong>'+m_end_hour+'</strong></p>';
            }
        });
        $(".a_start_hour").each(function(index) {
            key = $(this).data("key");
            a_start_hour = $('#a_start_hour_' + key).val();  
            a_end_hour = $('#a_end_hour_' + key).val();
            if(a_end_hour<a_start_hour){
                iError ++;
                msg = msg + '<p>La date de début : <strong>'+a_start_hour+'</strong> est supérieur a la date de fin : <strong>'+a_end_hour+'</strong></p>';
            }
        });
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
        }
    }
    _hideThTableDynamicly();
    function _hideThTableDynamicly(){
        myArrayMoonrningDuration = [];
        $(".m_duration").each(function(index) {
            key_m = $(this).data("key");
            myArrayMoonrningDuration[key_m] = parseFloat($(this).val());
        });
        myArrayAfternoonDuration = [];
        $(".a_duration").each(function(index) {
            key_a = $(this).data("key");
            myArrayAfternoonDuration[key_a] = parseFloat($(this).val());
        });
        if(myArrayMoonrningDuration.length==0){
            $("#th_s1,#th_start_s1,#th_end_s1,#th_duration_s1").hide();
        }
        if(myArrayAfternoonDuration.length==0){
            $("#th_s2,#th_start_s2,#th_end_s2,#th_duration_s2").hide();
        }
    }

    var _calculateTotalDuration = function() {
        var m_duration = 0;
        myArrayMoonrningDuration = [];
        $(".m_duration").each(function(index) {
            m_duration = m_duration + parseFloat($(this).val());
            key_m = $(this).data("key");
            myArrayMoonrningDuration[key_m] = parseFloat($(this).val());
        });
        var a_duration = 0;
        myArrayAfternoonDuration = [];
        $(".a_duration").each(function(index) {
            a_duration = a_duration + parseFloat($(this).val());
            key_a = $(this).data("key");
            myArrayAfternoonDuration[key_a] = parseFloat($(this).val());
        });
        
        if(myArrayMoonrningDuration.length>0){
            myArrayMoonrningDuration.map(function(num, idx) {
                //console.log(myArrayAfternoonDuration);
                var total =num; 
                if(myArrayAfternoonDuration.length>0 && myArrayAfternoonDuration[idx]){
                    total = num + myArrayAfternoonDuration[idx];
                }
                
                $('#row_duration_' + idx).val(total);
            });
        }else{
            myArrayAfternoonDuration.map(function(num, idx) {
                var total =num; 
                if(myArrayMoonrningDuration.length>0 && myArrayMoonrningDuration[idx]){
                    total = num + myArrayMoonrningDuration[idx];
                }
                $('#row_duration_' + idx).val(total);
            });
        }
        var total_duration = (m_duration + a_duration).toFixed(2);
        $('#total_duration').val(total_duration);
        //span total formated
        $('#SPAN_TOTAL_FORMATED').html(convertFloatToTime(total_duration));
    }
    _calculateTotalDuration();


    $(".m_start_hour").each(function(index) {
        key = $(this).data("key");
        $('#m_start_hour_' + key).on("input", function() {
            key_input = $(this).data("key");
            m_start_hour = $(this).val();
            m_end_hour = $('#m_end_hour_' + key_input).val();
            updateTotalDuration(m_start_hour, m_end_hour, key_input, 'm_duration_');
        });
        $('#m_end_hour_' + key).on("input", function() {
            key_m_input = $(this).data("key");
            m_end_hour_1 = $(this).val();
            m_start_hour_1 = $('#m_start_hour_' + key_m_input).val();
            updateTotalDuration(m_start_hour_1, m_end_hour_1, key_m_input, 'm_duration_');
        });
    });

    $(".a_start_hour").each(function(index) {
        key = $(this).data("key");
        $('#a_start_hour_' + key).on("input", function() {
            key_input_a = $(this).data("key");
            a_start_hour = $(this).val();
            a_end_hour = $('#a_end_hour_' + key_input_a).val();  
            updateTotalDuration(a_start_hour, a_end_hour, key_input_a, 'a_duration_');
        });
        $('#a_end_hour_' + key).on("input", function() {
            key_input_a_1 = $(this).data("key");
            a_end_hour_1 = $(this).val();
            a_start_hour_1 = $('#a_start_hour_' + key_input_a_1).val();
            updateTotalDuration(a_start_hour_1, a_end_hour_1, key_input_a_1, 'a_duration_');
        });
    });

    function updateTotalDuration(m_start_hour_1, m_end_hour_1, key_m_input, input_id) {
        var m_start_hour_1_float = timeStringToFloat(m_start_hour_1);
        var m_end_hour_1_float = timeStringToFloat(m_end_hour_1);
        m_duration_1 = parseFloat(m_end_hour_1_float) - parseFloat(m_start_hour_1_float);
        $('#' + input_id + key_m_input).val(m_duration_1.toFixed(2));
        _calculateTotalDuration();
    }

    var _formAddSessionDate = function(session_id, sessiondate_id) {
        var modal_id = 'modal_form_sessiondate';
        var modal_content_id = 'modal_form_sessiondate_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/sessiondate/' + session_id + '/' + sessiondate_id,
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
    </script>
    @endif