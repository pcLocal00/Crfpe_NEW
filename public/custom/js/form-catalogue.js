$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
ClassicEditor.create(document.querySelector("#description"))
    .then(editor => {
    })
    .catch(error => {
    });
$("#formCategorie").validate({
    rules: {},
    messages: {},
    submitHandler: function (form) {
        _showLoader('BTN_SAVE_CATEGORIE');
        datas = $("#parent_categorie_tree").jstree("get_selected");
        if (datas[0] > 0) {
            $("#categorie_id").val(datas[0]);
        }
        $.ajax({
            type: 'POST',
            url: '/form/categorie',
            data: $(form).serialize(),
            dataType: 'JSON',
            success: function (result) {
                _hideLoader('BTN_SAVE_CATEGORIE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_catalogue').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function (error) {
                _hideLoader('BTN_SAVE_CATEGORIE');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function (resultat, statut) {
                _hideLoader('BTN_SAVE_CATEGORIE');
                resfreshJSTreeCatalogues();
            }
        });
        return false;
    }
});


$('#parent_categorie_tree').jstree({
    "core": {
        "multiple": false,
        "themes": {
            "responsive": true
        },
        'data': {
            'url': function (node) {
                return '/api/catalogues/' + $("#INPUT_ID_CATEGORIE").val() + '/0';
            },
            'data': function (node) {
                return {
                    'parent': node.id
                };
            }
        },
    },
    "checkbox": {
        "three_state": false,
    },
    "plugins": ["state", "checkbox"]
});
//A la fin du chargement
$('#parent_categorie_tree').bind("ready.jstree", function () {
    initializeSelections();
}).jstree();

function initializeSelections() {
    var instance = $('#parent_categorie_tree').jstree(true);
    instance.deselect_all();
    instance.select_node($("#categorie_id").val());
}

$('[data-scroll="true"]').each(function () {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
var _generateOrderShowCategorie = function () {
    _showLoader('BTN_GERERATE_ORDER_CATEGORIE');
    var categorie_id = $('#INPUT_ID_CATEGORIE').val();
    datas = $("#parent_categorie_tree").jstree("get_selected");
    var parent_categorie_id = (datas[0] > 0) ? datas[0] : 0;
    $.ajax({
        url: "/api/order/categorie/" + parent_categorie_id,
        type: "GET",
        dataType: "JSON",
        success: function (result, status) {
            $("#ID_ORDER_CATEGORIE").val(result.order_show);
            $('#BTN_GERERATE_ORDER_CATEGORIE').html('<i class="flaticon2-reload"></i>');
        },
        error: function (result, status, error) {
            $('#BTN_GERERATE_ORDER_CATEGORIE').html('<i class="flaticon2-reload"></i>');
        },
        complete: function (result, status) {
            $('#BTN_GERERATE_ORDER_CATEGORIE').html('<i class="flaticon2-reload"></i>');
        }
    });

}
var _generateCodeCategorie = function () {
    var categorie_name = $('#ID_NAME_CATEGORIE').val();
    if (categorie_name) {
        if (categorie_name.length < 3) {
            _showResponseMessage('error',
                'Veuillez saisir au moins 3 caractères dans le nom du grain pour pouvoir générer un code.');
            return false;
        }
        _showLoader('BTN_GERERATE_CODE_CATEGORIE');
        var categorie_id = $('#INPUT_ID_CATEGORIE').val();
        datas = $("#parent_categorie_tree").jstree("get_selected");
        var parent_categorie_id = (datas[0] > 0) ? datas[0] : 0;
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: "/api/code/categorie",
            type: "POST",
            data: {
                _token: CSRF_TOKEN,
                categorie_id: categorie_id,
                parent_categorie_id: parent_categorie_id,
                categorie_name: categorie_name
            },
            dataType: "JSON",
            success: function (result, status) {
                $("#ID_CODE_CATEGORIE").val(result.code);
                $('#BTN_GERERATE_CODE_CATEGORIE').html('<i class="flaticon2-reload"></i>');
            },
            error: function (result, status, error) {
                $('#BTN_GERERATE_CODE_CATEGORIE').html('<i class="flaticon2-reload"></i>');
            },
            complete: function (result, status) {
                $('#BTN_GERERATE_CODE_CATEGORIE').html('<i class="flaticon2-reload"></i>');
            }
        });

    } else {
        _showResponseMessage('error', 'Veuillez renseigner le nom du grain pour pouvoir générer un code');
    }

};

$('#checkbox_broadcast').change(function () {
    _manageRequiredAttr('span_required_site_name', 'input_site_name', $(this).is(":checked"));
});

var _manageRequiredAttr = function (idSpanElement, idInputElement, isRequired) {
    if (isRequired) {
        $('#' + idSpanElement).removeAttr('class');
        $('#' + idSpanElement).addClass('text-danger');
        $('#' + idSpanElement).html('*');
        //add required
        $("#" + idInputElement).prop('required', true);
    } else {
        $('#' + idSpanElement).removeAttr('class');
        $('#' + idSpanElement).html('');
        //remove required
        $('#' + idInputElement).removeAttr('required');
    }
}
