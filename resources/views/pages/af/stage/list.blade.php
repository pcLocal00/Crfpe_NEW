{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<input type="hidden" name="id" id="VIEW_INPUT_AF_ID_HELPER" value="0">
<!--begin::Card-->
<div class="card card-custom">
    <div class="card-header flex-wrap border-0 ">
        <div class="card-title">
            <h3 class="card-label">Gestion des stages
            </h3>
        </div>
        <div class="card-toolbar">
            <!--begin::Dropdown-->
            <!--end::Dropdown-->
            <!--begin::Button-->
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_stages()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Ajouter une période de stage"
                onclick="_formStage(0,0)"><i class="flaticon2-add-1"></i></button>
            <!--end::Button-->
        </div>
    </div>
    <div class="card-body">
        <!--begin::filter-->
        <x-filter-form type="Stages" />
        <!--end::filter-->
        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_stages">
            <thead>
                <tr>
                    <th></th>
                    <th>Formation</th>
                    <th>Nom de période</th>
                    <th>Dates</th>
                    <th>Stagiaires</th>
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
<x-modal id="modal_form_stage" content="modal_form_stage_content" />
<x-modal id="modal_form_stageItem" content="modal_form_stageItem_content" />

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>


{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=2') }}"></script>
<script src="{{ asset('custom/js/list-stages.js?v=3') }}"></script>
@endsection