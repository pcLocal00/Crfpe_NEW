@php
$modal_title='Créer un avoir ('.$invoice->number.')';
$dtNow = Carbon\Carbon::now();
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_refund_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>

<div class="modal-body" id="modal_form_refund_body">
    <div data-scroll="true" data-height="400">
        <form id="formRefund" class="form">
            @csrf
            <input type="hidden" name="id" value="0" />
            <input type="hidden" id="INPUT_HIDDEN_INVOICE_ID" name="invoice_id" value="{{ $invoice->id }}" />

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Date : <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $dtNow = Carbon\Carbon::now();
                            $refund_date =$dtNow->format('d/m/Y');
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="refund_date"
                                id="refund_date_datepicker" placeholder="Sélectionner une date" autocomplete="off"
                                value="{{ $refund_date }}" required />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="reason">Raison</label>
                        <textarea class="form-control form-control-sm" id="reason" name="reason"
                            rows="5"></textarea>
                    </div>
                </div>
            </div>


        </form>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formRefund').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_REFUND"></span></button>
</div>
<script>
$(document).ready(function() {
    $('.select2').select2();
});
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$('#refund_date_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
$("#formRefund").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        $("#BTN_SAVE_REFUND").addClass("spinner-border spinner-border-sm");
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: "POST",
            url: "/form/refund",
            data: formData,
            dataType: "JSON",
            success: function(result) {
                if (result.success) {
                    _showResponseMessage("success", result.msg);
                    $("#modal_form_refund").modal("hide");
                } else {
                    _showResponseMessage("error", result.msg);
                }
            },
            error: function(error) {
                _showResponseMessage(
                    "error",
                    "Oooops..."
                );
            },
            complete: function(resultat, statut) {
                $("#BTN_SAVE_REFUND").removeClass("spinner-border spinner-border-sm");
                _reload_dt_invoices();
            },
        });
        return false;
    },
});
_showLoader('LOADER_FUNDING_PAYMENTS');
var selected_id = $('#selected_funding_payment_id').val();
_loadFundingPaymentsForSelectOptions('fundingPaymentsSelect', selected_id);

$('#fundingPaymentsSelect').on('change', function() {
    refreshAmountInput();
});

function refreshAmountInput(){
    _showLoader('LOADER_AMOUNT');
    var funding_payment_id = $('#fundingPaymentsSelect').val();
    if (funding_payment_id > 0) {
        $.ajax({
            url: '/get/fundingpayment/amount/' + funding_payment_id ,
            dataType: 'json',
            success: function(response) {
                $('#amount').val(response.amount);
            },
            error: function(x, e) {}
        }).done(function() {
            _hideLoader('LOADER_AMOUNT');
        });
    }
}

function _loadFundingPaymentsForSelectOptions(select_id, selected_id) {
    var invoice_id = $('#INPUT_HIDDEN_INVOICE_ID').val();
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/fundingpayments/' + invoice_id,
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
        if (selected_id != 0 && selected_id != '') {
            $('#' + select_id + ' option[value="' + selected_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_FUNDING_PAYMENTS');
        refreshAmountInput();
    });
}
</script>