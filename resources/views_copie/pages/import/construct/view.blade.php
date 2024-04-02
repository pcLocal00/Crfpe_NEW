@if ($viewtype == 1)
    <!--begin::Card-->
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 ">
            <div class="card-title">
                <h3 class="card-label">Import des fichiers parcours sup
                </h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <a class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                    title="Télécharger le modèle xlsx" href="/custom/xlsx/modele_parcoursup.xls"><i
                        class="flaticon-download"></i></a>
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                    onclick="_reload_dt_entities()"><i class="flaticon-refresh"></i></button>
                <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Importer un fichier"
                    onclick="_formFileUpload()"><i class="flaticon2-add-1"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <div class="card-body">

            <!--begin::filter-->

            <div class="row mb-6">
                <div class="col-lg-12">
                    <label>Les fichiers téléchargés:</label>
                    <select class="form-control datatable-input" data-col-index="2" name="file_imported"
                        id="file_imported">
                        <option value="">Séléctionnez un fichier</option>
                        @if ($datafilter->files)
                            @foreach ($datafilter->files as $file)
                                <option value="{{ $file->id }}">Fichier n° : {{ $file->id }} -
                                    {{ $file->name }} - {{ $file->created_at->format('d/m/Y H:i') }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-lg-12">
                    <button class="btn btn-outline-danger btn-sm mt-2 mr-2" data-toggle="tooltip"
                        onclick="_deleteSelectedFile()" title="Supprimer le fichier selectionné"><i
                            class="flaticon-delete"></i> Supprimer le fichier selectionné</button>
                    <button class="btn btn-outline-warning btn-sm mt-2 mr-2" data-toggle="tooltip"
                        onclick="_showLogsFile()" title="Afficher les logs du fichier selectionné"><i
                            class="flaticon-file"></i> Afficher les logs du fichier selectionné</button>
                </div>
                <div class="col-lg-12">
                    <label class="checkbox pb-5 pt-5">
                        <input type="checkbox" id="only_memorized"><span></span>Lignes mémorisées seulement
                    </label>
                </div>
            </div>

            <!--end::filter-->

            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_contacts">
                <thead>
                    <tr>
                        <th>N° Ligne</th>
                        <th>Infos Ligne</th>
                        <th>ID Contact</th>
                        <th>Infos</th>
                        <th>Etat</th>
                        <th>Nom</th>
                        <th>Date</th>
                        <th>Proposé</th>
                        {{-- <th></th> EDIT --}}
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <!--end::Card-->
@elseif ($viewtype == 2)
    <!--begin::Card-->
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 ">
            <div class="card-title">
                <h3 class="card-label">Import des fichiers des prospects
                </h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <a class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip"
                    title="Télécharger le modèle xlsx" href="/custom/xlsx/modele_prospects.xls"><i
                        class="flaticon-download"></i></a>
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                    onclick="_reload_dt_entities()"><i class="flaticon-refresh"></i></button>
                <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Importer un fichier"
                    onclick="_formFileUpload('PROSPECTS')"><i class="flaticon2-add-1"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <div class="card-body">

            <!--begin::filter-->

            <div class="row mb-6">
                <div class="col-lg-12">
                    <label>Les fichiers téléchargés:</label>
                    <select class="form-control datatable-input" data-col-index="2" name="file_imported"
                        id="file_imported">
                        <option value="">Séléctionnez un fichier</option>
                        @if ($datafilter->files)
                            @foreach ($datafilter->files as $file)
                                <option value="{{ $file->id }}">Fichier n° : {{ $file->id }} -
                                    {{ $file->name }} - {{ $file->created_at->format('d/m/Y H:i') }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-lg-12">
                    <button class="btn btn-outline-danger btn-sm mt-2 mr-2" data-toggle="tooltip"
                        onclick="_deleteSelectedFile()" title="Supprimer le fichier selectionné"><i
                            class="flaticon-delete"></i> Supprimer le fichier selectionné</button>
                    <button class="btn btn-outline-warning btn-sm mt-2 mr-2" data-toggle="tooltip"
                        onclick="_showLogsFile()" title="Afficher les logs du fichier selectionné"><i
                            class="flaticon-file"></i> Afficher les logs du fichier selectionné</button>
                </div>
                <div class="col-lg-6">
                    <label class="checkbox pb-5 pt-5">
                        <input type="checkbox" id="only_memorized"><span></span>Lignes mémorisées seulement
                    </label>
                </div>

                <div class="col-lg-12 accordion accordion-solid accordion-toggle-plus" id="accordionMultiple">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title collapsed" data-toggle="collapse" data-target="#collapseMultiple">
                                <i class="flaticon-map"></i> Création des tâches
                            </div>
                        </div>
                        <div id="collapseMultiple" class="collapse" data-parent="#accordionMultiple">
                            <div class="card-body">
                                <form id="import-form">
                                    <input type="hidden" name="import_attachments" id="import_attachments">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group d-flex flex-column">
                                                <label for="title">Responsable:</label>
                                                <select class="form-control" name="import_responsable" required
                                                    id="import_responsable">
                                                    <option value="">Séléctionnez</option>
                                                    @foreach ($import_repsonsables as $resp)
                                                        <option value="{{ $resp->id }}">{{ $resp->firstname }}
                                                            {{ $resp->lastname }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group d-flex flex-column">
                                                <label for="title">Type:</label>
                                                <select class="form-control" name="import_type" id="import_type"
                                                    required>
                                                    <option value="">Séléctionnez</option>
                                                    @foreach ($import_types as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="end_date">Date d'échéance:</label>
                                                <input class="form-control date_datepicker" type="text"
                                                    name="end_date" id="end_date" required aria-invalid="false">
                                            </div>
                                            <div class="form-group">
                                                <label for="callback_date">Date de Rappel:</label>
                                                <input class="form-control date_datepicker" type="text"
                                                    name="callback_date" id="callback_date" readonly
                                                    aria-invalid="false">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group d-flex flex-column">
                                                <label for="title">Mode de réponse:</label>
                                                <select class="form-control" name="import_rsp_mode" required
                                                    id="import_rsp_mode">
                                                    <option value="">Séléctionnez</option>
                                                    @foreach ($import_rsp_modes as $mode)
                                                        <option value="{{ $mode->id }}">{{ $mode->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <button class="btn btn-primary"><i class="fa fa-check"></i>Ajouter les tâches
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--end::filter-->

            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_contacts">
                <thead>
                    <tr>
                        {{-- <th>
                            <label class="checkbox checkbox-single">
                                <input type="checkbox" value="" class="group-checkable">
                                <span></span>
                            </label>
                        </th> --}}
                        <th>N° Ligne</th>
                        <th>Infos Ligne</th>
                        <th>ID Contact</th>
                        <th>Infos</th>
                        <th>Etat</th>
                        <th>Nom</th>
                        <th>Date</th>
                        <th>Proposé</th>
                        {{-- <th></th> EDIT --}}
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <!--end::Card-->
@endif
<x-modal id="modal_form_entitie" content="modal_form_entitie_content" />
<x-modal id="modal_logs" content="modal_logs_content" />

<script type="text/javascript">
    var attachments = new Set();
    @if ($viewtype == 2)
        var ct_table = $('#dt_contacts').dataTable({
            columnDefs: [{
                orderable: false,
                targets: 0
            }]
        });
    @else
        var ct_table = $('#dt_contacts').dataTable();
    @endif

    function _formFileUpload(category = 'PARCOURSUP') {
        var modal_id = 'modal_form_entitie';
        var modal_content_id = 'modal_form_entitie_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/formFileUpload/' + category,
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

    $('form#import-form').submit(function(e) {
        e.preventDefault();
        if (Array.from(attachments).length > 0) {
            message =
                "Vous êtes au mesure de générer des tâches sur les contacts sélectionnés. Voulez vous continuer ?";
            Swal.fire({
                title: message,
                icon: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                // focusConfirm: false,
                confirmButtonText: '<i class="fa fa-check"></i> Valider',
                cancelButtonText: '<i class="fa fa-times"></i> Annuler',
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: '/form/import/task',
                        type: 'POST',
                        // dataType: 'json',
                        data: $('form#import-form').serialize(),
                        success: function(data) {
                            Swal.fire({
                                title: data.message,
                                icon: data.success ? 'success' : 'warning',
                                // showCloseButton: true,
                                // focusConfirm: false,
                                timeout: 5000,
                                confirmButtonText: '<i class="fa fa-check"></i> OK',
                            });
                        },
                        beforeSend: function() {

                        },
                        complete: function(result, status) {

                        }
                    });
                }
            });
        } else {
            Swal.fire({
                title: "Aucun contact sélectionné.",
                icon: 'warning',
                timeout: 5000,
                confirmButtonText: '<i class="fa fa-check"></i> OK',
            });
        }

    });

    $(document).on('change', 'table td input[type=checkbox]', function(e) {
        const value = $(this).val();

        if ($(this).is(':checked')) {
            attachments.add(value);
        } else {
            attachments.delete(value);
        }

        $('input#import_attachments').val(Array.from(attachments).join(','));
    });

    $(document).on('change', 'table th input[type=checkbox]', function(e) {
        $('table td input[type=checkbox]').prop('checked', $(this).is(':checked')).trigger('change');
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var is_memorized = $('input#only_memorized').prop("checked") == true;
        var etat = data[4];
        return settings.sTableId !== 'dt_contacts' || !is_memorized || etat.indexOf('Mémorisé') > 0;
    });

    $('input#only_memorized').change(function() {
        $('#dt_contacts').dataTable().fnDraw();
    });

    $('#file_imported, #import_responsable, #import_type').select2();

    function _loadContactsByFile() {
        var file_id = $('#file_imported').val();
        if (file_id) {
            var dtUrl = '/api/sdt/file/contacts/' + file_id;
            var form_id = 'formFilterContacts';
            var formData = $(this).serializeArray();
            var table = 'dt_contacts';
            $.ajax({
                type: "GET",
                dataType: 'json',
                // data: formData,
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
            }).done(function(data) {});
        }
    }

    function _showSuggested(attachment) {
        var modal_id = 'modal_logs';
        var modal_content_id = 'modal_logs_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/view/suggested/' + attachment,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
                $('#suggested-table').dataTable();
            },
        });
    }

    function _selectSuggestedContact(contact, attachment) {
        $.ajax({
            url: '/api/suggested/select/{{ $viewtype == 2 ? 'prospect/' : '' }}' + contact + '/' +
                attachment,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modal_logs').modal('hide');
                    $('#file_imported').trigger('change');
                }

                Swal.fire({
                    icon: response.success ? 'success' : 'error',
                    title: '',
                    text: response.success ? 'Contact ' + (contact > 0 ? 'attaché' : 'créé') +
                        ' avec succès' : 'Erreur lors d\'attachement',
                });
            },
        });
    }

    function _selectSuggestedContactWithConfirm(contact, attachment, message =
        "Vous êtes au mesure de sélectionner le contact proposé. Voulez vous continuer?") {
        Swal.fire({
            title: message,
            icon: 'warning',
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: '<i class="fa fa-check"></i> Valider',
            cancelButtonText: '<i class="fa fa-times"></i> Annuler',
        }).then(function(result) {
            if (result.value) {
                _selectSuggestedContact(contact, attachment);
            }
        });
    }

    function _declineSugAndNew(attachment) {
        _selectSuggestedContactWithConfirm(0, attachment,
            "Vous êtes au mesure de refuser le contact proposé, et de créer un nouveau. Voulez vous continuer?");
    }

    function _declineSugAndId(attachment) {
        Swal.fire({
            title: "Vous êtes au mesure de refuser le contact proposé. Merci de saisir l'ID d'un autre contact:",
            icon: 'info',
            html: '<input class="form-control" name="other-contact" id="other-contact">',
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: '<i class="fa fa-check"></i> Valider',
            cancelButtonText: '<i class="fa fa-times"></i> Annuler',
        }).then(function(result) {
            const contact = $('input#other-contact').val();
            if (result.value && contact.length > 0 && parseInt(contact) > 0) {
                _selectSuggestedContact(contact, attachment);
            }
        });
    }

    function _deleteSelectedFile() {
        var file_id = $('#file_imported').val();
        if (!file_id) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Veuillez sélectionner un fichier!',
            });
        } else {
            var successMsg = "Le fichier a été supprimée.";
            var errorMsg = "Le fichier n\'a pas été supprimée.";
            var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le fichier?";
            var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
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
                        url: "/api/delete/gedattachment",
                        type: "DELETE",
                        data: {
                            file_id: file_id,
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
                            _loadContentImport(1);
                            KTApp.unblockPage();
                        }
                    });
                }
            });
        }
    }

    function _showLogsFile() {
        var file_id = $('#file_imported').val();
        if (!file_id) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Veuillez sélectionner un fichier!',
            });
        } else {
            var modal_id = 'modal_logs';
            var modal_content_id = 'modal_logs_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/view/logs/' + file_id,
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
    }

    function _displaySpinner(show = true) {
        $('#modal_form_entitie .spinner-border')[show ? 'show' : 'hide']();
        $('#modal_form_entitie .modal-footer button')[!show ? 'show' : 'hide']();
    }

    $('#file_imported').on('change', function(e) {
        _loadContactsByFile();
    });

    $('.date_datepicker').datepicker({
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
</script>
