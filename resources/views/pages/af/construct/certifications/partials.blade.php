@if ($block_id == 1)
    <!-- Accordion Filtrage -->
    <x-filter-form type="Cert" :datafilter='$datafilter' />
    <!-- END Accordion Filtrage -->

    <div class="float-right">
        <!-- Tree ADD BUTTON -->
        <button class="btn btn-light-primary" onclick="_formCertSession()" data-toggle="tooltip" title=""
            onclick="" data-original-title="Ajouter un niveau">
            <i class="flaticon2-add-1"></i> Ajouter un niveau
        </button>
        <!-- END ADD BUTTON -->
    </div>
    <!-- Tree MODULES -->
    <div id="structure_temporelle_tree" class="tree-cert"></div>
    <!-- END Tree MODULES -->
@elseif($block_id == 2)
    <x-filter-form type="Cert" :datafilter='$datafilter' />

    <!--begin::Card-->
    <div class="card card-custom">
        <div class="row">
            <div class="col-lg-6">
                <div class="card card-custom card-border">
                    <div class="card-body p-3">
                        <!-- Tree edit BUTTON -->
                        <div style="display: flex; position: relative; height: 38px;">
                            <button id="contacts_selection_edit" class="btn btn-light-primary"
                                onclick="_formScore(false, 2)" data-toggle="tooltip" title=""
                                data-original-title="Modifier les scores de la sélection" style="display: none;">
                                <i class="flaticon2-add-1"></i> Modifier les scores
                            </button>
                            <button id="presence_button" class="btn btn-success" onclick="pointPresence()" data-toggle="tooltip" title="" style="position: absolute; right: 0;">
                                <i class="far fa-check-square"></i>Présence
                            </button>
                        </div>
                        <!-- END edit BUTTON -->
                        <!--begin: jstree-->
                        <div id="structure_temporelle_tree" class="tree-cert" style="overflow-x: auto;"></div>
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
                                <button class="btn btn-sm btn-light-primary" data-toggle="tooltip" data-theme="dark"
                                    title="Affecter" data-original-title="Enregistrer"><i
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
        <!--end::Card-->
        <style>
            .jstree-anchor>.jstree-checkbox-disabled {
                display: none;
            }

            .jstree-default .jstree-anchor {
                height: 100% !important;
            }
        </style>
        <script>
            $('[data-toggle="tooltip"]').tooltip();
            $('#sessionsSelectFilter,#groupesSelectFilter').select2();

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
                                resfreshJSTreeTemporelle();
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
                                resfreshJSTreeTemporelle();
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
            var dtUrlSelectForMembers = '/api/sdt/select/sessions/members/' + af_id + '/' + enrollment_type;
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
                var dtUrlSelectForMembers = '/api/sdt/select/sessions/members/' + af_id + '/' + enrollment_type;
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
                    var schedules_ids = $("#structure_temporelle_tree").jstree("get_selected");
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
                            resfreshJSTreeTemporelle();
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

            function resfreshJSTreeSchedulecontacts() {
                resfreshJSTreeTemporelle();
            }
        </script>

    </div>
@elseif($block_id == 3)
    <x-filter-form type="CertGroups" :datafilter='$datafilter' />
    <div class="float-right">
        <!-- Tree ADD BUTTON -->
        <button id="contacts_selection_edit" class="btn btn-light-primary" onclick="_formScore(false, 3)"
            data-toggle="tooltip" title="" data-original-title="Modifier les scores de la sélection"
            style="display: none;">
            <i class="flaticon2-add-1"></i> Modifier les scores
        </button>

        <!-- END ADD BUTTON -->
    </div>
    <!-- Tree MODULES -->
    <div id="structure_temporelle_tree" class="tree-cert"></div>
@endif

