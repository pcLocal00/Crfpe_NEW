<input type="hidden" id="documentmodel_id" value="{{ $emailmodel ? $emailmodel->id : 0 }}" name="documentmodel_id">
@if ($view_vars)
    <div class="row">
        <div class="col-lg-12">
            <p class="bg-primary p-5 text-light mb-5">Cliquez sur un emplacement dans un champ, puis cliquez
                deux fois sur une des variables ci-dessous pour l'insérer.</p>
            <div class="view_vars">
                @foreach ($view_vars as $var)
                    <button class="var_tag btn btn-info btn-xs m-2 p-2" data-var="{{ $var }}"><i
                            class="fa fa-plus"></i>{{ $var }}</button>
                @endforeach
            </div>
        </div>
    </div>
@endif
<div class="row">
    <div class="col-lg-12">
        <div class="form-group">
            <label for="title">Sujet : <span class="text-danger">*</span></label>
            <input class="form-control " type="text" name="subject"
                value="{{ $emailmodel ? $emailmodel->name : '' }}" id="subject" required />
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

    $('select#view_table').trigger('change');
    var selected_field = false;
    var selected_field_pos = 0;

    $('textarea').on('click keypress', function() {
        selected_field = $(this);
        selected_field_pos = $(this).caret().start;
    });

    $('button.var_tag').click(function(e) {
        e.preventDefault();
        if (!selected) return false;
        const variable = $(e.currentTarget).closest('button').data('var');
        const viewFragment = selected.data.processor.toView(`${variable}`);
        const modelFragment = selected.data.toModel(viewFragment);
        selected.model.insertContent(modelFragment, selected.model.document.selection);
    });

    // function refreshViewVars(view) {
    //     if (!view.length) return false;
    //     $.ajax({
    //         type: 'GET',
    //         url: '/form/email/getviewvars',
    //         data: {
    //             view: view
    //         },
    //         dataType: 'JSON',
    //         success: function(vars) {
    //             $('.view_vars').empty();
    //             vars.map((v) => {
    //                 var v_tag = $(
    //                     '<button class="var_tag btn btn-info btn-xs m-2 p-2" data-var="' + v +
    //                     '"><i class="fa fa-plus"></i> ' +
    //                     v +
    //                     '</button>');
    //                 $('.view_vars').append(v_tag);
    //             });
    //             Swal.fire({
    //                 title: 'Attention!',
    //                 text: 'Il se peut que les variables chargées soient incohérentes avec celles insérées. Merci de vérifier les champs.',
    //                 icon: "warning",
    //                 confirmButtonText: "J'ai compris"
    //             });

    //             $('button.var_tag').click(function(e) {
    //                 e.preventDefault();
    //                 if (!selected) return false;
    //                 const variable = $(e.currentTarget).closest('button').data('var');
    //                 const viewFragment = selected.data.processor.toView(`{${variable}}`);
    //                 const modelFragment = selected.data.toModel(viewFragment);
    //                 selected.model.insertContent(modelFragment, selected.model.document.selection);
    //                 console.log(position);
    //             });
    //         },
    //         error: function(error) {
    //             _showResponseMessage('error', 'Erreur Inconnue.');
    //         }
    //     });
    // }
</script>
