@if($viewtype=='overview')
<!--begin::Card-->
<div class="card card-custom">
    <!--begin::Header-->
    <div class="card-header py-3">
        <div class="card-title align-items-start flex-column">
            <h3 class="card-label font-weight-bolder text-dark">Aperçu</h3>
            <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
        </div>
        <div class="card-toolbar">
        </div>
    </div>
    <!--end::Header-->
    <!--begin::Card-body-->
    <div class="card-body">
        <h4>Aperçu ...</h4>
    </div>
</div>
@endif
@if($viewtype=='ficheTechnique')
<!--begin::Card-->
<div class="card card-custom">
    <!--begin::Header-->
    <div class="card-header py-3">
        <div class="card-title align-items-start flex-column">
            <h3 class="card-label font-weight-bolder text-dark">Fiche technique</h3>
            <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
        </div>
        <div class="card-toolbar">
            <button onclick="_formSheet({{ $row->id }},{{ $default_sheet_id }})"
                class="btn btn-sm btn-icon btn-light-primary">
                <i class="{{ ($default_sheet_id>0)?'flaticon-edit':'flaticon2-add-1' }}"></i>
            </button>
        </div>
    </div>
    <!--end::Header-->
    <!--begin::Card-body-->
    <div class="card-body" id="BLOCK_DEFAULT_SHEET">

    </div>
</div>
<script>
var formation_id = $('#VIEW_INPUT_PF_ID_HELPER').val();
_viewDefaultSheet(formation_id, 'BLOCK_DEFAULT_SHEET');
</script>
@endif
@if($viewtype=='ficheExploitation')
<!--begin::Card-->
<div class="card card-custom">
    <!--begin::Header-->
    <div class="card-header py-3">
        <div class="card-title align-items-start flex-column">
            <h3 class="card-label font-weight-bolder text-dark">Fiche Exploitation</h3>
            <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
        </div>
    </div>
    <!--end::Header-->
    <!--begin::Card-body-->
    <div class="card-body">

    </div>
</div>
@endif
@if($viewtype=='versions')
<!--begin::Card-->
<div class="card card-custom">
    <!--begin::Header-->
    <div class="card-header py-3">
        <div class="card-title align-items-start flex-column">
            <h3 class="card-label font-weight-bolder text-dark"><i class="flaticon-folder-1"></i> Fiches techniques</h3>
            <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
        </div>
        <div class="card-toolbar">
            <button onclick="_formSheet({{ $row->id }},0)" class="btn btn-sm btn-icon btn-light-primary">
                <i class="flaticon2-add-1"></i>
            </button>
        </div>
    </div>
    <!--end::Header-->
    <!--begin::Card-body-->
    <div class="card-body" id="BLOCK_SHEETS">

    </div>
</div>
<script>
var formation_id = $('#VIEW_INPUT_PF_ID_HELPER').val();
_viewSheets(formation_id, 'BLOCK_SHEETS');
</script>
@endif
@if($viewtype=='catalogue')
<!--begin::Card-->
<div class="card card-custom">
    <!--begin::Header-->
    <div class="card-header py-3">
        <div class="card-title align-items-start flex-column">
            <h3 class="card-label font-weight-bolder text-dark">Catalogue</h3>
            <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
        </div>
    </div>
    <!--end::Header-->
    <!--begin::Card-body-->
    <div class="card-body">
        <input type="hidden" id="categorie_id" value="{{ $row->categorie_id }}" />
        <div id="catalogue_tree" class="tree-demo"></div>
    </div>
</div>
<script>
$('#catalogue_tree').jstree({
    "core": {
        "multiple": false,
        "themes": {
            "responsive": true
        },
        'data': {
            'url': function(node) {
                return '/get/categories/' + $("#categorie_id").val();
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
$('#catalogue_tree').bind("ready.jstree", function() {
    initializeSelections();
}).jstree();

function initializeSelections() {
    var instance = $('#catalogue_tree').jstree(true);
    instance.deselect_all();
    instance.select_node($("#categorie_id").val());
}
</script>
@endif
@if($viewtype=='tarification')
<!--begin::Card-->
<div class="card card-custom">
    <!--begin::Header-->
    <div class="card-header py-3">
        <div class="card-title align-items-start flex-column">
            <h3 class="card-label font-weight-bolder text-dark"><i class="flaticon-price-tag"></i> Tarifications</h3>
            <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
        </div>
        <div class="card-toolbar">
            <button onclick="_formPfRelPrice({{ $row->id }})" class="btn btn-sm btn-icon btn-light-primary mr-2">
                <i class="flaticon2-add-1"></i>
            </button>
            <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                onclick="_reload_dt_pf_prices()"><i class="flaticon-refresh"></i></button>
            <button class="btn btn-sm btn-icon btn-light-danger" data-toggle="tooltip" title="Supprimer une selection"
                    onclick="_deletePfRelPrice(0, {{ $row->id }})"><i class="flaticon-delete"></i></button>
        </div>
    </div>
    <!--end::Header-->
    <!--begin::Card-body-->
    <div class="card-body">
        <!--begin: Datatable-->
        <table class="table table-bordered table-checkable" id="dt_pf_prices">
            <thead>
                <tr>
                    <th></th>
                    <th>Titre</th>
                    <th>Entité</th>
                    <th>Type</th>
                    <th>Tarif</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <!--end: Datatable-->
    </div>
</div>
<x-modal id="modal_price_formation" content="modal_price_formation_content" />
<script>
var formation_id = $('#VIEW_INPUT_PF_ID_HELPER').val();
var dtUrl = '/api/sdt/prices/pf/' + formation_id;
var table = $('#dt_pf_prices');
// begin first table
table.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    ordering: false,
    ajax: {
        url: dtUrl,
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
        },
    },
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
        width: '30px',
        className: 'dt-left',
        orderable: false,
        /* render: function(data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        }, */
    }, {
        targets: 4,
        width: '150px',
    }],
});

