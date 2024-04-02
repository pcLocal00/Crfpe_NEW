<div class="modal-header">
    <h5 class="modal-title" id="modal_form_formation_title"><i class="flaticon-edit"></i> Détail Etudiant - commentaires
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form committee : begin -->
<div class="modal-body" id="modal_form_formation_body">
    <div data-scroll="true" data-height="600">
        @if (empty($errors))
            <form id="formCommittee" class="form">
                @csrf
                <input type="hidden" id="member_id" name="member_id" value="{{ $member_id }}" />

                <div class="form-group row">
                    <div class="col-lg-4"><i class="fa fa-user"></i> {{ $etudiant }}</div>
                    <div class="col-lg-4 text_info"><i class="fa fa-at"></i> {{ $email }}</div>
                    <div class="col-lg-4"><i class="fa fa-phone"></i> {{ $phone ?? 'aucun' }}</div>
                </div>
                <div class="py-3">
                    <div class="btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-sm btn-warning mr-1" for="no_action" style="display: {{ empty($member->stop_reason)  ? 'none' : 'unset' }}">
                            <input type="radio" class="btn-check" name="stop_reason" value="" id="no_action" {{ empty($member->stop_reason) ? 'checked' : '' }}
                                autocomplete="off">Annuler le choix
                        </label>
                        <label class="btn btn-sm btn-primary mr-1 {{ $member->stop_reason == 'suspend' ? 'active' : '' }}" for="suspend">
                            <input type="radio" class="btn-check" name="stop_reason" value="suspend" id="suspend" {{ $member->stop_reason == 'suspend' ? 'checked' : '' }}
                                autocomplete="off">
                            <i class="fa fa-pause"></i> Suspendre
                        </label>
                        <label class="btn btn-sm btn-primary mr-1 {{ $member->stop_reason == 'stop' ? 'active' : '' }}" for="stop">
                            <input type="radio" class="btn-check" name="stop_reason" value="stop" id="stop" {{ $member->stop_reason == 'stop' ? 'checked' : '' }}
                                autocomplete="off">
                            <i class="fa fa-ban"></i> Exclure
                        </label>
                        <label class="btn btn-sm btn-primary mr-1 {{ $member->stop_reason == 'cancel' ? 'active' : '' }}" for="cancel">
                            <input type="radio" class="btn-check" name="stop_reason" value="cancel" id="cancel" {{ $member->stop_reason == 'cancel' ? 'checked' : '' }}
                                autocomplete="off">
                            <i class="fa fa-sign-out-alt"></i> Abandonner
                        </label>
                    </div>

                    <div class="form-group row pt-3">
                        <div class="col-md-6" style="display: {{ empty($member->stop_reason)  ? 'none' : 'unset' }}">
                            <label for="title">Date de prise d'effet:</label>
                            <input class="form-control date_datepicker" type="text" name="effective_date" value="{{ $member->effective_date ? $member->effective_date->format('d/m/Y') : ''}}"
                                id="effective_date" readonly>
                        </div>
                        <div class="col-md-6" style="display: {{ $member->stop_reason != 'suspend'  ? 'none' : 'unset' }}">
                            <label for="title">Date de reprise prévisionnelle:</label>
                            <input class="form-control date_datepicker" type="text" name="resumption_date" value="{{ $member->resumption_date ? $member->resumption_date->format('d/m/Y') : ''}}"
                                id="resumption_date" readonly>
                        </div>
                    </div>
                </div>
                @foreach ($data as $ts => $periode)
                    @php
                        $avg = $periode['cumul_coef'] ? number_format(($periode['cumul_notes'] ?? 0) / $periode['cumul_coef'], 2) : 0;
                        $committee = $periode['committee'];
                    @endphp
                    <div class="form-group row">
                        <div class="col-lg-12">
                            <input type="hidden" id="committee_id_{{ $ts }}"
                                name="committee_id[{{ $ts }}]" value="{{ $committee['id'] }}" />
                            <div class="accordion accordion-toggle-arrow" id="accordionFormFormation">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title" data-toggle="collapse" data-target="#collapseOne3">
                                            <i class="flaticon-file-1"></i> {{ $periode['periode'] }} ( Moyenne :
                                            {{ $avg }} - ECTS : {{ $periode['cumul_ects'] ?? '-' }} )
                                        </div>
                                    </div>
                                    <div id="collapseOne3" class="collapse show" data-parent="#accordionFormFormation">
                                        <div class="card-body">
                                            <div class="row form-group">
                                                <div class="col-lg-4">
                                                    <label for="comment_{{ $ts }}">Commentaire
                                                        Période</label>
                                                </div>
                                                <div class="col-lg-8">
                                                    <textarea id="comment_{{ $ts }}" name="comment[{{ $ts }}]" class="form-control">{{ $periode['committee']['comment'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <div class="col-lg-4">
                                                    <label for="next_todo_comment_{{ $ts }}">A faire à la
                                                        suite</label>
                                                </div>
                                                <div class="col-lg-8">
                                                    <textarea id="next_todo_comment_{{ $ts }}" name="next_todo_comment[{{ $ts }}]"
                                                        class="form-control">{{ $periode['committee']['next_todo_comment'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <div class="col-lg-4">
                                                    <label>A envoyer</label>
                                                </div>
                                                <div class="col-lg-8">
                                                    <div class="checkbox-inline">
                                                        <label class="checkbox">
                                                            <input type="checkbox" value="1"
                                                                name="send_transcript[{{ $ts }}]"
                                                                {{ $committee['send_transcript'] ? 'checked' : '' }}>
                                                            <span></span>Relevé de note période</label>
                                                    </div>
                                                    <div class="checkbox-inline">
                                                        <label class="checkbox">
                                                            <input type="checkbox" value="1"
                                                                name="send_comment_mail[{{ $ts }}]"
                                                                {{ $committee['send_comment_mail'] ? 'checked' : '' }}>
                                                            <span></span>Courrier de commentaire</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </form>
        @else
            <ul>
                @foreach ($errors as $error)
                    <li>
                        <p class="text-danger">{{ $error }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formCommittee').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i>
        Valider <span id="BTN_SAVE_COMMITTEE"></span></button>
</div>

<script>
    $(document).ready(function() {
        $("#formCommittee").validate({
            rules: {
                'effective_date': {
                    required: {
                        depends: function(element) {
                            return $("input#suspend").is(":checked") || $("input#stop").is(
                                ":checked") || $("input#cancel").is(":checked");
                        }
                    }
                },
                'resumption_date': {
                    required: {
                        depends: function(element) {
                            return $("input#suspend").is(":checked");
                        }
                    }
                },
            },
            messages: {},
            submitHandler: function(form) {
                if (!$('input[name="stop_reason"]:checked').length || $('input[name="stop_reason"]:checked').val() == '') {
                    saveCommitte(form);
                    return false;
                }
                Swal.fire({
                    title: false,
                    text: 'Les séances ultérieurs à la date de prise d’effet seront désaffectées pour cet étudiant. Voulez-vous continuer?',
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Oui",
                    cancelButtonText: "Non",
                }).then(function(result) {
                    if (result.value) {
                        saveCommitte(form);
                    }
                });
            }
        });

        function saveCommitte(form) {
            _showLoader('BTN_SAVE_COMMITTEE');
            var formData = $(form).serializeArray();
            $.ajax({
                type: 'POST',
                url: '/form/committee',
                data: formData,
                dataType: 'JSON',
                success: function(result) {
                    _hideLoader('BTN_SAVE_COMMITTEE');
                    if (result.success) {
                        _showResponseMessage('success', result.msg);
                        $('#modal_form_committee').modal('hide');
                        // resfreshJSTreeTemporelle();
                        _load_certifications_tab(3);
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                },
                error: function(error) {
                    _hideLoader('BTN_SAVE');
                    _showResponseMessage('error',
                        'Veuillez vérifier les champs du formulaire...'
                    );
                },
                complete: function(resultat, statut) {
                    _hideLoader('BTN_SAVE_COMMITTEE');
                }
            });
            return false;
        }

        $('[name="stop_reason"]').change(function(e) {
            const id_value = $(this).attr('id');
            if (id_value == 'no_action') {
                $(this).closest('label').hide();
                $('input#effective_date, input#resumption_date').closest('[class*=col-md-]').hide();
            } else {
                $('input#no_action').closest('label').show();
                $('input#effective_date').closest('[class*=col-md-]').show();
                if (id_value == 'suspend') {
                    $('input#resumption_date').closest('[class*=col-md-]').show();
                } else {
                    $('input#resumption_date').closest('[class*=col-md-]').hide();
                }
            }
        });

        $('.date_datepicker').datepicker({
            language: 'fr',
            rtl: KTUtil.isRTL(),
            format: 'dd/mm/yyyy',
            todayHighlight: true,
            orientation: "bottom left",
            startDate: 'now',
            templates: {
                leftArrow: '<i class="la la-angle-left"></i>',
                rightArrow: '<i class="la la-angle-right"></i>'
            }
        });
    });
</script>
