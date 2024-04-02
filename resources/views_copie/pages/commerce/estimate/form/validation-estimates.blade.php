@php
$modal_title = 'Formulaire de validation de devis';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_documents_validation_estimates_fact"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_form_documents_validation_estimates_fact_body">
    <div data-scroll="true" data-height="650">
        <form id="form_estimates_fact_validation" class="form">
            @csrf
            <input type="hidden" id="hid_estimate_id" value="{{$estimate_id}}">
            <input type="hidden" id="hid_af_id" value="{{$af_id}}">

        <div class="row">
            <div class="col-md-12">
                <label style="font-size: 15px;">Validation de devis<span class="text-danger">*</span></label>
                <span class="switch switch-success switch-sm">
                    <span class="mr-2"><b>NON</b></span>
                    <label>
                        <input type="checkbox" name="yesnovalidation" id="yesnovalidation" checked>
                        <span></span>
                    </label>
                    <span class="ml-2"><b>OUI</b></span>
                </span>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label style="font-size: 15px;">Motif<span class="text-danger">*</span></label>
                    <textarea id="textarea_motif" name="textarea_motif" class="form-control" rows="5"></textarea>
                </div>
            </div>
        </div>
</form>
</div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i> Fermer</button>
    <button type="button" onclick="_validationdevis();" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span id="BTN_SAVE_ESTIMATES_FACT_VALIDATION"></span></button>
</div>
<script>
function _validationdevis(){

        var modal_id = 'modal_form_documents_validation_estimates_fact';
        var modal_content_id = 'modal_form_documents_validation_estimates_fact_content';

            var estimate_id = $("#hid_estimate_id").val();
            var af_id = $("#hid_af_id").val();
            var yesnovalidation;
            if($('#yesnovalidation').is(':checked')){
                yesnovalidation = 1;
            }
            else{
                yesnovalidation = 0;
            }

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
        
                $.ajax({
                    url: '/form/validationform/estimatesfact/' + estimate_id + '/' + af_id,
                    type: 'POST',
                    dataType: 'html',
                    data:{
                        textarea_motif : $("#textarea_motif").val(),
                        yesnovalidation : yesnovalidation
                    },
                    success: function(html, status) {
                        alert("L'operation est faite avec succés");
                        $('#' + modal_id).modal('hide');
                        $('#dt_estimates_fact').DataTable().ajax.reload();
                    }
                });

}
</script>
