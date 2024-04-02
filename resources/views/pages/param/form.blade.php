@php
$modal_title=($row)?'Edition paramétrage':'Ajouter un paramétrage';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_param_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->
<form id="formParam" class="form">
    <div class="modal-body" id="modal_form_param_body">
        <div data-scroll="true" data-height="400">
            @csrf
            @if($row)
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
            <input type="hidden" name="id" id="INPUT_PARAM_ID" value="{{ ($row)?$row->id:0 }}" />
            <!-- begin::param form -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        @php
                        $checkedIsActive = ($row && $row->is_active===1)?'checked=checked':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="is_active" {{ $checkedIsActive }}>
                                <span></span>Actif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Type de paramétrage</label>
                        <select class="form-control" name="param_code" id="select_param_code">
                            @if($paramCodes)
                            @foreach($paramCodes as $code)
                            @php
                            $selected_type = ($row && $row->param_code===$code['code'])?'selected':'';
                            @endphp
                            <option value="{{ $code['code'] }}" {{ $selected_type }}>{{ $code['name'] }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" value="{{ ($row)?$row->code:'' }}"
                            required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ ($row)?$row->name:'' }}"
                            required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Css </label>
                        <select class="form-control" name="css_class" id="cssClassSelects">
                            @if($cssClass)
                            @foreach($cssClass as $css)
                            @php
                            $selected_css = ($row && $row->css_class===$css)?'selected':'';
                            @endphp
                            <option class="text-{{ $css }}" value="{{ $css }}" {{ $selected_css }}>{{ $css }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Ordre </label>
                        <input type="number" class="form-control" name="order_show"
                            value="{{ ($row)?$row->order_show:'' }}" />
                    </div>
                </div>
            </div>
             
            
            <div class="row" id="BLOCK_INVOICE_INFOS" style="display:none;">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Code comptable </label>
                        <input type="text" class="form-control" name="accounting_code" id="input_accounting_code"
                            value="{{ ($row)?$row->accounting_code:'' }}" />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Code analytique </label>
                        <input type="text" class="form-control" name="analytical_code" id="input_analytical_code"
                            value="{{ ($row)?$row->analytical_code:'' }}" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Amount </label>
                        <input type="number" class="form-control" name="amount" value="{{ ($row)?$row->amount:'' }}" id="input_amount"/>
                    </div>
                </div>
            </div>

            <!--end::param form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form param : end -->
<!-- <script src="{{ asset('custom/js/form-param.js?v=1') }}"></script> -->
<script>
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formParam").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        var formData = $(form).serializeArray(); // convert form to array
        var param_name = $("#select_param_code option:selected").text();
        formData.push({
            name: "param_name",
            value: param_name
        });
        //console.log(formData);
        //return false;
        $.ajax({
            type: 'POST',
            url: '/form/param',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_param').modal('hide');
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
                if ($.fn.DataTable.isDataTable('#dt_params')) {
                    _reload_dt_params();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
_showInvoiceBlockInfos();
$('#select_param_code').on('change',function(){
    _showInvoiceBlockInfos();
});
function _showInvoiceBlockInfos(){
    var param_code=$('#select_param_code').val();
    //console.log(param_code);
    if(param_code=='INVOICE_ITEMS_TYPES'){
        $('#BLOCK_INVOICE_INFOS').show();
    }else{
        $('#BLOCK_INVOICE_INFOS').hide();
        $('#input_accounting_code').val(''); 
        $('#input_amount').val(''); 
    }
}
</script>