<input type="hidden" name="id" id="VIEW_INPUT_AF_ID_HELPER" value="{{ $row->id }}">
<input type="hidden" id="AF_DEVICE_TYPE" value="{{ $row->device_type }}">
@if ($viewtype == 'overview')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Aperçu</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <h4>Aperçu ...</h4>
        </div>
    </div>
@endif

@if ($viewtype == 'sheets')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Fiche technique</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            @if (auth()->user()->roles[0]->code != 'FORMATEUR')
                <div class="card-toolbar">
                    <button onclick="_formAfSheet({{ $row->id }},{{ $af_sheet_id }})"
                        class="btn btn-sm btn-icon btn-light-primary mr-2">
                        <i class="{{ $af_sheet_id > 0 ? 'flaticon-edit' : 'flaticon2-add-1' }}"></i>
                    </button>
                    <button onclick="_IMPORT_PF_DEFAULT_SHEET({{ $row->id }})"
                        class="btn btn-sm btn-icon btn-light-primary">
                        <i class="flaticon-download"></i>
                    </button>
                </div>
            @endif
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <p class="text-warning">Souhaitez vous importer la fiche technique configurées sur le produit de formation
                racine?
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                    title="Importer la fiche technique" onclick="_IMPORT_PF_DEFAULT_SHEET({{ $row->id }})"><i
                        class="flaticon-download"></i></button>
            </p>
            <!-- {!! $row->description !!} -->
            <div id="BLOCK_AF_SHEET"></div>
        </div>
    </div>
    <script>
        var _viewAfSheet = function(af_id, content_id) {
            var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
            $('#' + content_id).html(spinner);
            $.ajax({
                url: "/view/af/sheet/" + af_id,
                type: "GET",
                dataType: "html",
                success: function(html, status) {
                    $("#" + content_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {}
            });
        };
        var af_id = $("input[name='id']").val();
        _viewAfSheet(af_id, 'BLOCK_AF_SHEET');

        var _formAfSheet = function(af_id, id_sheet) {
            var modal_id = 'modal_sheet_formation';
            var modal_content_id = 'modal_sheet_formation_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/af/sheet/' + af_id + '/' + id_sheet,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {}
            });
        }
        var _IMPORT_PF_DEFAULT_SHEET = function(af_id) {
            KTApp.blockPage();
            $.ajax({
                url: '/copy/sheet/pf/af/' + af_id,
                type: "GET",
                dataType: "JSON",
                success: function(result, status) {
                    if (result.success) {
                        _showResponseMessage("success", result.msg);
                    } else {
                        _showResponseMessage("error", result.msg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error", 'Veuillez vérifier les champs du formulaire...');
                },
                complete: function(result, status) {
                    _loadContent('sheets');
                    KTApp.unblockPage();
                }
            });
        }
    </script>
@endif

@if ($viewtype == 'dates')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Les dates & séances</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">

                @if (count($sessions) > 0)

                    <div class="form-group row mb-0">
                        <label class="col-2 col-form-label">Session :</label>
                        <div class="col-10">

                            <select class="form-control select2" id="sessionsSelect" style="max-width:700px">

                                @foreach ($sessions as $session)
                                    <option value="{{ $session['id'] }}">{{ $session['title'] }}
                                        - {{ $session['code'] }}</option>
                                @endforeach

                            </select>

                        </div>
                    </div>
                @else
                    <div class="alert alert-custom alert-outline-danger fade show mb-5" role="alert">
                        <div class="alert-icon">
                            <i class="flaticon-warning"></i>
                        </div>
                        <div class="alert-text">Il faut créer une session pour pouvoir planifier
                            <button onclick="$('#NAV9').click();" class="btn btn-sm btn-icon btn-light-primary"><i
                                    class="flaticon2-add-1"></i></button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body" id="BLOCK_PLANNINGS">

        </div>
    </div>
    <script>
        $('.select2').select2();
        //var af_id = $("input[name='id']").val();
        //_loadDatasSessionsForSelectOptions('sessionsSelect', af_id, 0);
        $('#sessionsSelect').on('change', function() {
            _loadPlanningsDates();
        });
        var _loadPlanningsDates = function() {
            var session_id = $('#sessionsSelect').val();
            var block_id = 'BLOCK_PLANNINGS';
            $('#' + block_id).html('<div class="spinner spinner-primary spinner-lg"></div>');
            $.ajax({
                url: '/get/session/dates/' + ((session_id > 0) ? session_id : 0),
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + block_id).html(html);
                },
                error: function(result, status, error) {

                },
                complete: function(result, status) {}
            });
        }
        _loadPlanningsDates();

        var _scheduleDates = function() {
            var session_id = $('#sessionsSelect').val();
        }

        function _deleteSessionDate(sessiondate_id) {
            var successMsg = "La date a été supprimée.";
            var errorMsg = "La date n\'a pas été supprimée.";
            var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer la date?";
            var swalConfirmText =
                "La suppression comprend aussi la suppression des séances, planification (ressources,intervenants) !";
            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Supprimer"
            }).then(function(result) {
                if (result.value) {
                    KTApp.blockPage();
                    $.ajax({
                        url: "/api/delete/sessiondate",
                        type: "DELETE",
                        data: {
                            sessiondate_id: sessiondate_id
                        },
                        dataType: "JSON",
                        success: function(result, status) {
                            if (result.success) {
                                _showResponseMessage("success", successMsg);
                            } else {
                                _showResponseMessage("error", errorMsg);
                            }
                        },
                        error: function(result, status, error) {
                            _showResponseMessage("error", errorMsg);
                        },
                        complete: function(result, status) {
                            _loadPlanningsDates();
                            KTApp.unblockPage();
                        }
                    });
                }
            });
        }
    </script>
@endif

@if ($viewtype == 'sessions')
    <!--begin::Sessions-->

    <!--begin::Card-->
    <div class="card card-custom mb-5">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Sessions</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="col col-lg-8">
                <x-filter-form type="SessionsGrid" />
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title=""
                    onclick="_load_sessions()" data-original-title="Rafraîchir"><i class="flaticon-refresh"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title=""
                    onclick="_formSession(0)" data-original-title="Ajouter une session"><i
                        class="flaticon2-add-1"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
    </div>
    <!--end::Card-->

    <!--begin::Generate Feuille de présences-->
    <div class="accordion accordion-solid accordion-toggle-plus mb-4" id="accordionFeuillesPresence">
        <div class="card">
            <div class="card-header">
                <div class="card-title" data-toggle="collapse" data-target="#collapseFeuillesPresence">
                    <i class="flaticon-search"></i> Feuille de présence et absences :
                </div>
            </div>
            <div id="collapseFeuillesPresence" class="collapse" data-parent="#accordionFeuillesPresence">
                <div class="card-body">
                    <!-- <p><strong>Feuille de présence et absences : </strong></p> -->
                    <form id="formGenrateFeuillesPresence">
                        <input type="hidden" value="{{ $row->id }}" name="af_id">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4">
                                <label>Date du:</label>
                                <div class="input-daterange input-group" id="generate_pdf_datepicker">
                                    <input type="text" class="form-control datatable-input" name="start_date"
                                        value="" placeholder="Du" data-col-index="5" autocomplete="off" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-ellipsis-h"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control datatable-input" name="end_date"
                                        value="" placeholder="Au" data-col-index="5" autocomplete="off" />
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label>Lieu :</label>
                                <select class="form-control datatable-input" data-col-index="2" id="typesSelect"
                                    name="training_site">
                                    <option value="">Tous</option>
                                    @if (isset($lieux))
                                        @foreach ($lieux as $lieu)
                                            <option value="{{ $lieu['name'] }}">{{ $lieu['name'] }}</option>
                                        @endforeach
                                    @endif
                                    <option value="Chez le client">Chez le client</option>
                                    <option value="OTHER">Autre</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label>Groupe :</label>
                                <select class="form-control datatable-input" data-col-index="2" id="groupesSelect"
                                    name="group_id">
                                    <option value="0">Tous</option>
                                    @if (isset($groups))
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-lg-4 mt-2">
                                <label>Session :</label>
                                <select class="form-control select2" id="sessionsSelectFilter2" name="session_id"
                                    style="width:100%;">
                                    <option value="0">Toutes les sessions</option>
                                    @foreach ($sessions as $session)
                                        <option value="{{ $session['id'] }}">{{ $session['title'] }}
                                            ({{ $session['code'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 mt-2">
                                <label>Étudiant :</label>
                                <select class="form-control select2" id="membersSelectFilter" name="member_id"
                                    style="width:100%;">
                                    <option value="0">Tous les étudiants</option>
                                    @foreach ($members as $m)
                                        <option value="{{ $m->id }}">
                                            {{ $m->contact->firstname . ' ' . $m->contact->lastname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 ">
                                <button type="submit"
                                    class="btn btn-sm btn-outline-primary btn-outline-primary--icon mt-9">
                                    <span>
                                        <i class="far fa-file-pdf"></i>
                                        <span>Générer</span><span id="LOADER_SPINER_GENERATE"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-md-12">

                            <div class="alert alert-custom alert-outline-2x alert-outline-success mt-5" role="alert"
                                id="alert_results_pdf" style="display:none;">
                                <div class="alert-text">
                                    <!-- <p><strong>Les liens de téléchargement:</strong></p> -->
                                    <p id="block_results_pdf"></p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Generate Feuille de présences-->

    <!--begin::Row-->
    <div class="row" id="BLOCK_SESSIONS">

    </div>
    <!--end::Row-->

    <script>
        $('.select2').select2();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#generate_pdf_datepicker').datepicker({
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
        $('#formFilterSessionsGrid').validate({
            rules: {},
            messages: {},
            submitHandler: function(form) {

                var formData = $(form).serializeArray();
                var af_id = $("input[name='id']").val();
                var block_id = 'BLOCK_SESSIONS';
                $('#' + block_id).html(
                    '<div class="card-body"><div class="spinner spinner-primary spinner-lg"></div></div>');

                $.ajax({
                    type: 'POST',
                    url: '/session/gridlist/search/' + af_id,
                    data: formData,
                    dataType: 'html',
                    success: function(html, status) {
                        $('#' + block_id).html(html);
                    },
                    error: function(error) {

                    },
                    complete: function(resultat, statut) {


                    }
                });
                return false;
            }
        });

        $('#formGenrateFeuillesPresence').validate({
            rules: {},
            messages: {},
            submitHandler: function(form) {
                $('#alert_results_pdf').hide();
                var formData = $(form).serializeArray();
                $('#LOADER_SPINER_GENERATE').html('<div class="spinner spinner-primary spinner-lg"></div>');
                $.ajax({
                    type: 'POST',
                    url: '/pdf/generate/attendance-absence-sheet',
                    data: formData,
                    dataType: 'json',
                    success: function(res, status) {
                        $('#LOADER_SPINER_GENERATE').html('');
                        //console.log(res.links);
                        $('#block_results_pdf').html(res.links);
                        $('#alert_results_pdf').show();
                    },
                    error: function(error) {},
                    complete: function(resultat, statut) {}
                });
                return false;
            }
        });




        var _load_sessions = function() {
            var af_id = $("input[name='id']").val();
            var block_id = 'BLOCK_SESSIONS';
            $('#' + block_id).html(
                '<div class="card-body"><div class="spinner spinner-primary spinner-lg"></div></div>');
            $.ajax({
                url: '/get/session/gridlist/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + block_id).html(html);
                },
                error: function(result, status, error) {

                },
                complete: function(result, status) {}
            });
        }
        _load_sessions();
        var _formSession = function(session_id) {
            var af_id = $("input[name='id']").val();
            var modal_id = 'modal_form_session';
            var modal_content_id = 'modal_form_session_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/session/' + session_id + '/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {

                },
                complete: function(result, status) {

                }
            });
        }

        function _deleteSession(session_id) {
            var successMsg = "La session a été supprimée.";
            var errorMsg = "La session n\'a pas été supprimée.";
            var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer la session?";
            var swalConfirmText =
                "La suppression comprend aussi la suppression des dates, séances, planification (ressources,intervenants) !";
            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Supprimer"
            }).then(function(result) {
                if (result.value) {
                    KTApp.blockPage();
                    $.ajax({
                        url: "/api/delete/session",
                        type: "DELETE",
                        data: {
                            session_id: session_id
                        },
                        dataType: "JSON",
                        success: function(result, status) {
                            if (result.success) {
                                _showResponseMessage("success", successMsg);
                            } else {
                                _showResponseMessage("error", errorMsg);
                            }
                        },
                        error: function(result, status, error) {
                            _showResponseMessage("error", errorMsg);
                        },
                        complete: function(result, status) {
                            _load_sessions();
                            KTApp.unblockPage();
                        }
                    });
                }
            });
        }
    </script>

    <!--begin::Sessions-->
