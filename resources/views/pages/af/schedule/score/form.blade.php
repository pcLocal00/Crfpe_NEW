<div class="modal-header">
    <h5 class="modal-title" id="modal_form_contact_title"><i class="flaticon-edit"></i> Edition scores/ECTS </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>

<!-- Form : begin -->
<div class="modal-body" id="modal_form_score_body">
    <form id="formScore" class="form">
        @csrf
        <div data-scroll="true" data-height="550">
        @foreach ($scores as $ss => $schedulecontacts)
            <div class="form-group row">
                <div class="col-lg-12">
                    <div class="accordion accordion-toggle-arrow" id="accordionFormFormation">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseOne{{$ss}}">
                                    <i class="flaticon-file-1"></i> {{$schedulecontacts['session']}}
                                </div>
                            </div>
                            <div id="collapseOne{{$ss}}" class="collapse show" data-parent="#accordionFormFormation">
                                <div class="card-body">
                                    <div class="form-group row">
                                        <div class="col-lg-12">
                                            <table class="table table-bordered table-checkable" id="dt_scores">
                                                <thead>
                                                    <tr>
                                                        <th>Nom</th>
                                                        <th>Note</th>
                                                        <th>Note orale</th>
                                                        <th>ECTS</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($schedulecontacts['contacts'] as $sc)
                                                        <tr>
                                                            <td>
                                                                {{($sc->firstname . ' ' . $sc->lastname)}}
                                                                <input type="hidden" value="{{$sc->session_ects}}" id="session_ects_{{$sc->id}}" class="form-control">
                                                            </td>
                                                            <td><input type="number" value="{{$sc->score}}" name="score[{{$sc->id}}]" id="score_{{$sc->id}}" class="form-control"></td>
                                                            <td><input type="number" value="{{$sc->score_oral}}" name="score_oral[{{$sc->id}}]" id="score_oral_{{$sc->id}}" class="form-control"></td>
                                                            <td><input type="number" value="{{$sc->contact_ects}}" name="ects[{{$sc->id}}]" id="ects_{{$sc->id}}" class="form-control"></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times" ></i>
        Annuler
    </button>
    <button type="submit" class="btn btn-sm btn-primary" onclick="$('#formScore').submit();"><i class="fa fa-check"></i> Valider <span id="BTN_SAVE_SCORE"></span></button>
</div>


<!-- Form group : end -->


<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#formScore").validate({
        rules: {},
        messages: {},
        submitHandler: function(form) {
            _showLoader('BTN_SAVE_SCORE');
            var formData = $(form).serializeArray();
            $.ajax({
                type: 'POST',
                url: '/form/scores',
                data: formData,
                dataType: 'JSON',
                success: function(result) {
                    _hideLoader('BTN_SAVE_SCORE');
                    if (result.success) {
                        _showResponseMessage('success', result.msg);
                        $('#modal_form_score').modal('hide');
                        resfreshJSTreeTemporelle();
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                },
                error: function(error) {
                    _hideLoader('BTN_SAVE');
                    _showResponseMessage('error',
                        'Veuillez vérifier les champs du formulaire...');
                },
                complete: function(resultat, statut) {
                    _hideLoader('BTN_SAVE_SCORE');
                }
            });
            return false;
        }
    });

    $('#formScore [name*=score]').on('keyup change paste', function () {
        const score = parseFloat((this).value);
        var line = $(this).closest('tr');
        if (isNaN(score)) {
            return false;
        }
        const session_ects = line.find('[id*=session_ects]').val();
        const ects = score >= 10 && session_ects > 0 ? session_ects : 0;
        line.find('[name*=ects]').val(ects);
    });
</script>