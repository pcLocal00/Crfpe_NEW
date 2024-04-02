@php
$modal_title=($row)?'':'Affecter le fichier parcours sup';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_enrollment_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<div class="modal-body" id="modal_form_enrollment_body">
    <div data-scroll="true" data-height="200">

        <form id="formEnrollment" class="form">
            @csrf
            <input type="hidden" id="INPUT_HIDDEN_ACTION_TYPE">
            <input type="hidden" name="af_id" id="INPUT_HIDDEN_AF_ID" value="{{ ($row)?$row->af_id:$af_id }}" />
            <input type="hidden" name="id" id="INPUT_ENROLLMENT_ID" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="enrollment_type" value="{{ ($row)?$row->enrollment_type:'S' }}" />
            <!-- begin::form -->
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <input type="hidden" id="selected_file_id" value="">
                        <label>Fichier parcours sup <span class="text-danger">*</span> <span
                                id="LOADER_FILES"></span></label>
                        <div @if($row)class="d-none" @endif>
                            <select class="form-control" id="filesSelect" name="file_id" required>
                                <option value="">Sélectionnez un fichier</option>
                            </select>
                        </div> 
                    </div>
                </div>
            </div>
        
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Tarif <span class="text-danger">*</span> <span id="LOADER_PRICES"></span></label>
                        <select class="form-control select2" id="pricesSelect" name="price_id" required>
                            <option value="">Sélectionnez un tarif</option>
                        </select>
                    </div>
                </div>
            </div>   
        </form>
        
    </div>
    <!--end:: form-->
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i></button>
    <button type="button" onclick="_submit_form('SAVE');" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Enregistrer <span id="BTN_SAVE_ENROLLMENT"></span></button>
    <button type="button" onclick="_submit_form('SAVE_AND_CONTINUE');" class="btn btn-sm btn-info"><i class="fa fa-check"></i> Enregistrer et continuer <span id="BTN_SAVE_AND_CONTINUE_ENROLLMENT"></span></button>
</div>

<!-- Form  : end -->
<!-- <script src="{{ asset('custom/js/form-enrollment.js?v=1') }}"></script> -->
<script>

function _submit_form(action_type){
    $('#INPUT_HIDDEN_ACTION_TYPE').val(action_type);
    $('#formEnrollment').submit();
}
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formEnrollment").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        var action_type=$('#INPUT_HIDDEN_ACTION_TYPE').val();
        _showLoader('BTN_'+action_type+'_ENROLLMENT');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/enrollment',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_'+action_type+'_ENROLLMENT');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    //var action_type=$('#INPUT_HIDDEN_ACTION_TYPE').val();
                    if(action_type=='SAVE'){
                        $('#modal_form_enrollment').modal('hide');
                    }
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_'+action_type+'_ENROLLMENT');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_'+action_type+'_ENROLLMENT');
                if ($.fn.DataTable.isDataTable('#dt_enrollments')) {
                    _reload_dt_enrollments();
                } else {
                    location.reload();
                }
            }
        });
        return false;
    }
});

$('#filesSelect').select2();
$('#pricesSelect').select2();
_loadDatasFilesForSelectUpdateOptions('filesSelect',0);

function _loadDatasFilesForSelectUpdateOptions(select_id,selected_value = 0) {
    _showLoader('LOADER_FILES');
    $.ajax({
        url: '/api/select/options/files',
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name+'  '+array[i].date +"</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_value != 0 && selected_value != '') {
            $('#' + select_id + ' option[value="' + selected_value + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_FILES');
    });
}
_loadPrices();
function _loadPrices() {
    var af_id = $('#INPUT_HIDDEN_AF_ID').val();
    _loadDatasPricesByFileForSelectOptions('pricesSelect', af_id, 'P');
}

function _loadDatasPricesByFileForSelectOptions(select_id,af_id,entity_type) {
    var selected_value='';
    $('#LOADER_PRICES').html('<i class="fa fa-spinner fa-spin text-primary"></div>');
    $('#'+select_id).empty();
    $.ajax({
        url: '/api/select/options/pricesbytype/'+af_id+'/'+entity_type,
        dataType: 'json',
        success: function(response) {
          var array = response;
          if (array != '')
          {
        //      alert("yes");
            $('#'+select_id).html("<option>Sélectionnez un tarif</option>");
            for (i in array) {
             $('#'+select_id).append("<option value='"+array[i].id+"'>"+array[i].name+"</option>");
           }
          }
        },
        error: function(x, e) {

        }
    }).done(function() {
        if(selected_value!=0 && selected_value!=''){
            $('#'+select_id+' option[value="'+selected_value+'"]').attr('selected', 'selected');
        }
        $('#LOADER_PRICES').html('');
      });
}

</script>