@endif

@if ($viewtype == 'inscriptions')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Inscrits</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title=""
                    onclick="_reload_dt_enrollments()" data-original-title="Rafraîchir"><i
                        class="flaticon-refresh"></i></button>
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                    title="Ajouter une inscription" onclick="_formEnrollment(0,1)"
                    data-original-title="Ajouter une inscription"><i class="flaticon2-add-1"></i></button>
                <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                    title="Intégré un fichier parcours sup" onclick="_formEnrollment(0,2)"
                    data-original-title="Intégré un fichier parcours sup"><i class="flaticon-download"></i></button>
                <button class="btn btn-sm btn-icon btn-light-primary ml-2" data-toggle="tooltip"
                    title="Créer les comptes" onclick="_createAccounts()" data-original-title="Rafraîchir"><i
                        class="flaticon-add"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_enrollments">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Type</th>
                        <th>Nom</th>
                        <th>Infos</th>
                        <th>Tarif</th>
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
    <link href="{{ asset('custom/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <x-modal id="modal_form_contact" content="modal_form_contact_content" />

    <script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var af_id = $("input[name='id']").val();
        var dtUrl = '/api/sdt/enrollments/' + af_id + '/S';

        var table = $('#dt_enrollments');
        // begin first table
        table.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            processing: true,
            paging: true,
            ordering: false,
            ajax: {
                url: dtUrl,
                type: 'POST',
                data: {
                    pagination: {
                        perpage: 50,
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
            columnDefs: [{
                    targets: 0,
                    width: '20px',
                    className: 'details-control',
                    orderable: false,
                },
                {
                    targets: 1,
                    width: '30px',
                    className: 'dt-left',
                    orderable: false,
                    render: function(data, type, full, meta) {
                        return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="` + data + `" class="checkable"/>
                            <span></span>
                        </label>`;
                    },
                }
            ],
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

        table.on('click', 'td.details-control', function() {
            var tr = $(this).closest('tr');
            var row = table.DataTable().row(tr);
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                var enrollment_id = row.data()[1];
                row.child(_subTableMembers(enrollment_id)).show();
                tr.addClass('shown');
                _getMembers(enrollment_id);
            }
        });

        var _subTableMembers = function(enrollment_id) {
            return '<div id="child_data_members_' + enrollment_id +
                '" class="datatable datatable-default datatable-primary datatable-loaded"></div>';
        }

        var _getMembers = function(enrollment_id) {
            var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
            $('#child_data_members_' + enrollment_id).parent().addClass('bg-light');
            $('#child_data_members_' + enrollment_id).html(spinner);
            $.ajax({
                url: '/get/members/' + enrollment_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#child_data_members_' + enrollment_id).html(html);
                }
            });
        }

        function _formEditUnknownContact(member_id, enrollment_id) {
            if (enrollment_id > 0) {
                var modal_id = 'modal_form_contact';
                var modal_content_id = 'modal_form_contact_content';
                var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
                $('#' + modal_id).modal('show');
                $('#' + modal_content_id).html(spinner);
                $.ajax({
                    url: '/form/unknown/contact/' + member_id + '/' + enrollment_id,
                    type: 'GET',
                    dataType: 'html',
                    success: function(html, status) {
                        $('#' + modal_content_id).html(html);
                    },
                    error: function(result, status, error) {},
                    complete: function(result, status) {}
                });
            }
        }

        var _reload_dt_enrollments = function() {
            $('#dt_enrollments').DataTable().ajax.reload();
        }


        function _formEnrollment(enrollment_id, type) {
            /* 
                type = 1 ==> inscription normale
                type = 2 ==> inscription parcours sup
             */
            var af_id = $("input[name='id']").val();
            var modal_id = 'modal_form_enrollment';
            var modal_content_id = 'modal_form_enrollment_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/enrollment/' + af_id + '/' + enrollment_id + '/' + type,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {}
            });
        }

        /** about role**/
        function getCheckedCheckboxesFor(checkboxName) {
            var checkboxes = document.querySelectorAll('input[name="' + checkboxName + '"]:checked'),
                values = [];
            Array.prototype.forEach.call(checkboxes, function(el) {
                values.push(el.value);
            });
            return values;
        }

        function _createAccounts() {
            var af_id = $("input[name='id']").val();

            var table = $('#dt_enrollments').DataTable();

            $('#dt_enrollments tbody').on('click', 'tr', function() {
                $(this).toggleClass('active');
            });

            var count = table.rows('.active').data().length;
            var dataselected = [];

            $("input:checkbox[class=checkable]:checked").each(function() {
                dataselected.push($(this).val());
            });

            var successMsg = "Les utilisateurs ont été bien créés.";
            var errorMsg = "La création ne marche pas.";
            var swalConfirmTitle = "Créer les utilisateurs!";
            var swalConfirmText = "Êtes-vous sûr de bien créer Les utilisateur? Si oui merci de saisir le rôle!";

            var formData = [];

            var html = (dataselected.length == 0 ?
                    '<b>Vous êtes sûr de vouloir créer les comptes pour tous les intervenants ?</b><br/><br/>' : '') +
                '<span><input type="radio" id="role" name="role" value="1" class="role" checked> <label for="role">Etudiant</label></span>';

            Swal.fire({
                title: swalConfirmTitle,
                html: html,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Créer",
                cancelButtonText: "Non"
            }).then(function(result) {
                var role = document.querySelector('input[name="role"]:checked').value;

                formData = formData.concat([{
                        name: "af_id",
                        value: af_id
                    },
                    {
                        name: "count",
                        value: count
                    },
                    {
                        name: "data",
                        value: dataselected.length > 0 ? dataselected : 'all'
                    },
                    {
                        name: "role",
                        value: role
                    },
                    {
                        name: "type",
                        value: 'inscription'
                    }
                ]);

                if (result.value) {
                    $.ajax({
                        type: 'POST',
                        url: '/api/sdt/createAccounts',
                        data: formData,
                        dataType: 'JSON',
                        success: function(result) {
                            result.forEach(elem => {
                                if (elem.success) {
                                    toastr.success(elem.msg);
                                } else {
                                    toastr.error(elem.msg);
                                }
                            });
                        }
                    });
                }
            });
        }

        var _deleteEnrollmentMember = function(row_id, type) {
            var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
            if (type == "ENROLLMENT") {
                var urlDelete = "/api/delete/enrollment/" + row_id;
                var successMsg = "L'inscription a été supprimée.";
                var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer l'inscription?";
                var errorMsg = "L'inscription n\'a pas été supprimée.";
            } else if (type == "MEMBER") {
                var successMsg = "Le stagiaire a été supprimé.";
                var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le stagiaire?";
                var errorMsg = "Le stagiaire n\'a pas été supprimée.";
                var urlDelete = "/api/delete/member/" + row_id;
            }
            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Oui, supprimez-le!"
            }).then(function(result) {
                if (result.value) {
                    KTApp.blockPage();
                    $.ajax({
                        url: urlDelete,
                        type: "GET",
                        dataType: "JSON",
                        success: function(result, status) {
                            if (result.success) {
                                _showResponseMessage("success", successMsg);
                            } else {
                                _showResponseMessage("error", errorMsg);
                            }
                        },
                        error: function(result, status, error) {
                            _showResponseMessage("error", errorMsg);
                        },
                        complete: function(result, status) {
                            _reload_dt_enrollments();
                            KTApp.unblockPage();
                        }
                    });
                }
            });
        }
    </script>
@endif

@if ($viewtype == 'groups')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Groupes</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1">Liste des groupes</span>
            </div>
            <div class="card-toolbar">

                <!-- <button onclick="_formGroup(0)" class="btn btn-sm btn-icon btn-light-primary mr-2">
                <i class="flaticon2-add-1"></i>
            </button>

            <button onclick="_reload_dt_groups()" class="btn btn-sm btn-icon btn-light-info mr-2">
                <i class="flaticon-refresh"></i>
            </button> -->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">


            <!--begin: Datatable-->
            <!-- <table class="table table-bordered table-checkable" id="dt_groups">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Groupe</th>
                    <th>Infos</th>
                    <th style="width: 30%">Dates</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table> -->
            <!--end: Datatable-->

            <!-- begin::Line tab -->
            <ul class="nav nav-tabs nav-tabs-line mb-5">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab_groups">
                        <span class="nav-icon"><i class="flaticon2-chat-1"></i></span>
                        <span class="nav-text">Groupes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab_groupments">
                        <span class="nav-icon"><i class="flaticon2-pie-chart-4"></i></span>
                        <span class="nav-text">Groupements</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content mt-5" id="myTabContent">
                <div class="tab-pane fade show active" id="tab_groups" role="tabpanel" aria-labelledby="tab_groups">
                    <div class="card card-custom card-fit card-border">
                        <div class="card-header">
                            <div class="card-toolbar">
                                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                                    title="Rafraîchir" onclick="_reload_dt_groups()"><i
                                        class="flaticon-refresh"></i></button>
                                <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                                    title="Ajouter un groupe" onclick="_formGroup(0)"><i
                                        class="flaticon2-add-1"></i></button>
                            </div>
                        </div>
                        <div class="card-body">

                            <!--begin: Datatable-->
                            <table class="table table-bordered table-checkable" id="dt_groups">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Groupe</th>
                                        <th>Infos</th>
                                        <th style="width: 30%">Dates</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <!--end: Datatable-->

                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab_groupments" role="tabpanel" aria-labelledby="tab_groupments">

                    <div class="card card-custom card-fit card-border">
                        <div class="card-header">
                            <div class="card-toolbar">
                                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                                    title="Rafraîchir" onclick="_reload_dt_groupments()"><i
                                        class="flaticon-refresh"></i></button>
                                <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                                    title="Ajouter un groupment" onclick="_formGroupment(0)"><i
                                        class="flaticon2-add-1"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!--begin: Datatable-->
                            <table class="table table-bordered table-checkable" id="dt_groupments">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Groupement</th>
                                        <th>Infos</th>
                                        <th style="width: 30%">Dates</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <!--end: Datatable-->
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <!--end::Card-->

    <x-modal id="modal_form_group" content="modal_form_group_content" />
    <x-modal id="modal_form_groupment" content="modal_form_groupment_content" />
    <x-modal id="modal_affectation_group" content="modal_affectation_group_content" />
    <x-modal id="modal_affectation_groupment" content="modal_affectation_groupment_content" />

    {{-- <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script> --}}
    {{-- <script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script> --}}
    {{-- <script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script> --}}
    {{-- page scripts --}}
    <script src="{{ asset('custom/js/general.js?v=0') }}"></script>


    <script>
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var table_groups = $('#dt_groups');
        var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
        table_groups.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            paging: true,
            ordering: false,
            processing: true,
            ajax: {
                url: '/api/sdt/groups/' + af_id,
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
        table_groups.on('change', '.group-checkable', function() {
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
        table_groups.on('change', 'tbody tr .checkbox', function() {
            $(this).parents('tr').toggleClass('active');
        });
        var _reload_dt_groups = function() {
            $('#dt_groups').DataTable().ajax.reload();
        }

        var _formGroup = function(group_id) {
            var modal_id = 'modal_form_group';
            var modal_content_id = 'modal_form_group_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/group/' + group_id + '/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {

                },
                complete: function(result, status) {

                }
            });
        }

        var _affectationFormGroup = function(group_id) {
            var modal_id = 'modal_affectation_group';
            var modal_content_id = 'modal_affectation_group_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/affectation/group/' + group_id + '/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {

                },
                complete: function(result, status) {

                }
            });

        }
        //partie detail groupe
        /*table_groups.on('click', ' tbody td .row-details', function () {

            var nTr = $(this).parents('tr')[0];
            if (oTable.fnIsOpen(nTr)) {
                $(this).addClass("row-details-close").removeClass("row-details-open");
                oTable.fnClose(nTr);
            } else {
                getListContactGroup($(this).attr('id'));
                $(this).addClass("row-details-open").removeClass("row-details-close");
                oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'details details2');
            }
        });
        function getListContactGroup(idGrp){
            $('.table_detail_grp').html('');
             $.ajax({
                    url: '/..../'+idGrp,
                    success: function(data) {
                        $('#dt_groups_'+idGrp).html(data);
                    }
                });
        }
        function fnFormatDetails(oTable, nTr) {
           var aData = oTable.fnGetData(nTr);
           var sOut = '<div id="dt_groups_' + aData[1] + '" class="table_detail_grp"></div>';
           return sOut;
        }*/

        var dt_groupments = $('#dt_groupments');
        dt_groupments.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            paging: true,
            ordering: false,
            processing: true,
            ajax: {
                url: '/api/sdt/groupments/' + af_id,
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
        dt_groupments.on('change', '.group-checkable', function() {
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
        dt_groupments.on('change', 'tbody tr .checkbox', function() {
            $(this).parents('tr').toggleClass('active');
        });
        var _reload_dt_groupments = function() {
            $('#dt_groupments').DataTable().ajax.reload();
        }

        function _formGroupment(group_id) {
            var modal_id = 'modal_form_groupment';
            var modal_content_id = 'modal_form_groupment_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/groupment/' + group_id + '/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {}
            });
        }

        function _affectationFormGroupsToGroupment(groupment_id) {
            var modal_id = 'modal_affectation_groupment';
            var modal_content_id = 'modal_affectation_groupment_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/affectation/groupment/' + groupment_id + '/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {}
            });

        }
    </script>
@endif

@if ($viewtype == 'schedulecontacts')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Planification des inscrits & intervenants</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <button type="button" data-toggle="tooltip" title="Élargir tous"
                    onclick="ExpandCollapseAll('tree_schedulecontacts','EXPAND')"
                    class="btn btn-sm btn-icon btn-light-primary mr-2">
                    <i class="fa fa-chevron-down"></i>
                </button>
                <button type="button" data-toggle="tooltip" title="Réduire tous"
                    onclick="ExpandCollapseAll('tree_schedulecontacts','COLLAPSE')"
                    class="btn btn-sm btn-icon btn-light-success mr-2">
                    <i class="fa fa-chevron-up"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title=""
                    onclick="resfreshJSTreeSchedulecontacts()" data-original-title="Rafraîchir"><i
                        class="flaticon-refresh"></i></button>
                <!-- <button class="btn btn-sm btn-light-primary mr-2" data-toggle="tooltip" title=""
                    onclick="_formScheduleContacts(1)" data-original-title="Planifier les inscrits"><i
                        class="flaticon2-add-1"></i> inscrits</button>
                <button class="btn btn-sm btn-light-primary" data-toggle="tooltip" title=""
                    onclick="_formScheduleContacts(2)" data-original-title="Planifier les intervanants"><i
                        class="flaticon2-add-1"></i> intervanant</button> -->
                <!--end::Button-->
                @if (auth()->user()->roles[0]->code != 'FORMATEUR')
                    <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title=""
                        onclick="_formRemuneration({{ $row->id }},0)"
                        data-original-title="Rémunération des intervenants"><i class="flaticon2-add-1"></i></button>
                @endif
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <!--begin: jstree-->
            <!-- <div id="tree_schedulecontacts" class="tree-demo"></div> -->
            <!--end: jstree-->

            <!-- FILTRE:PLANNING -->
            <div class="accordion accordion-solid accordion-toggle-plus mb-4" id="accordionFilterPlanning">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title" data-toggle="collapse" data-target="#collapseOne">
                            <i class="flaticon-search"></i> Filtre :
                        </div>
                    </div>
                    <div id="collapseOne" class="collapse" data-parent="#accordionFilterPlanning">
                        <div class="card-body">
                            <form id="formFilterScheduleContacts">
                                <input type="hidden" value="{{ $row->id }}" name="af_id">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Session :</label>
                                        <select class="form-control select2" id="sessionsSelectFilter"
                                            name="session_id" style="width:100%;">
                                            <option value="0">Toutes les sessions</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session['id'] }}">{{ $session['title'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Groupe :</label>
                                        <select class="form-control select2" data-col-index="2"
                                            id="groupesSelectFilter" name="group_id" style="width:100%;"></select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Étudiant :</label>
                                        <select class="form-control select2" id="membersStudentSelectFilter"
                                            name="member_id" style="width:100%;">
                                            <option value="0">Tous les étudiants</option>
                                            @foreach ($members as $m)
                                                <option value="{{ $m->id }}">
                                                    {{ $m->contact->firstname . ' ' . $m->contact->lastname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Intervenant :</label>
                                        <select class="form-control select2" id="membersFormersSelectFilter"
                                            name="inter_id" style="width:100%;">
                                            <option value="0">Tous les intervenant</option>
                                            @foreach ($intervenant as $m)
                                                <option value="{{ $m->id }}">
                                                    {{ $m->contact->firstname . ' ' . $m->contact->lastname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-10">
                                        <label class="mt-2">Date du:</label>
                                        <div class="input-daterange input-group" id="filtre_datepicker">
                                            <input type="text" class="form-control datatable-input"
                                                name="start_date" value="" placeholder="Du" data-col-index="5"
                                                autocomplete="off" />
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    <i class="la la-ellipsis-h"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control datatable-input"
                                                name="end_date" value="" placeholder="Au" data-col-index="5"
                                                autocomplete="off" />
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="submit"
                                            class="btn btn-sm btn-outline-primary btn-outline-primary--icon mt-9">
                                            <span>
                                                <i class="la la-search"></i>
                                                <span>Rechecher</span><span id="LOADER_SPINER_FILTER_SCHEDULE"></span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- FILTRE:PLANNING -->
            @if (auth()->user()->roles[0]->code == 'FORMATEUR')
                <div class="row">
                    <div class="col-lg-12">
                        <!--begin::Card-->
                        <div class="card card-custom card-border">
                            <div class="card-body p-3">
                                <!--begin: jstree-->
                                <div id="tree_schedulecontacts" class="tree-demo font-size-sm"></div>
                                <!--end: jstree-->
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                </div>
            @endif
            @if (auth()->user()->roles[0]->code != 'FORMATEUR')
                <div class="row">
                    <div class="col-lg-6">
                        <!--begin::Card-->
                        <div class="card card-custom card-border">
                            <div class="card-body p-3">
                                <!--begin: jstree-->
                                <div id="tree_schedulecontacts" class="tree-demo font-size-sm"></div>
                                <!--end: jstree-->
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                    <div class="col-lg-6">
                        <!-- begin::Form -->
                        <form id="formScheduleContacts" class="form">
                            <!--begin::Card-->
                            <div class="card card-custom card-border">
                                <div style="min-height:0px;" class="card-header p-2">
                                    <div class="card-title">
                                        <h3 class="card-label font-size-h6">Inscrits & formateurs</h3>
                                    </div>
                                    <div class="card-toolbar">
                                        <button class="btn btn-sm btn-light-primary" data-toggle="tooltip"
                                            data-theme="dark" title="Affecter" data-original-title="Enregistrer"><i
                                                class="flaticon2-checkmark"></i> <span id="BTN_SAVE"></span></button>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="form-group">
                                        <label>Type d'affectation</label>
                                        <select class="form-control form-control-sm" name="affectation_type"
                                            id="typesAffectationSelect">
                                            <option value="S">Stagiaires</option>
                                            <option value="F">Formateurs</option>
                                            <option value="G">Groupes</option>
                                            <option value="GROUPMENT">Groupements</option>
                                        </select>
                                    </div>
                                    <span id="loader-rs"></span>
                                    <!--begin: Datatable-->
                                    <table class="table table-sm table-bordered" id="dt_members">
                                        <thead class="thead-light">
                                            <tr>
                                                <th></th>
                                                <th>Nom</th>
                                                <!-- <th>Entité</th> -->
                                                <th>Planif</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <!--end: Datatable-->
                                </div>
                            </div>
                            <!--end::Card-->
                        </form>
                        <!-- end::Form -->
                    </div>
                </div>
            @endif

        </div>
        <!--end::Card-->
        <style>
            .jstree-anchor>.jstree-checkbox-disabled {
                display: none;
            }

            .jstree-default .jstree-anchor {
                height: 100% !important;
            }
        </style>

        <script type="text/javascript">
            $(document).ready(function() {
                $('[data-toggle="tooltip"]').tooltip();
                $('.select2').select2();
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var af_id = $("input[name='id']").val();
            _loadDatasGroupsForSelectOptions('groupesSelectFilter', af_id, 0);

            function _loadDatasGroupsForSelectOptions(select_id, af_id, selected_value = 0) {
                $('#' + select_id).empty();
                $.ajax({
                    url: '/api/select/options/groups/' + af_id,
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
                }).done(function() {
                    if (selected_value != 0 && selected_value != '') {
                        $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
                    }
                });
            }

            function resfreshJSTreeSchedulecontacts() {
                var af_id = $("input[name='id']").val();
                _initJsTreePlanning(af_id);
            }

            _initJsTreePlanning(af_id);

            function _initJsTreePlanning(af_id) {
                var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
                $("#tree_schedulecontacts").html(spinner);
                $.ajax({
                    type: 'POST',
                    url: '/api/tree/schedules/' + af_id + '/withcontacts',
                    data: [],
                    dataType: 'json',
                    success: function(json, status) {
                        _createJSTree(json);
                    },
                    error: function(error) {},
                    complete: function(resultat, statut) {}
                });
            }
            $('#formFilterScheduleContacts').validate({
                rules: {},
                messages: {},
                submitHandler: function(form) {
                    var formData = $(form).serializeArray();
                    console.log(formData);
                    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
                    $("#tree_schedulecontacts").html(spinner);
                    $('#LOADER_SPINER_FILTER_SCHEDULE').html(spinner);
                    $.ajax({
                        type: 'POST',
                        url: '/api/tree/schedules/' + af_id + '/withcontacts',
                        data: formData,
                        dataType: 'json',
                        success: function(json, status) {
                            $('#LOADER_SPINER_FILTER_SCHEDULE').html('');
                            //console.log(json);
                            _createJSTree(json);
                            //$('#tree_schedulecontacts').jstree(true).refresh();
                        },
                        error: function(error) {},
                        complete: function(resultat, statut) {}
                    });
                    return false;
                }
            });

            function _createJSTree(jsondata) {
                var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
                $("#tree_schedulecontacts").html(spinner);
                $("#tree_schedulecontacts").jstree('destroy');

                $('#tree_schedulecontacts').jstree({
                    'core': {
                        "multiple": true,
                        "themes": {
                            "responsive": true
                        },
                        'data': jsondata
                    },
                    "checkbox": {
                        "three_state": true, // to avoid that fact that checking a node also check others
                    },
                    "plugins": ["state", "types", "wholerow", "checkbox"]
                });
                $('#tree_schedulecontacts').bind("ready.jstree", function() {
                    initializeSelections();
                }).jstree();

                $('#tree_schedulecontacts').bind("before_open.jstree", function() {
                    $('[data-toggle="tooltip"]').tooltip();
                }).jstree();
            }

            function initializeSelections() {
                var instance = $('#tree_schedulecontacts').jstree(true);
                instance.deselect_all();
                $('[data-toggle="tooltip"]').tooltip();
            }

            /* var tree_schedulecontacts = 'tree_schedulecontacts';
            $('#' + tree_schedulecontacts).jstree({
                "core": {
                    "multiple": true,
                    "themes": {
                        "responsive": true
                    },
                    'data': {
                        'url': function(node) {
                            return '/api/tree/schedules/' + af_id + '/withcontacts';
                        },
                        'data': function(node) {
                            return {
                                'parent': node.id
                            };
                        }
                    },
                },
                "checkbox": {
                    "three_state": true, // to avoid that fact that checking a node also check others
                },
                "plugins": ["state", "types", "wholerow", "checkbox"]
                //"plugins": ["state", "types"]
            });
            function resfreshJSTreeSchedulecontacts() {
                var af_id = $("input[name='id']").val();
                $('#tree_schedulecontacts').jstree(true).settings.core.data.url = '/api/tree/schedules/' + af_id +'/withcontacts';
                $('#tree_schedulecontacts').jstree(true).refresh();
            }

            $('#tree_schedulecontacts').bind("ready.jstree", function() {
                initializeSelections();
            }).jstree();

            $('#tree_schedulecontacts').bind("before_open.jstree", function() {
                $('[data-toggle="tooltip"]').tooltip();
            }).jstree(); */



            /* var _formScheduleContacts = function(type) {

                //type == 1 ==> les inscrits
                //type == 2 ==> les intervenants

                var af_id = $("input[name='id']").val();
                var modal_id = 'modal_form_schedulecontacts';
                var modal_content_id = 'modal_form_schedulecontacts_content';
                var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
                $('#' + modal_id).modal('show');
                $('#' + modal_content_id).html(spinner);
                $.ajax({
                    url: '/form/schedulecontact/' + af_id + '/' + type,
                    type: 'GET',
                    dataType: 'html',
                    success: function(html, status) {
                        $('#' + modal_content_id).html(html);
                    },
                    error: function(result, status, error) {

                    },
                    complete: function(result, status) {

                    }
                });
            } */

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

            var _deleteScheduleContact = function(row_id) {
                var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
                var successMsg = "L'\affectation a été supprimé.";
                var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer?";
                var errorMsg = "L'\affectation n\'a pas été supprimée.";
                var urlDelete = "/api/delete/schedulecontact/" + row_id;
                Swal.fire({
                    title: swalConfirmTitle,
                    text: swalConfirmText,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Oui, supprimez-le!"
                }).then(function(result) {
                    if (result.value) {
                        KTApp.blockPage();
                        $.ajax({
                            url: urlDelete,
                            type: "GET",
                            dataType: "JSON",
                            success: function(result, status) {
                                if (result.success) {
                                    _showResponseMessage("success", successMsg);
                                } else {
                                    _showResponseMessage("error", errorMsg);
                                }
                            },
                            error: function(result, status, error) {
                                _showResponseMessage("error", errorMsg);
                            },
                            complete: function(result, status) {
                                resfreshJSTreeSchedulecontacts();
                                KTApp.unblockPage();
                            }
                        });
                    }
                });
            }

            var _deleteScheduleGroup = function(contact_id, group_id) {
                var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
                var successMsg = "L'\affectation a été supprimé.";
                var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer?";
                var errorMsg = "L'\affectation n\'a pas été supprimée.";
                var urlDelete = "/api/delete/schedulegroup/" + contact_id + "/" + group_id;
                Swal.fire({
                    title: swalConfirmTitle,
                    text: swalConfirmText,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Oui, supprimez-le!"
                }).then(function(result) {
                    if (result.value) {
                        KTApp.blockPage();
                        $.ajax({
                            url: urlDelete,
                            type: "GET",
                            dataType: "JSON",
                            success: function(result, status) {
                                if (result.success) {
                                    _showResponseMessage("success", successMsg);
                                } else {
                                    _showResponseMessage("error", errorMsg);
                                }
                            },
                            error: function(result, status, error) {
                                _showResponseMessage("error", errorMsg);
                            },
                            complete: function(result, status) {
                                resfreshJSTreeSchedulecontacts();
                                KTApp.unblockPage();
                            }
                        });
                    }
                });
            }
            var _formRemuneration = function(af_id, member_id) {
                var modal_id = 'modal_form_remuneration';
                var modal_content_id = 'modal_form_remuneration_content';
                var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
                $('#' + modal_id).modal('show');
                $('#' + modal_content_id).html(spinner);
                $.ajax({
                    url: '/form/remuneration/' + af_id + '/' + member_id,
                    type: 'GET',
                    dataType: 'html',
                    success: function(html, status) {
                        $('#' + modal_content_id).html(html);
                    }
                });
            }
            //NEW FORM
            //enrollment_type = 'S' or 'F'; //stagiaires ou formateurs
            var enrollment_type = 'S';
            var dtUrlSelectForMembers = '/api/sdt/select/members/' + af_id + '/' + enrollment_type;
            var dt_members = $('#dt_members');
            // begin first table
            dt_members.DataTable({
                language: {
                    url: "/custom/plugins/datatable/fr.json"
                },
                responsive: true,
                processing: true,
                searching: true,
                paging: true,
                ordering: false,
                info: false,
                ajax: {
                    url: dtUrlSelectForMembers,
                    type: 'POST',
                    data: {
                        pagination: {
                            perpage: 50,
                        },
                    },
                },
                lengthMenu: [5, 10, 25, 50],
                pageLength: 25,
                headerCallback: function(thead, data, start, end, display) {
                    thead.getElementsByTagName('th')[0].innerHTML = `
                    <label class="checkbox checkbox-single">
                        <input type="checkbox" value="" class="group-checkable"/>
                        <span></span>
                    </label>`;
                },
            });
            dt_members.on('change', '.group-checkable', function() {
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
            dt_members.on('change', 'tbody tr .checkbox', function() {
                $(this).parents('tr').toggleClass('active');
            });

            $('#typesAffectationSelect').on('change', function() {
                _loadMembers();
            });
            var _loadMembers = function() {
                $('#loader-rs').html(
                    '<p class="text-primary"><i class="fas fa-spinner fa-spin text-primary"></i> Chargement en cours ...</p>'
                );
                var enrollment_type = $('#typesAffectationSelect').val();
                var af_id = $("input[name='id']").val();
                var table = 'dt_members';
                var dtUrlSelectForMembers = '/api/sdt/select/members/' + af_id + '/' + enrollment_type;
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    data: {},
                    url: dtUrlSelectForMembers,
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
                    $('#loader-rs').html('');
                });
                return false;
            }
            $("#formScheduleContacts").validate({
                rules: {},
                messages: {},
                submitHandler: function(form) {
                    _showLoader('BTN_SAVE');
                    var formData = $(form).serializeArray();
                    var schedules_ids = $("#tree_schedulecontacts").jstree("get_selected");
                    if (schedules_ids) {
                        formData = formData.concat([{
                            name: "schedules_ids",
                            value: schedules_ids
                        }, ]);
                    }
                    $.ajax({
                        type: 'POST',
                        url: '/form/schedulecontact',
                        data: formData,
                        dataType: 'JSON',
                        success: function(result) {
                            _hideLoader('BTN_SAVE');
                            if (result.success) {
                                _showResponseMessage('success', result.msg);
                                //$('#modal_form_schedulecontacts').modal('hide');
                            } else {
                                _showResponseMessage('error', result.msg);
                            }
                        },
                        error: function(error) {
                            _hideLoader('BTN_SAVE');
                            _showResponseMessage('error',
                                'Veuillez vérifier les champs du formulaire...');
                        },
                        complete: function(resultat, statut) {
                            _hideLoader('BTN_SAVE');
                            resfreshJSTreeSchedulecontacts();
                        }
                    });
                    return false;
                }
            });

            function _showScheduleDetails(af_id, member_id) {
                var modal_id = 'modal_schedule_details';
                var modal_content_id = 'modal_schedule_details_content';
                var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
                $('#' + modal_id).modal('show');
                $('#' + modal_content_id).html(spinner);
                $.ajax({
                    url: '/get/schedule/member/details/' + af_id + '/' + member_id,
                    type: 'GET',
                    dataType: 'html',
                    success: function(html, status) {
                        $('#' + modal_content_id).html(html);
                    }
                });
            }
            $('#filtre_datepicker').datepicker({
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

            function _formPointage(schedulecontact_id) {
                var modal_id = 'modal_form_pointage';
                var modal_content_id = 'modal_form_pointage_content';
                var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
                $('#' + modal_id).modal('show');
                $('#' + modal_content_id).html(spinner);
                $.ajax({
                    url: '/form/pointage/' + schedulecontact_id,
                    type: 'GET',
                    dataType: 'html',
                    success: function(html, status) {
                        $('#' + modal_content_id).html(html);
                    }
                });
            }
        </script>

@endif

@if ($viewtype == 'certifications')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Certifications</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">

            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">

            <!-- begin::Line tab -->
            <ul class="nav nav-tabs nav-tabs-line mb-5">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab_cert_1"
                        onclick="_load_certifications_tab(1)">
                        <span class="nav-icon"><i class="flaticon-notes"></i></span>
                        <span class="nav-text">Sessions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab_cert_2" onclick="_load_certifications_tab(2)">
                        <span class="nav-icon"><i class="flaticon-event-calendar-symbol"></i></span>
                        <span class="nav-text">Planification</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab_cert_3" onclick="_load_certifications_tab(3)">
                        <span class="nav-icon"><i class="flaticon-interface-10"></i></span>
                        <span class="nav-text">Suivis</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content mt-5" id="myTabContent">
                <div class="tab-pane fade show active" id="tab_cert_1" role="tabpanel" aria-labelledby="tab_cert_2">
                </div>
                <div class="tab-pane fade" id="tab_cert_2" role="tabpanel" aria-labelledby="tab_cert_2">
                </div>
                <div class="tab-pane fade" id="tab_cert_3" role="tabpanel" aria-labelledby="tab_cert_3">
                </div>
            </div>
            <!-- end::Line tab -->
        </div>
    </div>
    <style>
        .jstree-anchor>.jstree-checkbox-disabled {
            display: none;
        }

        .jstree-default .jstree-anchor {
            height: 100% !important;
        }
    </style>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        _load_certifications_tab(1);

        function _load_certifications_tab(id) {
            _reset_certifications_tab(id);
            var block_id = 'tab_cert_' + id;
            var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
            KTApp.block('#' + block_id, {
                overlayColor: '#000000',
                state: 'danger',
                message: 'Veuillez patienter svp...'
            });
            $.ajax({
                url: '/get/content/tab/certs/' + id + '/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#myTabContent .tab-pane').empty();
                    $('#' + block_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {
                    KTApp.unblock('#' + block_id);
                }
            });
        }

        function _reset_certifications_tab(id) {
            if (id == 1) {
                $('#tab_doc_2,#tab_doc_3').html('');
            }
            if (id == 2) {
                $('#tab_doc_1,#tab_doc_3').html('');
            }
            if (id == 3) {
                $('#tab_doc_1,#tab_doc_2').html('');
            }
        }
        $('[data-toggle="tooltip"]').tooltip();
    </script>
@endif

@if ($viewtype == 'tarification')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark"><i class="flaticon-price-tag"></i>
                    Tarifications
                </h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
                <button onclick="_formAfRelPrice({{ $row->id }})"
                    class="btn btn-sm btn-icon btn-light-primary mr-2">
                    <i class="flaticon2-add-1"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                    onclick="_reload_dt_af_prices()"><i class="flaticon-refresh"></i></button>
                <button class="btn btn-sm btn-icon btn-light-danger" data-toggle="tooltip"
                    title="Supprimer une selection" onclick="_deleteAfRelPrice(0, {{ $row->id }})"><i
                        class="flaticon-delete"></i></button>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <p class="text-warning">Souhaitez vous importer les tarifs configurées sur le produit de formation
                racine?
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                    title="Importer les tarifs" onclick="_IMPORT_PF_PRICES({{ $row->id }})"><i
                        class="flaticon-download"></i></button>
            </p>
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_af_prices">
                <thead>
                    <tr>
                        <th></th>
                        <th>Titre</th>
                        <th>Entité</th>
                        <th>Type</th>
                        <th>Tarif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <x-modal id="modal_price_af" content="modal_price_af_content" />
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
            var dtUrl = '/api/sdt/prices/af/' + af_id;
            var tableTarif = $('#dt_af_prices');
            // begin first table
            tableTarif.DataTable({
                language: {
                    url: "/custom/plugins/datatable/fr.json"
                },
                responsive: true,
                processing: true,
                paging: true,
                ordering: false,
                ajax: {
                    url: dtUrl,
                    type: 'POST',
                    data: {
                        pagination: {
                            perpage: 50,
                        },
                    },
                },
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
                    /* render: function(data, type, full, meta) {
                        return `
                    <label class="checkbox checkbox-single">
                        <input type="checkbox" value="" class="checkable"/>
                        <span></span>
                    </label>`;
                    }, */
                }, {
                    targets: 4,
                    width: '150px',
                }],
            });

            tableTarif.on('change', '.group-checkable', function() {
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
            tableTarif.on('change', 'tbody tr .checkbox', function() {
                $(this).parents('tr').toggleClass('active');
            });
        });


        var _reload_dt_af_prices = function() {
            $('#dt_af_prices').DataTable().ajax.reload();
        }

        /* var _deleteAfRelPrice = function(price_id, af_id) {
            var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
            var urlDelete = "/api/delete/afrelprice/" + price_id + '/' + af_id;
            var successMsg = "Le tarif a été supprimé.";
            var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer ce tarif?";
            var errorMsg = "Le tarif n\'a pas été supprimée.";
            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Oui, supprimez-le!"
            }).then(function(result) {
                if (result.value) {
                    KTApp.blockPage();
                    $.ajax({
                        url: urlDelete,
                        type: "GET",
                        dataType: "JSON",
                        success: function(result, status) {
                            if (result.success) {
                                _showResponseMessage("success", successMsg);
                            } else {
                                _showResponseMessage("error", errorMsg);
                            }
                        },
                        error: function(result, status, error) {
                            _showResponseMessage("error", errorMsg);
                        },
                        complete: function(result, status) {
                            _reload_dt_af_prices();
                            KTApp.unblockPage();
                        }
                    });
                }
            });
        } */

        function _deleteAfRelPrice(id, af_id) {
            var TableauIdProcess = new Array();
            var j = 0;
            if (id > 0) {
                TableauIdProcess[0] = id;
            } else {
                $('#dt_af_prices input[class="checkable"]').each(function() {
                    var checked = jQuery(this).is(":checked");
                    if (checked) {
                        TableauIdProcess[j] = jQuery(this).val();
                        j++;
                    }
                });
            }
            if (TableauIdProcess.length < 1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Veuillez sélectionner un ou plusieurs tarif(s)!',
                });
                //return false;
            } else {
                var successMsg = "Le tarif a été supprimée.";
                var errorMsg = "Le tarif n\'a pas été supprimée.";
                var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le(s) tarif(s)?";
                var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
                Swal.fire({
                    title: swalConfirmTitle,
                    text: swalConfirmText,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Supprimer"
                }).then(function(result) {
                    if (result.value) {
                        //console.log(TableauIdProcess);
                        //return false;
                        KTApp.blockPage();
                        $.ajax({
                            url: "/api/delete/afrelprice",
                            type: "DELETE",
                            data: {
                                af_id: af_id,
                                ids_prices: TableauIdProcess,
                            },
                            dataType: "JSON",
                            success: function(result, status) {
                                if (result.success) {
                                    _showResponseMessage("success", successMsg);
                                } else {
                                    _showResponseMessage("error", errorMsg);
                                }
                            },
                            error: function(result, status, error) {
                                _showResponseMessage("error", errorMsg);
                            },
                            complete: function(result, status) {
                                _reload_dt_af_prices();
                                KTApp.unblockPage();
                            }
                        });
                    }
                });
            }
        }


        var _IMPORT_PF_PRICES = function(af_id) {
            KTApp.blockPage();
            $.ajax({
                url: '/copy/price/pf/af/' + af_id,
                type: "GET",
                dataType: "JSON",
                success: function(result, status) {
                    if (result.success) {
                        _showResponseMessage("success", result.msg);
                    } else {
                        _showResponseMessage("error", result.msg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error", 'Veuillez vérifier les champs du formulaire...');
                },
                complete: function(result, status) {
                    _reload_dt_af_prices();
                    KTApp.unblockPage();
                }
            });
        }

        function _formAfRelPrice(af_id) {
            var modal_id = 'modal_price_af';
            var modal_content_id = 'modal_price_af_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/price/rel/af/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {

                },
                complete: function(result, status) {

                }
            });
        }
    </script>
@endif

@if ($viewtype == 'intervenants')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Intervenants</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title=""
                    onclick="_reload_dt_intervenants_members()" data-original-title="Rafraîchir"><i
                        class="flaticon-refresh"></i></button>
                <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title=""
                    onclick="_formEnrollmentIntervenants(0)" data-original-title="Ajouter une inscription">
                    <i class="flaticon2-add-1"></i></button>

                <button class="btn btn-sm btn-icon btn-light-primary ml-2" data-toggle="tooltip"
                    title="Créer les comptes" onclick="_createIntervenantes()"
                    data-original-title="Ajouter une inscription">
                    <i class="flaticon-add"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <!--begin: Datatable-->
            <table class="table table-sm table-bordered table-checkable" style="width:100%;"
                id="dt_intervenants_members">
                <thead class="thead-light">
                    <tr>
                        <th></th>
                        <th>Prénom</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Etat Planning</th>
                        <th>Nb heure</th>
                        <th>Cout</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end::Datatable-->

        </div>
    </div>
    <x-modal id="modal_form_afintervenant" content="modal_form_afintervenant_content"/>
    <!-- <link href="{{ asset('custom/css/custom.css') }}" rel="stylesheet" type="text/css" /> -->
    <script>
        function _selection_af(member_id) {
        var modal_id = 'modal_form_afintervenant';
        var modal_content_id = 'modal_form_afintervenant_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/selectionafintervenant/' + member_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
    }
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var af_id = $("input[name='id']").val();
        /* NEW TABLE */
        var dtUrlIntervenantsMembers = '/api/sdt/enrollmentsmembers/' + af_id;
        var tableIm = $('#dt_intervenants_members');

        tableIm.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            processing: true,
            paging: true,
            ordering: false,
            ajax: {
                url: dtUrlIntervenantsMembers,
                type: 'POST',
                data: {
                    pagination: {
                        perpage: 50,
                    },
                },
            },
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
                            <input type="checkbox" value="` + data + `" class="checkable"/>
                            <span></span>
                        </label>`;
                },
            }],
        });

        tableIm.on('change', '.group-checkable', function() {
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

        tableIm.on('change', 'tbody tr .checkbox', function() {
            $(this).parents('tr').toggleClass('active');
        });

        var _reload_dt_intervenants_members = function() {
            $('#dt_intervenants_members').DataTable().ajax.reload();
        }
        /* END TABLE */
        var _formEnrollmentIntervenants = function(enrollment_id) {
            var af_id = $("input[name='id']").val();
            var modal_id = 'modal_form_enrollment_intervenants';
            var modal_content_id = 'modal_form_enrollment_intervenants_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/enrollmentintervenants/' + af_id + '/' + enrollment_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                }
            });
        }

        function _createIntervenantes() {
            var af_id = $("input[name='id']").val();

            var table = $('#dt_intervenants_members').DataTable();

            $('#dt_intervenants_members tbody').on('click', 'tr', function() {
                $(this).toggleClass('active');
            });

            var count = table.rows('.active').data().length;
            var dataselected = [];

            $("input:checkbox[class=checkable]:checked").each(function() {
                dataselected.push($(this).val());
            });

            var successMsg = "Les utilisateurs ont été bien créés.";
            var errorMsg = "La création ne marche pas.";
            var swalConfirmTitle = "Créer les utilisateurs!";
            var swalConfirmText = "Êtes-vous sûr de bien créer Les utilisateur? Si oui merci de saisir le rôle!";

            var formData = [];

            var html = (dataselected.length == 0 ?
                    '<b>Vous êtes sûr de vouloir créer les comptes pour tous les inscrits ?</b><br/><br/>' : '') +
                '<span><input type="radio" id="role" name="role" value="4" class="role" checked> <label for="role">Formateur</label></span>'

            Swal.fire({
                title: swalConfirmTitle,
                html: html,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Créer",
                cancelButtonText: "Non"
            }).then(function(result) {
                var role = document.querySelector('input[name="role"]:checked').value;

                formData = formData.concat([{
                        name: "af_id",
                        value: af_id
                    },
                    {
                        name: "count",
                        value: count
                    },
                    {
                        name: "data",
                        value: dataselected.length > 0 ? dataselected : 'all'
                    },
                    {
                        name: "role",
                        value: role
                    },
                    {
                        name: "type",
                        value: 'intervenant'
                    }
                ]);

                if (result.value) {
                    $.ajax({
                        type: 'POST',
                        url: '/api/sdt/createAccounts',
                        data: formData,
                        dataType: 'JSON',
                        success: function(result) {
                            result.forEach(elem => {
                                if (elem.success) {
                                    toastr.success(elem.msg);
                                } else {
                                    toastr.error(elem.msg);
                                }
                            });
                        }
                    });
                }
            });
        }
        
    </script>
