<div class="modal-header">
    <h5 class="modal-title" id="modal_show_motif_title"><i class="flaticon-edit"></i> Motif de refus</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="motifRefus" class="form" enctype="multipart/form-data">
    @csrf
    <div class="modal-body" id="modal_show_motif_body">
        <div data-scroll="true" data-height="550">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="max_nb_trainees">Motif </label>
                        <input class="form-control " type="text" name="motif" required />
                        <input class="form-control " type="text" name="devis_id" id="devis_id" value="{{ $devis_id }}" hidden />
                        <input class="form-control " type="text" name="member_id" id="member_id" value="{{ $member_id }}" hidden />
                        <input class="form-control " type="text" name="step" id="member_id" value="{{ $step }}" hidden />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa fa-check"></i> Envoyer <span id="BTN_SAVE_POINTAGE"></span>
        </button>
    </div>
</form>

<script>

    $(document).ready(function() {
        $('#motifRefus').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "{{ route('send_motif_devis') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success == 'download') {
                        _show_documents(1);
                    } else if (response.success) {
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
</script>
