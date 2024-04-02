@php
$modal_title=($row)?'Edition echéance':'Ajouter une echéance';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_fundingpayment_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_form_fundingpayment_body">
    <div data-scroll="true" data-height="600">
        <form id="formFundingPayment" class="form">
            @csrf
            <input type="hidden" name="id" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="funding_id" value="{{ ($row)?$row->funding_id:$funding_id }}" />

            <div class="row">
                <div class="col-md-12">
                    <p>Montant du financeur : <strong class="text-info">{{number_format($funder_amount,2)}} €</strong> - Echéances : <strong class="text-success">{{number_format($echeance_amount,2)}} €</strong> - Reste : <strong class="text-danger">{{number_format($rest_amount,2)}} €</strong> 
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Mode</label>
                        <select id="modeSelectTypes" name="amount_type" class="form-control form-control-sm" required>
                            @php
                            $selectedPercent = ($row && $row->amount_type ==='percentage')?'selected':'';
                            $selectedFixed = ($row && $row->amount_type ==='fixed_amount')?'selected':'';
                            @endphp
                            <option {{$selectedPercent}} value="percentage">Pourcentage (%)</option>
                            <option {{$selectedFixed}} value="fixed_amount">Montant fixe</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="form-group">

                        <input type="hidden" value="{{$rest_amount}}" id="input_hidden_remain_fixed_p">
                        <input type="hidden" value="{{$remain_percentage}}" id="input_hidden_remain_percentage_p">

                        <label for="amount_item">Montant : (Max : <span id="label_amount_funding_p"></span>) <span class="text-danger">*</span></label>
                        <input class="form-control form-control-sm" type="number" name="amount"
                            value="{{ ($row)?$row->amount:1 }}" id="amount_item" required />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Date <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $due_date ='';
                            if($row && $row->due_date!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d',$row->due_date);
                            $due_date = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control" name="due_date" id="due_date_datepicker"
                                placeholder="Sélectionner une date" value="{{ $due_date }}" autocomplete="off" required />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-info" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formFundingPayment').submit();" class="btn btn-sm btn-success"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_FUNDING_PAYMENT"></span></button>
</div>

<script>
updateLabelAmountEcheance();
function updateLabelAmountEcheance(){
    var amount_type = $('#modeSelectTypes').find(":selected").val();
    sign='%';
    if(amount_type=='percentage'){
        sign='%';
        var remain=$('#input_hidden_remain_percentage_p').val();
    }else if(amount_type=='fixed_amount'){
        sign='€';
        var remain=$('#input_hidden_remain_fixed_p').val();
    }
    $('#label_amount_funding_p').html(remain+sign);
}
$('#due_date_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
$("#formFundingPayment").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        //controlle
        var amount_type = $('#modeSelectTypes').find(":selected").val();
        var amount_item=$('#amount_item').val();
        var sign='%';
        if(amount_type=='percentage'){
            sign='%';
            var remain=$('#input_hidden_remain_percentage_p').val();
        }else if(amount_type=='fixed_amount'){
            sign='€';
            var remain=$('#input_hidden_remain_fixed_p').val();
        }
        if(amount_item - remain > 0){
            _showResponseMessage('error', 'Attention vous ne pouvez pas dépasser '+remain+sign);
            return false;
        }

        _showLoader('BTN_SAVE_FUNDING_PAYMENT');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/fundingpayment',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_FUNDING_PAYMENT');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_fundingpayment').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_FUNDING_PAYMENT');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_FUNDING_PAYMENT');
                _loadFundings();
            }
        });
        return false;
    }
});
</script>