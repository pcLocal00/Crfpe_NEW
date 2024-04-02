$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formContact").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        $.ajax({
            type: 'POST',
            url: '/form/contact',
            data: $(form).serialize(),
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_contact').modal('hide');
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
                _reload_dt_contacts();
            }
        });
        return false;
    }
});
$('#dateofbirth_datepicker').datepicker({
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

//Block type d'intervention formateur
$("input[name='is_former']").change(function() {
    _showTypeFormerInterventionBlock();
});
var _showTypeFormerInterventionBlock = function() {
    var rs = $("input[name='is_former']").is(":checked");
    if (rs === true) {
        $('#BLOCK_TYPE_INTERVENSION_FORMER').show();
        $("select[name='c_type_former_intervention']").prop('required', true);
    } else {
        $('#BLOCK_TYPE_INTERVENSION_FORMER').hide();
        $("select[name='c_type_former_intervention']").removeAttr('required');
        $("select[name='c_type_former_intervention']").prop('selectedIndex', 0);
    }
}
//default
_showTypeFormerInterventionBlock();