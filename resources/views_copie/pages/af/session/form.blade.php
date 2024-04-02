@php
$modal_title=($row)?'Edition session':'Ajouter une session';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_session_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form : begin -->
<form id="formSession" class="form">
    <div class="modal-body" id="modal_form_session_body">
        <div data-scroll="true" data-height="600">
            @csrf
            @if($row)
            <!-- Infos date : begin -->
            <div class="form-group row">
                <div class="col-lg-12">
                    @if($createdAt)<span class="label label-inline label-outline-info mr-2">Crée le :
                        {{ $createdAt }}</span>@endif
                    @if($updatedAt)<span class="label label-inline label-outline-info mr-2">Modifié le :
                        {{ $updatedAt }}</span>@endif
                </div>
                @if($deletedAt)
                <div class="col-lg-12 mt-5">
                    <div class="alert alert-custom alert-outline-info fade show mb-0" role="alert">
                        <div class="alert-icon"><i class="flaticon-warning"></i></div>
                        <div class="alert-text">Archivé le : {{ $deletedAt }}</div>
                    </div>
                </div>
                @endif
            </div>
            <!-- Infos date : end -->
            <div class="separator separator-dashed my-5"></div>
            @endif
            <input type="hidden" name="id" id="INPUT_SESSION_ID" value="{{ ($row)?$row->id:0 }}" />
            <input type="hidden" name="af_id" value="{{ ($row)?$row->af_id:$af_id }}" />
            <!-- begin::form -->

            <div class="row">
                
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Etat <span class="text-danger">*</span></label>
                        <select name="state" class="form-control " required>
                            <option value="">Sélectionnez</option>
                            @foreach ($states_af as $s)
                            @php
                            $selected_state = ($row && $row->state==$s["code"])?'selected':'';
                            @endphp
                            <option {{ $selected_state }} value="{{ $s["code"] }}">{{ $s["name"] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <label>Mode session </label>
                        <select name="session_mode" class="form-control " required>
                            <option value="SESSION" <?=($row && $row->session_mode=='SESSION')?'selected':''?>>Session</option>
                            <option value="HIERARCHIE" <?=($row && $row->session_mode=='HIERARCHIE')?'selected':''?>>Hiérarchie</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for=""></label>
                        @php
                        $checkedIsUknownDate = ($row && $row->is_uknown_date===1)?'checked=checked':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="is_uknown_date" {{ $checkedIsUknownDate }}>
                                <span></span>Pas de dates connues actuellement</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        @php
                        $checkedIsActive = ($row && $row->is_active===1)?'checked=checked':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="is_active" {{ $checkedIsActive }}>
                                <span></span>Diffuser la session</label>
                        </div>
                    </div>
                </div>
                @if($row)
                <div class="col-lg-6">
                    <div class="form-group">
                        <span class="label label-md label-outline-primary label-pill label-inline">Code :
                            {{ $row->code }}</span>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-lg-12">
                    @php
                    $defaultTitle = ($af && $af->title)?$af->title:'';
                    @endphp
                    <div class="form-group">
                        <label for="title">Titre : <span class="text-danger">*</span></label>
                        <input class="form-control " type="text" name="title"
                            value="{{ ($row)?$row->title:$defaultTitle }}" id="title" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Date de début <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $started_at ='';
                            if($row && $row->started_at!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$row->started_at);
                            $started_at = $dt->format('d/m/Y');
                            }
                            $defaultStartedAt ='';
                            if($af && $af->started_at!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$af->started_at);
                            $defaultStartedAt = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control" name="started_at" id="started_at_datepicker"
                                placeholder="Sélectionner une date"
                                value="{{ ($started_at)?$started_at:$defaultStartedAt }}" autocomplete="off" required />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    @php
                    $ended_at ='';
                    if($row && $row->ended_at!=null){
                    $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$row->ended_at);
                    $ended_at = $dt->format('d/m/Y');
                    }
                    @endphp
                    <div class="form-group">
                        <label>Date de fin</label>
                        <input type="text" class="form-control" readonly="readonly" value="{{ $ended_at }}">
                    </div>
                </div>
            </div>

            @php
                $af_nb_days = ($af && $af->nb_days)?$af->nb_days:0;
                $af_nb_hours = ($af && $af->nb_hours)?$af->nb_hours:0;
            @endphp

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_days">Nb de jours <span class="text-danger">*</span></label>
                        <input class="form-control " type="number" min="0" name="nb_days"
                            value="{{ ($row)?$row->nb_days:$af_nb_days }}" id="nb_days" required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_hours">Nb d'heures <span class="text-danger">*</span></label>
                        <input class="form-control " type="number" min="0" name="nb_hours"
                            value="{{ ($row)?$row->nb_hours:$af_nb_hours }}" id="nb_hours" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_dates_to_program">Nombre de dates connues à programmer </label>
                        <input class="form-control " type="number" name="nb_dates_to_program"
                            value="{{ ($row)?$row->nb_dates_to_program:1 }}" id="nb_dates_to_program" required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_total_dates_to_program">Nombre de dates totales à programmer </label>
                        <input class="form-control " type="number" name="nb_total_dates_to_program"
                            value="{{ ($row)?$row->nb_total_dates_to_program:1 }}" id="nb_total_dates_to_program" required />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <!-- BEGIN::Type de sessions -->
                    <div class="form-group">
                        <div class="radio-list">
                            @foreach ($session_types as $st)
                            @php
                            $checked = ($row && $row->session_type == $st["code"])?'checked="checked"':'';
                            @endphp
                            <label class="radio">
                                <input type="radio" {{ $checked }} value="{{ $st['code'] }}" name="session_type" />
                                <span></span>
                                {{ $st['name'] }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <!-- END::Type de sessions -->
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Modèle de planification des séances </label>
                        <select name="planning_template_id" class="form-control ">
                            <option value="">Sélectionnez</option>
                            @foreach ($templates as $tpl)
                            @php
                            $selected = ($row && $row->planning_template_id == $tpl["id"])?'selected':'';
                            @endphp
                            <option {{ $selected }} value="{{ $tpl["id"] }}">{{ $tpl["name"] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for=""></label>
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="is_duplicate" id="is_duplicate" onclick="checkDuplicate();">
                                <span></span>Mode Duplication</label>
                        </div>
                       
                    </div>
                   
                </div>
            </div>
            <div class="row" id="mode">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_de_seances">Nombre de séances à la même heure</label>
                        <input class="form-control " type="number" name="nb_de_seances"
                            value="{{ ($row)?$row->nb_de_seances:1 }}" id="nb_de_seances" required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_de_decoupages">Nombre de découpages par jour</label>
                        <input class="form-control " type="number" name="nb_de_decoupages"
                            value="{{ ($row)?$row->nb_de_decoupages:1 }}" id="nb_de_decoupages" required />
                    </div>
                </div>
            </div>
                <!-- </div> -->
            <!-- </div> -->


            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="training_site_select">Lieu de formation de l'AF <span
                                class="text-danger">*</span></label>
                        <select name="training_site" id="training_site_select" class="form-control " required>
                            @foreach ($lieux as $lieu)
                                @php
                                $selected_lieu = ($row && $row->training_site == $lieu["name"])?'selected':'';
                                @endphp
                            <option {{ $selected_lieu }} value="{{ $lieu['name'] }}">
                                {{ $lieu["name"] }}</option>
                            @endforeach
                            <option value="Chez le client" {{($row && $row->training_site == 'Chez le client')?'selected':''}}>Chez le client</option>
                            <option value="OTHER" {{($row && $row->training_site == 'OTHER')?'selected':''}}>Autre</option>
                        </select>
                    </div>
                </div>
            </div>

            @if($row and $row->is_evaluation)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="ects">Mode d'évaluation :</label>
                            <input type="hidden" name="is_evaluation" value="1">
                            <select name="evaluation_mode" class="form-control">
                                <option value="">--Séléctionner--</option>
                                @foreach($evaluation_modes as $key => $name)
                                    <option value="{{$key}}" {{($key == $row->evaluation_mode) ? 'selected':''}}>{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="ects">ECTS Module :</label>
                            <input class="form-control " type="number" min="1" name="ects"
                                value="{{ ($row)?$row->ects:'' }}" id="ects" />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="coefficient">Coéfficient :</label>
                            <input class="form-control " type="number" min="1" name="coefficient"
                                value="{{ ($row)?$row->coefficient:'' }}" id="coefficient" />
                        </div>
                    </div>
                </div>
            @endif

            <div class="row" id="BLOCK_OTHER_TRAINING_SITE" style="display:none;">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="other_training_site">Veuillez saisir le lieu de formation</label>
                        <textarea class="form-control" id="other_training_site" name="other_training_site"
                            rows="2">{{ ($row)?$row->other_training_site:'' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description"
                            rows="3">{{ ($row)?$row->description:'' }}</textarea>
                    </div>
                </div>
            </div>
            <!--end:: form-->
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler</button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE"></span></button>
    </div>
</form>
<!-- Form  : end -->
<!-- <script src="{{ asset('custom/js/form-session.js?v=1') }}"></script> -->
<script>
ClassicEditor.create(document.querySelector("#description"))
    .then(editor => {})
    .catch(error => {});
$('[data-scroll="true"]').each(function() {
    var el = $(this);
    KTUtil.scrollInit(this, {
        mobileNativeScroll: true,
        handleWindowResize: true,
        rememberPosition: (el.data('remember-position') == 'true' ? true : false)
    });
});
$("#formSession").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/session',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $("#NAV15").click();
                    $('#modal_form_session').modal('hide');
                } else {
                    _showResponseMessage('error', result.msg);
                }
            },
            error: function(error) {
                _hideLoader('BTN_SAVE');
                _showResponseMessage('error', 'Veuillez vérifier les champs du formulaire...');
            },
            complete: function(resultat, statut) {
                _hideLoader('BTN_SAVE');
                _load_sessions();
                _updateAfUknownDate();
            }
        });
        return false;
    }
});
$('#started_at_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
$('#nb_days').on("input", function() {
    nb_days = this.value;
    calculateHours(nb_days,'nb_hours');
});

$('#training_site_select').on('change', function() {
    _refresh_other_training_site();
});
_refresh_other_training_site();

function _refresh_other_training_site() {
    var v = $('#training_site_select').val();
    if (v == 'OTHER') {
        $('#BLOCK_OTHER_TRAINING_SITE').show();
    } else {
        $('#other_training_site').val('');
        $('#BLOCK_OTHER_TRAINING_SITE').hide();
    }
}

$("#mode").hide();
function checkDuplicate()
{
  if (document.getElementById('is_duplicate').checked) 
  {
        $("#mode").show();
  } else {
        $("#mode").hide();
  }
}

</script>