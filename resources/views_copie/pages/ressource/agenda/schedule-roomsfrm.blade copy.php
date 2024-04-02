{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!--begin::Card-->
<div class="card card-custom">
    <div class="card-header flex-wrap border-0 ">
        <div class="card-title">
            <h3 class="card-label">Planning des ressources
            </h3>
        </div>
        <div class="card-toolbar">
            <!--begin::Button-->

            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin::filter-->
        <div class="card mb-4">
            <div class="card-body p-2">

                <div class="row">
                    <div class="col-md-8">
                    @php
                    $dt=Carbon\Carbon::createFromFormat('Y-m-d', $startOfWeek);
                    $frStartOfWeek=$dt->format('d-m-Y');
                    @endphp    
                    <!-- date picker input -->
                        <div class="form-group row mb-0">
                            <div class="col-lg-3">
                                <div class="input-group date">
                                    <input type="text" class="form-control form-control-sm" readonly placeholder="Sélectionner une date"
                                        id="input_datepicker" autocomplete="off" value="{{$frStartOfWeek}}"/>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-calendar-check-o"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                {{-- <label><span id="LOADER_ACTIONS"></span></label> --}}
                                <select class="form-control select2" id="actionsSelect">
                                    <option value="0">Toutes les actions</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" onclick="_search_date()" class="btn btn-sm btn-outline-primary btn-outline-primary--icon"><span><i class="la la-search"></i><span>Filtrer</span></span></button>
                            </div>
                        </div>
                        <!-- date picker input -->
                    </div>
                    <div class="col-md-4">
                        <div class="btn-group float-right">
                            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                                title="Rafraîchir" onclick="_initView()"><i class="flaticon-refresh"></i></button>
                            <button type="button" class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                                title="La semaine précédente" onclick="_reload_calendar(1)"><i
                                    class="flaticon2-back"></i></button>
                            <button type="button" class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                                title="La semaine suivante" onclick="_reload_calendar(2)"><i
                                    class="flaticon2-next"></i></button>
                        </div>
                    </div>
                </div>



            </div>
        </div>
        <!--end::filter-->
        <div class="card">
            <div class="card-body p-2">
                <input id="INPUT_START_OF_CURRENT_WEEK" type="hidden" value="{{$startOfWeek}}">
                <input id="INPUT_START_OF_WEEK" type="hidden" value="{{$startOfWeek}}">
                <div id="block_table"></div>
            </div>
        </div>
    </div>
</div>
<!--end::Card-->

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>


{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=2') }}"></script>
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$('#input_datepicker').datepicker({
    language: 'fr',
    rtl: KTUtil.isRTL(),
    format: 'dd-mm-yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});

var start_date = $('#INPUT_START_OF_WEEK').val();
get_table(start_date, 6);

function get_table(start_date, nb_days) {
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#block_table').html(spinner);
    var af_id=$('#actionsSelect').val();
    //console.log(af_id);
    $.ajax({
        url: '/api/get/agendafrm/' + start_date + '/' + nb_days+'/'+af_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#block_table').html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}

function _initView() {
    var start_date = $('#INPUT_START_OF_CURRENT_WEEK').val();
    $('#INPUT_START_OF_WEEK').val(start_date);
    get_table(start_date, 6);
}
function _search_date(){
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#block_table').html(spinner);
    var date = $('#input_datepicker').val();
    $.ajax({
        url: '/api/get/date/3/' + date,
        type: 'GET',
        dataType: 'json',
        success: function(res, status) {
            get_table(res.date, 6);
            $('#INPUT_START_OF_WEEK').val(res.date);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}
function _reload_calendar(param) {
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#block_table').html(spinner);
    //param = 1 --> précédente
    //param = 2 --> suivante
    var current_start_date = $('#INPUT_START_OF_WEEK').val();
    ///get/date/{next_previous}/{current_start_date}
    $.ajax({
        url: '/api/get/date/' + param + '/' + current_start_date,
        type: 'GET',
        dataType: 'json',
        success: function(res, status) {
            //$('#block_table').html(html);
            //console.log(res.date);
            get_table(res.date, 6);
            $('#INPUT_START_OF_WEEK').val(res.date);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}
$('#actionsSelect').select2();
_loadDatasActionsForSelectOptions();
function _loadDatasActionsForSelectOptions() {
        var selected_value = 0;
        var select_id='actionsSelect';
        //_showLoader('LOADER_ACTIONS');
        $.ajax({
            url: '/api/select/options/afs/0',
            dataType: 'json',
            success: function (response) {
                var array = response;
                if (array != '') {
                    for (i in array) {
                        $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +"</option>");
                    }
                }
            },
            error: function (x, e) {
            }
        }).done(function () {
            if (selected_value != 0 && selected_value != '') {
                $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
            }
            //_hideLoader('LOADER_ACTIONS');
        });
}
</script>
@endsection