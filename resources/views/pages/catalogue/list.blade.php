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
                        <i class="flaticon-map text-primary"></i>
                    </span>
                    <h3 class="card-label">Catalogue
                    </h3>
                </div>
                <div class="card-toolbar">
                    <button type="button" data-toggle="tooltip" title="Élargir tous" onclick="ExpandCollapseAll('tree_catalogues','EXPAND')"
                        class="btn btn-sm btn-icon btn-light-primary mr-2">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                    <button type="button" data-toggle="tooltip" title="Réduire tous" onclick="ExpandCollapseAll('tree_catalogues','COLLAPSE')"
                        class="btn btn-sm btn-icon btn-light-success mr-2">
                        <i class="fa fa-chevron-up"></i> 
                    </button>

                    <button type="button" data-toggle="tooltip" title="Afficher les grains archivés"
                        class="btn btn-sm btn-icon btn-light-info mr-2" onclick="resfreshJSTreeCatalogues(1)"><i
                            class="fas fa-archive"></i></button>
                    
                    <button type="button" data-toggle="tooltip" title="Rafraîchir tous"
                        class="btn btn-sm btn-icon btn-light-danger mr-2" onclick="resfreshJSTreeCatalogues(0)"><i
                            class="flaticon-refresh"></i></button>

                    <button type="button" data-toggle="tooltip" title="Afficher les PFs"
                        class="btn btn-sm btn-icon btn-light-warning mr-2" onclick="resfreshJSTreeCatalogues(2)">PF</button>

                    <button type="button" data-toggle="tooltip" title="Ajouter" onclick="_formCategorie(0)"
                        class="btn btn-sm btn-icon btn-light-primary">
                        <i class="flaticon2-add-1"></i> 
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!--begin: jstree-->
                <div id="tree_catalogues" class="tree-demo"></div>
                <!--end: jstree-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

<x-modal id="modal_form_catalogue" content="modal_form_catalogue_content" />

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('custom/plugins/jstree/dist/themes/default/style.min.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
<script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>
<script src="{{ asset('custom/plugins/jstree/dist/jstree.min.js') }}"></script>

{{-- page scripts --}}
<script src="{{ asset('custom/js/general.js?v=2') }}"></script>
<script src="{{ asset('custom/js/list-catalogue.js?v=1') }}"></script>
@endsection