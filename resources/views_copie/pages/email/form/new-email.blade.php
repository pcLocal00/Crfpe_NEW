{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 ">
            <div class="card-title">
                <h3 class="card-label">Ajouter un nouveau modèle d'Email
                </h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <button type="button" onclick="_submit_form()" class="btn btn-outline-info btn-sm mr-2"><i
                        class="flaticon2-checkmark"></i> Enregistrer <span id="BTN_SAVE_EMAIL"></span></button>
                <!--end::Button-->
            </div>
        </div>
        <div class="card-body">
            <form id="formDocument" class="form">
                @csrf
                <input type="hidden" id="documentmodel_id" value="0" name="id">

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="title">Type : <span class="text-danger">*</span></label>
                            <input class="form-control " type="text" name="name" value="" id="name"
                                required />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="title">Code : <span class="text-danger">*</span></label>
                            <input class="form-control " type="text" name="code" value="" id="code"
                                required />
                        </div>
                    </div>
                </div>
                <!-- <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-1">
                                <label>Header :</label>
                                <textarea class="form-control classic-editor" name="default_header" id="default_header" rows="3" required></textarea>
                            </div>
                        </div>
                    </div> -->
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-1">
                            <label>Header personalisé :</label>
                            <textarea class="form-control classic-editor" name="custom_header" id="custom_header" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <!-- <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-1">
                                <label>Contenu :</label>
                                <textarea class="form-control classic-editor" name="default_content" id="default_content" rows="3" required></textarea>
                            </div>
                        </div>
                    </div> -->
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-1">
                            <label>Contenu personalisé:</label>
                            <textarea class="form-control classic-editor" name="custom_content" id="custom_content" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <!-- <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-1">
                                <label>Footer:</label>
                                <textarea class="form-control classic-editor" name="default_footer" id="default_footer" rows="3" required></textarea>
                            </div>
                        </div>
                    </div> -->
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-1">
                            <label>Footer personalisé:</label>
                            <textarea class="form-control classic-editor" name="custom_footer" id="custom_footer" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <button class="d-none" type="submit" id="BTN_SUBMIT_FORM"></button>
            </form>
        </div>
    </div>
    <!--end::Card-->
@endsection

{{-- Styles Section --}}
@section('styles')
@endsection


{{-- Scripts Section --}}
@section('scripts')
    {{-- vendors --}}
    <script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>


    {{-- page scripts --}}
    <script src="{{ asset('custom/js/general.js?v=1') }}"></script>
    <!-- <script src="{{ asset('custom/js/list-ptemplates.js?v=1') }}"></script> -->
    <script>
        // ClassicEditor.create(document.querySelector("#default_header")).then(editor => {}).catch(error => {});
        ClassicEditor.create(document.querySelector("#custom_header")).then(editor => {}).catch(error => {});
        ClassicEditor.create(document.querySelector("#custom_content")).then(editor => {}).catch(error => {});
        // ClassicEditor.create(document.querySelector("#default_content")).then(editor => {}).catch(error => {});
        ClassicEditor.create(document.querySelector("#custom_footer")).then(editor => {}).catch(error => {});
        // ClassicEditor.create(document.querySelector("#default_footer")).then(editor => {}).catch(error => {});

        $('#name').change(function() {
            const code = $(this).val().normalize("NFD").replaceAll(/[\u0300-\u036f]/g, "").replaceAll(
                /[^a-zA-Z\d]+/g, '_').toUpperCase();
            $('#code').val(code);
        });

        function _submit_form() {
            $("#BTN_SUBMIT_FORM").click();
        }
        $("#formDocument").validate({
            rules: {},
            messages: {},
            submitHandler: function(form) {
                _showLoader('BTN_SAVE_DOCUMENT');
                var formData = $(form).serializeArray();
                $.ajax({
                    type: 'POST',
                    url: '/form/email',
                    data: formData,
                    dataType: 'JSON',
                    success: function(result) {
                        _hideLoader('BTN_SAVE_DOCUMENT');
                        if (result.success) {
                            _showResponseMessage('success', result.msg);
                            setTimeout(function() {
                                window.location.href = "/admin/emails";
                            }, 1500);
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
                    }
                });
                return false;
            }
        });
    </script>
@endsection
