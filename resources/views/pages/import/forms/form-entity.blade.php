@php
$modal_title = $row ? '' : 'Importer un fichier XLSX';
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modal_form_entitie_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<div class="modal-body" id="modal_form_entitie_body">
    <div data-scroll="true" data-height="auto">
        <form id="formFilesUpload" class="form" enctype="multipart/form-data">
            <div class="row justify-content-md-center">
                <div class="col-lg-12">
                    {{-- <div class="custom-file"> --}}
                    {{-- <input type="file" class="custom-file-input" id="file_to_upload" name='file_to_upload'
                            data-browse="Parcourir" required>
                        <label class="custom-file-label" for="files_to_upload">Choisir un fichier...</label> --}}
                    {{-- <div class="invalid-feedback"></div> --}}
                    {{-- </div> --}}
                    <input name='file_category' type="hidden" value="{{ $file_category ?? 'PARCOURSUP' }}">
                    <div class="form-group">
                        <input id="file_to_upload" name='file_to_upload' type="file" class="file"
                            data-preview-file-type="any" required>
                    </div>
                </div>
            </div>
            <!-- begin::hidden input helper -->
            <input type="hidden" id="INPUT_ENTITY_ID_HELPER" value="{{ $row ? $row->id : 0 }}" />
            @csrf
        </form>
    </div>
</div>
<div class="modal-footer">
    <div class="spinner-border text-primary" role="status" style="display: none">
        <span class="sr-only">Loading...</span>
    </div>
    <button type="button" onclick="$('#formFilesUpload').submit();" class="btn btn-sm btn-primary"><i
            class="fas fa-upload"></i> Envoyer <span id="LOADER_BTN_UPLOAD"></span></button>
    <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Fermer</button>
</div>
<!-- Form : end -->

<script type="text/javascript">
    //$('document').on('ready', function() {
    $("#file_to_upload").fileinput({
        language: 'fr',
        showPreview: false,
        showRemove: true,
        showUpload: false,
        maxFileCount: 1,
        allowedFileTypes: ['object'], // allow only images
        allowedFileExtensions: ['xls,xlsx'],
        showCancel: true,
        initialPreviewAsData: false,
        overwriteInitial: false,
        theme: 'fas',
    });
    //});
    var form_id = 'formFilesUpload';
    $("#" + form_id).submit(function(event) {
        event.preventDefault();
        _displaySpinner();
        formData = new FormData($(this)[0]);
        $.ajax({
            type: 'POST',
            url: "/form/formFileUpload/uploadfile",
            data: formData,
            // async: false,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(json, status) {
                //$('#LOADER_BTN_UPLOAD').html('');
                _showResponseMessage('success', 'Le fichier a été importé avec succès');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(error) {
                _showResponseMessage('error', 'Le fichier n\'a été importé');
            },
            complete: function(resultat, statut) {
                _displaySpinner(false);
            }
        });
    });

    /* $("#" + form_id).validate({
        rules: {},
        messages: {},
        submitHandler: function(form) {
            _showLoader('LOADER_BTN_UPLOAD');
            //return false;
            var formData = $(form).serializeArray();
            console.log(formData);
            $.ajax({
                type: 'POST',
                url: "/form/formFileUpload/uploadfile",
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(json, status) {
                    //$('#LOADER_BTN_UPLOAD').html('');
                    _showResponseMessage('success', 'Le fichier a été importé avec succès');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
                error: function(error) {
                    _showResponseMessage('error', 'Le fichier n\'a été importé');
                },
                complete: function(resultat, statut) {
                    _hideLoader('LOADER_BTN_UPLOAD');
                }
            });
            return false;
        }
    }); */

    //$(document).ready(function() {
    /* $('#formFilesUpload').on('submit', e => {
        e.preventDefault();
        formData = new FormData($('#formFilesUpload')[0]);
        $('#LOADER_BTN_UPLOAD').html('<i class="fa fa-spinner fa-spin"></div>');
            //return false;
        $.ajax({
            type: 'POST',
            url: "/form/formFileUpload/uploadfile",
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(json, status) {
                $('#LOADER_BTN_UPLOAD').html('');
                location.reload();
            },
            error: function(error) {},
            complete: function(resultat, statut) {
                //_loadContentImport(1);
            }
        });

        // return false;
    }); */
    //});
</script>
