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
                        <h3 class="card-label">Structure temporelle
                        </h3>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" data-toggle="tooltip" title="Élargir tous"
                                onclick="ExpandCollapseAll('tree_structures','EXPAND')"
                                class="btn btn-sm btn-icon btn-light-primary mr-2">
                            <i class="fa fa-chevron-down"></i>
                        </button>
                        <button type="button" data-toggle="tooltip" title="Réduire tous"
                                onclick="ExpandCollapseAll('tree_structures','COLLAPSE')"
                                class="btn btn-sm btn-icon btn-light-success mr-2">
                            <i class="fa fa-chevron-up"></i>
                        </button>
                        <button type="button" data-toggle="tooltip" title="Rafraîchir tous"
                                class="btn btn-sm btn-icon btn-light-danger mr-2" onclick="resfreshJSTreeStructures(0)">
                            <i
                                class="flaticon-refresh"></i></button>

                        {{--    <button type="button" data-toggle="tooltip" title="Afficher les PFs"
                                    class="btn btn-sm btn-icon btn-light-warning mr-2"
                                    onclick="resfreshJSTreeStructures(2)">PF
                            </button>--}}

                        <button type="button" data-toggle="tooltip" title="Ajouter" onclick="_formStructure(0)"
                                class="btn btn-sm btn-icon btn-light-primary">
                            <i class="flaticon2-add-1"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!--begin: jstree-->
                    <div id="tree_structures" class="tree-demo"></div>
                    <!--end: jstree-->
                </div>
            </div>
            <!--end::Card-->
        </div>
    </div>

    <x-modal id="modal_form_structure" content="modal_form_structure_content"/>

    <br>
    <!-- Modeles Table-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Modèles</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1">Liste des modèles</span>
            </div>
            <div class="card-toolbar">

                <button onclick="_formModel(0)" class="btn btn-sm btn-icon btn-light-primary mr-2">
                    <i class="flaticon2-add-1"></i>
                </button>

                <button onclick="_reload_dt_models()" class="btn btn-sm btn-icon btn-light-info mr-2">
                    <i class="flaticon-refresh"></i>
                </button>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">


            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_models">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th style="width: 10%">Name</th>
                    <th style="width: 30%">Sort</th>
                    <th>Infos</th>
                    <th>Dates</th>
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
    <x-modal id="modal_form_model" content="modal_form_model_content"/>


@endsection

{{-- Styles Section --}}
@section('styles')
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
    <script src="{{ asset('custom/js/general.js?v=2') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var urlSrc = '/api/structures/0/0';
        $("#tree_structures").jstree({
            "core": {
                "themes": {
                    "responsive": false
                },
                // so that create works
                "check_callback": true,
                "data": {
                    'url': function (node) {
                        return urlSrc;
                    },
                    'data': function (node) {
                        return {
                            'parent': node.id
                        };
                    }
                }
            },
            "plugins": ["dnd", "state", "types"]
            //"plugins": ["state", "types"]
        }).bind("move_node.jstree", function (e, data) {
            _dragAndDropMove(data.node.id, data.parent, data.position);
        });

        var _formStructure = function (structure_id) {
            var modal_id = 'modal_form_structure';
            var modal_content_id = 'modal_form_structure_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/structure/' + structure_id,
                type: 'GET',
                dataType: 'html',
                success: function (html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function (result, status, error) {

                },
                complete: function (result, status) {

                }
            });
        }

        function resfreshJSTreeStructures(param) {
            $('#tree_structures').jstree(true).settings.core.data.url = '/api/structures/0/' + param;
            $('#tree_structures').jstree(true).refresh();
        }

        function resfreshJSTreeStructuresWithTrashed() {
            $('#tree_structures').jstree(true).settings.core.data.url = '/api/structures/0/1';
            $('#tree_structures').jstree(true).refresh();
        }

        function ExpandCollapseAll(idTree, type) {
            if (idTree != '' && type != '') {
                var action = '';
                if (type == 'EXPAND') {
                    action = 'open_all';
                } else if (type == 'COLLAPSE') {
                    action = 'close_all';
                }
                if (action != '') {
                    $('#' + idTree).jstree(action);
                }
            }
        }

        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        var table_sessions = $('#dt_models');

        // begin first table
        table_sessions.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            paging: true,
            ordering: false,
            processing: true,
            ajax: {
                url: '/api/sdt/models/0',
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
            lengthMenu: [5, 10, 25, 50],
            pageLength: 5,
            headerCallback: function (thead, data, start, end, display) {
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
                render: function (data, type, full, meta) {
                    return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
                },
            }],
        });

        var _reload_dt_models = function () {
            $('#dt_models').DataTable().ajax.reload();
        }

        var _formModel = function (model_id) {
            var modal_id = 'modal_form_model';
            var modal_content_id = 'modal_form_model_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';

            $('#' + modal_id).modal('show');


            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/model/' + model_id,
                type: 'GET',
                dataType: 'html',
                success: function (html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function (result, status, error) {

                },
                complete: function (result, status) {

                }
            });
        }

        $("#formModel").validate({
            rules: {},
            messages: {},
            submitHandler: function (form) {
                _showLoader('BTN_SAVE_MODEL');
                $.ajax({
                    type: 'POST',
                    url: '/form/model',
                    data: $(form).serialize(),
                    dataType: 'JSON',
                    success: function (result) {
                        _hideLoader('BTN_SAVE_STRUCTURE');
                        if (result.success) {
                            _showResponseMessage('success', result.msg);
                            $('#modal_form_model').modal('hide');
                            // location.reload();
                        } else {
                            _showResponseMessage('error', result.msg);
                        }

                    },
                    error: function (error) {
                        _hideLoader('BTN_SAVE_MODEL');
                        _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
                    },
                    complete: function (resultat, statut) {
                        _hideLoader('BTN_SAVE_MODEL');
                        resfreshJSTreeCatalogues();
                    }

                });
                _reload_dt_models();
                return false;
            }
        });


    </script>
@endsection
