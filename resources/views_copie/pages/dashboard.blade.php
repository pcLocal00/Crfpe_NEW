{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

{{-- Dashboard 1 --}}

<div class="row">
    <div class="col-lg-12 col-xxl-12">
        @include('pages.widgets._widget-stats', ['class' => 'bgi-no-repeat'])
    </div>
</div>

@endsection

{{-- Scripts Section --}}
@section('scripts')
<script type="text/javascript">
_get_widgets_stats();
function _get_widgets_stats() {
    var spinner = '<span class="spinner spinner-primary spinner-sm"></span>';
    $('#nb_pf,#nb_clients,#nb_contacts,#nb_afs,#nb_sessions,#nb_devis,#nb_agreements,#nb_invoices').html(spinner);
    $.ajax({
        url: '/api/statistics/dashboard/widgets',
        type: 'GET',
        dataType: 'JSON',
        success: function(result, status) {
            $('#nb_pf').html(result.nb_pf);
            $('#nb_clients').html(result.nb_clients);
            $('#nb_contacts').html(result.nb_contacts);
            $('#nb_afs').html(result.nb_afs);
            $('#nb_sessions').html(result.nb_sessions);
            $('#nb_devis').html(result.nb_devis);
            $('#nb_agreements').html(result.nb_agreements);
            $('#nb_invoices').html(result.nb_invoices);
            $('#nb_tasks').html(result.nb_tasks);
            $('#nb_task_in_progress').html(result.nb_task_in_progress);
            $('#nb_task_unread').html(result.nb_task_unread);
            $('#nb_task_depassed').html(result.nb_task_depassed);
            $('#nb_task_ended').html(result.nb_task_ended);
        },
        error: function(result, status) {},
        complete: function(result, status) {}
    });
}
</script>
@endsection