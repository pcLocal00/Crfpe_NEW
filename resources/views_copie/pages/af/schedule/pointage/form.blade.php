<div class="modal-header">
    <h5 class="modal-title" id="modal_form_contact_title"><i class="flaticon-edit"></i> Edition pointage </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>

<!-- Form : begin -->
<form id="formPointage" class="form">
    <div class="modal-body" id="modal_form_pointage_body">
        <div data-scroll="true" data-height="550">
            <input type="hidden" id="hidden_contract_id" value="{{ $schedulecontact->contract_id }}">
            <input type="hidden" name="schedulecontact_id" value="{{ $schedulecontact->id }}" />
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="training_site_select">Pointage <span
                                class="text-danger">*</span></label>
                        <select name="pointing" id="pointing" class="form-control " required>
                            <option value="not_pointed" {{($schedulecontact->pointing == 'not_pointed')?'selected':''}}>Non pointé</option>
                            <option value="absent" {{($schedulecontact->pointing == 'absent')?'selected':''}}>Absent</option>
                            <option value="present" {{($schedulecontact->pointing == 'present')?'selected':''}}>Présent</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" id="is_abs_justified_block" style="display: none;">
                <div class="col-lg-12">
                    <div class="form-group">
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="is_abs_justified" {{$schedulecontact->is_abs_justified ? 'checked' : ''}}>
                                <span></span>Absence justifiée</label>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler
        </button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE_POINTAGE"></span></button>
    </div>


</form>
<!-- Form group : end -->


<script>
display_abs_justified();
$('select#pointing').change(() => display_abs_justified());

 $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#formPointage").validate({
        rules: {},
        messages: {},
        submitHandler: function (form) {
            _showLoader('BTN_SAVE_POINTAGE');
            var formData = $(form).serializeArray();
            $.ajax({
                type: 'POST',
                url: '/form/pointage',
                data: formData,
                dataType: 'JSON',
                success: function (result) {
                    _hideLoader('BTN_SAVE_POINTAGE');
                    if (result.success) {
                        _showResponseMessage('success', result.msg);
                        $('#modal_form_pointage').modal('hide');
                        if($('#modal_schedule_details').length){
                            $('#modal_schedule_details').modal('hide');
                        }
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                },
                error: function (error) {
                    _hideLoader('BTN_SAVE_POINTAGE');
                    _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
                },
                complete: function (resultat, statut) {
                    _hideLoader('BTN_SAVE_POINTAGE');
                    if ($.fn.DataTable.isDataTable('#dt_contracts')) {
                        var contract_id=$('#hidden_contract_id').val();
                        $('#BTN_SHOW_FORMER_SCHEDULE_DETAILS_'+contract_id).click();
                        //$('#modal_schedule_details').modal('hide');
                    } else {
                        resfreshJSTreeSchedulecontacts();
                    }
                }
            });
            return false;
        }
    }); 

    function display_abs_justified()
    {
        const display = $('select#pointing').val() === 'absent' ? 'block' : 'none';
        $('#is_abs_justified_block').css('display', display);
    }
</script>