table.on('change', '.group-checkable', function() {
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

table.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_pf_prices = function() {
    $('#dt_pf_prices').DataTable().ajax.reload();
}

var _formPfRelPrice = function(formation_id) {
    var modal_id='modal_price_formation';
    var modal_content_id='modal_price_formation_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#'+modal_id).modal('show');
    $('#'+modal_content_id).html(spinner);
    $.ajax({
        url : '/form/price/rel/pf/'+formation_id,
        type : 'GET',
        dataType : 'html',
        success : function(html, status){
            $('#'+modal_content_id).html(html);
        },
        error : function(result, status, error){

        },
        complete : function(result, status){

        }
    });
}
function _deletePfRelPrice(id, pf_id) {
        var TableauIdProcess = new Array();
        var j = 0;
        if (id > 0) {
            TableauIdProcess[0] = id;
        } else {
            $('#dt_pf_prices input[class="checkable"]').each(function() {
                var checked = jQuery(this).is(":checked");
                if (checked) {
                    TableauIdProcess[j] = jQuery(this).val();
                    j++;
                }
            });
        }
        if (TableauIdProcess.length < 1) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Veuillez sélectionner un ou plusieurs tarif(s)!',
            });
            //return false;
        } else {
            var successMsg = "Le tarif a été supprimée.";
            var errorMsg = "Le tarif n\'a pas été supprimée.";
            var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le(s) tarif(s)?";
            var swalConfirmText ="Vous ne pourrez pas revenir en arrière!";
            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Supprimer"
            }).then(function(result) {
                if (result.value) {
                    KTApp.blockPage();
                    $.ajax({
                        url: "/api/delete/pfrelprice",
                        type: "DELETE",
                        data: {
                            pf_id: pf_id,
                            ids_prices: TableauIdProcess,
                        },
                        dataType: "JSON",
                        success: function(result, status) {
                            if (result.success) {
                                _showResponseMessage("success", successMsg);
                            } else {
                                _showResponseMessage("error", errorMsg);
                            }
                        },
                        error: function(result, status, error) {
                            _showResponseMessage("error", errorMsg);
                        },
                        complete: function(result, status) {
                            _reload_dt_pf_prices();
                            KTApp.unblockPage();
                        }
                    });
                }
            });
        }
    }
