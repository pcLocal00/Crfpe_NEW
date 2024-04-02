@php
$modal_title = 'Formulaire facture intervenant sur facture';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_attached_documents_invoice_fact"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_form_attached_documents_invoice_fact_body">
    <div data-scroll="true">
        <form id="form_invoice_fact" class="form">
            @csrf
        <input type="hidden" id="ch_invoice_id" value="{{$invoice_id}}">
        <input type="hidden" id="ch_af_id" value="{{$af_id}}">

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Titre Facture <span class="text-danger">*</span></label>
                    <input type="text" name="titre_invoice_fact" id="titre_invoice_fact" class="form-control" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Montant unitaire HT <span class="text-danger">*</span></label>
                    <input type="text" name="montantHT_invoice_fact" id="montantHT_invoice_fact" class="form-control inputval"  required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Quantité <span class="text-danger">*</span></label>
                    <input type="number" id="quantite_invoice_fact" name="quantite_invoice_fact" class="form-control inputval" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Taux TVA <span class="text-danger">*</span></label>
                    <input type="text" name="TauxTVA_invoice_fact" id="TauxTVA_invoice_fact" class="form-control inputval"  required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Montant TTC</label>
                    <input type="text" name="montantTTC_invoice_fact" id="montantTTC_invoice_fact" class="form-control" disabled>
                </div>
            </div>
        </div>

        {{-- Begin::dropzone --}}
        <div class="form-group row">
            <div class="col-md-12">
                <div class="dropzone dropzone-default dropzone-primary" id="docs_dropzone_invoice_intfact">
                    <div class="dropzone-msg dz-message needsclick">
                        <h3 class="dropzone-msg-title">Déposez la facture ici ou cliquez pour télécharger.</h3>
                        <span class="dropzone-msg-desc">Téléchargez un seul fichier</span>
                    </div>
                </div>
            </div>
        </div>
        {{-- End::dropzone --}}

        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i> Fermer</button>
    <button type="button" onclick="_Storeforminvoicefact();" class="btn btn-sm btn-primary"><i
        class="fa fa-check"></i> Valider <span id="BTN_SAVE_INVOICE_FACT"></span></button>
</div>
<script>
    $('[data-scroll="true"]').each(function() {
        var el = $(this);
        KTUtil.scrollInit(this, {
            mobileNativeScroll: true,
            handleWindowResize: true,
            rememberPosition: (el.data('remember-position') == 'true' ? true : false)
        });
    });
    // multiple file upload
    $('#docs_dropzone_invoice_intfact').dropzone({
            
            url: "/form/upload/invoice/intfac/attached/documents", // Set the url for your upload script location
            paramName: "document", // The name that will be used to transfer the file
            maxFiles: 1,
            //maxFilesize: 10, // MB
            addRemoveLinks: true,
            autoProcessQueue: false,
            accept: function(file, done) {
                /* if (file.name == "justinbieber.jpg") {
                    done("Naha, you don't.");
                } else {
                    done();
                } */
                done();
            },
            sending: function(file, xhr, formData) {
                formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
                formData.append("af_id", $('#ch_af_id').val());
                formData.append("invoice_id", $('#ch_invoice_id').val());
                formData.append("titre_invoice_fact", $("#titre_invoice_fact").val());
                formData.append("montantHT_invoice_fact", $("#montantHT_invoice_fact").val());
                formData.append("quantite_invoice_fact", $("#quantite_invoice_fact").val());
                formData.append("TauxTVA_invoice_fact", $("#TauxTVA_invoice_fact").val());
                
            },
            complete: function(file) {
                _reload_dt_upload_documents_invoice_fact();
            },
});

function _Storeforminvoicefact(){
        
        if($("#titre_invoice_fact").val() != "" && $("#montantHT_invoice_fact").val() != "" && $("#quantite_invoice_fact").val() != "" && $("#TauxTVA_invoice_fact").val() != "")
        {
            var invoice_id = $("#ch_invoice_id").val();
            var af_id = $("#ch_af_id").val();

            var myDropzone = Dropzone.forElement("#docs_dropzone_invoice_intfact");
            myDropzone.processQueue();

            var modal_id = 'modal_form_attached_documents_invoice_fact';
            var modal_content_id = 'modal_form_attached_documents_invoice_fact_content';

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
        
                $.ajax({
                    url: '/form/upload/invoice/intfac/attached/documents',
                    type: 'POST',
                    dataType: 'html',
                    data:{
                        titre_invoice_fact : $("#titre_invoice_fact").val(),
                        montantHT_invoice_fact : $("#montantHT_invoice_fact").val(),
                        quantite_invoice_fact : $("#quantite_invoice_fact").val(),
                        TauxTVA_invoice_fact : $("#TauxTVA_invoice_fact").val(),
                        invoice_id : invoice_id,
                        af_id : af_id
                    },
                    success: function(html, status) {
                        alert("Votre facture est enregistré avec succés");
                        $('#' + modal_id).modal('hide');
                        $('#dt_invoice_fact').DataTable().ajax.reload();
                    }
                });
        }
        else{
            alert("Veuillez reseigner toutes les champs obligatoire !!");
        }

}

$(".inputval").keyup(function() {

        var mhtt;
        var mht = $("#montantHT_invoice_fact").val();
        var qte = $("#quantite_invoice_fact").val();
        var tauxtva = $("#TauxTVA_invoice_fact").val();
        var tva = tauxtva/100;
        var tttc;
        var montantTTC;
        mhtt = parseFloat(mht) * parseFloat(qte);
        tttc = parseFloat(mhtt) * parseFloat(tva);
        montantTTC = mhtt + tttc;
        $("#montantTTC_invoice_fact").val(montantTTC);
  
});
</script>
