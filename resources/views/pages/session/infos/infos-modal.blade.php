@php
    $modal_title = "Les infos de la session : ".$session->code;
$cssClassDates=\App\Library\Helpers\Helper::getCssClassForDatesPlanned($number_of_dates_planned,$session->nb_total_dates_to_program);
$cssClassHours=\App\Library\Helpers\Helper::getCssClassForHoursPlanned($number_of_hours_planned,$session->nb_hours);
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modal_form_session_title"><i class="flaticon-edit"></i> {{ $modal_title }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>

<!--begin::Card-->
<div class="card card-custom card-fit card-border" style="margin:24px ">
    <div class="card-body p-1">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-dark-75 font-weight-bolder mr-2">Modèle de planification:</span>
            <span class="text-muted">{{ $sessionPlanningTemplate }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-dark-75 font-weight-bolder mr-2">Type de session:</span>
            <span class="text-muted">{{ $session_type }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-dark-75 font-weight-bolder mr-2">Nb de dates connues à programmer:</span>
            <span class="label label-primary">{{ $session->nb_dates_to_program }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-dark-75 font-weight-bolder mr-2">Nb de dates totales à programmer:</span>
            <span class="label label-primary">{{ $session->nb_total_dates_to_program }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-dark-75 font-weight-bolder mr-2">Nb de jours:</span>
            <span class="label label-primary">{{ $session->nb_days }}j</span>
        </div>
        <div class="d-flex justify-content-between align-items-cente my-1">
            <span class="text-dark-75 font-weight-bolder mr-2">Nb d'heures:</span>
            <span class="label label-primary">{{ $session->nb_hours }}h</span>
        </div>
    </div>
</div><!--begin::Card-->

<div class="card card-custom card-fit card-border" style="margin:24px ">
    <div class="card-title ext-dark-90 font-weight-bolder mr-2" style="margin-bottom: 2px">
        Dates & Séances :
    </div>
    <div class="card-body p-1">

        <div class="p-2">
            <p class="text-{{ $cssClassDates }}"><strong
                    class="font-weight-bolder">{{ $number_of_dates_planned }}</strong> dates planifiées /
                <strong class="font-weight-bolder">{{ $session->nb_total_dates_to_program }}</strong> dates
                totales à programmer</p>
        </div>
        <div class="p-2">
            <p class="text-{{ $cssClassHours }} float-right"><strong
                    class="font-weight-bolder">{{ \App\Library\Helpers\Helper::convertTime($number_of_hours_planned) }}</strong>
                planifiées / <strong class="font-weight-bolder">{{ $session->nb_hours }}h00</strong> programmées
            </p>
        </div>

    </div>
</div>
<!--begin::Card-->
<div class="modal-body" id="modal_info_session_body">
    <div data-scroll="true" data-height="300">

        <input type="hidden" value="{{$session->id}}" id="info_session_id">
        <div class="card card-custom card-border">
            <div class="card-title ext-dark-90 font-weight-bolder mr-2" style="margin-top: 2px;
margin-bottom: 10px">
                Planning :
            </div>
            <div class="card-body p-3">
                <!--begin: jstree-->
                <div id="tree_schedulecontacts" class="tree-demo font-size-sm"></div>
                <!--end: jstree-->
            </div>
        </div>
    </div>
</div>
<!--end::Card-->

<style>
    .jstree-anchor > .jstree-checkbox-disabled {
        display: none;
    }

    .jstree-default .jstree-anchor {
        height: 100% !important;
    }
</style>
<script>
    var mode = "withcontacts";
    var sessionId = $('#info_session_id').val();
    var tree_schedulecontacts = 'tree_schedulecontacts';
    $('#' + tree_schedulecontacts).jstree({
        "core": {
            "multiple": true,
            "themes": {
                "responsive": true
            },
            'data': {
                'url': function (node) {
                    return '/api/tree/schedules/session/' + sessionId + '/' + mode;
                },
                'data': function (node) {
                    return {
                        'parent': node.id
                    };
                }
            },
        },
        "checkbox": {
            "three_state": true, // to avoid that fact that checking a node also check others
        },
        "plugins": ["state", "types", "wholerow", "checkbox"]
        //"plugins": ["state", "types"]
    });

    $('[data-scroll="true"]').each(function () {
        var el = $(this);
        KTUtil.scrollInit(this, {
            mobileNativeScroll: true,
            handleWindowResize: true,
            rememberPosition: (el.data('remember-position') == 'true' ? true : false)
        });
    });
</script>
