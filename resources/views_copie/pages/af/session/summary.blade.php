@php
$cssClassDates=\App\Library\Helpers\Helper::getCssClassForDatesPlanned($number_of_dates_planned,$session->nb_total_dates_to_program);
$cssClassHours=\App\Library\Helpers\Helper::getCssClassForHoursPlanned($number_of_hours_planned,$session->nb_hours);
@endphp
<div class="col-lg-12">
    <p class="text-dark-75 font-weight-bolder">Dates & séances :</p>
</div>
<div class="col-lg-12">
<p class="text-{{ $cssClassDates }}"><strong class="font-weight-bolder">{{ $number_of_dates_planned }}</strong> dates planifiées / <strong class="font-weight-bolder">{{ $session->nb_total_dates_to_program }}</strong> dates totales à programmer</p>
<p class="text-{{ $cssClassHours }}"><strong class="font-weight-bolder">{{ \App\Library\Helpers\Helper::convertTime($number_of_hours_planned) }}</strong> planifiées / <strong class="font-weight-bolder">{{ $session->nb_hours }}h00</strong> programmées</p>
</div>
@if(count($sessiondatesArray)>0)
@foreach($sessiondatesArray as $key=>$sd)
<div class="col-lg-6 mb-2">
    <div class="card card-custom card-fit card-border">
        <div class="card-body p-1">
            <ul class="list-unstyled mb-0">
                @php
                    $h=\App\Library\Helpers\Helper::convertTime($sd['duration']);
                @endphp
                <li class="text-primary font-weight-bolder">{{ $sd['planning_date'] }} ({{ $h }})
                    <ul>
                        @if(count($sd['schedules'])>0)
                        @foreach($sd['schedules'] as $ds)
                        @if($ds['type']=='M')
                        <li class="text-info font-weight-bold">S1 : {{ $ds['start_hour'] }} - {{ $ds['end_hour'] }}</li>
                        @elseif($ds['type']=='A')
                        <li class="text-info font-weight-bold">S2 : {{ $ds['start_hour'] }} - {{ $ds['end_hour'] }}</li>
                        @endif
                        @endforeach
                        @endif
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
@endforeach
@endif