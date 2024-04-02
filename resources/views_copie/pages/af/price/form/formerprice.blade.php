<input type="hidden" name="type_former_intervention" value="{{ $type_former_intervention }}" />
<p class="text-primary">Type : {{ $type_former_intervention  }}</p>
@if($type_former_intervention=='Sur contrat')
<!-- begin::Sur contrat -->
<div class="form-group">
    <input type="hidden" value="TTF_HEURE" name="price_type">
    <label>Tarif horaire Brut € : <span class="text-danger">*</span> <span id="LOADER_PRICES"></span></label>
    <select class="form-control select2" id="pricesSelect" name="price" required>
        <option value="">Sélectionnez un tarif</option>
        @foreach ($prices as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
        <option value="0">Gratuit</option>
    </select>
    <script>
        $('#pricesSelect').select2();
        $('#pricesSelect').on('change', function() {
            _updatePrice();
        });
        _loadPrices();

        function _loadPrices() {
            $('#LOADER_PRICES').html('<i class="fa fa-spinner fa-spin text-primary"></i>');
            var selected_price_id = 0;
            _loadDatasPricesForFormersOptionsFunction('pricesSelect', selected_price_id);
            $('#LOADER_PRICES').html('');
        }

        function _loadDatasPricesForFormersOptionsFunction(select_id,selected_value = 0) {
            $('#' + select_id).empty().append('<option value="">Sélectionnez un tarif</option><option value="0">Gratuit</option>');
            $.ajax({
                url: '/api/select/options/prices/formers',
                dataType: 'json',
                success: function(response) {
                    var array = response;
                    if (array != '') {
                        for (i in array) {
                            $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name + "</option>");
                        }
                    }
                }
            }).done(function() {
                if (selected_value != 0 && selected_value != '') {
                    $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
                }
            });
        }

        function _updatePrice() {
            var price = $('#pricesSelect').val();
            $('#INPUT_OTHER_PRICE').val(price);
            if (price >= 0) {
                $('#BLOCK_OTHER_PRICE').addClass('d-none');
                $("#INPUT_OTHER_PRICE").prop('required', false);
                $('#INPUT_IS_OTHER').val(0);
            } else {
                $('#BLOCK_OTHER_PRICE').removeClass('d-none');
                $("#INPUT_OTHER_PRICE").prop('required', true);
                $('#INPUT_OTHER_PRICE').val('');
                $('#INPUT_IS_OTHER').val(1);
            }
        }
    </script>
</div>
<div class="form-group d-none" id="BLOCK_OTHER_PRICE">
    <label>Autre</label>
    <input type="hidden" value="0" name="is_other_price" id="INPUT_IS_OTHER">
    <input type="number" class="form-control" id="INPUT_OTHER_PRICE" name="other_price" value="0" />
</div>
@endif

@if($type_former_intervention=='Sur facture')
<!-- begin::Sur facture -->
<div class="form-group">
    <label>Tarif TTC(€): <span class="text-danger">*</span></label>
    <input type="number" class="form-control form-control-sm" name="price" value="0" />
</div>
<!-- end::Sur facture -->
@endif
