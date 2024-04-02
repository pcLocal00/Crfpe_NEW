@php
$modal_title=($row)?'Edition indexes':'Ajouter un index';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_helpindex_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="formHelpindexe" class="form">
    <div class="modal-body" id="modal_form_helpindex_body">
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
            <input type="hidden" name="id" value="{{ ($row)?$row->id:0 }}" />
            <!-- begin::form -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Type index :</label>
                        <select class="form-control" name="type" disabled>
                            @if($typesCodes)
                            @foreach($typesCodes as $code)
                            @php
                            $selected_type = ($row && $row->type===$code['code'])?'selected':'';
                            @endphp
                            <option value="{{ $code['code'] }}" {{ $selected_type }}>{{ $code['name'] }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date index <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                                $dtNow = Carbon\Carbon::now();
                                $index_date =$dtNow->format('d/m/Y');
                                if($row && $row->index_date!=null){
                                    $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$row->index_date);
                                    $index_date = $dt->format('d/m/Y');
                                }
                            @endphp
                            <input type="text" class="form-control form-control-sm" name="index_date"
                                id="index_date_datepicker" placeholder="Sélectionner une date" value="{{ $index_date }}"
                                autocomplete="off" required {{($row)?'readonly':''}}/>
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
                        <label for="index">Index : <span class="text-danger">*</span></label>
                        <input class="form-control form-control-sm" type="number" name="index"
                            value="{{ ($row)?$row->index:0 }}" id="index" required />
                    </div>
                </div>
            </div>
            <!--end::form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i> Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form : end -->
<script>
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$('#index_date_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
$("#formHelpindexe").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        var formData = $(form).serializeArray(); // convert form to array
        $.ajax({
            type: 'POST',
            url: '/form/helpindex',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_helpindex').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE');
                _showResponseMessage('error', 'Ouups...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE');
                if ($.fn.DataTable.isDataTable('#dt_helpindexes')) {
                    _reload_dt_helpindexes();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});
</script>