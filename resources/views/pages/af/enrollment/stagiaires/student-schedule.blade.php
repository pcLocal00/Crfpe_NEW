@php
$name = '';
if ($member->contact) {
    $name = $member->contact->firstname . ' ' . $member->contact->lastname;
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_student_status_title"><i class="flaticon-edit"></i> Planning de l'étudiant :
        {{ $name }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body">
    <h5>Status de l'étudiant</h5>
    <div data-scroll="true" id="modal_student_status_body">
        <button class="btn btn-sm btn-icon btn-light-primary mb-2" data-toggle="tooltip" title="Ajouter un status"
            onclick="_formStudentStatus(0)"><i class="flaticon2-add-1"></i></button>
        <input type="hidden" id="MEMBER_ID" value="{{ $member->id }}">
        {{-- begin::table --}}
        <table class="table table-bordered table-sm" id="dt_studentstatus">
            <thead class="thead-light">
                <tr>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        {{-- end::table --}}
    </div>
    <hr>
    <h5>Liste des étudiants</h5>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="start_date">Date de début</label>
            <div class="input-group">
                <input type="text" class="form-control" id="start_date" name="start_date"
                    aria-describedby="start_datePrepend">
                <div class="input-group-prepend">
                    <button class="input-group-text" id="start_datePrepend" onclick="refreshStartDate()"><i
                            class="fa fa-undo"></i></button>
                </div>
                <div class="invalid-tooltip" style="display: none">
                    Ce champs est obligatoire.
                </div>
            </div>
        </div>
    </div>

    <div data-scroll="true" id="modal_student_status_body">
        {{-- begin::table --}}
        <table class="table table-bordered table-sm" id="dt_students">
            <thead class="thead-light">
                <tr>
                    <th>Étudiant</th>
                    <th>Groupe</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        {{-- end::table --}}
    </div>
</div>

<script>
    var dtUrl = '/api/sdt/studentstatus/' + $('#MEMBER_ID').val();
    var table_status = $('#dt_studentstatus');
    var default_date;
    table_status.DataTable({
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
        "initComplete": function(settings, json) {
            const data = json.data ?? [];
            if (data.length > 0) {
                default_date = data[0][0].split(' - ')[0];
                $('#start_date').val(default_date);
                $('#start_date').datepicker("setDate", default_date);
            }
        },
        lengthMenu: [5, 10, 25, 50],
        pageLength: 25,
    });

    var std_table = $('#dt_students');
    std_table.DataTable({
        language: {
            url: "/custom/plugins/datatable/fr.json"
        },
        responsive: true,
        processing: true,
        paging: true,
        // ordering: false,
        "columnDefs": [{
            "targets": 2,
            "orderable": false
        }],
        ajax: {
            url: '/api/sdt/students',
            type: 'POST',
            data: {
                af_id: $('#VIEW_INPUT_AF_ID_HELPER').val(),
                pagination: {
                    perpage: 50,
                },
            },
        },
        lengthMenu: [5, 10, 20, 50],
        pageLength: 5,
    });

    $('#start_date').datepicker({
        language: 'fr',
        format: 'dd/mm/yyyy',
        todayHighlight: true,
        orientation: "bottom left",
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
    });

    function _reload_dt_studentstatus() {
        $('#dt_studentstatus').DataTable().ajax.reload();
    }

    function _formStudentStatus(student_status_id) {
        var member_id = $('#MEMBER_ID').val();
        if (member_id > 0) {
            var modal_id = 'modal_form_student_status';
            var modal_content_id = 'modal_form_student_status_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/student/status/' + member_id + '/' + student_status_id,
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

    function _copyStudentSchedule(member_id) {
        const date = $('#start_date').val();
        var successMsg = "Planning importé avec succés.";
        var errorMsg = "Erreur lors d'import.";
        Swal.fire({
            title: 'Avertissement',
            text: 'Voulez vous importer toutes les séances de l\'étudiant sélectionné, à partir de la date ' +
                date +
                ' ?',
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Oui"
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "/form/student/importschedule/" + member_id,
                    type: "POST",
                    data: {
                        new_member: {{ $member->id }},
                        start_date: date,
                    },
                    dataType: "JSON",
                    success: function(result, status) {
                        if (result.success) {
                            _refreshMembers({{ $member->enrollment_id }});
                            $('#modal_student_status').modal('hide');
                            _showResponseMessage("success", result.msg);
                        } else {
                            _showResponseMessage("error", result.msg);
                        }
                    },
                    error: function(result, status, error) {
                        _showResponseMessage("error", result.msg);
                    },
                    complete: function(result, status) {

                    }
                });
            }
        });
    }

    var _refreshMembers = function(enrollment_id) {
        $.ajax({
            url: '/get/members/' + enrollment_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#child_data_members_' + enrollment_id).html(html);
            }
        });
    }

    function refreshStartDate() {
        $('#start_date').val(default_date);
        $('#start_date').datepicker("setDate", default_date);
    }
</script>
