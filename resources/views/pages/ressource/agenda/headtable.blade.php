@php
$currentDate=Carbon\Carbon::now();
$currentDay=$currentDate->dayOfWeek;
$arrayDays=[0=>'Dimanche',1=>'Lundi',2=>'Mardi',3=>'Mercredi',4=>'Jeudi',5=>'Vendredi',6=>'Samedi'];
@endphp
<style>
    .current-day{
        background-color: #E1F0FF !important;
    }
</style>
<!--begin: Datatable-->
<div class="table-responsive">
    <table class="table table-bordered" id="dt_schedulerooms">
        <thead class="thead-light">
            <tr>
                <th><strong>Salle</strong></th>
                @if($dates)
                @foreach($dates as $d)
                @php
                    $newDate=Carbon\Carbon::createFromFormat('Y-m-d', $d);
                    $day=$newDate->dayOfWeek;
                @endphp
                <th class="{{($currentDay==$day)?'current-day':''}}"><p class="text-center">{{$arrayDays[$day]}}</p><p class="text-center"><strong>{{$newDate->format('d-m-Y')}}</strong></p></th>
                @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
<div>
<!--end: Datatable-->
<script>
var dtUrl = '/api/sdt/agenda/{{$start_date}}/{{$nb_days}}';
//console.log(dtUrl);
var table = $('#dt_schedulerooms');
// begin first table
table.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    ordering: false,
    ajax: {
        url: dtUrl,
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
            af_id : {{$af_id}},
        },
    },
    lengthMenu: [5, 10, 25, 50],
    pageLength: 25,
    columnDefs: [{}],
});
</script>