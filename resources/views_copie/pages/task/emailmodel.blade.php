<!--begin::Card-->
<div class="modal-header">
    <span class="modal-title">Envoyer le mail de la tâche</span>
</div>
<div class="modal-body">
    <form class="form" id="sendTaskMail">
        @method('post')
        @csrf
        <h4>
            Envoyer à : {{ $task->contact->firstname }} {{ $task->contact->lastname }}
            <{{ $task->contact->email ?? ($task->contact->entitie->email ?? $task->contact->user->email) }}>
        </h4>

        <div class="form-group">
            <label>Modèle: <span class="text-danger">*</span> <span id="LOADER_PRODUCTS"></span></label>
            <select class="form-control select2" id="mail_template" name="mail_template" required>
                <option value="">Sélectionnez un modèle...</option>
                @foreach ($emailmodels as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" id="selected_template"></div>

        <div class="form-group mb-1 attachments" style="display: none;">
            <label>Pièces jointes: </label>
            <div id="uploader">
                <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
            </div>
        </div>
    </form>
</div>

<div class="modal-footer">
    <div class="pull-right">
        <a class="btn btn-light-primary font-weight-bold" id="BTN_SAVE"
            onclick="$('#sendTaskMail').submit()">Envoyer</a>
        <a href="#" class="btn btn-clean font-weight-bold" id="clear" data-dismiss="modal">Annuler</a>
    </div>
</div>

<script>
    $('.select2').select2();

    $('#mail_template').change(function() {
        const model_id = $(this).val();
        _loadFormDocumentModel(model_id);
        $('.attachments').show();
    });

    var _loadFormDocumentModel = function(document_model_id) {
        var block_id = 'selected_template';
        KTApp.block('#' + block_id, {
            overlayColor: '#000000',
            state: 'danger',
            message: 'Veuillez patienter...'
        });
        $.ajax({
            url: '/form/sentaskemail/' + document_model_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + block_id).html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {
                KTApp.unblock('#' + block_id);
            }
        });
        $(".css-af").each(function() {
            $(this).removeClass("active");
        });
        $('#NAV' + document_model_id).addClass("active");
    }

    $('#sendTaskMail').submit(function(e) {
        e.preventDefault();
        // _showLoader('BTN_SAVE_DOCUMENT');
        var formData = new FormData(this);
        $('#uploader').plupload('getFiles').forEach(file => {
            // console.log(file.getNative());
            formData.append('attachments[]', file.getNative());
        });

		formData.set('custom_header', custom_header.getData());
		formData.set('custom_content', custom_content.getData());
		formData.set('custom_footer', custom_footer.getData());

        $.ajax({
            type: 'POST',
            url: '/email/sendMailTask/' + {{ $task->id }},
            data: formData,
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function(result) {
                if (result.success) {
                    _showResponseMessage('success', result.msg);
					$('#modal_form_email').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                // _hideLoader('BTN_SAVE_DOCUMENT');
                _showResponseMessage('error',
                    'Veuillez vérifier les champs du formulaire...');
            },
        });
    });


    $(function() {
        $("#uploader").plupload({
            // General settings
            runtimes: 'html5,flash,silverlight,html4',
            url: "/examples/upload",

            // Maximum file size
            max_file_size: '2mb',

            chunk_size: '1mb',

            // Resize images on clientside if we can
            resize: {
                width: 200,
                height: 200,
                quality: 90,
                crop: true // crop to exact dimensions
            },

            // Specify what files to browse for
            filters: [{
                    title: "Image files",
                    extensions: "jpg,gif,png"
                },
                {
                    title: "PDF files",
                    extensions: "pdf"
                }
            ],

            // Rename files by clicking on their titles
            rename: true,

            // Sort files
            sortable: true,

            // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
            dragdrop: true,

            // Views to activate
            views: {
                list: true,
                thumbs: true, // Show thumbs
                active: 'thumbs'
            },

            // Flash settings
            flash_swf_url: '/plupload/js/Moxie.swf',

            // Silverlight settings
            silverlight_xap_url: '/plupload/js/Moxie.xap'
        });
    });
</script>
