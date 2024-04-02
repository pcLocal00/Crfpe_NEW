@php
$title = $emailmodel ? 'Edition' : 'Ajout';
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
            <!-- <button type="button" onclick="_restore_default_value({{ $emailmodel ? $emailmodel->id : 0 }})" class="btn btn-outline-danger btn-sm mr-2"><i class="flaticon2-refresh"></i> Rétablir les options par défaut <span id="BTN_RESET_DOCUMENT"></span></button> -->
            <a href="/email/overviewmail/{{ $emailmodel ? $emailmodel->id : 0 }}"
                class="btn btn-outline-primary btn-sm mr-2" target="_blank"><i class="flaticon-medical"></i> Aperçu</a>
        </div>
    </div>
    <!--end::Header-->
    <!--begin::Card-body-->
    <div class="card-body">
        <form id="formDocument" class="form">
            @csrf
            <input type="hidden" id="documentmodel_id" value="{{ $emailmodel ? $emailmodel->id : 0 }}" name="id">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Vue :</label>
                        <select id="view_table" name="view_table" class="form-control"
                            onchange="refreshViewVars(this.value)">
                            <option value="">--Sélectionnez--</option>
                            @foreach ($views as $v_name)
                                <option value="{{ $v_name }}"
                                    {{ $emailmodel->view_table == $v_name ? 'selected' : '' }}>
                                    {{ $v_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <p class="bg-primary p-5 text-light mb-5">Cliquez sur un emplacement dans un champ, puis cliquez
                        deux fois sur une des variables ci-dessous pour l'insérer.</p>
                    <div class="view_vars"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="title">Document : <span class="text-danger">*</span></label>
                        <input class="form-control " type="text" name="name"
                            value="{{ $emailmodel ? $emailmodel->name : '' }}" id="name" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-1">
                        <label>Header :</label>
                        <textarea class="form-control classic-editor" name="custom_header" id="custom_header" rows="3">{{ $emailmodel ? $emailmodel->custom_header : '' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-1">
                        <label>Contenu :</label>
                        <textarea class="form-control classic-editor" name="custom_content" id="custom_content" rows="3">{{ $emailmodel ? $emailmodel->custom_content : '' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-1">
                        <label>Footer :</label>
                        <textarea class="form-control classic-editor" name="custom_footer" id="custom_footer" rows="3">{{ $emailmodel ? $emailmodel->custom_footer : '' }}</textarea>
                    </div>
                </div>
            </div>
            <button class="d-none" type="submit" id="BTN_SUBMIT_FORM"></button>
        </form>
    </div>
</div>
<!--end::Card-->
<script>
    var custom_header;
    var selected = false;
    ClassicEditor.create($("#custom_header")[0]).then(editor => {
        custom_header = editor;
        initEditorEvents(custom_header);
    }).catch(error => {});

    var custom_content;
    ClassicEditor.create($("#custom_content")[0]).then(editor => {
        custom_content = editor;
        initEditorEvents(custom_content);
    }).catch(error => {});

    var custom_footer;
    ClassicEditor.create($("#custom_footer")[0]).then(editor => {
        custom_footer = editor;
        initEditorEvents(custom_footer);
    }).catch(error => {});

    function initEditorEvents(editor) {
        editor.ui.focusTracker.on('change:isFocused', (evt, name, value) => {
            // editor.model.document.selection.getLastPosition()
            if (value) {
                position = false;
            } else {
                selected = editor;
            }
        });
    }

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

    $('select#view_table').trigger('change');
    var selected_field = false;
    var selected_field_pos = 0;

    $('textarea').on('click keypress', function() {
        selected_field = $(this);
        selected_field_pos = $(this).caret().start;
    });

    function refreshViewVars(view) {
        if (!view.length) return false;
        $.ajax({
            type: 'GET',
            url: '/form/email/getviewvars',
            data: {
                view: view
            },
            dataType: 'JSON',
            success: function(vars) {
                $('.view_vars').empty();
                vars.map((v) => {
                    var v_tag = $(
                        '<button class="var_tag btn btn-info btn-xs m-2 p-2" data-var="' + v +
                        '"><i class="fa fa-plus"></i> ' +
                        v +
                        '</button>');
                    $('.view_vars').append(v_tag);
                });
                Swal.fire({
                    title: 'Attention!',
                    text: 'Il se peut que les variables chargées soient incohérentes avec celles insérées. Merci de vérifier les champs.',
                    icon: "warning",
                    confirmButtonText: "J'ai compris"
                });

                $('button.var_tag').click(function(e) {
                    e.preventDefault();
                    if (!selected) return false;
                    const variable = $(e.currentTarget).closest('button').data('var');
                    const viewFragment = selected.data.processor.toView(`{${variable}}`);
                    const modelFragment = selected.data.toModel(viewFragment);
                    selected.model.insertContent(modelFragment, selected.model.document.selection);
                    console.log(position);
                });
            },
            error: function(error) {
                _showResponseMessage('error', 'Erreur Inconnue.');
            }
        });
    }
</script>
