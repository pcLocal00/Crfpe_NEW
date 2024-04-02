@php
$name = '';
if ($member->contact) {
    $name = $member->contact->firstname . ' ' . $member->contact->lastname;
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_student_status_title"><i class="flaticon-edit"></i> Annulation de l'étudiant(e) :
        {{ $member->contact->firstname }} {{ $member->contact->lastname }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body">
    <h5></h5>
    <form id="stdCancellation" class="form">
        @csrf
        <div class="py-3">
            <div class="btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-sm btn-warning mr-1" for="no_action"
                    style="display: {{ empty($member->stop_reason) ? 'none' : 'unset' }}">
                    <input type="radio" class="btn-check" name="stop_reason" value="" id="no_action"
                        {{ empty($member->stop_reason) ? 'checked' : '' }} autocomplete="off">Annuler le choix
                </label>
                <label class="btn btn-sm btn-primary mr-1 {{ $member->stop_reason == 'suspend' ? 'active' : '' }}"
                    for="suspend">
                    <input type="radio" class="btn-check" name="stop_reason" value="suspend" id="suspend"
                        {{ $member->stop_reason == 'suspend' ? 'checked' : '' }} autocomplete="off">
                    <i class="fa fa-pause"></i> Suspendre
                </label>
                <label class="btn btn-sm btn-primary mr-1 {{ $member->stop_reason == 'stop' ? 'active' : '' }}"
                    for="stop">
                    <input type="radio" class="btn-check" name="stop_reason" value="stop" id="stop"
                        {{ $member->stop_reason == 'stop' ? 'checked' : '' }} autocomplete="off">
                    <i class="fa fa-ban"></i> Exclure
                </label>
                <label class="btn btn-sm btn-primary mr-1 {{ $member->stop_reason == 'cancel' ? 'active' : '' }}"
                    for="cancel">
                    <input type="radio" class="btn-check" name="stop_reason" value="cancel" id="cancel"
                        {{ $member->stop_reason == 'cancel' ? 'checked' : '' }} autocomplete="off">
                    <i class="fa fa-sign-out-alt"></i> Abandonner
                </label>
            </div>

            <div class="form-group row pt-3">
                <div class="col-md-6" style="display: {{ empty($member->stop_reason) ? 'none' : 'unset' }}">
                    <label for="title">Date de prise d'effet:</label>
                    <input class="form-control date_datepicker" type="text" name="effective_date"
                        value="{{ $member->effective_date ? $member->effective_date->format('d/m/Y') : '' }}"
                        id="effective_date" readonly>
                </div>
                <div class="col-md-6" style="display: {{ $member->stop_reason != 'suspend' ? 'none' : 'unset' }}">
                    <label for="title">Date de reprise prévisionnelle:</label>
                    <input class="form-control date_datepicker" type="text" name="resumption_date"
                        value="{{ $member->resumption_date ? $member->resumption_date->format('d/m/Y') : '' }}"
                        id="resumption_date" readonly>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button class="pull-right btn btn-default" data-dismiss="modal">Annuler</button>
    <button class="pull-right btn btn-primary" onclick="_cancelStudent({{$member->id}})">Enregistrer</button>
</div>

<script>
    $('[name="stop_reason"]').change(function(e) {
        const id_value = $(this).attr('id');
        if (id_value == 'no_action') {
            $(this).closest('label').hide();
            $('input#effective_date, input#resumption_date').closest('[class*=col-md-]').hide();
        } else {
            $('input#no_action').closest('label').show();
            $('input#effective_date').closest('[class*=col-md-]').show();
            if (id_value == 'suspend') {
                $('input#resumption_date').closest('[class*=col-md-]').show();
            } else {
                $('input#resumption_date').closest('[class*=col-md-]').hide();
            }
        }
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

    function _cancelStudent(member_id) {
        var successMsg = "Étudiant annulé avec succés.";
        var errorMsg = "Erreur lors d'annulation.";
        $.ajax({
            url: "/form/student/cancel/" + member_id,
            type: "POST",
            data: $('#stdCancellation').serialize(),
            dataType: "JSON",
            success: function(result, status) {
                if (result.success) {
                    _refreshMembers({{ $member->enrollment_id }});
                    $('#modal_student_status').modal('hide');
                    _showResponseMessage("success", successMsg);
                } else {
                    _showResponseMessage("error", errorMsg);
                }
            },
            error: function(result, status, error) {
                _showResponseMessage("error", errorMsg);
            },
            complete: function(result, status) {

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
</script>
