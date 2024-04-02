{{-- Extends layout --}}
@extends('layout.default')
{{-- Styles Section --}}
@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"
        integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .select2-dropdown {
            z-index: 1061;
        }

        .select2-container {
            width: 100% !important;
        }

        .navi .navi-item:last-child {
            padding-top: 12px;
        }

        #chartdiv,
        #chartdiv2 {
            width: 100%;
            height: 500px;
        }
    </style>
@endsection

{{-- Content --}}
@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 ">
            <div class="card-title">
                <h3 class="card-label">Statistiques et Export</h3>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h3>Le nombre de tickets non terminés par statuts et types</h3>
                <div class="dropdown dropdown-inline mr-2">
                    <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="la la-download"></i></button>
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                <a href="{{ url('/api/sdt/tasks/export') }}" class="navi-link">
                                    <span class="navi-icon">
                                        <i class="la la-file-excel-o"></i>
                                    </span>
                                    <span class="navi-text">Excel</span>
                                </a>
                            </li>
                        </ul>
                        <!--end::Navigation-->
                    </div>
                    <!--end::Dropdown Menu-->
                </div>
            </div>

            
            

            <div id="chartdiv"></div>

            <div class="d-flex justify-content-between">
                <h3>suivre l'évolution des tickets terminés/non terminés en focntion des types</h3>
                <div class="dropdown dropdown-inline mr-2">
                    <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="la la-download"></i></button>
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                {{-- <a href="{{ url('/api/sdt/tasksevolution/export') }}" class="navi-link"> --}}
                                <a href="/api/sdt/tasksevolution/export" id="export-tasks" class="navi-link">
                                    <span class="navi-icon">
                                        <i class="la la-file-excel-o"></i>
                                    </span>
                                    <span class="navi-text">Excel</span>
                                </a>
                            </li>
                        </ul>
                        <!--end::Navigation-->
                    </div>
                    <!--end::Dropdown Menu-->
                </div>
            </div>
            <select id="ticketContact" onchange="refreshChart()" class="mt-5" name="" id="">
                <option value="1">tickets terminer</option>
                <option value="0">tickets non terminer</option>
            </select>

            <select id="ticketType" onchange="refreshChart()" class="mt-5" name="" id="">
                <option value="">--tous les types--</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            <div id="chartdiv2"></div>

            {{-- <x-filter-form type="task" /> --}}
            {{-- <div class="row">
                <div class="col ">
                    <div class="d-flex justify-content-start">
                        <div class="col-lg-4">
                            <label>Responsable:</label>
                            <select class="form-control datatable-input" data-col-index="7" id="responsableSelect"
                                name="filter_responsable">
                                <option value="">Tous</option>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label>Date de création:</label>
                            <div class="input-daterange input-group" id="filter_datepicker">
                                <input type="text" class="form-control datatable-input" name="filter_start"
                                    value="" placeholder="Du" data-col-index="5" autocomplete="off" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="la la-ellipsis-h"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control datatable-input" name="filter_end" value=""
                                    placeholder="Au" data-col-index="5" autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-lg-2 d-flex align-items-end">
                            <button type="button" class="btn btn-info"><i class="fa-solid fa-arrows-rotate"></i></button>
                        </div>
                    </div>
                    <table style="margin-top: 10px;" class="table table-striped table-bordered table-hover "
                        id="dt_tasks_stats">
                        <thead>
                            <tr>
                                <th class=""></th>
                                <th class="">Créé</th>
                                <th class="">Validé</th>
                                <th class="">PEC</th>
                                <th class="">En Cours</th>
                                <th class="bg-success">Terminé</th>
                                <th class="bg-warning">En Attente</th>
                                <th class="bg-danger">Annulée</th>
                            </tr>
                        </thead>
                        <tbody >
                        </tbody>
                    </table>
                </div>
                <div class="col">

                </div>
            </div> --}}


            <!--begin::filter-->
            <!--end::filter-->
        </div>
    </div>

    <!--end::Card-->
@endsection

{{-- Styles Section --}}
@section('styles')
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

