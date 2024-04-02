
@php
    $modal_title=($row)?'Edition statut':'Créer un statut';
    $createdAt = $updatedAt = $deletedAt = '';
    if($row){
    $createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
    $updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
    }
    $dtNow = Carbon\Carbon::now();
@endphp
<div class="modal-header" style="background-color: #f3edfe;">
    <h5 class="modal-title" id="modal_form_student_status_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->

<div class="modal-body" id="modal_form_student_status_body" style="background-color: #f3edfe;">
    <div data-scroll="true" data-height="210">

        @if($row)
        <!-- Infos date : begin -->
        <div class="form-group row">
            <div class="col-lg-12">
                @if($createdAt)<span class="label label-inline label-outline-info mr-2">Crée le :
                    {{ $createdAt }}</span>@endif
                @if($updatedAt)<span class="label label-inline label-outline-info mr-2">Modifié le :
                    {{ $updatedAt }}</span>@endif
            </div>
            @if($deletedAt)
            <div class="col-lg-12 mt-5">
                <div class="alert alert-custom alert-outline-info fade show mb-0" role="alert">
                    <div class="alert-icon"><i class="flaticon-warning"></i></div>
                    <div class="alert-text">Archivé le : {{ $deletedAt }}</div>
                </div>
            </div>
            @endif
        </div>
        <!-- Infos date : end -->
        <div class="separator separator-dashed my-5"></div>
        @endif
    <form id="formStudentStatus" class="form">
            @csrf
            <input type="hidden" name="id" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="member_id" value="{{ $member_id }}" />
    {{-- Begin student status  --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Statut de l'étudiant </label>
                <select name="student_status" class="form-control select2" id="student_status" required>
                    <option value="">Aucun</option>
                    <option value="student" {{($row && $row->student_status == 'student')? 'selected' : '' }}>Etudiant</option>
                    <option value="apprentices" {{($row && $row->student_status == 'apprentices')? 'selected' : '' }}>Apprentis</option>
                    <option value="employees" {{($row && $row->student_status == 'employees')? 'selected' : '' }}>Salariés</option>
                    <option value="jobseeker" {{($row && $row->student_status == 'jobseeker')? 'selected' : '' }}>Demandeur d’emploi</option>
                </select>
            </div>
        </div>
    </div>
    {{-- End student status --}}

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date de début <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $start_date =$dtNow->format('d/m/Y');
                            if($row && $row->start_date!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->start_date);
                            $start_date = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control form-control-sm datepicker" name="start_date"
                                id="" placeholder="Sélectionner une date" value="{{ $start_date }}"
                                autocomplete="off" required/>
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date de fin <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $newDateTime = Carbon\Carbon::now()->addMonth();
                            $end_date =$newDateTime->format('d/m/Y');
                            if($row && $row->end_date!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->end_date);
                            $end_date = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control form-control-sm datepicker" name="end_date"
                                id="" placeholder="Sélectionner une date" value="{{ $end_date }}"
                                autocomplete="off" required/>
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </form>

    </div>
</div>
<div class="modal-footer" style="background-color: #f3edfe;">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i> Annuler</button>
    <button type="button" onclick="$('#formStudentStatus').submit();" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span id="BTN_SAVE_STUDENT_STATUS"></span></button>
</div>
<script>
$(document).ready(function() {
    $('.select2').select2();
});
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$('.datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
$("#formStudentStatus").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_STUDENT_STATUS');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/student/status',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_STUDENT_STATUS');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_STUDENT_STATUS');
                _showResponseMessage('error', 'Oups...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_STUDENT_STATUS');
                if ($.fn.DataTable.isDataTable('#dt_studentstatus')) {
                    _reload_dt_studentstatus();
                }
                $('#modal_form_student_status').modal('hide');
            }
        });
        return false;
    }
});
</script>