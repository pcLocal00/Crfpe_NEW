@php
$modal_title='Envoyer la '.$agreement_type.' par e-mail';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_invoice_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->
<div class="modal-body" id="modal_form_invoice_body">
    <div data-scroll="true" data-height="600">
        <form id="formSendEmail" class="form">
            @csrf
            <input type="hidden" id="INPUT_HIDDEN_AGREEMENT_ID" name="id" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" id="entitie_id" name="entitie_id" value="{{ ($row)?$row->entitie_id:0 }}" />
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>À :<span id="LOADER_CONTACTS"></span> <span class="text-danger">*</span> </label>
                        <input type="hidden" id="selected_contact_id" name="contact_id"
                            value="{{ ($row)?$row->contact_id:0 }}" />
                        <select id="selectContacts" name="contact_id" class="form-control form-control-sm" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="reference">Cc : </label>
                        <input class="form-control form-control-sm" type="text" name="cc"
                        value="{{$user}}" id="input_cc" />
                        <span class="form-text text-muted">Veuillez saisir les emails séparer par des tabulations</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="reference">Bcc : </label>
                        <input class="form-control form-control-sm" type="text" name="bcc"
                        value="" id="input_bcc" />
                        <span class="form-text text-muted">Veuillez saisir les emails séparer par des tabulations</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="reference">Object :<span class="text-danger">*</span> </label>
                        <input class="form-control form-control-sm" type="text" name="subject"
                            value="{{$subject}} {{ ($row)?$row->af->title:'' }}" id="" required/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="note">Contenu</label>
                        <textarea class="form-control form-control-sm" id="content_email" name="content" rows="5">{{$content}}</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formSendEmail').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i> Envoyer <span id="BTN_SEND_EMAIL"></span></button>
</div>
<!-- Form param : end -->
<script>
ClassicEditor.create(document.querySelector("#content_email")).then(editor => {}).catch(error => {});
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});

//input cc
var input_cc = document.getElementById('input_cc'),
tagifyCc = new Tagify(input_cc);
//input bcc
var input_bcc = document.getElementById('input_bcc'),
tagifyBcc = new Tagify(input_bcc);

var entitie_id = $('#entitie_id').val();
var selected_contact_id = $('#selected_contact_id').val();
_loadContactsForSelectOptions('selectContacts', entitie_id, selected_contact_id);

function _loadContactsForSelectOptions(select_id, entity_id, selected_contact_id) {
    _showLoader('LOADER_CONTACTS');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/mail/contacts/' + entity_id,
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
        if (selected_contact_id != 0 && selected_contact_id != '') {
            $('#' + select_id + ' option[value="' + selected_contact_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_CONTACTS');
    });
}

$("#formSendEmail").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SEND_EMAIL');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/mail/agreement',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SEND_EMAIL');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SEND_EMAIL');
                _showResponseMessage('error', 'Ooops...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SEND_EMAIL');
            }
        });
        return false;
    }
});

</script>