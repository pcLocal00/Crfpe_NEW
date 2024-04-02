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
    <h5 class="modal-title" id="modal_form_estimateitem_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form ITEM : begin -->

<div class="modal-body" id="modal_form_estimateitem_body">
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
        <form id="formEstimateItem" class="form">
            @csrf
            <input type="hidden" name="id" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="estimate_id" value="{{ ($row)?$row->estimate_id:$estimate_id }}" />
            <input type="hidden" id="IS_MAIN_ITEM" name="is_main_item" value="{{ ($row)?$row->is_main_item:0 }}" />
           
           @php
           $is_main_item=($row)?$row->is_main_item:0;
           @endphp

            @if($is_main_item==0)        
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Produit <span class="text-danger">*</span></label>
                        <input type="hidden" id="selected_formation_id" value="{{ ($row)?$row->pf_id:0 }}">
                        <select class="form-control select2" id="formationsSelect" name="formation_id" required>
                            <option value="">Sélectionnez un produit de formation</option>
                        </select>
                    </div>
                </div>
            </div>
            @else
                <input type="hidden" id="formation_id" name="formation_id" value="{{ ($row)?$row->pf_id:0 }}">
            @endif
                

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="title">Titre : <span class="text-danger">*</span></label>
                        <input class="form-control " type="text" name="title" value="{{ ($row)?$row->title:'' }}"
                            id="title" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control form-control-sm" id="description" name="description"
                            rows="5">{{ ($row)?$row->description:'' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="quantity">Quantité : <span class="text-danger">*</span></label>
                        <input class="form-control" min="1" type="number" name="quantity"
                            value="{{ ($row)?$row->quantity:'' }}" id="quantity" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="unit_type">Type d'unité : </label>
                        <input class="form-control " type="text" name="unit_type"
                            value="{{ ($row)?$row->unit_type:'' }}" id="unit_type" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="rate">Prix unitaire : <span class="text-danger">*</span></label>
                        <input class="form-control" type="number" name="rate" value="{{ ($row)?$row->rate:'' }}"
                            id="rate" required />
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-info" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formEstimateItem').submit();" class="btn btn-sm btn-success"><i
            class="fa fa-check"></i> Valider <span id="BTN_SAVE_ESTIMATE_ITEM"></span></button>
</div>

<!-- Form ITEM : end -->

<script>
//ClassicEditor.create(document.querySelector("#description")).then(editor => {}).catch(error => {});
_refresh_formation_select();
function _refresh_formation_select(){
    var IS_MAIN_ITEM=$('#IS_MAIN_ITEM').val();
    if(IS_MAIN_ITEM==0){
        var selected_formation_id = $('#selected_formation_id').val();
        //console.log(selected_formation_id);
        _loadDatasFormationsForSelectOptions('formationsSelect', selected_formation_id,0);
        $('#formationsSelect').select2();
    }
}
$('#formationsSelect').on('change', function() {
    _getItemTitleData();
});
function _getItemTitleData() {
    var data = $("#formationsSelect option:selected").text();
    var val = $("#formationsSelect option:selected").val();
    if (val) {
        $("#title").val(data);
    }
}

$("#formEstimateItem").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE_ESTIMATE_ITEM');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/estimate-item',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE_ESTIMATE_ITEM');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_estimateItem').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE_ESTIMATE_ITEM');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE_ESTIMATE_ITEM');
                _loadItems();
                if ($.fn.DataTable.isDataTable('#dt_estimates')) {
                    _reload_dt_estimates();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
</script>