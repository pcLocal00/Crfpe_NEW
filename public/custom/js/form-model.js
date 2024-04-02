var _formModel = function (model_id) {
    var modal_id = 'modal_form_model';
    var modal_content_id = 'modal_form_model_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';

    $('#' + modal_id).modal('show');


    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/model/' + model_id,
        type: 'GET',
        dataType: 'html',
        success: function (html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function (result, status, error) {

        },
        complete: function (result, status) {

        }
    });
}


$("#formModel").validate({
    rules: {},
    messages: {},
    submitHandler: function (form) {
        _showLoader('BTN_SAVE_MODEL');
        $.ajax({
            type: 'POST',
            url: '/form/model',
            data: $(form).serialize(),
            dataType: 'JSON',
            success: function (result) {
                _hideLoader('BTN_SAVE_STRUCTURE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_model').modal('hide');
                    // location.reload();
                } else {
                    _showResponseMessage('error', result.msg);
                }

            },
            error: function (error) {
                _hideLoader('BTN_SAVE_MODEL');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function (resultat, statut) {
                _hideLoader('BTN_SAVE_MODEL');
                resfreshJSTreeCatalogues();
            }

        });
        _reload_dt_models();
        return false;
    }
});
