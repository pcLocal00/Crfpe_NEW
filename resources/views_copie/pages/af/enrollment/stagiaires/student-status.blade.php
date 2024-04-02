@php
$name = '';
if ($member->contact) {
    $name = $member->contact->firstname . ' ' . $member->contact->lastname;
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_student_status_title"><i class="flaticon-edit"></i> Statuts de l'etudiant :
        {{ $name }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body">
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
</div>
{{-- <x-modal id="modal_form_student_status" content="modal_form_student_status_content" /> --}}
<script>
    var dtUrl = '/api/sdt/studentstatus/' + $('#MEMBER_ID').val();
    var std_status_table = $('#dt_studentstatus');
    std_status_table.DataTable({
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

    function _deleteStudentStatus(student_status_id) {
        Swal.fire({
            title: "Vous êtes au mesure de supprimer le status. Voulez vous continuer?",
            icon: 'warning',
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: '<i class="fa fa-check"></i> Valider',
            cancelButtonText: '<i class="fa fa-times"></i> Annuler',
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: '/form/student/status/' + student_status_id,
                    type: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            _showResponseMessage('success', response.msg);
                            _reload_dt_studentstatus();
                        } else {
                            _showResponseMessage('danger', response.msg);
                        }
                    },
                });
            }
        });
    }
</script>