{{-- Scripts Section --}}
@section('scripts')
    {{-- charts --}}
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    {{-- vendors --}}
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>

    {{-- page scripts --}}
    <script src="{{ asset('custom/js/general.js?v=0') }}"></script>
    <!-- <script src="{{ asset('custom/js/list-afs.js?v=1') }}"></script> -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        _loadDatasForSelectOptions('statusSelect', 'AF_STATUS', 0, 1);
        _loadDatasForSelectOptions('statesSelect', 'AF_STATES', 0, 1);
        _loadDatasForSelectOptions('typesDispositifSelect', 'AF_DISPOSITIF_TYPES', 0, 1);
        _loadDatasFormationsForSelectOptions('pfFormationsSelect', 0, 1);
        $('#pfFormationsSelect').select2();

        var dtUrl = '/api/select/options/getStatsTable';
        var table = $('#dt_tasks_stats');
        // begin first table
        table.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            "responsive": true,
            "paging": false,
            "info": false,
            "fixedHeader": true,
            "searching": false,
            ajax: {
                url: dtUrl,
                type: 'GET',
                data: {
                    pagination: {
                        perpage: 2,
                    },
                },
            },
            "columns": [{
                    "data": "nom"
                },
                {
                    "data": "etat_1"
                },
                {
                    "data": "etat_2"
                },
                {
                    "data": "etat_3"
                },
                {
                    "data": "etat_4"
                },
                {
                    "data": "etat_5"
                },
                {
                    "data": "etat_6"
                },
                {
                    "data": "etat_7"
                }
            ],
            lengthMenu: [5, 10, 25, 50],
            pageLength: 5,

            // createdRow: function ( tr ) {
            //    $(tr).addClass('parent');
            //  },
            columnDefs: [{
                targets: 0,
                width: '20px',
                className: 'details-control',
                orderable: false,
                //added to avoid the problem from DB // ERROR => Requested unknown parameter '15' for row 0, column 15.
                "defaultContent": "0",
                "targets": "_all"
            }],
        });


        // table.on('change', '.group-checkable', function() {
        //     var set = $(this).closest('table').find('td:first-child .checkable');
        //     var checked = $(this).is(':checked');

        //     $(set).each(function() {
        //         if (checked) {
        //             $(this).prop('checked', true);
        //             $(this).closest('tr').addClass('active');
        //         } else {
        //             $(this).prop('checked', false);
        //             $(this).closest('tr').removeClass('active');
        //         }
        //     });
        // });

        // table.on('change', 'tbody tr .checkbox', function() {
        //     $(this).parents('tr').toggleClass('active');
        // });

        // var _reload_dt_tasks_stats = function() {
        //     $('#dt_tasks_stats').DataTable().ajax.reload();
        // }

        //filtre 
        _getSource('sourceSelect');
        _getType('typeSelect');
        _getEtat('etatSelect');
        _loadcontacts('responsableSelect');
        //_getTicketsNonTerminerParContact();
        // _loadstatscontacts('responsableSelect');
        $('#responsableSelect').select2();

        // function _loadstatscontacts(select_id) {
        //     $.ajax({
        //         type: 'GET',
        //         url: 'api/select/options/getStatscontact',
        //         headers: {
        //             'X-Requested-With': 'XMLHttpRequest'
        //         },
        //         dataType: 'json',
        //         success: function(response) {
        //             var array = response;
        //             if (array != '') {
        //                 for (i in array) {
        //                     $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
        //                         "</option>");
        //                 }
        //             }
        //         },
        //         error: function(x, e) {}
        //     }).done(function() {});
        // }

        function _loadcontacts(select_id, intern = false) {
            $.ajax({
                type: 'GET',
                url: 'api/select/options/getcontacts' + (intern ? '?intern=1' : ''),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataType: 'json',
                success: function(response) {
                    var array = response;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                                "</option>");
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }


        function _getSource(select_id) {
            $.ajax({
                type: 'GET',
                url: 'getSource',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataType: 'json',
                success: function(response) {
                    var array = response.datas;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                                "</option>");
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        function _getType(select_id) {
            $.ajax({
                type: 'GET',
                url: 'getType',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataType: 'json',
                success: function(response) {
                    var array = response.datas;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                                "</option>");
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        function _getEtat(select_id) {
            $.ajax({
                type: 'GET',
                url: 'getEtat',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataType: 'json',
                success: function(response) {
                    var array = response.datas;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                                "</option>");
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {});
        }

        $('#filter_datepicker,#datetimepicker').datepicker({
            language: 'fr',
            rtl: KTUtil.isRTL(),
            format: 'dd/mm/yyyy',
            todayHighlight: true,
            orientation: "bottom left",
            templates: {
                leftArrow: '<i class="la la-angle-left"></i>',
                rightArrow: '<i class="la la-angle-right"></i>'
            }
        });

        var form_id = 'formFiltertask';
        $("#" + form_id).submit(function(event) {
            event.preventDefault();
            KTApp.blockPage();
            var formData = $(this).serializeArray();
            var table = 'dt_tasks_stats';
            $.ajax({
                type: "POST",
                dataType: 'json',
                data: formData,
                url: dtUrl,
                success: function(response) {
                    if (response.data.length == 0) {
                        $('#' + table).dataTable().fnClearTable();
                        return 0;
                    }
                    $('#' + table).dataTable().fnClearTable();
                    $("#" + table).dataTable().fnAddData(response.data, true);
                },
                error: function() {
                    $('#' + table).dataTable().fnClearTable();
                }
            }).done(function(data) {
                KTApp.unblockPage();
            });
            return false;
        });
        var _reset = function() {
            _reload_dt_tasks_stats();
        }
        var _viewTask = function(row_id) {
            window.location.href = "/view/task/" + row_id;
        }
        var _formTask = function(row_id) {
            window.location.href = "/view/edit/" + row_id;
        }

        var _cancelTask = function(row_id) {
            var successMsg = "La tâche a été annulée.";
            var errorMsg = "La tâche n\'a pas été annulée.";
            var swalConfirmTitle = "Annulation de cette tâche";
            var swalConfirmText = "Êtes-vous sûr de bien vouloir Annuler l'état de cette tâche?";

            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Annuler",
                cancelButtonText: "Non"
            }).then(function(result) {
                if (result.value) {

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: 'GET',
                        url: '/annulateTask/' + row_id,
                        dataType: 'JSON',
                        success: function(result) {
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'succès',
                                    text: 'Votre tâche a été annulée !',
                                })
                                _reload_dt_tasks_stats();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'L\'annulation n\'a pas marché!',
                                })
                            }
                        }
                    });
                }
            });
        }

        var _terminateTask = function(row_id) {
            var successMsg = "La tâche a été terminée.";
            var errorMsg = "La tâche n\'a pas été terminée.";
            var swalConfirmTitle = "Terminer cette tâche!";
            var swalConfirmText = "Êtes-vous sûr de bien vouloir terminer l'état de cette tâche?";

            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Terminer",
                cancelButtonText: "Non"
            }).then(function(result) {
                if (result.value) {

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: 'GET',
                        url: '/terminateTask/' + row_id,
                        dataType: 'JSON',
                        success: function(result) {
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'succès',
                                    text: 'Votre tâche a été terminée !',
                                })
                                _reload_dt_tasks_stats();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'La tâche n\'est pas terminée!',
                                })
                            }
                        }
                    });
                }
            });
        }

        var _reportTask = function(row_id) {
            var successMsg = "La tâche a été reportée.";
            var errorMsg = "La tâche n\'a pas été reportée.";
            var swalConfirmTitle = "Reporter cette tâche!";
            var swalConfirmText = "Êtes-vous sûr de bien vouloir reporter cette tâche? Si oui saisir une date !";

            Swal.fire({
                title: swalConfirmTitle,
                html: '<input id="datereport" class="form-control" autofocus>',
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Reporter",
                cancelButtonText: "Non",
                didOpen: function() {
                    $('#datereport').datepicker({
                        language: 'fr',
                        rtl: KTUtil.isRTL(),
                        format: 'dd/mm/yyyy',
                        todayHighlight: true,
                        orientation: "bottom left",
                        templates: {
                            leftArrow: '<i class="la la-angle-left"></i>',
                            rightArrow: '<i class="la la-angle-right"></i>'
                        }
                    });
                },
                preConfirm: () => {
                    var datereport = $('#datereport').val();
                    if (datereport == "") {
                        Swal.showValidationMessage(
                            `Merci de saisir une date!`
                        )
                    }
                }
            }).then(function(result) {
                if (result.value) {
                    var formData = [];
                    var datereport = $("#datereport").val();

                    formData = formData.concat([{
                            name: "id",
                            value: row_id
                        },
                        {
                            name: "date",
                            value: datereport
                        }
                    ]);

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: 'POST',
                        url: '/reportTask',
                        dataType: 'JSON',
                        data: formData,
                        success: function(result) {
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'succès',
                                    text: 'Votre tâche a été reportée!',
                                })
                                _reload_dt_tasks_stats();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'La tâche n\'a pas été reportée!',
                                })
                            }
                        }
                    });
                }
            });
        }

        var _validateTask = function(row_id) {
            var successMsg = "La transformation a été faite.";
            var errorMsg = "La transformation n\'a pas été faite.";
            var swalConfirmTitle = "Transférer cette tâche!";
            var swalConfirmText = "Êtes-vous sûr de bien transférer cette tâche?";

            Swal.fire({
                title: swalConfirmTitle,
                html: '<select id="my-select2"><option value="">Tous</option></select>',
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Transférer",
                cancelButtonText: "Non",
                didOpen: function() {
                    _loadcontacts('my-select2', true);
                    $('#my-select2').select2();
                },
                preConfirm: () => {
                    var responsable = $('#my-select2').val();
                    if (responsable == "") {
                        Swal.showValidationMessage(
                            `Merci de sélectionner un responsable!`
                        )
                    }
                }
            }).then(function(result) {
                if (result.value) {
                    var formData = [];
                    var responsable = $("#my-select2").val();

                    formData = formData.concat([{
                            name: "id",
                            value: row_id
                        },
                        {
                            name: "responsable",
                            value: responsable
                        }
                    ]);

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: 'POST',
                        url: '/transfertTask',
                        dataType: 'JSON',
                        data: formData,
                        success: function(result) {
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'succès',
                                    text: 'Votre tâche a été transférée!',
                                })
                                _reload_dt_tasks_stats();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'La tâche n\'a pas été transférée!',
                                })
                            }
                        }
                    });
                }
            });
        }

        var _subTask = function(row_id) {
            window.location.href = "/view/subtask/" + row_id;
        }

        var _generatecomment = function(row_id) {
            var successMsg = "Votre commentaire a été bien crée";
            var errorMsg = "votre commentaire n\'a pas été crée.";
            var swalConfirmTitle = "Crée Le(s) commentaire(s)!";
            var swalConfirmText = "Êtes-vous sûr de vouloir Créer Le(s) commentaire(s)?";

            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Créer",
                cancelButtonText: "Non"
            }).then(function(result) {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: 'GET',
                        url: '/email/sendMailTask/' + row_id,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'succès',
                                    text: 'Votre mail a été bien envoyé!',
                                })
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Votre n\'a pas été envoyé!',
                                })
                            }
                        },
                        error: function(x, e) {}
                    }).done(function() {});

                }
            });
        }

        var _sendTask = function(row_id) {
            var successMsg = "Votre mail a été envoyé.";
            var errorMsg = "votre mail n\'a pas été envoyé.";
            var swalConfirmTitle = "Envoyer un mail!";
            var swalConfirmText = "Êtes-vous sûr de vouloir envoyer ce courriel?";

            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Envoyer",
                cancelButtonText: "Non"
            }).then(function(result) {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: 'GET',
                        url: '/email/sendMailTask/' + row_id,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'succès',
                                    text: 'Votre mail a été bien envoyé!',
                                })
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Votre n\'a pas été envoyé!',
                                })
                            }
                        },
                        error: function(x, e) {}
                    }).done(function() {});

                }
            });
        }
    </script>

    <script>
        /**
         * ---------------------------------------
         * This demo was created using amCharts 5.
         * 
         * For more information visit:
         * https://www.amcharts.com/
         * 
         * Documentation is available at:
         * https://www.amcharts.com/docs/v5/
         * ---------------------------------------
         */

        // Create root element
        // https://www.amcharts.com/docs/v5/getting-started/#Root_element
        var root2 = am5.Root.new("chartdiv2");


        // Set themes
        // https://www.amcharts.com/docs/v5/concepts/themes/
        root2.setThemes([
            am5themes_Animated.new(root2)
        ]);


        // Create chart2
        // https://www.amcharts.com/docs/v5/charts/xy-chart/
        var chart2 = root2.container.children.push(am5xy.XYChart.new(root2, {
            panX: true,
            panY: true,
            wheelX: "panX",
            wheelY: "zoomX",
            pinchZoomX: true
        }));

        // Add cursor
        // https://www.amcharts.com/docs/v5/charts/xy-chart/cursor/
        var cursor2 = chart2.set("cursor", am5xy.XYCursor.new(root2, {}));
        cursor2.lineY.set("visible", false);


        // Create axes
        // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
        var xRenderer2 = am5xy.AxisRendererX.new(root2, {
            minGridDistance: 30
        });
        xRenderer2.labels.template.setAll({
            rotation: -90,
            centerY: am5.p50,
            centerX: am5.p100,
            paddingRight: 15
        });

        var xAxis2 = chart2.xAxes.push(am5xy.CategoryAxis.new(root2, {
            maxDeviation: 0.3,
            categoryField: "nom",
            renderer: xRenderer2,
            tooltip: am5.Tooltip.new(root2, {})
        }));

        var yAxis2 = chart2.yAxes.push(am5xy.ValueAxis.new(root2, {
            maxDeviation: 0.3,
            renderer: am5xy.AxisRendererY.new(root2, {})
        }));


        // Create series
        // https://www.amcharts.com/docs/v5/charts/xy-chart/series/
        var series2 = chart2.series.push(am5xy.ColumnSeries.new(root2, {
            name: "Series 1",
            xAxis: xAxis2,
            yAxis: yAxis2,
            valueYField: "value",
            sequencedInterpolation: true,
            categoryXField: "nom",
            tooltip: am5.Tooltip.new(root2, {
                labelText: "{valueY}"
            })
        }));

        series2.columns.template.setAll({
            cornerRadiusTL: 5,
            cornerRadiusTR: 5
        });
        series2.columns.template.adapters.add("fill", function(fill, target) {
            return chart2.get("colors").getIndex(series2.columns.indexOf(target));
        });

        series2.columns.template.adapters.add("stroke", function(stroke, target) {
            return chart2.get("colors").getIndex(series2.columns.indexOf(target));
        });

        //chart.dataSource.url="/getType";
        // Set data

        var data2 = [];

        var dataexcel = [];

        

        function refreshChart(){

            var finito = $('#ticketContact').val();
            var selecttype = $('#ticketType').val();
            
            $.ajax({
            type: 'GET',
            url: '/getTicketsTerminerParContact',
            data: {
                finito : finito,
                selecttype : selecttype
            },
            dataType: 'json',
            success: function(response) {
                xAxis2.data.setAll(response);
                series2.data.setAll(response);
                dataexcel=response;
                $('#export-tasks').attr('href', '/api/sdt/tasksevolution/export?exportData='+JSON.stringify(dataexcel));
            },
            error: function(x, e) {}
            }).done(function() {});
        }

        refreshChart();
        

        // xAxis2.data.setAll(data2);
        // series2.data.setAll(data2);


        // Make stuff animate on load
        // https://www.amcharts.com/docs/v5/concepts/animations/
        series2.appear(1000);
        chart2.appear(1000, 100);
    </script>

    <script>
        /**
         * ---------------------------------------
         * This demo was created using amCharts 5.
         * 
         * For more information visit:
         * https://www.amcharts.com/
         * 
         * Documentation is available at:
         * https://www.amcharts.com/docs/v5/
         * ---------------------------------------
         */

        // Create root element
        // https://www.amcharts.com/docs/v5/getting-started/#Root_element
        var root = am5.Root.new("chartdiv");

        // Set themes
        // https://www.amcharts.com/docs/v5/concepts/themes/
        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        // Create chart
        // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/
        var chart = root.container.children.push(
            am5percent.PieChart.new(root, {
                endAngle: 270
            })
        );

        // Create series
        // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Series
        var series = chart.series.push(
            am5percent.PieSeries.new(root, {
                valueField: "value",
                categoryField: "name",
                endAngle: 270
            })
        );

        series.states.create("hidden", {
            endAngle: -90
        });

        // Set data
        // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Setting_data
        //chart.dataSource.url="/getType";

        series.data.setAll([]);

        $.ajax({
            type: 'GET',
            url: '/getTicketsNonTerminer',
            dataType: 'json',
            success: function(response) {
                series.data.setAll(response);
                // series.data.setAll(response);
            },
            error: function(x, e) {}
        }).done(function() {});

        series.appear(1000, 100);

    </script>

    {{-- <script>
        function _exportEvolutionTasks(){

            $.ajax({
            type: 'POST',
            url: '/api/sdt/tasksevolution/export',
            data: {
                exportData : JSON.stringify(dataexcel)
            },
            dataType: 'json',
            success: function(response) {
                alert(response);
                // series.data.setAll(response);
            },
            error: function(x, e) {}
        }).done(function() {});
        }
    </script> --}}
@endsection
