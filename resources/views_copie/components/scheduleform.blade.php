<div class="d-flex flex-row  mb-3">
    <!--begin::Dates -->
    <div class="p-1 ">
        <input type="hidden" name="sessiondates[{{ $key }}][id]" value="{{ ($sd && $sd['id'])?$sd['id']:0 }}">
        <input type="hidden" name="sessiondates[{{ $key }}][session_id]" value="{{ $sessionid }}">
        <input type="text" name="sessiondates[{{ $key }}][planning_date]"
            class="form-control form-control-sm date_datepicker" value="{{ ($sd && $sd['planning_date'])?$sd['planning_date']:'' }}" required/>

    </div>
    <!--end::Dates -->
    @if(isset($sd['schedules']['M']['id']))
    <!--begin::Début (h) -->
    <div class="p-1 ">

        <input type="hidden" name="sessiondates[{{ $key }}][schedules][M][id]"
            value="{{ ($sd && $sd['schedules']['M']['id'])?$sd['schedules']['M']['id']:0 }}">
        <input type="hidden" name="sessiondates[{{ $key }}][schedules][M][sessiondate_id]" value="{{ ($sd && $sd['id'])?$sd['id']:0 }}">
        <input type="hidden" name="sessiondates[{{ $key }}][schedules][M][type]" value="M">
        <input type="text" name="sessiondates[{{ $key }}][schedules][M][start_hour]" data-key="{{ $key }}" id="m_start_hour_{{ $key }}"
            value="{{ ($sd && $sd['schedules']['M']['start_hour'])?$sd['schedules']['M']['start_hour']:'' }}" class="form-control form-control-sm datetimepicker-input-m m_start_hour"
            data-toggle="datetimepicker" />

    </div>
    <!--end::Début (h) -->
    <!--begin::Fin  (h) -->
    <div class="p-1 ">
        <input type="text" name="sessiondates[{{ $key }}][schedules][M][end_hour]"
            value="{{ ($sd && $sd['schedules']['M']['end_hour'])?$sd['schedules']['M']['end_hour']:'' }}" data-key="{{ $key }}" id="m_end_hour_{{ $key }}" class="form-control form-control-sm datetimepicker-input-m m_end_hour"
            data-toggle="datetimepicker" />
    </div>
    <!--end::Fin  (h) -->

    <!--begin::Durée (h) -->
    <div class="p-1 ">
        <input type="text" name="sessiondates[{{ $key }}][schedules][M][duration]" data-key="{{ $key }}" id="m_duration_{{ $key }}"
            value="{{ ($sd && $sd['schedules']['M']['duration'])?$sd['schedules']['M']['duration']:'' }}" class="form-control form-control-sm form-control-solid m_duration"
            readonly>
    </div>
    <!--end::Durée (h) -->
    @endif

    @if(isset($sd['schedules']['A']['id']))
    <!--begin::Début  (h) -->
    <div class="p-1">
        <input type="hidden" name="sessiondates[{{ $key }}][schedules][A][id]" value="{{ ($sd && $sd['schedules']['A']['id'])?$sd['schedules']['A']['id']:0 }}">
        <input type="hidden" name="sessiondates[{{ $key }}][schedules][A][sessiondate_id]" value="{{ ($sd && $sd['id'])?$sd['id']:0 }}">
        <input type="hidden" name="sessiondates[{{ $key }}][schedules][A][type]" value="A">
        <input type="text" name="sessiondates[{{ $key }}][schedules][A][start_hour]" data-key="{{ $key }}" id="a_start_hour_{{ $key }}"
            value="{{ ($sd && $sd['schedules']['A']['start_hour'])?$sd['schedules']['A']['start_hour']:'' }}" class="form-control form-control-sm datetimepicker-input-a a_start_hour"
            data-toggle="datetimepicker" />
    </div>
    <!--end::Début  (h) -->

    <!--begin::Fin  (h) -->
    <div class="p-1">
        <input type="text" name="sessiondates[{{ $key }}][schedules][A][end_hour]" data-key="{{ $key }}" id="a_end_hour_{{ $key }}"
            value="{{ ($sd && $sd['schedules']['A']['end_hour'])?$sd['schedules']['A']['end_hour']:'' }}" class="form-control form-control-sm datetimepicker-input-a a_end_hour"
            data-toggle="datetimepicker" />
    </div>
    <!--end::Fin  (h) -->

    <!--begin::Durée (h) -->
    <div class="p-1">
        <input type="text" name="sessiondates[{{ $key }}][schedules][A][duration]" data-key="{{ $key }}" id="a_duration_{{ $key }}"
            value="{{ ($sd && $sd['schedules']['A']['duration'])?$sd['schedules']['A']['duration']:'' }}" class="form-control form-control-sm form-control-solid a_duration" readonly>
    </div>
    <!--end::Durée (h) -->
    @endif

    <!--begin::Total  (h) -->
    <div class="p-1">
        <input type="text" name="sessiondates[{{ $key }}][duration]" value="{{ ($sd && $sd['duration'])?$sd['duration']:'' }}" id="row_duration_{{ $key }}"
            class="form-control form-control-sm form-control-solid" readonly>
    </div>
    <!--end::Total  (h) -->
    @php
        $sessiondate_id=($sd && $sd['id'])?$sd['id']:0;
    @endphp
    <div class="p-1"><a href="javascript:;" class="btn btn-sm btn-light-danger" onclick="_deleteSessionDate({{$sessiondate_id}})"><i class="la la-trash-o"></i></a></div>
</div>