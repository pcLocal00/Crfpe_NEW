<!-- funders -->
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Financeur <span id="LOADER_FUNDINGS_INV"></span> <span class="text-danger">*</span></label>
            <select id="fundersSelectInv" name="entitie_funder_id" class="form-control form-control-sm select2"
                required>
                <option value="">Sélectionnez</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Contact financeur <span id="LOADER_CONTACT_FUNDINER_INV"></span> <span
                    class="text-danger">*</span></label>
            <select id="funderContactsSelectInv" name="contact_funder_id" class="form-control form-control-sm select2"
                required>
                <option value="">Sélectionnez</option>
            </select>
        </div>
    </div>
</div>
<!-- due_date_funder -->
<script>
$(document).ready(function() {
    $('.select2').select2();
});
_loadFundungsForSelectOptions('fundersSelectInv');
function _loadFundungsForSelectOptions(select_id) {
    _showLoader('LOADER_FUNDINGS_INV');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/listfundings',
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
        _hideLoader('LOADER_FUNDINGS_INV');
        _loadFunderListContactsForSelectOptions('funderContactsSelectInv');
    });
}
$('#fundersSelectInv').on('change', function() {
    _loadFunderListContactsForSelectOptions('funderContactsSelectInv');
});
function _loadFunderListContactsForSelectOptions(select_id) {
    var entity_id = $('#fundersSelectInv').val();
    _showLoader('LOADER_CONTACT_FUNDINER_INV');
    $('#' + select_id).empty();
    $.ajax({
        url: '/api/select/options/funder/listcontacts/' + entity_id,
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
        _hideLoader('LOADER_CONTACT_FUNDINER_INV');
    });
}
</script>