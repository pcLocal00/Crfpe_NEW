{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Sessions</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1">Liste des sessions</span>
            </div>
            <div class="card-toolbar">

                <button onclick="_formSession(0,0)" class="btn btn-sm btn-icon btn-light-primary mr-2">
                    <i class="flaticon2-add-1"></i>
                </button>

                <button onclick="_reload_dt_sessions()" class="btn btn-sm btn-icon btn-light-info mr-2">
                    <i class="flaticon-refresh"></i>
                </button>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">

            <!--begin::filter-->
            <x-filter-form type="Sessions"/>
            <!--end::filter-->


            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_sessions">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Session</th>
                    <th style="width: 10%">AF</th>
                    <th style="width: 30%">Date début-fin</th>
                    <th>Infos</th>
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
    <x-modal id="modal_form_session" content="modal_form_session_content"/>
    <x-modal id="modal_date_session" content="modal_date_session_content"/>

@endsection

{{-- Styles Section --}}
@section('styles')
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('custom/plugins/jstree/dist/themes/default/style.min.css') }}" rel="stylesheet"
          type="text/css"/>
@endsection


{{-- Scripts Section --}}
@section('scripts')
    {{-- vendors --}}
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>
    <script src="{{ asset('custom/plugins/jstree/dist/jstree.min.js') }}"></script>
    {{-- page scripts --}}
    <script src="{{ asset('custom/js/general.js?v=0') }}"></script>
    <script src="{{ asset('custom/js/list-sessions.js?v=1') }}"></script>
@endsection
