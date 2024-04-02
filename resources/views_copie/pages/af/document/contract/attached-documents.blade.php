@php
$modal_title = 'Documents attachés';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_attached_documents"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body" id="modal_form_attached_documents_body">
    <div data-scroll="true" data-height="650">
        <input type="hidden" id="input_contract_id" value="{{$contract_id}}">
        <input type="hidden" id="input_af_id" value="{{$af_id}}">
        {{-- Begin::dropzone --}}
        <div class="form-group row">
            <div class="col-md-12">
                <div class="dropzone dropzone-default dropzone-primary" id="docs_dropzone">
                    <div class="dropzone-msg dz-message needsclick">
                        <h3 class="dropzone-msg-title">Déposez les fichiers ici ou cliquez pour télécharger.</h3>
                        <span class="dropzone-msg-desc">Téléchargez jusqu'à 10 fichiers</span>
                    </div>
                </div>
            </div>
        </div>
        {{-- End::dropzone --}}

        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_uploaded_documents">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->

    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i> Fermer</button>
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
    $('#docs_dropzone').dropzone({
            url: "/form/upload/contract/attached/documents", // Set the url for your upload script location
            paramName: "document", // The name that will be used to transfer the file
            maxFiles: 10,
            //maxFilesize: 10, // MB
            addRemoveLinks: true,
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
                formData.append("af_id", $('#input_af_id').val());
                formData.append("contract_id", $('#input_contract_id').val());
            },
            complete: function(file) {
                _reload_dt_uploaded_documents();
            },
    });

var contract_id=$('#input_contract_id').val();
var dtCertUrl = '/api/sdt/contract/attached/documents/'+contract_id; 
var dt_uploaded_documents = $('#dt_uploaded_documents');
// begin first table
dt_uploaded_documents.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    searching: false,
    info: false,
    ordering: false,
    ajax: {
        url: dtCertUrl,
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
        },
    },
    lengthMenu: [5, 10, 25, 50],
    pageLength: 10,
    drawCallback: function(settings) {
        $("a.fancybox-file").fancybox();
    },
});
function _reload_dt_uploaded_documents() {
    $('#dt_uploaded_documents').DataTable().ajax.reload();
}
</script>
