{{-- Extends layout --}}
@extends('layout.default')
{{-- Styles Section --}}
{{-- @section('styles')
    <style>
        .select2-dropdown {
            z-index: 1061;
        }

        .select2-container{
            width: 100% !important;
        }

        .navi .navi-item:last-child{
            padding-top: 12px;
        }
        .dataTables_wrapper .dataTables_scroll .dataTable {
            width: 1600px !important;
            margin: 0 auto;
        }

        pre{
            width:100px;
            height: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            color:blue;
            text-decoration: underline;
            white-space: normal !important;
        }
        pre:hover{
            width: 100%;
            height: 100%;
            color:black;
            text-decoration: none;
        }
    </style>
@endsection --}}

{{-- Content --}}
@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 ">
            <div class="card-title">
                <h3 class="card-label">Mes tâches</h3>
            </div>
            <div class="card-toolbar">
                <a class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Ajouter une tâche"
                    href="{{ url('/addtask') }}"><i class="flaticon2-add-1"></i></a>
            </div>
        </div>
        <div class="card-body" style="width:100%">
            <x-filter-form type="task" />
            <!--begin::filter-->
            <!--end::filter-->
            <!--begin: Datatable-->
            <table class="table table-bordered table-hover" id="dt_tasks" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        {{-- <th></th> --}}
                        <th></th>
                        <th>Entité</th>
                        <th>Contact</th>
                        <th>Dates</th>
                        <th>État</th>
                        <th>Résumé</th>
                        <!-- <th>Type</th> -->
                        <!-- <th>Source</th> -->
                        <!-- <th>Description</th> -->
                        <th>Superviseur</th>
                        <th>Responsable</th>
                        <!-- <th>Mode de rappel</th>
                        <th>Mode de réponse</th> -->
                        <!-- <th>Concerne</th> -->
                        <!-- <th>Commentaire</th> -->
                        <!-- <th>Priorité</th> -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <link href="{{ asset('custom/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <x-modal id="modal_form_contact" content="modal_form_contact_content" />
    <x-modal id="modal_form_email" content="modal_form_email_content" />

    <!--end::Card-->
@endsection

{{-- Styles Section --}}
@section('styles')
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css"
        type="text/css" />
    <link rel="stylesheet" type="text/css"
        href="{{ asset('custom/plugins/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css') }}">

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

        .dataTables_wrapper .dataTables_scroll .dataTable {
            width: 1600px !important;
            margin: 0 auto;
        }

        pre {
            width: 100px;
            height: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            color: blue;
            text-decoration: underline;
            white-space: normal !important;
        }

        pre:hover {
            width: 100%;
            height: 100%;
            color: black;
            text-decoration: none;
        }

        .modal #uploader_start {
            display: none;
        }
    </style>
@endsection