@endif

@if ($viewtype == 'ressources')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Planification des ressources</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <button type="button" data-toggle="tooltip" title="Élargir tous"
                    onclick="ExpandCollapseAll('tree_scheduleressources','EXPAND')"
                    class="btn btn-sm btn-icon btn-light-primary mr-2">
                    <i class="fa fa-chevron-down"></i>
                </button>
                <button type="button" data-toggle="tooltip" title="Réduire tous"
                    onclick="ExpandCollapseAll('tree_scheduleressources','COLLAPSE')"
                    class="btn btn-sm btn-icon btn-light-success mr-2">
                    <i class="fa fa-chevron-up"></i>
                </button>
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title=""
                    onclick="resfreshJSTreeScheduleressources()" data-original-title="Rafraîchir"><i
                        class="flaticon-refresh"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <!--begin::Card-->
                    <div class="card card-custom card-border">
                        <div class="card-body p-3">
                            <!--begin: jstree-->
                            <div id="tree_scheduleressources" class="tree-demo font-size-sm"></div>
                            <!--end: jstree-->
                        </div>
                    </div>
                    <!--end::Card-->
                </div>
                <div class="col-lg-6">
                    <!-- begin::Form -->
                    <form id="formScheduleRessources" class="form">
                        <!--begin::Card-->
                        <div class="card card-custom card-border">
                            <div style="min-height:0px;" class="card-header p-2">
                                <div class="card-title">
                                    <h3 class="card-label font-size-h6">Ressources</h3>
                                </div>
                                <div class="card-toolbar">
                                    <button class="btn btn-sm btn-light-primary" data-toggle="tooltip"
                                        data-theme="dark" title="Affecter" data-original-title="Enregistrer"><i
                                            class="flaticon2-checkmark"></i> <span id="BTN_SAVE"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="form-group">
                                    <label>Type de ressource</label>
                                    <select class="form-control form-control-sm" name="type"
                                        id="typesRessourcesSelect">
                                    </select>
                                </div>
                                <span id="loader-rs"></span>
                                <!--begin: Datatable-->
                                <table class="table table-sm table-bordered" id="dt_ressources">
                                    <thead class="thead-light">
                                        <tr>
                                            <th></th>
                                            <th>Ressource</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <!--end: Datatable-->
                            </div>
                        </div>
                        <!--end::Card-->
                    </form>
                    <!-- end::Form -->
                </div>
            </div>
        </div>
    </div>
    <!--end::Card-->


    <!-- Modal -->
    <div class="modal fade" id="showRessourceMessagesModal" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdrop" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_form_entitie_title"><i class="flaticon-information"></i>
                        Contrôle
                        des
                        ressources </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div data-scroll="true" data-height="600" id="showRessourceMessagesModalBody"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i
                            class="fa fa-times"></i> Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->

    <style>
        .jstree-anchor>.jstree-checkbox-disabled {
            display: none;
        }
    </style>
    <script>
        $('[data-scroll="true"]').each(function() {
            var el = $(this);
            KTUtil.scrollInit(this, {
                mobileNativeScroll: true,
                handleWindowResize: true,
                rememberPosition: (el.data('remember-position') == 'true' ? true : false)
            });
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        _loadDatasForSelectOptions('typesRessourcesSelect', 'RES_TYPES', 0, 1);

        var af_id = $("input[name='id']").val();
        var tree_scheduleressources = 'tree_scheduleressources';
        $('#' + tree_scheduleressources).jstree({
            "core": {
                "multiple": true,
                "themes": {
                    "responsive": true
                },
                'data': {
                    'url': function(node) {
                        return '/api/tree/schedules/ressources/' + af_id + '/withressources';
                    },
                    'data': function(node) {
                        return {
                            'parent': node.id
                        };
                    }
                },
            },
            "checkbox": {
                "three_state": true, // to avoid that fact that checking a node also check others
            },
            "plugins": ["state", "types", "wholerow", "checkbox"]
            //"plugins": ["state", "types"]
        });

        function resfreshJSTreeScheduleressources() {
            var af_id = $("input[name='id']").val();
            $('#tree_scheduleressources').jstree(true).settings.core.data.url = '/api/tree/schedules/ressources/' + af_id +
                '/withressources';
            $('#tree_scheduleressources').jstree(true).refresh();

            $('#tree_scheduleressources').bind("ready.jstree", function() {
                initializeSelections();
            }).jstree();
        }

        $('#tree_scheduleressources').bind("ready.jstree", function() {
            initializeSelections();
        }).jstree();

        function initializeSelections() {
            var instance = $('#tree_scheduleressources').jstree(true);
            instance.deselect_all();
        }

        /* var _formScheduleRessources = function(type) {
            var af_id = $("input[name='id']").val();
            var modal_id = 'modal_form_schedulecontacts';
            var modal_content_id = 'modal_form_schedulecontacts_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/schedulecontact/' + af_id + '/' + type,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {

                },
                complete: function(result, status) {

                }
            });
        } */

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

        var _deleteScheduleRessource = function(row_id) {
            var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
            var successMsg = "L'\affectation a été supprimé.";
            var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer?";
            var errorMsg = "L'\affectation n\'a pas été supprimée.";
            var urlDelete = "/api/delete/scheduleressource/" + row_id;
            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Oui, supprimez-le!"
            }).then(function(result) {
                if (result.value) {
                    KTApp.blockPage();
                    $.ajax({
                        url: urlDelete,
                        type: "GET",
                        dataType: "JSON",
                        success: function(result, status) {
                            if (result.success) {
                                _showResponseMessage("success", successMsg);
                            } else {
                                _showResponseMessage("error", errorMsg);
                            }
                        },
                        error: function(result, status, error) {
                            _showResponseMessage("error", errorMsg);
                        },
                        complete: function(result, status) {
                            resfreshJSTreeScheduleressources();
                            KTApp.unblockPage();
                        }
                    });
                }
            });
        }

        function showRessourceMessages(html) {
            $("#showRessourceMessagesModalBody").html(html);
            $("#showRessourceMessagesModal").modal('show');
        }

        $("#formScheduleRessources").validate({
            rules: {},
            messages: {},
            submitHandler: function(form) {
                _showLoader('BTN_SAVE');
                var formData = $(form).serializeArray();
                var schedules_ids = $("#tree_scheduleressources").jstree("get_selected");
                if (schedules_ids) {
                    formData = formData.concat([{
                        name: "schedules_ids",
                        value: schedules_ids
                    }, ]);
                }
                $.ajax({
                    type: 'POST',
                    url: '/form/scheduleressource',
                    data: formData,
                    dataType: 'JSON',
                    success: function(result) {
                        _hideLoader('BTN_SAVE');
                        if (result.success) {
                            _showResponseMessage('success', result.msg);
                            //$('#modal_form_schedulecontacts').modal('hide');
                        } else {
                            _showResponseMessage('error', result.msg);
                        }
                        if (result.htmlMessage != '') {
                            /* swal.fire({
                                html: result.htmlMessage,
                                icon: 'error',
                                buttonsStyling: false,
                                confirmButtonText: '<i class="far fa-times-circle"></i> Fermer',
                                customClass: {
                                    confirmButton: "btn btn-light-primary"
                                },
                                //timer: 1500
                            }).then(function() {}); */
                            showRessourceMessages(result.htmlMessage);
                        }

                    },
                    error: function(error) {
                        _hideLoader('BTN_SAVE');
                        _showResponseMessage('error',
                            'Veuillez vérifier les champs du formulaire...');
                    },
                    complete: function(resultat, statut) {
                        _hideLoader('BTN_SAVE');
                        resfreshJSTreeScheduleressources();
                    }
                });
                return false;
            }
        });

        var ressource_type = 'ALL';
        var dtUrlSelectForRessources = '/api/sdt/select/ressources/' + ressource_type;
        var dt_ressources = $('#dt_ressources');
        // begin first table
        dt_ressources.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            processing: true,
            searching: false,
            paging: true,
            ordering: false,
            info: false,
            ajax: {
                url: dtUrlSelectForRessources,
                type: 'POST',
                data: {
                    pagination: {
                        perpage: 50,
                    },
                },
            },
            lengthMenu: [5, 10, 25, 50],
            pageLength: 25,
            headerCallback: function(thead, data, start, end, display) {
                thead.getElementsByTagName('th')[0].innerHTML = `
                    <label class="checkbox checkbox-single">
                        <input type="checkbox" value="" class="group-checkable"/>
                        <span></span>
                    </label>`;
            },
        });
        dt_ressources.on('change', '.group-checkable', function() {
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
        dt_ressources.on('change', 'tbody tr .checkbox', function() {
            $(this).parents('tr').toggleClass('active');
        });

        $('#typesRessourcesSelect').on('change', function() {
            _loadRessources();
        });
        var _loadRessources = function() {
            $('#loader-rs').html(
                '<p class="text-primary"><i class="fas fa-spinner fa-spin text-primary"></i> Chargement en cours ...</p>'
            );
            var ressource_type = $('#typesRessourcesSelect').val();
            if (ressource_type == "") {
                ressource_type = "ALL";
            }
            var table = 'dt_ressources';
            $.ajax({
                type: "POST",
                dataType: 'json',
                data: {},
                url: '/api/sdt/select/ressources/' + ressource_type,
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
                $('#loader-rs').html('');
            });
            return false;
        }
    </script>
@endif

@if ($viewtype == 'documents')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Documents Client</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">

            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">

            <!-- begin::Line tab -->
                @if (auth()->user()->roles[0]->code != 'FORMATEUR')
                    <ul class="nav nav-tabs nav-tabs-line mb-5">
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_doc_2" onclick="_load_documents_tab(2)">
                                    <span class="nav-icon"><i class="flaticon-file-2"></i></span>
                                    <span class="nav-text">Devis</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_doc_3" onclick="_load_documents_tab(3)">
                                    <span class="nav-icon"><i class="flaticon-interface-10"></i></span>
                                    <span class="nav-text">Conventions & contrats</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_doc_4" onclick="_load_documents_tab(4)">
                                    <span class="nav-icon"><i class="flaticon-bell-1"></i></span>
                                    <span class="nav-text">Convocations</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_doc_5" onclick="_load_documents_tab(5)">
                                    <span class="nav-icon"><i class="flaticon-interface-11"></i></span>
                                    <span class="nav-text">Factures</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_doc_6" onclick="_load_documents_tab(6)">
                                    <span class="nav-icon"><i class="flaticon-medal"></i></span>
                                    <span class="nav-text">Attestations</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_doc_7" onclick="_load_documents_tab(7)">
                                    <span class="nav-icon"><i class="flaticon-medal"></i></span>
                                    <span class="nav-text">Attestations étudiants</span>
                                </a>
                            </li>
                    </ul>
                @endif
                <div class="tab-content mt-5" id="myTabContent">
                    <div class="tab-pane fade " id="tab_doc_1" role="tabpanel" aria-labelledby="tab_doc_1">
                    </div>
                    <div class="tab-pane fade show active" id="tab_doc_2" role="tabpanel" aria-labelledby="tab_doc_2">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_3" role="tabpanel" aria-labelledby="tab_doc_3">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_4" role="tabpanel" aria-labelledby="tab_doc_4">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_5" role="tabpanel" aria-labelledby="tab_doc_5">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_6" role="tabpanel" aria-labelledby="tab_doc_6">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_7" role="tabpanel" aria-labelledby="tab_doc_7">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_8" role="tabpanel" aria-labelledby="tab_doc_8">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_9" role="tabpanel" aria-labelledby="tab_doc_9">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_10" role="tabpanel" aria-labelledby="tab_doc_10">
                    </div>
                </div>
            <!-- end::Line tab -->
        </div>
    </div>
    <style>
        .jstree-anchor>.jstree-checkbox-disabled {
            display: none;
        }

        .jstree-default .jstree-anchor {
            height: 100% !important;
        }

        #myTabContent {
            width: 100%;
        }
    </style>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        _load_documents_tab(2);

        function _load_documents_tab(id) {
            _reset_documents_tab(id);
            var block_id = 'tab_doc_' + id;
            var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
            KTApp.block('#' + block_id, {
                overlayColor: '#000000',
                state: 'danger',
                message: 'Veuillez patienter svp...'
            });
            $.ajax({
                url: '/get/content/tab/docs/' + id + '/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + block_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {
                    KTApp.unblock('#' + block_id);
                }
            });
        }

        function _reset_documents_tab(id) {
            if (id == 1) {
                $('#tab_doc_2,#tab_doc_3,#tab_doc_4,#tab_doc_5,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 2) {
                $('#tab_doc_1,#tab_doc_3,#tab_doc_4,#tab_doc_5,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 3) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_4,#tab_doc_5,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 4) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_3,#tab_doc_5,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 5) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_3,#tab_doc_4,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 6) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_3,#tab_doc_4,#tab_doc_5,#tab_doc_7').html('');
            } else if (id == 7) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_3,#tab_doc_4,#tab_doc_5,#tab_doc_6').html('');
            }
        }
        $('[data-toggle="tooltip"]').tooltip();
    </script>
