@php
    use Carbon\Carbon;
    $dateNow = Carbon::now()->format('Y-m-d');
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modal_show_prestation_title"><i class="flaticon-edit"></i> Dépot de contrat de préstation de service</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="acceptPrestation" class="form" enctype="multipart/form-data">
    @csrf
    @if (isset($devis))
        <div class="modal-body" id="modal_show_prestation_body">
            <div data-scroll="true" data-height="550">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="max_nb_trainees">Date du contrat de préstation de service : </label>
                            <label for="max_nb_trainees"> {{ $devis->created_at->format('Y-m-d') }} </label>
                            <input class="form-control " type="text" name="af_id" id="af_id"
                                value="{{ $devis->action->id }}" hidden />
                            <input class="form-control " type="text" name="devis_id" id="af_id"
                                value="{{ $devis->id }}" hidden />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <a href="{{ asset('uploads/document/' . $devis->path) }}" target="_blank">
                            {{ $devis->path }} <i style="margin-left:5px;color:blue;" class="fas fa-paperclip"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger"onclick="_show_motif({{ $devis->id }},{{ $member_id }})">
            <i class="fa fa-times"></i> Refuser
        </button>
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa fa-check"></i> Accepter <span id="BTN_SAVE_POINTAGE"></span>
        </button>
    </div>
</form>

<script>
    function _show_motif(devis_id, member_id) {
        var modal_id = 'modal_show_motif';
        var modal_content_id = 'modal_show_motif_content';
        var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
        $('#' + modal_id).modal('show');
        $('#' + modal_content_id).html(spinner);
        $.ajax({
            url: '/view/show/motif/devis/' + devis_id + '/' + member_id +'/2', 
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
    }
    $(document).ready(function() {
        $('#acceptPrestation').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('step', 2);
            $.ajax({
                url: "{{ route('update_subtask') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        _showResponseMessage('success', response.msg);
                        $('#modal_show_prestation').modal('hide');
                        _loadContent('intervenants')
                    } else {
                        _showResponseMessage('error', response.msg);
                    }
                },
                error: function(xhr, status, error) {
                    _showResponseMessage('error',
                        'Veuillez vérifier les champs du formulaire...');
                }
            });
        });
    });
</script>