{{-- Scripts Section --}}
@section('scripts')
    {{-- vendors --}}
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.fr.min.js"
        crossorigin="anonymous"></script>
    <script src="{{ asset('custom/plugins/plupload/plupload.full.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/plupload/jquery.ui.plupload/jquery.ui.plupload.js') }}"></script>

    {{-- page scripts --}}
    <script src="{{ asset('custom/js/general.js?v=0') }}"></script>
    <!-- <script src="{{ asset('custom/js/list-afs.js?v=1') }}"></script> -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var dtUrl = '/api/sdt/tasks';
        var table = $('#dt_tasks');
        // begin first table

        table.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            // "scrollX": "100%",
            // "scroller": true,
            responsive: {
                details: {
                    type: "column",
                }
            },
            "paging": false,
            "info": false,
            "fixedHeader": false,
            "searching": false,
            "deferRender": true,
            ajax: {
                url: dtUrl,
                type: 'POST',
                data: {
                    only_my_tasks: $('input[name=only_my_tasks]').is(':checked') ? 1 : 0,
                    pagination: {
                        perpage: 2,
                    },
                },
            },
            lengthMenu: [5, 10, 25, 50],
            pageLength: 25,
            headerCallback: function(thead, data, start, end, display) {
                thead.getElementsByTagName('th')[1].innerHTML = `
            <label class="checkbox checkbox-single">
                <input type="checkbox" value="" class="group-checkable"/>
                <span></span>
            </label>`;
            },
            columnDefs: [
                {
                    targets: 0,
                    width: '30px',
                    className: 'dt-left',
                    orderable: false,
                    render: function(data, type, full, meta) {
                        return `
                <div class="flaticon-add" title="Afficher la liste des sous tâches">
                </div>`;
                    },
                },
                {
                    targets: 1,
                    width: '40px',
                    className: 'dt-left',
                    orderable: false,
                    render: function(data, type, full, meta) {
                        return `
                <label class="checkbox checkbox-single">
                    <input type="checkbox" value="` + data + `" class="checkable"/>
                    <span></span>
                </label>`;
                    },
                },
                {
                    targets: [9, 6],
                    width: '229px',
                    // className: 'details-control',
                    // orderable: false,
                },
            ],
        });

        $(".dataTables_scrollBody thead tr").hide();

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


        table.on('click', 'td.dt-left', function() {
            var tr = $(this).closest('tr');
            var row = table.DataTable().row(tr);
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                var taskid = row.data()[0];
                row.child(_subTabletasks(taskid)).show();
                tr.addClass('shown');
                _getSubTasks(taskid);
            }
        });

        var _subTabletasks = function(taskid) {
            return '<div id="child_data_subtasks_' + taskid +
                '" class="datatable datatable-default datatable-primary datatable-loaded"></div>';
        }

        var _getSubTasks = function(taskid) {
            var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
            $('#child_data_subtasks_' + taskid).parent().addClass('bg-light');
            $('#child_data_subtasks_' + taskid).html(spinner);
            $.ajax({
                url: '/get/subtasks/' + taskid,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#child_data_subtasks_' + taskid).html(html);
                }
            });
        }
        // table.on('click', 'td.details-control', function() {
        //     var tr = $(this).parents('tr');
        //     var row = table.row(tr);

        //     if ( row.child.isShown() ) {
        //         // This row is already open - close it
        //         row.child.hide();
        //         tr.removeClass('shown');
        //     }
        //     else {
        //         // Open this row (the format() function would return the data to be shown)
        //         row.child( format(row.data()) ).show();
        //         tr.addClass('shown');
        //     }
        // });

        var _reload_dt_tasks = function() {
            $('#dt_tasks').DataTable().ajax.reload();
        }

        //filtre 
        _getSource('sourceSelect');
        _getType('typeSelect');
        _getEtat('etatSelect');
        _loadcontacts('responsableSelect');
        $('#responsableSelect').select2();

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
            var table = 'dt_tasks';
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
            _reload_dt_tasks();
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
                                _reload_dt_tasks();
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
                                _reload_dt_tasks();
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
                                _reload_dt_tasks();
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
                                _reload_dt_tasks();
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
                    window.location.href = "/view/addcomment/" + row_id;
                }
            });
        }

        var _sendTask = function(row_id) {
            $.ajax({
                type: 'GET',
                url: '/email/sendMailTask/' + row_id,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataType: 'html',
                success: function(response) {
                    $('#modal_form_email_content').html(response);
                    $('#modal_form_email').modal('show');
                }
            })
        }

        // var _sendTask = function(row_id) {
        //     var successMsg = "Votre mail a été envoyé.";
        //     var errorMsg = "votre mail n\'a pas été envoyé.";
        //     var swalConfirmTitle = "Envoyer un mail!";
        //     var swalConfirmText ="Êtes-vous sûr de vouloir envoyer ce courriel?";

        //     Swal.fire({
        //         title: swalConfirmTitle,
        //         text: swalConfirmText,
        //         icon: "warning",
        //         showCancelButton: true,
        //         confirmButtonText: "Envoyer",
        //         cancelButtonText: "Non"
        //     }).then(function(result) {
        //         if (result.value) {
        //             $.ajaxSetup({
        //                 headers: {
        //                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //                 }
        //             });

        //             $.ajax({
        //                 type: 'GET',
        //                 url: '/email/sendMailTask/'+row_id ,
        //                 headers: {'X-Requested-With': 'XMLHttpRequest'},
        //                 dataType: 'json',
        //                 success: function(response) {
        //                     if(response.success){
        //                         Swal.fire({
        //                             icon: 'success',
        //                             title: 'succès',
        //                             text: 'Votre mail a été bien envoyé!',
        //                         })
        //                     }else{
        //                         Swal.fire({
        //                             icon: 'error',
        //                             title: 'Oops...',
        //                             text: 'Votre n\'a pas été envoyé!',
        //                         })
        //                     }
        //                 },
        //                 error: function(x, e) {}
        //             }).done(function() {         
        //             });

        //         }
        //     });
        // }

    // function Tabsubtasks(id){

    //     $.ajax({
    //         url: '/api/sdt/sub_tasks/' + id,
    //         type: 'GET',
    //         dataType: 'html',
    //         success: function(html, status) {
    //             console.log("OK OK");
    //         }
    //     });
    // }
    </script>
@endsection