@endif

@if ($viewtype == 'documentsfrm')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Documents Formateur</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">

            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">

            <!-- begin::Line tab -->
                @if (auth()->user()->roles[0]->code != 'FORMATEUR')
                    <ul class="nav nav-tabs nav-tabs-line mb-5">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab_doc_1" onclick="_load_documents_tab(1)">
                                <span class="nav-icon"><i class="flaticon-notes"></i></span>
                                <span class="nav-text">Contrats intervenants</span>
                            </a>
                        </li>
                        <li class="nav-item" >
                            <a class="nav-link" data-toggle="tab" href="#tab_doc_8" onclick="_load_documents_tab(8)">
                                <span class="nav-icon"><i class="flaticon-file-2"></i></span>
                                <span class="nav-text">Devis intervenants</span>
                            </a>
                        </li>
                        <li class="nav-item" >
                            <a class="nav-link" data-toggle="tab" href="#tab_doc_9" onclick="_load_documents_tab(9)">
                                <span class="nav-icon"><i class="flaticon-file-2"></i></span>
                                <span class="nav-text">Contrats sur facture</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab_doc_10" onclick="_load_documents_tab(10)">
                                <span class="nav-icon"><i class="flaticon-file-2"></i></span>
                                <span class="nav-text">Factures intervenants</span>
                            </a>
                        </li>
                    </ul>
                @endif
                <div class="tab-content mt-5" id="myTabContent">
                    <div class="tab-pane fade show active" id="tab_doc_1" role="tabpanel" aria-labelledby="tab_doc_1">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_2" role="tabpanel" aria-labelledby="tab_doc_2">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_3" role="tabpanel" aria-labelledby="tab_doc_3">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_4" role="tabpanel" aria-labelledby="tab_doc_4">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_5" role="tabpanel" aria-labelledby="tab_doc_5">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_6" role="tabpanel" aria-labelledby="tab_doc_6">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_7" role="tabpanel" aria-labelledby="tab_doc_7">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_8" role="tabpanel" aria-labelledby="tab_doc_8">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_9" role="tabpanel" aria-labelledby="tab_doc_9">
                    </div>
                    <div class="tab-pane fade" id="tab_doc_10" role="tabpanel" aria-labelledby="tab_doc_10">
                    </div>
                </div>
            <!-- end::Line tab -->
        </div>
    </div>
    <style>
        .jstree-anchor>.jstree-checkbox-disabled {
            display: none;
        }

        .jstree-default .jstree-anchor {
            height: 100% !important;
        }

        #myTabContent {
            width: 100%;
        }
    </style>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        _load_documents_tab(1);

        function _load_documents_tab(id) {
            _reset_documents_tab(id);
            var block_id = 'tab_doc_' + id;
            var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
            KTApp.block('#' + block_id, {
                overlayColor: '#000000',
                state: 'danger',
                message: 'Veuillez patienter svp...'
            });
            $.ajax({
                url: '/get/content/tab/docs/' + id + '/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + block_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {
                    KTApp.unblock('#' + block_id);
                }
            });
        }

        function _reset_documents_tab(id) {
            if (id == 1) {
                $('#tab_doc_2,#tab_doc_3,#tab_doc_4,#tab_doc_5,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 2) {
                $('#tab_doc_1,#tab_doc_3,#tab_doc_4,#tab_doc_5,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 3) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_4,#tab_doc_5,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 4) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_3,#tab_doc_5,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 5) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_3,#tab_doc_4,#tab_doc_6,#tab_doc_7').html('');
            } else if (id == 6) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_3,#tab_doc_4,#tab_doc_5,#tab_doc_7').html('');
            } else if (id == 7) {
                $('#tab_doc_1,#tab_doc_2,#tab_doc_3,#tab_doc_4,#tab_doc_5,#tab_doc_6').html('');
            }
        }
        $('[data-toggle="tooltip"]').tooltip();
    </script>
