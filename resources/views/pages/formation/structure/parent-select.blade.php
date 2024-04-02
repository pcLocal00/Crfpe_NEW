
<div class="card mb-2">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <input type="hidden" value="{{ ($row)?$row->product_type:'' }}" id="INPUT_SELECTED_PRODUCT_TYPE">
                    <label>Type de formation :<span id="LOADER_PRODUCT_TYPE"></span></label>
                    <select name="product_type" id="SELECT_PRODUCT_TYPE" class="form-control select2"></select>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-group">
                    <input type="hidden" value="{{ ($row)?$row->parent_id:0 }}" id="INPUT_SELECTED_PARENT">
                    <label>Produit parent :<span id="LOADER_PARENTS"></span></label>
                    <select name="parent_id" id="SELECT_PARENTS_PRODUCT" class="form-control select2">
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="timestructure_sort">Ordre temporelle :</label>
                    <input class="form-control " type="number" min="0" name="timestructure_sort" value="{{ ($row)?$row->timestructure_sort:1 }}"
                        id="timestructure_sort" />
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="sort">Ordre hiérarchique :</label>
                    <input class="form-control " type="number" min="0" name="sort" value="{{ ($row)?$row->sort:0 }}"
                        id="sort" />
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="nb_sessiondates">Nb de date à créer pour une session :</label>
                    <input class="form-control " type="number" min="0" name="nb_sessiondates"
                        value="{{ ($row)?$row->nb_sessiondates:1 }}" id="nb_sessiondates" />
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="nb_session_duplication">Nb de session à créer :</label>
                    <input class="form-control " type="number" min="0" name="nb_session_duplication"
                        value="{{ ($row)?$row->nb_session_duplication:1 }}" id="nb_session_duplication" />
                </div>
            </div>
        </div>

        <!-- certification -->
        <div class="row">
            <div class="col-md-12">
                <div class="accordion  accordion-toggle-arrow mb-2" id="accordionEvaluation">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title" data-toggle="collapse" data-target="#collapseEvaluation">
                                <i class="flaticon-interface-10"></i> Evaluation
                            </div>
                        </div>
                        <div id="collapseEvaluation" class="collapse show"
                            data-parent="#accordionEvaluation">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            @php
                                                $checkedEvaluation = ($row && $row->is_evaluation==1)?'checked=checked':'';
                                            @endphp
                                            <div class="checkbox-inline">
                                                <label class="checkbox">
                                                    <input type="checkbox" value="1" name="is_evaluation"
                                                        {{ $checkedEvaluation }}>
                                                    <span></span>Evaluation</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="ects">Mode d'évaluation :</label>
                                            <select name="evaluation_mode" class="form-control select2">
                                                @foreach($evaluation_modes as $key => $name)
                                                    <option value="{{$key}}" {{($row && $key == $row->evaluation_mode) ? 'selected':''}}>{{$name}}</option>
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
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- certification -->

        <!-- structure temporelle -->
        <div class="row">
            <div class="col-md-12">
                <div class="accordion  accordion-toggle-arrow" id="accordionTimeStructure">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title" data-toggle="collapse" data-target="#collapseTimeStructure">
                                <i class="flaticon-map"></i> Structure temporelle
                            </div>
                        </div>
                        <div id="collapseTimeStructure" class="collapse show"
                            data-parent="#accordionTimeStructure">
                            <div class="card-body">
                                <input type="hidden" id="timestructure_id" name="timestructure_id" value="{{ ($row)?$row->timestructure_id:0 }}" />
                                <div id="timestructures_tree" class="tree-demo"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- structure temporelle -->

    </div>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2();
});
var selected_parent_id = $('#INPUT_SELECTED_PARENT').val();
var pf_id = $('#INPUT_ID_FORMATION').val();
_loadParentProductsForSelectOptions('SELECT_PARENTS_PRODUCT', selected_parent_id, pf_id);

function _loadParentProductsForSelectOptions(select_id, selected_parent_id, pf_id) {
    _showLoader('LOADER_PARENTS');
    $('#' + select_id).append('<option value="">--</option>');
    $.ajax({
        url: '/api/select/options/products/' + pf_id,
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_parent_id != 0 && selected_parent_id != '') {
            $('#' + select_id + ' option[value="' + selected_parent_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_PARENTS');
    });
}

var selected_id = $('#INPUT_SELECTED_PRODUCT_TYPE').val();
_loadTypesProductsForSelectOptions('SELECT_PRODUCT_TYPE', selected_id);

function _loadTypesProductsForSelectOptions(select_id, selected_id) {
    _showLoader('LOADER_PRODUCT_TYPE');
    //$('#' + select_id).append('<option value="">--</option>');
    $.ajax({
        url: '/api/select/options/type_products',
        dataType: 'json',
        success: function(response) {
            var array = response;
            if (array != '') {
                for (i in array) {
                    $('#' + select_id).append("<option value='" + array[i].id + "'>" + array[i].name +
                        "</option>");
                }
            }
        },
        error: function(x, e) {}
    }).done(function() {
        if (selected_id != 0 && selected_id != '') {
            $('#' + select_id + ' option[value="' + selected_id + '"]').attr('selected', 'selected');
        }
        _hideLoader('LOADER_PRODUCT_TYPE');
    });
}

var pf_id = $('#INPUT_ID_FORMATION').val();
$('#timestructures_tree').jstree({
    "core": {
        "multiple": false,
        "themes": {
            "responsive": true
        },
        //"check_callback" : false,
        'data': {
            'url': function(node) {
                return '/get/tree/time/structure/' + pf_id+"/1";
            },
            'data': function(node) {
                return {
                    'parent': node.id
                };
            }
        },
    },
    "checkbox": {
        "three_state": false,
    },
    "plugins": ["state", "checkbox"]
});
//A la fin du chargement
$('#timestructures_tree').bind("ready.jstree", function () {initializeTreeTimeStructureSelections();}).jstree();
function initializeTreeTimeStructureSelections(){
	var instance = $('#timestructures_tree').jstree(true);
	instance.deselect_all();
    instance.select_node('S'+$("#timestructure_id").val());
}
</script>
