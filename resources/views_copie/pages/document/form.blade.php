@php
    $title = $documentmodel ? 'Edition' : 'Ajout';
@endphp
<!--begin::Card-->
<div class="card card-custom">
    <!--begin::Header-->
    <div class="card-header py-3">
        <div class="card-title align-items-start flex-column">
            <!-- <h3 class="card-label font-weight-bolder text-dark">{{ $title }}</h3>
            <span class="text-muted font-weight-bold font-size-sm mt-1"></span> -->
        </div>
        <div class="card-toolbar">
            <button type="button" onclick="_submit_form()" class="btn btn-outline-info btn-sm mr-2"><i
                    class="flaticon2-checkmark"></i> Enregistrer <span id="BTN_SAVE_DOCUMENT"></span></button>
            <button type="button" onclick="_restore_default_value({{ $documentmodel ? $documentmodel->id : 0 }})"
                class="btn btn-outline-danger btn-sm mr-2"><i class="flaticon2-refresh"></i> Rétablir les options par
                défaut <span id="BTN_RESET_DOCUMENT"></span></button>
            <a href="/pdf/overview/{{ $documentmodel ? $documentmodel->id : 0 }}"
                class="btn btn-outline-primary btn-sm mr-2" target="_blank"><i class="flaticon-medical"></i> Aperçu</a>
        </div>
    </div>
    <!--end::Header-->
    <!--begin::Card-body-->
    <div class="card-body">
        <form id="formDocument" class="form">
            @csrf
            <input type="hidden" id="documentmodel_id" value="{{ $documentmodel ? $documentmodel->id : 0 }}"
                name="id">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="title">Document : <span class="text-danger">*</span></label>
                        <input class="form-control " type="text" name="name"
                            value="{{ $documentmodel ? $documentmodel->name : '' }}" id="name" required />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-1">
                        <label>Header :</label>
                        <textarea class="form-control classic-editor" name="custom_header" id="custom_header" rows="3">{{ $documentmodel ? $documentmodel->custom_header : '' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-1">
                        <label>Contenu :</label>
                        <textarea class="form-control classic-editor" name="custom_content" id="custom_content" rows="3">{{ $documentmodel ? $documentmodel->custom_content : '' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-1">
                        <label>Footer :</label>
                        <textarea class="form-control classic-editor" name="custom_footer" id="custom_footer" rows="3">{{ $documentmodel ? $documentmodel->custom_footer : '' }}</textarea>
                    </div>
                </div>
            </div>
            <button class="d-none" type="submit" id="BTN_SUBMIT_FORM"></button>
        </form>
    </div>
</div>
<!--end::Card-->
<script>
    ClassicEditor.create(document.querySelector("#custom_header")).then(editor => {}).catch(error => {});
    ClassicEditor.create(document.querySelector("#custom_content")).then(editor => {}).catch(error => {});
    ClassicEditor.create(document.querySelector("#custom_footer")).then(editor => {}).catch(error => {});

    function _submit_form() {
        $("#BTN_SUBMIT_FORM").click();
    }

    $("#formDocument").validate({
        rules: {},
        messages: {},
        submitHandler: function(form) {
            _showLoader('BTN_SAVE_DOCUMENT');
            var formData = $(form).serializeArray();
            // console.log(formData);
            $.ajax({
                type: 'POST',
                url: '/form/document',
                data: formData,
                dataType: 'JSON',
                success: function(result) {
                    _hideLoader('BTN_SAVE_DOCUMENT');
                    if (result.success) {
                        _showResponseMessage('success', result.msg);
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                },
                error: function(error) {
                    _hideLoader('BTN_SAVE_DOCUMENT');
                    _showResponseMessage('error',
                        'Veuillez vérifier les champs du formulaire...');
                },
                complete: function(resultat, statut) {
                    _hideLoader('BTN_SAVE_DOCUMENT');
                    _loadFormDocumentModel($('#documentmodel_id').val());
                }
            });
            return false;
        }
    });
</script>
