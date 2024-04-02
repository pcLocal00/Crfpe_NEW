@php
$modal_title=($payment)?'Edition paiement':'Ajouter un paiement';

$createdAt = $updatedAt = $deletedAt = '';
if($payment){
$createdAt = ($payment->created_at)?$payment->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($payment->updated_at)?$payment->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($payment->deleted_at)?$payment->deleted_at->format('d/m/Y H:i'):'';
}
$dtNow = Carbon\Carbon::now();
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_payment_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>

<div class="modal-body" id="modal_form_payment_body">
    <div data-scroll="true" data-height="400">
        @if($payment)
        <!-- Infos date : begin -->
        <div class="form-group row">
            <div class="col-lg-12">
                @if($createdAt)<span class="label label-inline label-outline-info mr-2">Crée le :
                    {{ $createdAt }}</span>@endif
                @if($updatedAt)<span class="label label-inline label-outline-info mr-2">Modifié le :
                    {{ $updatedAt }}</span>@endif
            </div>
            @if($deletedAt)
            <div class="col-lg-12 mt-5">
                <div class="alert alert-custom alert-outline-info fade show mb-0" role="alert">
                    <div class="alert-icon"><i class="flaticon-warning"></i></div>
                    <div class="alert-text">Archivé le : {{ $deletedAt }}</div>
                </div>
            </div>
            @endif
        </div>
        <!-- Infos date : end -->
        <div class="separator separator-dashed my-5"></div>
        @endif
        <form id="formPayment" class="form">
            @csrf
            <input type="hidden" name="id" value="{{ ($payment)?$payment->id:0 }}" />
            <input type="hidden" id="INPUT_HIDDEN_INVOICE_ID" name="invoice_id"
                value="{{ ($payment)?$payment->invoice_id:$invoice_id }}" />
            <input type="hidden" id="hidden_invoice_type" value="{{$invoice_type}}">
            @if($invoice_type=="cvts_ctrs")        
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Echéance <span id="LOADER_FUNDING_PAYMENTS"></span><span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_funding_payment_id" value="{{ ($payment)?$payment->funding_payment_id:0 }}">
                        <select id="fundingPaymentsSelect" name="funding_payment_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="amount">Montant : <span id="LOADER_AMOUNT"></span><span class="text-danger">*</span></label>
                        <input class="form-control form-control-sm" type="number" name="amount"
                            value="{{ ($payment)?$payment->amount:$amount }}" id="amount" required />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reference">Référence : </label>
                        <input class="form-control form-control-sm" type="text" name="reference"
                            value="{{ ($payment)?$payment->reference:'' }}" id="reference" />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date de paiement <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $dtNow = Carbon\Carbon::now();
                            $payment_date =$dtNow->format('d/m/Y');
                            if($payment && $payment->payment_date!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d',$payment->payment_date);
                            $payment_date = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="payment_date"
                                id="payment_date_datepicker" placeholder="Sélectionner une date" autocomplete="off"
                                value="{{ $payment_date }}" required />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Mode de paiement<span class="text-danger">*</span></label>
                        <select id="payment_method" name="payment_method" class="form-control form-control-sm" required>
                            <option value="Cash" {{ ($payment)?(($payment->payment_method=='Cash')?'selected':''):'' }}>
                                Comptant</option>
                            <option value="Cheque"
                                {{ ($payment)?(($payment->payment_method=='Cheque')?'selected':''):'' }}>Chèque</option>
                            <option value="Transfer"
                                {{ ($payment)?(($payment->payment_method=='Transfer')?'selected':''):'' }}>Virement
                            </option>
                            <option value="Sample"
                                {{ ($payment)?(($payment->payment_method=='Sample')?'selected':''):'' }}>Prélèvement
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea class="form-control form-control-sm" id="note" name="note"
                            rows="5">{{ ($payment)?$payment->note:'' }}</textarea>
                    </div>
                </div>
            </div>


        </form>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formPayment').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_PAYMENT"></span></button>
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
$('#payment_date_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
$("#formPayment").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        $("#BTN_SAVE_PAYMENT").addClass("spinner-border spinner-border-sm");
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: "POST",
            url: "/form/payment",
            data: formData,
            dataType: "JSON",
            success: function(result) {
                if (result.success) {
                    _showResponseMessage("success", result.msg);
                    $("#modal_form_payment").modal("hide");
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
                $("#BTN_SAVE_PAYMENT").removeClass("spinner-border spinner-border-sm");
                _reload_dt_invoices();
            },
        });
        return false;
    },
});

_loadFundingPaymentsForSelectOptions('fundingPaymentsSelect');

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

function _loadFundingPaymentsForSelectOptions(select_id) {
    var invoice_type=$("#hidden_invoice_type").val();
    if(invoice_type=="cvts_ctrs"){
        _showLoader('LOADER_FUNDING_PAYMENTS');
        var selected_id = $('#selected_funding_payment_id').val();
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
}
</script>