</script>
@endif
@if($viewtype=='historique')
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Historique</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">
                <!--begin::Button-->
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title=""
                    onclick="_reload_dt_historique()" data-original-title="Rafraîchir"><i
                        class="flaticon-refresh"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_historique">
                <thead>
                    <tr>
                        <th></th>
                        <th>Date</th>
                        <th>Qui</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var formation_id = $('#VIEW_INPUT_PF_ID_HELPER').val();
    var dtUrl = '/api/sdt/historique/pf/' + formation_id;
    var table = $('#dt_historique');
    // begin first table
    table.DataTable({
        language: {
            url: "/custom/plugins/datatable/fr.json"
        },
        responsive: true,
        processing: true,
        paging: true,
        ordering: true,
        order: [[ 1, "desc" ]],
        ajax: {
            url: dtUrl,
            type: 'POST',
            data: {
                pagination: {
                    perpage: 50,
                },
            },
        },
        lengthMenu: [5, 10, 25, 50],
        pageLength: 25,
        headerCallback: function(thead, data, start, end, display) {
            thead.getElementsByTagName('th')[0].innerHTML = `
                    <label class="checkbox checkbox-single">
                        <input type="checkbox" value="" class="group-checkable"/>
                        <span></span>
                    </label>`;
        },
        columnDefs: [
            {
                targets: 0,
                width: '30px',
                className: 'dt-left',
                orderable: false,
                render: function(data, type, full, meta) {
                    return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
                },
            },
            {
                targets: 1,
                orderable: true,
            }
        ],
    });
    table.on('change', '.group-checkable', function() {
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
    table.on('change', 'tbody tr .checkbox', function() {
        $(this).parents('tr').toggleClass('active');
    });
    var _reload_dt_historique = function() {
    $('#dt_historique').DataTable().ajax.reload();
    }
    </script>
    @endif

@if($viewtype=='structure')
<!--begin::Card-->
<div class="card card-custom">
    <!--begin::Header-->
    <div class="card-header py-3">
        <div class="card-title align-items-start flex-column">
            <h3 class="card-label font-weight-bolder text-dark">Structure hiérarchique et temporelle</h3>
            <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
        </div>
        <div class="card-toolbar">
            <button class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Ajouter une nouvelle formation"
                    onclick="_formFormation({{$row->id}},1,1)"><i class="flaticon2-add-1"></i></button>
        </div>
    </div>
    <!--end::Header-->
    <!--begin::Card-body-->
    <div class="card-body">
        <input type="hidden" id="product_id" value="{{ $row->id }}" />

        <div class="card card-custom mb-4">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-icon">
                        <i class="flaticon-map text-primary"></i>
                    </span>
                    <h3 class="card-label">Structure hiérarchique
                    </h3>
                </div>    
                <div class="card-toolbar">
                    <button type="button" data-toggle="tooltip" title="Élargir tous" onclick="ExpandCollapseAll('structure_tree','EXPAND')"
                            class="btn btn-sm btn-icon btn-light-primary mr-2">
                            <i class="fa fa-chevron-down"></i>
                    </button>
                    <button type="button" data-toggle="tooltip" title="Réduire tous" onclick="ExpandCollapseAll('structure_tree','COLLAPSE')"
                            class="btn btn-sm btn-icon btn-light-success mr-2">
                            <i class="fa fa-chevron-up"></i> 
                    </button>
                    <button type="button" data-toggle="tooltip" title="Rafraîchir tous"
                        class="btn btn-sm btn-icon btn-light-danger mr-2" onclick="resfreshJSTreeStructures()"><i
                            class="flaticon-refresh"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div id="structure_tree" class="tree-demo"></div>
            </div>
        </div>

        <div class="card card-custom mb-4">
            <div class="card-header">
                <div class="card-title">
                    <span class="card-icon">
                        <i class="flaticon-map text-primary"></i>
                    </span>
                    <h3 class="card-label">Structure temporelle
                    </h3>
                </div>    
                <div class="card-toolbar">
                    <button type="button" data-toggle="tooltip" title="Élargir tous" onclick="ExpandCollapseAll('structure_temporelle_tree','EXPAND')"
                            class="btn btn-sm btn-icon btn-light-primary mr-2">
                            <i class="fa fa-chevron-down"></i>
                    </button>
                    <button type="button" data-toggle="tooltip" title="Réduire tous" onclick="ExpandCollapseAll('structure_temporelle_tree','COLLAPSE')"
                            class="btn btn-sm btn-icon btn-light-success mr-2">
                            <i class="fa fa-chevron-up"></i> 
                    </button>
                    <button type="button" data-toggle="tooltip" title="Rafraîchir tous"
                        class="btn btn-sm btn-icon btn-light-danger mr-2" onclick="resfreshJSTreeTemporelle()"><i
                            class="flaticon-refresh"></i></button>
                </div>
            </div>    
            <div class="card-body">
                <div id="structure_temporelle_tree" class="tree-demo"></div>
            </div>
        </div>

    </div>
</div>
<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
$('#structure_tree').jstree({
    "core": {
        "multiple": false,
        "themes": {
            "responsive": true
        },
        //"check_callback" : false,
        'data': {
            'url': function(node) {
                return '/get/tree/hierarchical/structure/'+$("#product_id").val();
            },
            'data': function(node) {
                return {
                    'parent': node.id
                };
            }
        },
    },
    "checkbox": {
        "three_state": false, // to avoid that fact that checking a node also check others
        //"whole_node" : false,  // to avoid checking the box just clicking the node
        //"tie_selection" : true // for checking without selecting and selecting without checking
    },
    "plugins": ["state"]
});

//temporelle
$('#structure_temporelle_tree').jstree({
    "core": {
        "multiple": false,
        "themes": {
            "responsive": true
        },
        //"check_callback" : false,
        'data': {
            'url': function(node) {
                return '/get/tree/time/structure/'+$("#product_id").val()+"/0";
            },
            'data': function(node) {
                return {
                    'parent': node.id
                };
            }
        },
    },
    "checkbox": {
        "three_state": false, // to avoid that fact that checking a node also check others
        //"whole_node" : false,  // to avoid checking the box just clicking the node
        //"tie_selection" : true // for checking without selecting and selecting without checking
    },
    "plugins": ["state"]
});

function resfreshJSTreeStructures() {
    $('#structure_tree').jstree(true).settings.core.data.url = '/get/tree/hierarchical/structure/'+$("#product_id").val();
    $('#structure_tree').jstree(true).refresh();
}

function resfreshJSTreeTemporelle() {
    $('#structure_temporelle_tree').jstree(true).settings.core.data.url = '/get/tree/time/structure/'+$("#product_id").val()+"/0";
    $('#structure_temporelle_tree').jstree(true).refresh();
}

function ExpandCollapseAll(idTree, type) {
    if (idTree != '' && type != '') {
        var action = '';
        if (type == 'EXPAND') {
            action = 'open_all';
        } else if (type == 'COLLAPSE') {
            action = 'close_all';
        }
        if (action != '') {
            $('#' + idTree).jstree(action);
        }
    }
}

</script>
@endif
