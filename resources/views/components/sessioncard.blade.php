@php

$createdAt = ($session->created_at)?$session->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($session->updated_at)?$session->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($session->deleted_at)?$session->deleted_at->format('d/m/Y H:i'):'';

$labelActive='Désactivée';
$cssClassActive='danger';
if($session->is_active==1){
$labelActive='Activée';
$cssClassActive='success';
}
$stateArray=\App\Library\Helpers\Helper::getNameParamByCodeStatic($session->state);
$state=$stateArray['name'];
$stateCssClass=$stateArray['css_class'];
$sessionTypeArray=\App\Library\Helpers\Helper::getNameParamByCodeStatic($session->session_type);
$session_type=$sessionTypeArray['name'];
$sessionTypeCssClass=$sessionTypeArray['css_class'];
$sessionPlanningTemplate=\App\Library\Helpers\Helper::getNamePlanningtemplateStatic($session->planning_template_id);
@endphp
<!--begin::Card-->
<div class="card card-custom gutter-b card-stretch">
    <!--begin::Body-->
    <div class="card-body pt-4">
        <!--begin::Toolbar-->
        <div class="d-flex justify-content-end">
                        
            <div class="dropdown dropdown-inline" data-toggle="tooltip" title="" data-placement="left"
                data-original-title="Quick actions">
                <a href="#" class="btn btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="ki ki-bold-more-hor"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-md dropdown-menu-right" style="">
                    <!--begin::Navigation-->
                    <ul class="navi navi-hover">
                        <li class="navi-item">
                            <a style="cursor:pointer;" class="btn btn-clean font-weight-bold btn-sm"
                                onclick="_formSession({{ $session->id }})">
                                <i class="flaticon-edit"></i></a>
                        </li>
                        <li class="navi-item">
                            <a style="cursor:pointer;" class="btn btn-clean font-weight-bold btn-sm"
                                onclick="_deleteSession({{ $session->id }})">
                                <i class="flaticon-delete"></i></a>
                        </li>
                    </ul>
                    <!--end::Navigation-->
                </div>
            </div>
        </div>
        <!--end::Toolbar-->
        <input type="hidden" id="INPUT_SESSION_ID_{{ $session->id }}" name="session_id" value="{{ $session->id }}">
        <!--begin::Session titre-->
        <span class="label label-outline-{{ $stateCssClass }} label-pill label-inline mr-2 mb-2">{{ $state }}</span>
        <span
            class="label label-outline-{{ $cssClassActive }} label-pill label-inline mr-2 mb-2">{{ $labelActive }}</span>

        <p>
            <span class="label label-outline-info label-pill label-inline mr-2 mb-2">C : {{ $createdAt }}</span>
            <span class="label label-outline-primary label-pill label-inline mr-2 mb-2">M : {{ $updatedAt }}</span>
        </p>

        <span class="text-muted font-weight-bold">{{ $session->code }}</span>

        <p class="mb-7">{{ $session->title }}</p>
        @php
        $started_at = $ended_at = '';
        if($session->started_at!=null){
        $dst = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$session->started_at);
        $started_at = $dst->format('d/m/Y');
        }
        if($session->ended_at!=null){
        $det = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$session->ended_at);
        $ended_at = $det->format('d/m/Y');
        }
        @endphp
        <div class="d-flex flex-wrap">
            <div class="mr-12 d-flex flex-column mb-7">
                <span class="d-block font-weight-bold mb-4">Date début</span>
                <span class="btn btn-light-primary btn-sm font-weight-bold btn-upper btn-text">{{ $started_at }}</span>
            </div>
            <div class="mr-12 d-flex flex-column mb-7">
                <span class="d-block font-weight-bold mb-4">Date fin</span>
                <span class="btn btn-light-danger btn-sm font-weight-bold btn-upper btn-text">{{ $ended_at }}</span>
            </div>
        </div>
        <!-- <p class="mb-7"><span class="text-danger">28h planifiées / 21h programmées</span></p> -->
        <!--end::Session titre-->
        <!--begin::Info-->
        <div class="mb-7">
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
        <!--end::Info-->
        <div class="row" id="BLOCK_SESSION_DATES_{{ $session->id }}">
        </div>
    </div>
    <!--end::Body-->
</div>
<!--end:: Card-->
<script>
//BLOCK_DATES
var _load_session_dates_schedules_summary= function(session_id) {
    var block_id = 'BLOCK_SESSION_DATES_'+session_id;
    $('#' + block_id).html('<div class="col-md-12"><div class="spinner spinner-primary spinner-lg"></div></div>');
    $.ajax({
        url: '/get/session/summary/dates/' + session_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + block_id).html(html);
        },
        error: function(result, status, error) {

        },
        complete: function(result, status) {}
    });
}
_load_session_dates_schedules_summary({{ $session->id }});
</script>