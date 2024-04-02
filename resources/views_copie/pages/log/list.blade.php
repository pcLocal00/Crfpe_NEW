{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

<div class="row">
    <div class="col-lg-12">
        <!--begin::Card-->
        <div class="card card-custom">

            <div class="card-header">
                <div class="card-title">
                    <span class="card-icon">
                        <i class="fas fa-history text-primary"></i>
                    </span>
                    <h3 class="card-label">Logs
                    </h3>
                </div>
                <div class="card-toolbar">
                    <button type="button" data-toggle="tooltip" title="Afficher les grains archivés"
                        class="btn btn-sm btn-icon btn-light-info mr-2"
                        onclick="resfreshJSTreeCataloguesWithTrashed()"><i class="fas fa-archive"></i></button>
                </div>
            </div>

            <div class="card-body">
                <!--begin: Datatable-->
                <table class="table table-bordered table-checkable" id="kt_dt_logs">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>log_name</th>
                            <th>description</th>
                            <th>subject_type</th>
                            <th>subject_id</th>
                            <th>causer_type</th>
                            <th>causer_id</th>
                            <th>properties</th>
                            <th>infos</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>

{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=1') }}"></script>
<!-- <script src="{{ asset('custom/js/list-logs.js?v=8') }}"></script> -->

<script>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var table = $('#kt_dt_logs');
// begin first table
table.DataTable({
    responsive: true,
    paging: true,
    ordering: false,
    ajax: {
        url: '/api/sdt/logs',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
        },
    },
    columns: [
        {
            data: 'id'
        },
        {
            data: 'log_name'
        },
        {
            data: 'description'
        },
        {
            data: 'subject_type'
        },
        {
            data: 'subject_id'
        },
        {
            data: 'causer_type'
        },
        {
            data: 'causer_id'
        },
        {
            data: 'properties'
        },
        {
            data: 'infos'
        },
        {
            data: 'Actions',
            responsivePriority: -1
        },
    ],
    lengthMenu: [5, 10, 25, 50],
    pageLength: 25,
    headerCallback: function(thead, data, start, end, display) {
        thead.getElementsByTagName('th')[0].innerHTML = `
                    <label class="checkbox checkbox-single">
                        <input type="checkbox" value="" class="group-checkable"/>
                        <span></span>
                    </label>`;
    },
    columnDefs: [{
        targets: 0,
        width: '30px',
        className: 'dt-left',
        orderable: false,
        render: function(data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        },
    }],
});

table.on('change', '.group-checkable', function() {
    var set = $(this).closest('table').find('td:first-child .checkable');
    var checked = $(this).is(':checked');

    $(set).each(function() {
        if (checked) {
            $(this).prop('checked', true);
            $(this).closest('tr').addClass('active');
        } else {
            $(this).prop('checked', false);
            $(this).closest('tr').removeClass('active');
        }
    });
});

table.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_sheets = function(){
    $('#kt_dt_logs').DataTable().ajax.reload();
}
</script>

@endsection