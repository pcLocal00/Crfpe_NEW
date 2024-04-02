@php
$modal_title=($row)?'Edition produit de formation':'Ajouter un produit de formation';
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_formation_title"><i class="flaticon-edit"></i> {{ $modal_title }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<!-- Form formation : begin -->

<div class="modal-body" id="modal_form_formation_body">
    <div data-scroll="true" data-height="600">
        <form id="formFormation" class="form">
            @csrf
            <input type="hidden" id="INPUT_ID_FORMATION" name="id" value="{{ ($type==0)?$row->id:0 }}" />

            <div class="form-group row">
                <div class="col-lg-12">
                    <div class="accordion  accordion-toggle-arrow" id="accordionFormFormation">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseOne3">
                                    <i class="flaticon-file-1"></i> Détails {{ ($row)?'- Code : '.$row->code:'' }}
                                </div>
                            </div>
                            <div id="collapseOne3" class="collapse show" data-parent="#accordionFormFormation">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                @php
                                                $checkedAutorizeAf = ($row &&
                                                $row->autorize_af===1)?'checked=checked':'';
                                                @endphp
                                                <div class="checkbox-inline">
                                                    <label class="checkbox">
                                                        <input type="checkbox" value="1" name="autorize_af"
                                                            {{ $checkedAutorizeAf }}>
                                                        <span></span>Autoriser la création d'action de formation</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Titre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control " name="title"
                                                    value="{{ ($row&&$type==0)?$row->title:'' }}" required />
                                            </div>

                                        </div>
                                    </div>

                                    <!-- <div class="row">
                                        <div class="col-lg-12">

                                            <div class="form-group">
                                                <label>Code <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" placeholder=""
                                                        id="ID_CODE_PF" name="code" value="{{ ($row)?$row->code:'' }}"
                                                        required>
                                                    <div class="input-group-append">
                                                        <button type="button" onclick="_generateCodeFormation()"
                                                            data-toggle="tooltip" title="Générer un code"
                                                            class="btn btn-icon btn-outline-primary"><span
                                                                id="BTN_GERERATE_CODE_PF"><i
                                                                    class="flaticon2-reload"></i></span></button>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div> -->

                                    <!-- BEGIN::BPF -->
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>BPF : Objectif général </label>
                                                <select name="bpf_main_objective" class="form-control">
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
                                                <select name="bpf_training_specialty" class="form-control">
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
                                                <label for="max_availability">Nbre maximum de participants </label>
                                                <input class="form-control " type="number" name="max_availability"
                                                    value="{{ ($row)?$row->max_availability:'' }}"
                                                    id="max_availability" />
                                            </div>

                                            <div class="form-group">
                                                <label for="nb_days">Nombre de jours (Théoriques)</label>
                                                <input class="form-control " type="number" min="1" name="nb_days"
                                                    value="{{ ($row)?$row->nb_days:1 }}" id="nb_days" required />
                                            </div>

                                            <div class="form-group">
                                                <label for="nb_hours">Nombre d'heures (Théoriques)</label>
                                                <input class="form-control " type="number" min="1" name="nb_hours"
                                                    value="{{ ($row)?round($row->nb_hours,1):7 }}" id="nb_hours"
                                                       step="0.1" required />
                                            </div>

                                            <div class="form-group">
                                                <label for="nb_pratical_days">Nombre de jours (Pratiques)</label>
                                                <input class="form-control " type="number" min="0"
                                                    name="nb_pratical_days"
                                                    value="{{ ($row)?$row->nb_pratical_days:0 }}"
                                                    id="nb_pratical_days" />
                                            </div>

                                            <div class="form-group">
                                                <label for="nb_pratical_hours">Nombre d'heures (Pratiques)</label>
                                                <input class="form-control " type="number" min="0"
                                                    name="nb_pratical_hours"
                                                    value="{{ ($row)?round($row->nb_pratical_hours,1):0 }}"
                                                    id="nb_pratical_hours"
                                                       step="0.1"/>
                                            </div>

                                        </div>
                                        <div class="col-lg-6">

                                            <div class="form-group">
                                                <label>Type </label>
                                                <select name="param_type_id" class="form-control "
                                                    id="SELECT_TYPE_FORMATION">

                                                    @foreach ($type_params as $type)
                                                    @php
                                                    $selected_type = (count($formationParams)>0 &&
                                                    $formationParams->contains($type["id"]))?'selected':'';
                                                    @endphp
                                                    <option {{ $selected_type }} value="{{ $type["id"] }}">
                                                        {{ $type["name"] }}
                                                    </option>
                                                    @endforeach

                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Etat </label>
                                                <select name="param_state_id" class="form-control ">
                                                    @foreach ($states_params as $state)
                                                    @php
                                                    $selected_state = (count($formationParams)>0 &&
                                                    $formationParams->contains($state["id"]))?'selected':'';
                                                    @endphp
                                                    <option {{ $selected_state }} value="{{ $state["id"] }}">
                                                        {{ $state["name"] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Statut </label>
                                                <select name="param_status_id" class="form-control ">
                                                    @foreach ($status_params as $status)
                                                    @php
                                                    $selected_status = (count($formationParams)>0 &&
                                                    $formationParams->contains($status["id"]))?'selected':'';
                                                    @endphp
                                                    <option {{ $selected_status }} value="{{ $status["id"] }}">
                                                        {{ $status["name"] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Code comptable </label>
                                                <input type="text" class="form-control" name="accounting_code"
                                                    value="{{ ($row)?$row->accounting_code:'' }}" />
                                            </div>
                                            <div class="form-group">
                                                <label>Code analytique </label>
                                                <input type="text" class="form-control" name="analytical_codes"
                                                    value="{{ ($row)?$row->analytical_codes:'' }}" />
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

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Begin::parent -->
            <div id="BLOCK_PARENT_PRODUCT">
            </div>
            <!-- End::parent -->

            <!-- Begin::Structure hérarchiques -->
            <div class="form-group row">
                <div class="col-lg-12" id="BLOCK_STRUCTURE_HERARCHIQUE">

                </div>
            </div>
            <!-- End::Structure hérarchiques -->

            <!-- Begin categories -->
            <div class="form-group row">
                <div class="col-lg-12">
                    <div class="accordion  accordion-toggle-arrow" id="accordionFormFormationCategories">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title" data-toggle="collapse" data-target="#collapseOne4">
                                    <i class="flaticon-map"></i> Catégories
                                </div>
                            </div>
                            <div id="collapseOne4" class="collapse show"
                                data-parent="#accordionFormFormationCategories">
                                <div class="card-body">
                                    <input type="hidden" id="categorie_id" name="categorie_id"
                                        value="{{ ($row)?$row->categorie_id:0 }}" />
                                    <div id="categorie_tree" class="tree-demo"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End categories -->



        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
        Annuler</button>
    <button type="button" onclick="$('#formFormation').submit();" class="btn btn-sm btn-primary"><i
            class="fa fa-check"></i>
        Valider <span id="BTN_SAVE_FORMATION"></span></button>
</div>

<!-- Form formation : end -->
<script src="{{ asset('custom/js/form-formation.js?v=0') }}"></script>
<script>
$('#SELECT_TYPE_FORMATION').on('change', function() {
    _load_hierarchical_structure();
});
_load_hierarchical_structure();

function _load_hierarchical_structure() {
    var formation_id = $('#INPUT_ID_FORMATION').val();
    if (formation_id > 0) {
        var formation_type = $('#SELECT_TYPE_FORMATION').val();
        var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
        //console.log(formation_type);
        if (formation_type == 2) {
            $('#BLOCK_STRUCTURE_HERARCHIQUE').html(spinner);
            $.ajax({
                url: '/get/hierarchical/structure/' + formation_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#BLOCK_STRUCTURE_HERARCHIQUE').html(html);
                },
                error: function(result, status, error) {},
                complete: function(result, status) {}
            });
        } else {
            $('#BLOCK_STRUCTURE_HERARCHIQUE').html('');
        }
    }
}

_load_parent_product();
function _load_parent_product() {
    var formation_id = $('#INPUT_ID_FORMATION').val();
    var formation_type = $('#SELECT_TYPE_FORMATION').val();
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    if (formation_type == 2) {
        $('#BLOCK_PARENT_PRODUCT').html(spinner);
        $.ajax({
            url: '/get/parent/product/view/' + formation_id,
            type: 'GET',
            dataType: 'html',
            success: function(html, status) {
                $('#BLOCK_PARENT_PRODUCT').html(html);
            },
            error: function(result, status, error) {},
            complete: function(result, status) {}
        });
    } else {
        $('#BLOCK_PARENT_PRODUCT').html('');
    }
}
</script>
