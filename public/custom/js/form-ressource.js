$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});

$('#select_type').on('change', function() {
    _showInternalExternalRadio();
});
var _showInternalExternalRadio = function() {
    var type = $('#select_type').val();
    if(type=="RES_TYPE_LIEU"){
        $('#INTERNAL_EXTERNAL_BLOCK').removeClass('d-none');
    }else{
        $('#INTERNAL_EXTERNAL_BLOCK').addClass('d-none');
    }
}
_showInternalExternalRadio();

$("#formRessource").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/ressource',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_ressource').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE');
                _reload_dt_ressources();
            }
        });
        return false;
    }
});