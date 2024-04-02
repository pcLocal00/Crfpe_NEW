$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formAdresse").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        $.ajax({
            type: 'POST',
            url: '/form/adresse',
            data: $(form).serialize(),
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_adresse').modal('hide');
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
                _reload_dt_adresses();
            }
        });
        return false;
    }
});

var _constructRadioCity = function(city) {
    var label = '<label class="radio"><input type="radio" name="RSAPI_CITY"' + '" value="' + city +
        '" /><span></span>' + city + '</label>';
    return label;
}
var _choice_city_api = function() {
    var city = $("input[name='RSAPI_CITY']:checked").val();
    if (!city) {
        _showResponseMessage('error', 'Veuillez sélectionner une ville parmi la liste !.');
        return false;
    }
    $("input[name='a_city']").val(city);
}
var _call_api_to_search_cities = function() {
    var codePostal = $('#ZIPCODE').val();
    if (codePostal > 0) {
        _showLoader('BTN_SEARCH_CITIES');
        $.ajax({
            url: "/api/geo/cities/" + codePostal,
            type: "GET",
            dataType: "JSON",
            success: function(result, status) {
                if (result.length > 0) {
                    var blockHtml = '<div class="radio-list" id="RADIO_LIST_CITIES_API">';
                    $.each(result, function(key, value) {
                        var html = _constructRadioCity(value.city);
                        blockHtml = blockHtml.concat(html);
                    });
                    blockHtml = blockHtml.concat('</div>');
                    Swal.fire({
                        title: 'Veuillez sélectionner une ville :',
                        icon: 'success',
                        html: blockHtml,
                        showCloseButton: true,
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: '<i class="fa fa-check"></i> Choisir',
                        cancelButtonText: '<i class="fa fa-times"></i>',
                    }).then(function(result) {
                        if (result.value) {
                            _choice_city_api();
                        }
                    });
                } else {
                    _showResponseMessage('error', 'Aucun résultat ! ');
                }
                $('#BTN_SEARCH_CITIES').html('<i class="flaticon2-search"></i>');
            },
            error: function(result, status, error) {
                $('#BTN_SEARCH_CITIES').html('<i class="flaticon2-search"></i>');
            },
            complete: function(result, status) {
                $('#BTN_SEARCH_CITIES').html('<i class="flaticon2-search"></i>');
            }
        });

    } else {
        _showResponseMessage('error', 'Veuillez renseigner le code postal pour pouvoir chercher la ville');
    }

};