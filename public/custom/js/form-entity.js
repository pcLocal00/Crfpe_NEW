var constructFormByEntityType = function(row_id, entityType, block_id) {
    var spinner =
        '<div class="form-group row"><div class="col-lg-12"><div class="spinner spinner-primary spinner-lg"></div></div></div>';
    $('#' + block_id).html(spinner);
    var url = '/form/construct/' + row_id + '/' + entityType;
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + block_id).html(html);
        },
        error: function(result, status, error) {

        },
        complete: function(result, status) {

        }
    });
}
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formEntitie").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_ENTITIE');
        $.ajax({
            type: 'POST',
            url: '/form/entitie',
            data: $(form).serialize(),
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_ENTITIE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_entitie').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_ENTITIE');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_ENTITIE');
                if ( $.fn.DataTable.isDataTable( '#dt_entities' ) ) {
                    _reload_dt_entities();
                }else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
$('input[type=radio][name=entity_type]').change(function() {
    _loadFormByEntityType();
});
var _loadFormByEntityType = function() {
    var entity_type = $('input[name=entity_type]:checked').val()
    var entity_id = $('#INPUT_ENTITY_ID_HELPER').val();
    constructFormByEntityType(entity_id, entity_type, 'BLOCK_FORM');
}
_loadFormByEntityType();