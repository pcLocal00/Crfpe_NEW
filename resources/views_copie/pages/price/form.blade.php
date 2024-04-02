@php
$modal_title=($row)?'Edition modèle de plannification':'Ajouter un modèle de plannification';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_price_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form param : begin -->
<form id="formPrice" class="form">
    <div class="modal-body" id="modal_form_price_body">
        <div data-scroll="true" data-height="350">
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
            <input id="INPUT_HIDDEN_ID_PRICE" type="hidden" name="id" value="{{ ($row)?$row->id:0 }}" />
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input id="is_former_price" type="checkbox" value="1" {{ ($row && $row->is_former_price===1)?'checked="checked"':'' }}>
                                <span></span>Tarif formateur</label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- begin::form -->
            <div id="FORM_CONTENT">
            
            </div>            
            <!-- end::form -->            


            

        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form : end -->
<!-- <script src="{{ asset('custom/js/form-ptemplate.js?v=1') }}"></script> -->
<script>
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formPrice").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/price',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_price').modal('hide');
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
                if ($.fn.DataTable.isDataTable('#dt_prices')) {
                    _reload_dt_prices();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});


_formPriceByType({{ ($row && $row->is_former_price===1)?1:0 }});
$('#is_former_price').change(function() {
   if($(this).is(":checked")) {
    _formPriceByType(1);
   }else{
    _formPriceByType(0);
   }
});
function _formPriceByType(is_former) {
    var row_id = $('#INPUT_HIDDEN_ID_PRICE').val();
    var div_content_id = 'FORM_CONTENT';
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#' + div_content_id).html(spinner);
    $.ajax({
        url: '/form/price/type/' + is_former+'/'+row_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + div_content_id).html(html);
        },
        error: function(result, status, error) {

        },
        complete: function(result, status) {

        }
    });
}

</script>