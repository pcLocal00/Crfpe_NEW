$( ".sp-ckeditor" ).each(function( index,item ) {
    var id = $(item).data('index');
    ClassicEditor.create(document.querySelector("#sp-ckeditor-"+id))
    .then(editor => {})
    .catch(error => {});
});

//
ClassicEditor.create(document.querySelector("#sheet_description"))
    .then(editor => {})
    .catch(error => {});

$("#form_sheet").validate({
    rules: {
        code: "required",
        description: "required",
        version: {
            required: true,
            minlength: 0
        },
    },
    messages: {
        code: "Please enter code",
        description: "Please enter description",
    },
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_SHEET');
        $.ajax({
            type: 'POST',
            url: '/form/sheet',
            data: $(form).serialize(),
            dataType: 'JSON',
            success: function(result) {
                if (result.success) {
                    _hideLoader('BTN_SAVE_SHEET');
                    _showResponseMessage('success', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_SHEET');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_SHEET');
                _update_pf_stats();
                if ( $.fn.DataTable.isDataTable( '#kt_sheets_datatable' ) ) {
                    //$('#NAV4').click();
                    _reload_dt_sheets();
                }else {
                    $('#NAV2').click();
                }
                $('#modal_sheet_formation').modal('hide');
            }
        });
        return false;
    }
});


$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});