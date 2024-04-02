@php
$modal_title=($row)?'Edition financeur':'Ajouter un financeur';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_agreementitem_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_form_agreementitem_body">
    <div data-scroll="true" data-height="600">
        <form id="formFunding" class="form">
            @csrf
            <input type="hidden" name="id" id="INPUT_FUNDING_ID" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" id="INPUT_AGREEMENT_ID" name="agreement_id" value="{{ ($row)?$row->agreement_id:$agreement_id }}" />
            <input type="hidden" id="agreement_amount" value="{{ $agreement_amount }}" />

            <div class="row">
                <div class="col-md-12">
                    <p class="text-info">Montant globale : <strong>{{number_format($agreement_amount,2)}} €</strong> - Financeurs : <strong>{{number_format($funders_amount,2)}} €</strong> - Reste : <strong>{{number_format($rest_amount,2)}} €</strong> 
                    </p>
                </div>
            </div>

            <div class="row">
            <div class="col-lg-12">
                    <div class="form-group">
                        <label>Financeur <span id="LOADER_FUNDERS"></span><span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_entity_id" value="{{ ($row)?$row->entitie_id:0 }}">
                        <select id="fundersSelect" name="entitie_id" class="form-control form-control-sm select2" required>
                            <option value="">Sélectionnez</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">   
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Mode</label>
                        <select id="modeSelect" name="amount_type" class="form-control form-control-sm" required>
                            @php
                            $selectedPercent = ($row && $row->amount_type ==='percentage')?'selected':'';
                            $selectedFixed = ($row && $row->amount_type ==='fixed_amount')?'selected':'';
                            @endphp
                            <option {{$selectedPercent}} value="percentage">Pourcentage (%)</option>
                            <option {{$selectedFixed}} value="fixed_amount">Montant fixe</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <!-- <div class="form-group">
                        <label for="amount_funding">Montant : <span class="text-danger">*</span></label>
                        <input class="form-control form-control-sm" type="number" name="amount" max="{{$rest_amount}}"
                            value="{{ ($row)?$row->amount:0 }}" id="amount_funding" required />
                    </div> -->
                    <input type="hidden" value="{{$rest_amount}}" id="input_hidden_remain_fixed">
                    <input type="hidden" value="{{$remain_percentage}}" id="input_hidden_remain_percentage">
                    <div class="form-group">
                        <label for="amount_funding">Montant : (Max : <span id="label_amount_funding"></span>) <span class="text-danger">*</span></label>
                        <input class="form-control form-control-sm" type="number" name="amount"
                            value="{{ ($row)?$row->amount:1 }}" id="amount_funding" required />
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        @php
                        $checkedIsCfa = ($row && $row->is_cfa===1)?'checked=checked':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="is_cfa" {{ $checkedIsCfa }}>
                                <span></span>CFA</label>
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
    <button type="button" onclick="$('#formFunding').submit();" class="btn btn-sm btn-success"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_FUNDING"></span></button>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2();
});
updateLabelAmount();
function updateLabelAmount(){
    var amount_type = $('#modeSelect').find(":selected").val();
    sign='%';
    if(amount_type=='percentage'){
        sign='%';
        var remain=$('#input_hidden_remain_percentage').val();
    }else if(amount_type=='fixed_amount'){
        sign='€';
        var remain=$('#input_hidden_remain_fixed').val();
    }
    $('#label_amount_funding').html(remain+sign);
}
$("#formFunding").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        //controlle
        var amount_type = $('#modeSelect').find(":selected").val();
        var amount_funding=$('#amount_funding').val();
        var sign='%';
        if(amount_type=='percentage'){
            sign='%';
            var remain=$('#input_hidden_remain_percentage').val();
        }else if(amount_type=='fixed_amount'){
            sign='€';
            var remain=$('#input_hidden_remain_fixed').val();
        }
        //console.log(amount_funding+' ->'+remain);
        amount_funding=Number(amount_funding);
        remain=Number(remain);
        if(amount_funding>remain){
            //console.log(amount_funding>remain);
            _showResponseMessage('error', 'Attention vous ne pouvez pas dépasser '+remain+sign);
            return false;
        }
        //console.log('okkkkk');
        //return false;

        _showLoader('BTN_SAVE_FUNDING');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/funding',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_FUNDING');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_funding').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_FUNDING');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_FUNDING');
                _loadFundings();
            }
        });
        return false;
    }
});
var funding_id = $('#INPUT_FUNDING_ID').val();
var agreement_id = $('#INPUT_AGREEMENT_ID').val();
var selected_entity_id = $('#selected_entity_id').val();
_loadFundersEntitiesForSelectOptions('fundersSelect',agreement_id,funding_id,selected_entity_id);
function _loadFundersEntitiesForSelectOptions(select_id,agreement_id,funding_id,selected_entity_id) {
    _showLoader('LOADER_FUNDERS');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/funders/entities/'+agreement_id+'/'+funding_id,
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
        if (selected_entity_id != 0 && selected_entity_id != '') {
            $('#' + select_id + ' option[value="' + selected_entity_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_FUNDERS');
    });
}

$('#modeSelect').on('change', function() {
    updateLabelAmount();
    //onChangeMode();
});
function onChangeMode(){
    var mode = $('#modeSelect').val();
    var amount_funding=$('#amount_funding').val();
    if (mode=='percentage') {
        $('#SPAN_AMOUNT').html(amount_funding);
    }
}
</script>