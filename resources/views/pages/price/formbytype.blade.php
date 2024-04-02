@if($is_former==0)
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <div class="checkbox-inline">
                <label class="checkbox">
                    <input type="checkbox" value="1" name="is_broadcast"
                        {{ ($row && $row->is_broadcast===1)?'checked="checked"':'' }}>
                    <span></span>Diffusé site</label>
                <label class="checkbox">
                    <input type="checkbox" value="1" name="is_forbidden"
                        {{ ($row && $row->is_forbidden===1)?'checked="checked"':'' }}>
                    <span></span>Non applicable</label>
                <label class="checkbox">
                    <input type="checkbox" value="1" name="is_ondemande"
                        {{ ($row && $row->is_ondemande===1)?'checked="checked"':'' }}>
                    <span></span>Sur devis</label>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Type d'entité <span class="text-danger">*</span></label>
            <select id="entityTypesSelect" name="entity_type" class="form-control " required>
                <option value="S" {{ (($row && $row->entity_type=='S')?'selected':'') }}>Société</option>
                <option value="P" {{ (($row && $row->entity_type=='P')?'selected':'') }}>Particulier
                </option>
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Type de dispositif <span class="text-danger">*</span></label>
            <input type="hidden" id="selected_device_type" value="{{ ($row)?$row->device_type:0 }}">
            <select id="dispositifTypesSelect" name="device_type" class="form-control " required>
                <option value="">Sélectionnez</option>
            </select>
        </div>
    </div>
</div>
<script>
_loadDatasSelects();
$('#entityTypesSelect').on('change', function() {
    _loadDatasSelects();
});
/* $('#dispositifTypesSelect').on('change', function() {
    _loadDatasSelects();
}); */
function _loadDatasSelects() {
    var entity_type = $('#entityTypesSelect').val();
    //console.log(entity_type);
    if (entity_type == "S") {
        var selected_device_type = $('#selected_device_type').val();
        _loadDatasForSelectOptions('dispositifTypesSelect', 'PRICE_DISPOSITIF_ENTREPRISE_TYPES', selected_device_type,
            1);
        var selected_price_type = $('#selected_price_type').val();
        _loadDatasForSelectOptions('priceTypesSelect', 'TYPE_TARIFICATION_ENTREPRISE_INTER_INTRA', selected_price_type,
            1);
    } else if (entity_type == "P") {
        var selected_device_type = $('#selected_device_type').val();
        _loadDatasForSelectOptions('dispositifTypesSelect', 'PRICE_DISPOSITIF_PARTICULIER_TYPES', selected_device_type,
            1);
        var selected_price_type = $('#selected_price_type').val();
        _loadDatasForSelectOptions('priceTypesSelect', 'TYPE_TARIFICATION_PARTICULIER_INTER', selected_price_type, 1);
    }
}
</script>
@endif
<input type="hidden" value="{{ $is_former }}" name="is_former_price">
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Titre</label>
            <input type="text" class="form-control" name="title" value="{{ ($row)?$row->title:'' }}" />
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Tarif (€)<span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="price" value="{{ ($row)?$row->price:'' }}" required />
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Type de tarif <span class="text-danger">*</span></label>
            <input type="hidden" id="selected_price_type" value="{{ ($row)?$row->price_type:0 }}">
            <select id="priceTypesSelect" name="price_type" class="form-control " required>
                <option value="">Sélectionnez</option>
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label>Code comptable </label>
            <input type="text" class="form-control" name="accounting_code" value="{{ ($row)?$row->accounting_code:'' }}" />
        </div>
    </div>
</div>
@if($is_former==1)
<script>
var selected_price_type = $('#selected_price_type').val();
_loadDatasForSelectOptions('priceTypesSelect', 'TYPE_TARIFICATION_FORMATEUR', selected_price_type, 1);
</script>
@endif
