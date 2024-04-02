@php
$modal_title=($row)?'Edition élement':'Ajouter un élement';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_invoiceItem_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form ITEM : begin -->

<div class="modal-body" id="modal_form_invoiceItem_body">
    <div data-scroll="true" data-height="600">

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
        <form id="formInvoiceItem" class="form">
            @csrf
            <input type="hidden" name="id" id="item_invoice_id" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="invoice_id" id="invoice_id" value="{{ ($row)?$row->invoice_id:$invoice_id }}" />
            <input type="hidden" name="invoice_type" value="{{ ($row)?$row->invoice->invoice_type:'students' }}" />

            @foreach($params as $p)
                <input type="hidden" id="title_{{$p->id}}" value="{{$p->name}}">
                <input type="hidden" id="accounting_code_{{$p->id}}" value="{{$p->accounting_code}}">
                <input type="hidden" id="amount_{{$p->id}}" value="{{$p->amount}}">
            @endforeach
           
            @if(!$row || ($row && $row->invoice->invoice_type=="students"))
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Element <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="paramsItemsSelect" required>
                            @foreach($params as $p)
                                <option {{($row && $p->name==$row->title)?'selected':''}} value="{{$p->id}}">{{$p->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            @endif

            <input type="hidden" id="title" name="title" value="{{ ($row)?$row->title:'' }}">

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control form-control-sm" id="description" name="description"
                            rows="5">{{ ($row)?$row->description:'' }}</textarea>
                    </div>
                </div>
            </div>
            @if(!$row || ($row && $row->invoice->invoice_type=="students"))
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="accounting_code">Code comptable : </label>
                        <input class="form-control " type="text" name="accounting_code"
                            value="{{ ($row)?$row->accounting_code:'' }}" id="accounting_code" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="analytical_code">Code analytique : </label>
                        <input class="form-control " type="text" name="analytical_code"
                            value="{{ ($row)?$row->analytical_code:'' }}" id="analytical_code" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="rate">Montant : <span class="text-danger">*</span></label>
                        <input class="form-control" type="number" name="rate" value="{{ ($row)?$row->rate:'' }}" id="rate" required />
                    </div>
                </div>
            </div>
            @endif
        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-info" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formInvoiceItem').submit();" class="btn btn-sm btn-success"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_INVOICE_ITEM"></span></button>
</div>

<!-- Form ITEM : end -->
<script>
var myEditor;
ClassicEditor.create(document.querySelector("#description"))
    .then(editor => {
        //console.log(editor.getData());
        myEditor=editor;
    })
    .catch(error => {});

$('#paramsItemsSelect').select2();
_getItemInfosData();
$('#paramsItemsSelect').on('change', function() {
    _getItemInfosData();
});
function _getItemInfosData() {
    //var item_invoice_id=$("#item_invoice_id").val();
    //if(item_invoice_id==0){
        var id = $("#paramsItemsSelect option:selected").val();
        //console.log(id);
        if(id>0){
            var title=$("#title_"+id).val();
            var accounting_code=$("#accounting_code_"+id).val();
            var amount=$("#amount_"+id).val();
            $("#title").val(title);
            $("#accounting_code").val(accounting_code);
            $("#rate").val(amount);
        }
    //}
}

$("#formInvoiceItem").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        //var data=editor.getData();
        //console.log(myEditor.getData());
        $('#description').val(myEditor.getData());
        _showLoader('BTN_SAVE_INVOICE_ITEM');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/invoice-item',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_INVOICE_ITEM');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_invoiceItem').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_INVOICE_ITEM');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_INVOICE_ITEM');
                _loadItems();
                if ($.fn.DataTable.isDataTable('#dt_invoices')) {
                    _reload_dt_invoices();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
</script>