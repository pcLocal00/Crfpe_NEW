@php

$entityName = $title = '';
if($enrollment->entity->entity_type=='S'){
    $entityName = $enrollment->entity->name.' ('.$enrollment->entity->ref.')';
    $title = 'Liste des stagiaires inscrits :';
    $methodeFormName = '_formEnrollment';
    if($enrollment->enrollment_type=='F'){
        $title = 'Liste des formateurs :';
        $methodeFormName = '_formEnrollmentIntervenants';
    }
}elseif($enrollment->entity->entity_type=='P'){
    $title = '';
    $methodeFormName = '_formEnrollment';
    if($enrollment->enrollment_type=='F'){
        $title = '';
        $methodeFormName = '_formEnrollmentIntervenants';
    }
}
@endphp
<p>{{ $title }} 
<button class="btn btn-sm btn-clean btn-icon" onclick="{{ $methodeFormName }}({{ $enrollment->id }})" title="Edition"><i class="flaticon-edit"></i></button>
<button class="btn btn-sm btn-clean btn-icon" onclick="_deleteEnrollmentMember({{ $enrollment->id }},'ENROLLMENT')" title="Suppression"><i class="flaticon-delete"></i></button>
</p>
<table class="table table-bordered table-sm" style="width:100%;" id="dt_registrants_{{ $enrollment->id }}">
    <thead class="thead-light">
        <tr>
            <th>Prénom Nom</th>
            <th>Heures planifiées / programmée</th>
            @if($enrollment->enrollment_type=='F')<th>Type d'intervention</th>@endif
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<!--begin::Modal Statut de l'etudiant-->
<div class="modal fade" id="modal_student_status" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" id="modal_student_status_content">
            
            

        </div>
    </div>
</div>
<!--end::Modal Statut de l'etudiant-->

<x-modal id="modal_form_student_status" content="modal_form_student_status_content" />

<script>
// begin first table
$('#dt_registrants_{{ $enrollment->id }}').DataTable({
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
        url: '/api/sdt/select/registrants/' + {{ $enrollment->id }},
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

function _viewStudentStatus(member_id){
    var modal_id = 'modal_student_status';
    var modal_content_id = 'modal_student_status_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/view/student/status/'+member_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}

function _setStudentSchedule(member_id){
    var modal_id = 'modal_student_status';
    var modal_content_id = 'modal_student_status_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/view/student/schedule/'+member_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}

function _setStudentCancellation(member_id){
    var modal_id = 'modal_student_status';
    var modal_content_id = 'modal_student_status_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/view/student/cancellation/'+member_id,
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