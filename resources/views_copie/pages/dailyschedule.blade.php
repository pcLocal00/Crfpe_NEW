{{-- Extends layout --}}
@extends('layout.login_layout')

{{-- Content --}}
@section('content')

<!--begin::Card-->
<div class="card card-custom">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-label"><img alt="Plateforme de formation SOLARIS" src="/media/logo/logo-light.png"> Planning journalier des salles pour la base LILLE</h3>
        </div>
        <div class="card-toolbar">
        </div>
    </div>
    <div class="card-body">
        <div id="LOADER" style="display:none;" class="spinner spinner-primary mb-4"></div>
        <div id="kt_calendar"></div>
    </div>
</div>
<!--end::Card-->

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" />
<style>
    .fc-event{
        width: 300px !important;
    }
</style>
@endsection

{{-- Scripts Section --}}
@section('scripts')
<script src="{{ asset('plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/fr.min.js" integrity="sha512-vz2hAYjYuxwqHQAgHPZvry+DTuwemFT/aBIDmgE0cnmYENu/+t8c3u/nX2Ont6e+3m+W6FEKxN1granjgGfr1Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
            var todayDate = moment().startOf('day');
            var current_time=moment().format("HH:mm:ss");
            var YM = todayDate.format('YYYY-MM');
            var YESTERDAY = todayDate.clone().subtract(1, 'day').format('YYYY-MM-DD');
            var TODAY = todayDate.format('YYYY-MM-DD');
            var TOMORROW = todayDate.clone().add(1, 'day').format('YYYY-MM-DD');

            var calendarEl = document.getElementById('kt_calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: [ 'bootstrap', 'interaction', 'dayGrid', 'timeGrid', 'list' ],
                locale: 'fr',
                themeSystem: 'bootstrap',
                isRTL: false,
                header: {
                    //left: 'prev,next today',
                    left: 'prev,next',
                    center: 'title',
                    //right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    right: ''
                },
                height: 800,
                contentHeight: 780,
                aspectRatio: 3,  // see: https://fullcalendar.io/docs/aspectRatio

                nowIndicator: true,
                now: TODAY + 'T'+current_time,

                views: {
                    //dayGridMonth: { buttonText: 'month' },
                    //timeGridWeek: { buttonText: 'week' },
                    //timeGridDay: { buttonText: 'day' }
                },

                defaultView: 'timeGridDay',
                defaultDate: TODAY,
                allDaySlot:false,
                slotDuration:"00:30:00",
                minTime:"05:00:00",
                maxTime:"23:00:00",
                editable: true,
                eventLimit: true, // allow "more" link when too many events
                navLinks: true,
                displayEventTime : false,

                loading: function (isLoading) {
                    //alert('events are being rendered'); // Add your script to show loading
                    if (isLoading) {
                        $('#LOADER').show();
                    }else {                
                        $('#LOADER').hide();
                    }
                },
                events: '/dailyschedule/json',

                eventRender: function(info) {
                    var element = $(info.el);

                    if (info.event.extendedProps && info.event.extendedProps.description) {
                        if (element.hasClass('fc-day-grid-event')) {
                            element.data('content', info.event.extendedProps.description);
                            element.data('placement', 'top');
                            KTApp.initPopover(element);
                        } else if (element.hasClass('fc-time-grid-event')) {
                            element.find('.fc-title').append('<div class="fc-description">' + info.event.extendedProps.description + '</div>');

                            element.data('content', info.event.extendedProps.description);
                            element.data('placement', 'top');
                            element.data('html', true);
                            KTApp.initPopover(element);
                        } else if (element.find('.fc-list-item-title').lenght !== 0) {
                            element.find('.fc-list-item-title').append('<div class="fc-description">' + info.event.extendedProps.description + '</div>');
                        }
                    }
                }
            });

            calendar.render();
</script>
@endsection