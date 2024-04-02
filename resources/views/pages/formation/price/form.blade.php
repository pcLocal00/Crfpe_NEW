<div class="modal-header">
    <h5 class="modal-title" id="modal_price_formation_title"><i class="flaticon-edit"></i> Ajouter des tarifs </h5>
    <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Ajouter un tarif"
        onclick="_formPrice(0)"><i class="flaticon2-add-1"></i></button>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->

<div class="modal-body" id="modal_price_formation_body">
    <div data-scroll="true" data-height="600">
        <!-- begin::form new price -->
        <div class="row" id="ROW_FORM_PRICE" style="display:none;">
            <div class="col-lg-12">
                <form id="formPrice" class="form">
                    @csrf
                    <input type="hidden" name="id" value="0" />
                    <div class="card card-custom card-border mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <span class="card-icon">
                                    <i class="flaticon2-add-1 text-primary"></i>
                                </span>
                                <h3 class="card-label">Ajouter un tarif</h3>
                            </div>
                            <div class="card-toolbar">
                                <button type="button" onclick="_showHideCardFormPrice('HIDE')"
                                    class="btn btn-sm btn-light-danger mr-2"><i class="fa fa-times"></i>
                                    Annuler</button>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i>
                                    Enregistrer <span id="BTN_SAVE_PRICE"></span></button>
                            </div>
                        </div>
                        <div class="card-body" id="BLOCK_FORM_PRICE">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- end::form new price -->
        <form id="formPfRelPrice" class="form">
            <!-- begin::form -->
            @csrf
            <input type="hidden" name="formation_id" value="{{ $pf_id }}" />
            <div class="row">
                <div class="col-lg-12">
                    <!--begin: Datatable-->
                    <table class="table table-bordered table-checkable table-sm" id="dt_prices_for_select">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Titre</th>
                                <th>Entité</th>
                                <th>Type</th>
                                <th>Tarif</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <!--end: Datatable-->
                </div>
            </div>
            <input style="display:none;" type="submit" id="BTN_SUBMIT_FORM" value="SAVE">
        </form>
        <!--end:: form-->
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="SUBMIT_FORM()" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
            id="BTN_SAVE"></span></button>
</div>

<!-- Form  : end -->
<!-- <script src="{{ asset('custom/js/form-pfrelprice.js?v=1') }}"></script> -->
<script>
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
function SUBMIT_FORM(){
    $("#BTN_SUBMIT_FORM").click(); 
}
$("#formPfRelPrice").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/price/rel/pf',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_price_formation').modal('hide');
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
                _reload_dt_pf_prices();
            }
        });
        return false;
    }
});
var formation_id = $('#VIEW_INPUT_PF_ID_HELPER').val();
var dtUrlSelectForPrices = '/api/sdt/select/prices/' + formation_id+'/0';
var dt_prices_for_select = $('#dt_prices_for_select');
// begin first table
dt_prices_for_select.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    searching: true,
    paging: true,
    ordering: false,
    ajax: {
        url: dtUrlSelectForPrices,
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
        },
    },
    info: true,
    lengthMenu: [5, 10, 25, 50],
    pageLength: 10,
    headerCallback: function(thead, data, start, end, display) {
        thead.getElementsByTagName('th')[0].innerHTML = `
                    <label class="checkbox checkbox-single">
                        <input type="checkbox" value="" class="group-checkable"/>
                        <span></span>
                    </label>`;
    },
});
dt_prices_for_select.on('change', '.group-checkable', function() {
    var set = $(this).closest('table').find('td:first-child .checkable');
    var checked = $(this).is(':checked');

    $(set).each(function() {
        if (checked) {
            $(this).prop('checked', true);
            $(this).closest('tr').addClass('active');
        } else {
            $(this).prop('checked', false);
            $(this).closest('tr').removeClass('active');
        }
    });
});
dt_prices_for_select.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_prices_for_select = function() {
    $('#dt_prices_for_select').DataTable().ajax.reload();
}

function _formPrice(row_id) {
    _showHideCardFormPrice('SHOW');
    var div_content_id = 'BLOCK_FORM_PRICE';
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#' + div_content_id).html(spinner);
    $.ajax({
        url: '/form/price/type/0/' + row_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + div_content_id).html(html);
        },
    });
}

function _showHideCardFormPrice(param) {
    if (param == 'SHOW') {
        $('#ROW_FORM_PRICE').show();
    } else {
        $('#ROW_FORM_PRICE').hide();
    }
}

$("#formPrice").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_PRICE');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/price',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_PRICE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    _showHideCardFormPrice('HIDE');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_PRICE');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_PRICE');
                _reload_dt_prices_for_select();
            }
        });
        return false;
    }
});
</script>