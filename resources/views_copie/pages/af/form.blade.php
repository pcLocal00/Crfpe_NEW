@php
$modal_title=($row)?'Edition AF':'Ajouter une AF';
$createdAt = $updatedAt = $deletedAt = '';
if($row){
$createdAt = ($row->created_at)?$row->created_at->format('d/m/Y H:i'):'';
$updatedAt = ($row->updated_at)?$row->updated_at->format('d/m/Y H:i'):'';
$deletedAt = ($row->deleted_at)?$row->deleted_at->format('d/m/Y H:i'):'';
}
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_af_title"><i class="flaticon-edit"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
    @if(!$row)
    <input type="hidden" value="{{json_encode($herited_account_codes)}}" id="herited_account_codes">
    <input type="hidden" value="{{json_encode($herited_analytic_codes)}}" id="herited_analytic_codes">
    @endif
</div>
<!-- Form : begin -->
<form id="formAf" class="form">
    <div class="modal-body" id="modal_form_af_body">
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
            <input type="hidden" name="id" id="INPUT_AF_ID" value="{{ ($row)?$row->id:0 }}" />
            <!-- begin::form -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        @php
                        $checkedIsActive = ($row && $row->is_active==1)?'checked=checked':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" value="1" name="is_active" {{ $checkedIsActive }}>
                                <span></span>Activer l'action de formation</label>
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
                    <div class="form-group">
                        <label>Produit <span class="text-danger">*</span> <span id="LOADER_PRODUCTS"></span></label>
                        <input type="hidden" id="selected_formation_id" value="{{ ($row)?$row->formation_id:0 }}">
                        <select class="form-control select2" id="formationsSelect" name="formation_id" required>
                            <option value="">Sélectionnez un produit de formation</option>
                        </select>
                        <script>
                        var selected_formation_id = $('#selected_formation_id').val();
                        var af_id = $("input[name='id']").val();
                        _showLoader('LOADER_PRODUCTS');
                        _loadDatasFormationsForSelectOptions('formationsSelect', selected_formation_id, 1);
                        $('#formationsSelect').select2();
                        _hideLoader('LOADER_PRODUCTS');
                        </script>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="title">Titre AF : <span class="text-danger">*</span></label>
                        <input class="form-control " type="text" name="title" value="{{ ($row)?$row->title:'' }}"
                            id="title" required />
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
                            @endphp
                            <input type="text" class="form-control" name="started_at" id="started_at_datepicker"
                                placeholder="Sélectionner une date" value="{{ $started_at }}" autocomplete="off"
                                required />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Date de fin <span class="text-danger">*</span></label>
                        <div class="input-group date">
                            @php
                            $ended_at ='';
                            if($row && $row->ended_at!=null){
                            $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$row->ended_at);
                            $ended_at = $dt->format('d/m/Y');
                            }
                            @endphp
                            <input type="text" class="form-control" name="ended_at" id="ended_at_datepicker"
                                placeholder="Sélectionner une date" value="{{ $ended_at }}" autocomplete="off"
                                required />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="la la-calendar-check-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Etat <span class="text-danger">*</span></label>
                        <select name="state" class="form-control " required>
                            <option value="">Sélectionnez</option>
                            @foreach ($states_af as $s)
                            @php
                            $selected_state = ($row && $row->state==$s["code"])?'selected':'';
                            @endphp
                            <option {{ $selected_state }} value="{{ $s['code'] }}">{{ $s["name"] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Statut <span class="text-danger">*</span></label>
                        <select name="status" class="form-control " required>
                            <option value="">Sélectionnez</option>
                            @foreach ($status_af as $status)
                            @php
                            $selected_status = ($row && $row->status == $status["code"])?'selected':'';
                            @endphp
                            <option {{ $selected_status }} value="{{ $status["code"] }}">
                                {{ $status["name"] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <!-- BEGIN::BPF -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>BPF : Objectif général </label>
                        <select id="select_bpf_main_objective" name="bpf_main_objective" class="form-control">
                            <option value="">Sélectionnez</option>
                            @foreach ($bpf_main_params as $p)
                            @php
                            $selected = ($row && $row->bpf_main_objective ===
                            $p["code"])?'selected':'';
                            @endphp
                            <option {{ $selected }} value="{{ $p["code"] }}">
                                {{ $p["name"] }}
                            </option>
                            @endforeach

                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>BPF : Spécialité de formation </label>
                        <select id="select_bpf_training_specialty" name="bpf_training_specialty" class="form-control">
                            <option value="">Sélectionnez</option>
                            @foreach ($bpf_speciality_params as $p)
                            @php
                            $selected = ($row && $row->bpf_training_specialty ===
                            $p["code"])?'selected':'';
                            @endphp
                            <option {{ $selected }} value="{{ $p["code"] }}">
                                {{ $p["name"] }}
                            </option>
                            @endforeach

                        </select>
                    </div>
                </div>
            </div>
            <!-- END::BPF -->

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Type de dispositif <span class="text-danger">*</span></label>
                        <select name="device_type" class="form-control " required>
                            <option value="">Sélectionnez</option>
                            @foreach ($device_types as $p)
                            <option value="{{ $p['code'] }}"
                                {{ (($row && $row->device_type==$p['code'])?'selected':'') }}>{{ $p['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="max_nb_trainees">Nombre de stagiaires max </label>
                        <input class="form-control " type="number" name="max_nb_trainees"
                            value="{{ ($row)?$row->max_nb_trainees:'' }}" id="max_nb_trainees" />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_days">Nombre de jours théoriques<span class="text-danger">*</span></label>
                        <input class="form-control " type="number" min="1" name="nb_days"
                            value="{{ ($row)?$row->nb_days:1 }}" id="nb_days" required />
                    </div>
                    <div class="form-group">
                        <label for="nb_hours">Nombre d'heures théoriques<span class="text-danger">*</span></label>
                        <input class="form-control " type="number" min="1" name="nb_hours"
                            value="{{ ($row)?$row->nb_hours:7 }}" id="nb_hours" required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nb_pratical_days">Nombre de jours pratiques</label>
                        <input class="form-control " type="number" min="1" name="nb_pratical_days"
                            value="{{ ($row)?$row->nb_pratical_days:0 }}" id="nb_pratical_days" />
                    </div>
                    <div class="form-group">
                        <label for="nb_pratical_hours">Nombre d'heures pratiques</label>
                        <input class="form-control " type="number" min="1" name="nb_pratical_hours"
                            value="{{ ($row)?$row->nb_pratical_hours:7 }}" id="nb_pratical_hours" />
                    </div>
                </div>
            </div>

            <!-- Nombre de groupe -->
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="accounting_code">Code comptable </label>
                        <input class="form-control " type="text" name="accounting_code"
                            value="{{ ($row)?$row->accounting_code:'' }}" id="accounting_code" />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="analytical_code">Code analytique </label>
                        <input class="form-control " type="text" name="analytical_code"
                            value="{{ ($row)?$row->analytical_code:'' }}" id="analytical_code" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nb_groups">Nombre de groupe </label>
                        <input class="form-control " type="number" name="nb_groups"
                            value="{{ ($row)?$row->nb_groups:'' }}" id="nb_groups" />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        @php
                        $checkedIsUknownDate = ($row && $row->is_uknown_date===1)?'checked=checked':'';
                        @endphp
                        <div class="checkbox-inline">
                            <label class="checkbox">
                                <input type="checkbox" id="is_uknown_date_checkbox" value="1" name="is_uknown_date"
                                    {{ $checkedIsUknownDate }}>
                                <span></span>L'AF à créer n'a pas toutes ses dates connues actuellement</label>
                        </div>
                    </div>

                    @if($row==null)
                    <!--BEGIN::DEFAULT SESSION -->
                    <div class="accordion accordion-solid accordion-toggle-plus mb-4 d-none" id="accordionSession">
                        <div class="card">
                            <div class="card-header" id="headingOne6">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseSession"
                                    aria-expanded="true">
                                    <i class="flaticon-pie-chart-1"></i> Session <span
                                        id="LOADER_FORMATIONS_SESSIONS"></span>
                                </div>
                            </div>
                            <div id="collapseSession" class="collapse show" data-parent="#accordionSession" style="">
                                <div class="card-body">

                                    <!-- Cas de formation diplomantes -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <!--begin: Datatable-->
                                            <table class="table table-bordered table-checkable" id="dt_pf_formations">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>Infos</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                            <!--end: Datatable-->
                                        </div>
                                    </div>
                                    <!-- Cas de formation diplomantes -->

                                    <!--begin::formsession-->
                                    <!-- <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="nb_dates_to_program">Nombre de dates connues à programmer
                                                </label>
                                                <input class="form-control " type="number" name="nb_dates_to_program"
                                                    value="1" id="nb_dates_to_program" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="nb_total_dates_to_program">Nombre de dates totales à
                                                    programmer </label>
                                                <input class="form-control " type="number"
                                                    name="nb_total_dates_to_program" value="1"
                                                    id="nb_total_dates_to_program" required />
                                            </div>
                                        </div>
                                    </div> -->

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <!-- BEGIN::Type de sessions -->
                                            <!-- <div class="form-group">
                                                <div class="radio-list">
                                                    @foreach ($session_types as $st)
                                                    @php
                                                    $checked =
                                                    ($st["code"]=="AF_SESSION_TYPE_SCSS")?'checked="checked"':'';
                                                    @endphp
                                                    <label class="radio">
                                                        <input type="radio" {{ $checked }} value="{{ $st['code'] }}"
                                                            name="session_type" />
                                                        <span></span>
                                                        {{ $st['name'] }}
                                                    </label>
                                                    @endforeach
                                                </div>
                                            </div> -->
                                            <!-- END::Type de sessions -->
                                        </div>
                                        <!-- <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Modèle de planification des séances </label>
                                                <select id="select_planning_template_id" name="planning_template_id"
                                                    class="form-control ">
                                                    <option value="">Sélectionnez</option>
                                                    @foreach ($templates as $tpl)
                                                    <option value="{{ $tpl['id'] }}">{{ $tpl['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> -->
                                    </div>



                                    <!--end::formsession-->

                                </div>
                            </div>
                        </div>
                    </div>
                    <!--END::DEFAULT SESSION -->
                    @endif
                </div>
            </div>

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
                            <option value="Chez le client"
                                {{($row && $row->training_site == 'Chez le client')?'selected':''}}>Chez le client
                            </option>
                            <option value="OTHER" {{($row && $row->training_site == 'OTHER')?'selected':''}}>Autre
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            
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
<!-- <script src="{{ asset('custom/js/form-af.js?v=1') }}"></script> -->
<script>
@if(!$row)
var herited_account_codes = JSON.parse($('#herited_account_codes').val());
var herited_analytic_codes = JSON.parse($('#herited_analytic_codes').val());
var overridden_account_codes = false;
var overridden_analytic_codes = false;
@endif

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
$("#formAf").validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
        _showLoader('BTN_SAVE');
        var formData = $(form).serializeArray();
        $.ajax({
            type: 'POST',
            url: '/form/af',
            data: formData,
            dataType: 'JSON',
            success: function(result) {
                _hideLoader('BTN_SAVE');
                if (result.success) {
                    _showResponseMessage('success', result.msg);
                    $('#modal_form_af').modal('hide');
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
                if ($.fn.DataTable.isDataTable('#dt_afs')) {
                    _reload_dt_afs();
                } else {
                    location.reload();
                }
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
$('#ended_at_datepicker').datepicker({
    language: 'fr',
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
var _getTitleData = function() {
    var data = $("#formationsSelect option:selected").text();
    var val = $("#formationsSelect option:selected").val();
    if (val) {
        $("#title").val(data);
    }
}

@if(!$row)
var _heritePfCodes = function() {
    const id_pf = $("#formationsSelect option:selected").val();
    var h_account_code = '', h_analytic_code = '';
    
    if (!overridden_account_codes) {
        if (herited_account_codes.hasOwnProperty(id_pf)) {
            h_account_code = herited_account_codes[id_pf];
        }
        $('[name=accounting_code]').val(h_account_code);
    }
    if (!overridden_analytic_codes) {
        if (herited_analytic_codes.hasOwnProperty(id_pf)) {
            h_analytic_code = herited_analytic_codes[id_pf];
        }
        $('[name=analytical_code]').val(h_analytic_code);
    }
}
@endif

var _updateInfosFromPf = function() {
    var af_id = $('#INPUT_AF_ID').val();
    var pf_id = $("#formationsSelect option:selected").val();
    if (af_id == 0 && pf_id > 0) {
        $.ajax({
            url: '/get/infos/pf/' + pf_id,
            type: 'GET',
            dataType: 'json',
            success: function(result, status) {
                if (result.success) {
                    $('#max_nb_trainees').val(result.max_availability);
                    $('#nb_days').val(result.nb_days);
                    $('#nb_hours').val(result.nb_hours);
                    $('#nb_pratical_days').val(result.nb_pratical_days);
                    $('#nb_pratical_hours').val(result.nb_pratical_hours);
                    $('#select_bpf_main_objective').val(result.bpf_main_objective);
                    $('#select_bpf_training_specialty').val(result.bpf_training_specialty);
                }
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    }
}

@if(!$row)
$('[name=accounting_code]').on('change', function(e) {
    overridden_account_codes = true;
});
$('[name=analytical_code]').on('change', function(e) {
    overridden_analytic_codes = true;
});
@endif

$('#formationsSelect').on('change', function() {
    _getTitleData();
    _updateInfosFromPf();
    @if(!$row)
    _heritePfCodes();
    @endif
});
var id = $('#INPUT_AF_ID').val();
if (id == 0) {
    _getTitleData();
}
$('#nb_days').on("input", function() {
    nb_days = this.value;
    calculateHours(nb_days, 'nb_hours');
});
/* SESSION PARTS */
showFormSession();
$('#is_uknown_date_checkbox').change(function() {
    showFormSession();
});

function showFormSession() {
    //accordionSession
    if ($('#is_uknown_date_checkbox').is(':checked')) {
        $('#accordionSession').addClass('d-none');
        required = false;
    } else {
        $('#accordionSession').removeClass('d-none');
        required = true;
    }
    console.log(required);
    $("#nb_dates_to_program").prop('required', required);
    $("#select_planning_template_id").prop('required', required);
}
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

/* Selection pour creation des sessions */
//var pf_id = $("#formationsSelect option:selected").val();
//if(pf_id>0){
var dt_pf_formations = $('#dt_pf_formations');
// begin first table
dt_pf_formations.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    searching: false,
    paging: false,
    ordering: false,
    info: false,
    lengthMenu: [5, 10, 25, 50],
    pageLength: 25,
    headerCallback: function(thead, data, start, end, display) {
        thead.getElementsByTagName('th')[0].innerHTML = `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="group-checkable"/>
                            <span></span>
                        </label>`;
    },
    columnDefs: [{
        targets: 0,
        width: '20px',
        className: 'dt-left',
        orderable: false,
    }],
});
dt_pf_formations.on('change', '.group-checkable', function() {
    var set = $(this).closest('table').find('td:first-child .checkable');
    var checked = $(this).is(':checked');

    $(set).each(function() {
        if (checked) {
            $(this).prop('checked', true);
            $(this).closest('tr').addClass('active');
        } else {
            $(this).prop('checked', false);
            $(this).closest('tr').removeClass('active');
        }
    });
});
dt_pf_formations.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});
//}

$('#formationsSelect').on('change', function() {
    _loadPfFormations();
});

var _loadPfFormations = function() {
    var dt_pf_formations = $('#dt_pf_formations');
    var pf_id = $("#formationsSelect option:selected").val();
    if (pf_id > 0) {
        $('#LOADER_FORMATIONS_SESSIONS').html('<i class="fa fa-spinner fa-spin text-primary"></div>');
        var table = 'dt_pf_formations';
        $.ajax({
            type: "POST",
            dataType: 'json',
            data: {
                pagination: {
                    perpage: 50,
                }
            },
            url: '/api/sdt/select/pf_formation/to/sessions/' + pf_id,
            success: function(response) {
                if (response.data.length == 0) {
                    $('#' + table).dataTable().fnClearTable();
                    $('#LOADER_FORMATIONS_SESSIONS').html('');
                    return 0;
                }
                $('#' + table).dataTable().fnClearTable();
                $("#" + table).dataTable().fnAddData(response.data, true);
                $('#LOADER_FORMATIONS_SESSIONS').html('');
            },
            error: function() {
                $('#' + table).dataTable().fnClearTable();
            }
        }).done(function(data) {});
    }
    return false;
}
</script>