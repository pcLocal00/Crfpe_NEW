<div class="modal-header">
    <h5 class="modal-title" id="modal_download_document_title"><i class="flaticon-edit"></i> Motif de refus</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
@if ($id == 1)
<form id="downloadDocument" class="form" enctype="multipart/form-data">
    @csrf
    <div class="modal-body" id="modal_download_document_body">
        <div data-scroll="true" data-height="550">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-between">
                        <label for="max_nb_trainees">Telecharger le devis  </label>
                        <button type="button" class="btn btn-sm btn-primary" data-file-url="{{ asset('uploads/document/' . $devis->path) }}">
                            <i class="fa fa-check"></i> Telecharger
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-between">
                        <label for="max_nb_trainees">Telecharger le contrat de prestation </label>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa fa-check"></i> Telecharger <span id="BTN_SAVE_POINTAGE"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@else
<form id="downloadDocument" class="form" enctype="multipart/form-data">
    @csrf
    <div class="modal-body" id="modal_download_document_body">
        <div data-scroll="true" data-height="550">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-between">
                        <label for="max_nb_trainees">Telecharger le devis </label>
                        <a href="{{ route('download_documents', ['path' => $devis->path]) }}" target="_blank">
                            Telecharger <i style="margin-left:5px;color:blue;" class="fas fa-paperclip"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-between">
                        <label for="max_nb_trainees">Telecharger le contrat de prestation </label>
                        <a href="{{ route('download_documents', ['path' => $contrat->path]) }}" target="_blank">
                            Telecharger <i style="margin-left:5px;color:blue;" class="fas fa-paperclip"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-between ">
                        <label for="max_nb_trainees">Telecharger le facture </label>
                        <a href="{{ route('download_documents', ['path' => $facture->path]) }}" target="_blank">
                            Telecharger <i style="margin-left:5px;color:blue;" class="fas fa-paperclip"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group d-flex justify-content-between">
                        <label for="max_nb_trainees">Telecharger les 3 documents </label>
                        <button type="button" class="btn btn-sm btn-primary" onclick="downloadAllDocuments()">
                            <i class="fa fa-check"></i> Télécharger tous les documents
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endif


<script>

    $(document).ready(function() {
        $('#downloadDocument').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "{{ route('send_motif_devis') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        _showResponseMessage('success', response.msg);
                    } else {
                        _showResponseMessage('error', response.msg);
                    }
                },
                error: function(xhr, status, error) {
                    _showResponseMessage('error','Veuillez vérifier les champs du formulaire...');
                }
            });
        });
    });

    function downloadAllDocuments() {
        var devisPath = "{{ asset('uploads/document/' . $devis->path) }}";
        var contratPath = "{{ asset('uploads/document/' . $contrat->path) }}";
        var facturePath = "{{ asset('uploads/document/' . $facture->path) }}";

        downloadFile(devisPath, "{{ $devis->path }}");
        downloadFile(contratPath, "{{ $contrat->path }}");
        downloadFile(facturePath, "{{ $facture->path }}");
    }

    function downloadFile(path, filename) {
        var link = document.createElement("a");
        link.href = path;
        link.download = filename;
        link.style.display = "none"; // Hide the link
        document.body.appendChild(link); // Append the link to the body element
        link.click(); // Trigger the click event
        document.body.removeChild(link); // Remove the link from the body after the download
    }


</script>
