@php
$modal_title='Création des factures';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_invoice_from_agreement_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_form_invoice_from_agreement_body">
    <div data-scroll="true" data-height="600">
        <form id="formInvoiceFormAgreement" class="form">
            @csrf
            <input type="hidden" name="agreement_id" value="{{ $agreement_id }}" />

            <div class="row">
                <div class="col-md-12">
                    {!!$htmlFinance!!}
                </div>
            </div>

        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-info" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formInvoiceFormAgreement').submit();" class="btn btn-sm btn-success"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_I"></span></button>
</div>

<script>
$("#formInvoiceFormAgreement").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_I');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/invoice-from-agreement',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_I');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_invoice_from_agreement').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_I');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_I');
                //_loadFundings();
            }
        });
        return false;
    }
});
</script>