@endif

@if ($viewtype == 'historique')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Historique</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title=""
                    onclick="_reload_dt_historique()" data-original-title="Rafraîchir"><i
                        class="flaticon-refresh"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_historique">
                <thead>
                    <tr>
                        <th></th>
                        <th>Date</th>
                        <th>Qui</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var af_id = $("input[name='id']").val();
        var dtUrl = '/api/sdt/historique/af/' + af_id;
        var table = $('#dt_historique');
        // begin first table
        table.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            processing: true,
            paging: true,
            ordering: true,
            order: [
                [1, "desc"]
            ],
            ajax: {
                url: dtUrl,
                type: 'POST',
                data: {
                    pagination: {
                        perpage: 50,
                    },
                },
            },
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
                },
                {
                    targets: 1,
                    orderable: true,
                }
            ],
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
        var _reload_dt_historique = function() {
            $('#dt_historique').DataTable().ajax.reload();
        }
    </script>
@endif

@if ($viewtype == 'stages')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Périodes de stage</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                    onclick="_reload_dt_stages()"><i class="flaticon-refresh"></i></button>
                <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                    title="Ajouter une période de stage" onclick="_formStage(0,{{ $row->id }})"><i
                        class="flaticon2-add-1"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <!--begin::filter-->
            <x-filter-form type="Stages" />
            <!--end::filter-->
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_stages">
                <thead>
                    <tr>
                        <th></th>
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
    <x-modal id="modal_form_stage" content="modal_form_stage_content" />
    <x-modal id="modal_form_stageItem" content="modal_form_stageItem_content" />
    <script src="{{ asset('custom/js/list-stages.js?v=3') }}"></script>
