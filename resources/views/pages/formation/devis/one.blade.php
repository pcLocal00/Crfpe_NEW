@php
    use Carbon\Carbon;
    $dateNow = Carbon::now()->format('Y-m-d');
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="modal_show_devis_title"><i class="flaticon-edit"></i> Dépot de devis</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="acceptDevis" class="form" enctype="multipart/form-data">
    @csrf
    @if (isset($devis))
        <div class="modal-body" id="modal_show_devis_body">
            <div data-scroll="true" data-height="550">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="max_nb_trainees">Date de devis : </label>
                            <label for="max_nb_trainees"> {{ $devis->created_at }} </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="max_nb_trainees">Action Formation : </label>
                            <label for="max_nb_trainees">{{ $devis->action->title }} </label>
                            <input class="form-control " type="text" name="af_id" id="af_id"
                                value="{{ $devis->action->id }}" hidden />
                            <input class="form-control " type="text" name="devis_id" id="devis_id"
                                value="{{ $devis->id }}" hidden />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="max_nb_trainees">Montant de devis HT : </label>
                            <input class="form-control " type="number" min="1" name="montant_ht" id="montant_ht"
                                required value="{{ $devis->montant_ht }}" disabled/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="max_nb_trainees">TVA : </label>
                            <input class="form-control " type="number" min="1" name="tva" id="tva"
                                required value="{{ $devis->tva }}" disabled/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="max_nb_trainees">Montant de devis TTC : </label>
                            <input class="form-control " type="number" min="1" name="montant_ttc"
                                id="montant_ttc" required value="{{ $devis->montant_ttc }}" disabled/>
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
        <button type="button" class="btn btn-sm btn-light-danger"
            onclick="_show_motif({{ $devis->id }},{{ $member_id }})"><i class="fa fa-times"></i>
            Refuser
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
            url: '/view/show/motif/devis/' + devis_id + '/' + member_id +'/1', 
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#' + modal_content_id).html(html);
            }
        });
    }
    $(document).ready(function() {
        $('#acceptDevis').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('step', 1);
            $.ajax({
                url: "{{ route('update_subtask') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        _showResponseMessage('success', response.msg);
                        $('#modal_depot_devis').modal('hide');
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