<script>
    var af_id = $('#VIEW_INPUT_AF_ID_HELPER').val();
    var block_id = '{{ $block_id }}';
    var tree_url = block_id == 3 ?
        '/get/tree/timeMember/structure/' + af_id :
        '/get/tree/timeSession/structure/' + af_id + '/' + block_id;
    var jstree_plugins = ["state", "themes", "json_data"];

    if (block_id == 2) {
        jstree_plugins.push("checkbox");
    } else if (block_id == 3) {
        jstree_plugins.push("checkbox");
        $('#CertificationSelectGroupFilter').select2();
        var _loadDatasGroupsForSelectOptions = function(select_id, selected_value = 0, autorize_af = 1) {
            $.ajax({
                url: '/api/select/options/groups/' + autorize_af,
                dataType: 'json',
                success: function(response) {
                    var array = response;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i]
                                .name + "</option>");
                        }
                    }
                },
                error: function(x, e) {}
            }).done(function() {
                if (selected_value != 0 && selected_value != '') {
                    $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected',
                        'selected');
                }
            });
        }
        _loadDatasGroupsForSelectOptions('CertificationSelectGroupFilter', 0, af_id);
    }

    $('#structure_temporelle_tree').jstree({
        "core": {
            "multiple": block_id != 1,
            "themes": {
                "responsive": true
            },
            //"check_callback" : false,
            'data': {
                'url': function(node) {
                    return tree_url;
                },
                'data': function(node) {
                    if (block_id == 3) {
                        return {
                            'parent': node.id,
                            'filter_group_id': $('[name=filter_group_id]').val(),
                        };
                    }
                    return {
                        'parent': node.id,
                        'filter_periode': $('select[name=filter_periode]').val(),
                        'filter_start': $('input[name=filter_start]').val(),
                        'filter_end': $('input[name=filter_end]').val(),
                    };
                }
            },
        },
        "checkbox": {
            "keep_selected_style": block_id != 1,
            "three_state": block_id != 1, // to avoid that fact that checking a node also check others
            "whole_node": block_id != 1, // to avoid checking the box just clicking the node
            "tie_selection": true, // for checking without selecting and selecting without checking
        },
        "plugins": jstree_plugins
    });
    $('[name=filter_start], [name=filter_end]').datepicker({
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

    $('form#formFilterCert, form#formFilterCertGroups').submit(function(e) {
        e.preventDefault();
        resfreshJSTreeTemporelle();
    });

    $('#structure_temporelle_tree').on('changed.jstree', function(obj, selected) {
        var found = false; /* a selection is found */
        $.each(selected.selected, function(index, elm) {
            const id = elm.split('SCONTACT')[1];
            if (typeof id !== 'undefined') {
                found = true;
                return false;
            }
        });

        if (found) {
            $('#contacts_selection_edit').slideDown();
        } else {
            $('#contacts_selection_edit').slideUp();
        }
    });

    function resfreshJSTreeTemporelle() {
        $('#structure_temporelle_tree').jstree(true).settings.core.data.url = tree_url;
        $('#structure_temporelle_tree').jstree(true).refresh();
    }

    function _reset() {
        $('select#CertificationSelectGroupFilter').val(0).trigger('change');;
        setTimeout(() => resfreshJSTreeTemporelle(), 500);
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

    function _formCertSession() {
        var modal_id = 'modal_form_cert_sessions';
        var modal_content_id = 'modal_form_cert_sessions_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/certSession/' + af_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    }

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

    function _memberCommittee(member_id, id) {
        var modal_id = 'modal_form_committee';
        var modal_content_id = 'modal_form_committee_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/committee/' + member_id + '/' + id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    }

    function _memberTranscript(member_id, ts_id) {
        window.open('/pdf/transcript/' + af_id + '/' + member_id + '/' + ts_id, '_blank')
    }

    function _formScore(schedulecontact_id = false, block_id = 3) {
        var modal_id = 'modal_form_score';
        var modal_content_id = 'modal_form_score_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        const schedulecontact_ids = schedulecontact_id ? ['SCONTACT' + schedulecontact_id] : $(
            "#structure_temporelle_tree").jstree("get_selected");;
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/form/scores/' + block_id,
            type: 'GET',
            data: {
                schedules_ids: schedulecontact_ids.join(',')
            },
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
    }

    function pointPresence() {
        var elements = document.querySelectorAll('a.jstree-anchor.jstree-clicked');
        var numbers = [];

        elements.forEach(function(element) {
            var id = element.id;
            var match = id.match(/SCONTACT(\d+)/);
            if (match) {
                var number = match[1];
                numbers.push(number);
            }
        });
        _showLoader('presence_button');
        $.ajax({
            url: '/form/pointage/bulk',
            type: 'POST',
            data: {
                schedule_ids: numbers
            },
            
            success: function(result, status) {
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            complete: function(result, status) {
                _load_certifications_tab(2);
            }
        });
    }
</script>
