<!--begin::Card-->
<input type="hidden" id="hidden_input_formation_id" value="{{ $formation_id }}">
<!--begin: Datatable-->
<table class="table table-bordered table-checkable" id="kt_sheets_datatable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Version</th>
            <th>Infos</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<!--end: Datatable-->
<!--end::Card-->
<script src="{{ asset('custom/js/list-sheet.js?v=2') }}"></script>