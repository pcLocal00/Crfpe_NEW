@php
$modal_title='Ajouter un contact';
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modal_form_contact_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form contact : begin -->
<form id="formContact" class="form">
    <div class="modal-body" id="modal_form_contact_body">
        <div data-scroll="true" data-height="550">

            <input type="hidden" id="enrollment_id" name="enrollment_id" value="{{$enrollment_id}}">
            <input type="hidden" id="member_id" name="member_id" value="{{$member_id}}">
            <input type="hidden" id="entitie_id" name="entitie_id" value="{{$entitie_id}}">

            <p class="text-primary">{{ ($entity)?($entity->name.' - '.$entity->ref.' - '.$entity->entity_type):'' }}</p>
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="genderSelects">Civilité</label>
                        <select class="form-control " name="gender" id="genderSelects">
                            <option value="M">M</option>
                            <option value="Mme">Mme
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="firstname" value="" required />
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lastname" value="" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" value="" required />
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
                id="BTN_SAVE_CONTACT"></span></button>
    </div>

</form>
<!-- Form contact : end -->

<script>
    $("#formContact").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_CONTACT');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/unknown/contact',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_CONTACT');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_contact').modal('hide');
                    
                    if(result.enrollment_id>0)
                        _getMembers(result.enrollment_id);

                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_CONTACT');
                _showResponseMessage('error', 'Oops...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_CONTACT');
            }
        });
        return false;
    }
});
</script>