@endif

@if ($viewtype == 'proposals')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Propositions de stage</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
                <!--begin::Dropdown-->
                <div class="dropdown dropdown-inline mr-2">
                    <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="la la-download"></i></button>
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-item">
                                <a href="javascript:void(0)" onclick="_mergeStageProposal(0)" class="navi-link">
                                    <span class="navi-icon">
                                        <i class="la la-print"></i>
                                    </span>
                                    <span class="navi-text">Impression en masse</span>
                                </a>
                            </li>
                            <li class="navi-item">
                                <a href="javascript:void(0)" onclick="_downloadStageProposal(0)" class="navi-link">
                                    <span class="navi-icon">
                                        <i class="la la-file-pdf-o"></i>
                                    </span>
                                    <span class="navi-text">Génération pdf en masse</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--end::Dropdown--> 
                <!--begin::Button-->
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                    onclick="_reload_dt_stage_proposals()"><i class="flaticon-refresh"></i></button>
                <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip"
                    title="Ajouter une période de stage" onclick="_formStageProposal(0,{{ $row->id }})"><i
                        class="flaticon2-add-1"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <!--begin::filter-->
            <x-filter-form type="StageProposals" />
            <!--end::filter-->
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_stage_proposals">
                <thead>
                    <tr>
                        <th></th>
                        <th>N°</th>
                        <th>Stagiaire</th>
                        <th>L'organisme d'accueil</th>
                        <th>Période de stage</th>
                        <th>Infos stage</th>
                        <th>Etat</th>
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
    <x-modal id="modal_form_stage_proposal" content="modal_form_stage_proposal_content" />
    <x-modal id="modal_form_stage_proposal_attachment" content="modal_form_stage_proposal_attachment_content" />
    <!-- <script src="{{ asset('custom/js/list-stage-proposals.js?v=1') }}"></script> -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('[data-toggle="tooltip"]').tooltip();
        var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
        var dtUrl = '/api/sdt/stages/proposals/' + af_id;
        var table = $('#dt_stage_proposals');
        // begin first table
        table.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            processing: true,
            paging: true,
            ordering: false,
            ajax: {
                url: dtUrl,
                type: 'POST',
                data: {
                    pagination: {
                        perpage: 50,
                    },
                },
            },
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
                            <input type="checkbox" value="` + data + `" class="checkable"/>
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

        function _reload_dt_stage_proposals() {
            $('#dt_stage_proposals').DataTable().ajax.reload();
        }

        function _formStageProposal(internshiproposal_id, af_id) {
            var modal_id = 'modal_form_stage_proposal';
            var modal_content_id = 'modal_form_stage_proposal_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/stage/proposal/' + internshiproposal_id + '/' + af_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {}
            });
        }

        function _formStageProposalDocuments(internshiproposal_id, af_id) {
            var modal_id = 'modal_form_stage_proposal_attachment';
            var modal_content_id = 'modal_form_stage_proposal_attachment_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/stage/proposal/attachments/' + internshiproposal_id + '/' + af_id,
                type: 'GET',
                async: false,
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {}
            });
            $('#' + modal_content_id).css('width', '160%');
        }

        $('#filter_datepicker').datepicker({
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
        var form_id = 'formFilterStageProposals';
        $("#" + form_id).submit(function(event) {
            event.preventDefault();
            KTApp.blockPage();
            var formData = $(this).serializeArray();
            var table = 'dt_stage_proposals';
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
            _reload_dt_stage_proposals();
        }

        _loadSessionsSelectStagePeriodsFilterOptions('sessionsSelectStagePeriodsFilter');

        function _loadSessionsSelectStagePeriodsFilterOptions(select_id) {
            var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
            var selected_session_id = 0;
            var default_session_id = 0;
            _showLoader('LOADER_PERIODES_FILTER');
            //$('#' + select_id).empty();
            $.ajax({
                url: '/api/select/options/sessions/periods/' + default_session_id + '/' + af_id,
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
            }).done(function() {
                if (selected_session_id != 0 && selected_session_id != '') {
                    $('#' + select_id + ' option[value="' + selected_session_id + '"]').attr('selected',
                        'selected');
                }
                _hideLoader('LOADER_PERIODES_FILTER');
            });
        }
        _loadMembersSelectStagePeriodsFilterOptions('membersSelectFilter');

        function _loadMembersSelectStagePeriodsFilterOptions(select_id) {
            var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
            var selected_member_id = 0;
            var default_member_id = 0;
            _showLoader('LOADER_STAGIAIRES_FILTER');
            //$('#' + select_id).empty();
            $.ajax({
                url: '/api/select/options/stagiaires/members/' + default_member_id + '/' + af_id,
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
            }).done(function() {
                if (selected_member_id != 0 && selected_member_id != '') {
                    $('#' + select_id + ' option[value="' + selected_member_id + '"]').attr('selected', 'selected');
                }
                _hideLoader('LOADER_STAGIAIRES_FILTER');
            });
        }

        // add mass download pdf files
        function _mergeStageProposal(id){
            var TableauIdProcess = new Array();
            var j = 0;
            if(id>0){
                TableauIdProcess[0]=id;
            }else{	
                $('#dt_stage_proposals input[class="checkable"]').each(function(){
                    var checked = jQuery(this).is(":checked");
                    if(checked){
                        TableauIdProcess[j] = jQuery(this).val();
                        j++;
                    }
                });
            }
            if(TableauIdProcess.length<1){
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Veuillez sélectionner une ou plusieurs propositions!',
                })
            }else{
                KTApp.blockPage();
                $.ajax({
                    type: 'get',
                    url: '/api/merge/pdf/proposals',
                    data: {
                        ids_stage_proposals: TableauIdProcess,
                    },
                    cache: false,
                    xhrFields:{
                        responseType: 'blob'
                    },
                    success: function(data) {
                        const time = Date.now();
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(data);
                        link.download = 'Proposition-'+time+'.pdf';
                        link.click();
                    },
                    error: function(error) {},
                    complete: function(resultat, statut) {
                        KTApp.unblockPage();
                    }
                });
                return false; 	
            }
        }
        function _downloadStageProposal(id){
            var TableauIdProcess = new Array();
            var j = 0;
            if(id>0){
                TableauIdProcess[0]=id;
            }else{	
                $('#dt_stage_proposals input[class="checkable"]').each(function(){
                    var checked = jQuery(this).is(":checked");
                    if(checked){
                        TableauIdProcess[j] = jQuery(this).val();
                        j++;
                    }
                });
            }
            if(TableauIdProcess.length<1){
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Veuillez sélectionner une ou plusieurs propositions!',
                })
            }else{
                KTApp.blockPage();
                $.ajax({
                    type: 'get',
                    url: '/api/download/zip/proposals',
                    data: {
                        ids_stage_proposals: TableauIdProcess,
                    },
                    cache: false,
                    xhrFields:{
                        responseType: 'blob'
                    },
                    success: function(data) {
                        const time = Date.now();
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(data);
                        link.download = 'propositions-'+time+'.zip';
                        link.click();
                    },
                    error: function(error) {},
                    complete: function(resultat, statut) {
                        KTApp.unblockPage();
                    }
                });
                return false; 	
    	}
        }
    </script>
@endif