$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var urlSrc = '/api/catalogues/0/0';
$("#tree_catalogues").jstree({
    "core": {
        "themes": {
            "responsive": false
        },
        // so that create works
        "check_callback": true,
        "data": {
            'url': function(node) {
                return urlSrc;
            },
            'data': function(node) {
                return {
                    'parent': node.id
                };
            }
        }
    },
    "plugins": ["dnd", "state", "types"]
    //"plugins": ["state", "types"]
}).bind("move_node.jstree", function(e, data) {
    _dragAndDropMove(data.node.id,data.parent,data.position);
 });

function _dragAndDropMove(node_id,node_parent,position){
    var successMsg="Votre grain de l'arborescence a été déplacée.";
    var errorMsg="Votre grain de l'arborescence n'a pas été déplacée.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir déplacer ce grain de l'arborescence?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, déplacez!"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/move/categorie",
                type: "POST",
                dataType: "JSON",
                data : {node_id : node_id,node_parent : node_parent,position : position},
                success: function(result, status) {
                    if(result.success){
                        _showResponseMessage("success",successMsg);
                    }else{
                        _showResponseMessage("error",errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error",errorMsg);
                },
                complete: function(result, status) {
                    resfreshJSTreeCatalogues(0);
                    KTApp.unblockPage();
                }
            }); 
        }
    });
} 

function resfreshJSTreeCatalogues(param) {
    $('#tree_catalogues').jstree(true).settings.core.data.url = '/api/catalogues/0/'+param;
    $('#tree_catalogues').jstree(true).refresh();
}
function resfreshJSTreeCataloguesWithTrashed() {
    $('#tree_catalogues').jstree(true).settings.core.data.url = '/api/catalogues/0/1';
    $('#tree_catalogues').jstree(true).refresh();
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
var _formCategorie = function(categorie_id) {
    var modal_id = 'modal_form_catalogue';
    var modal_content_id = 'modal_form_catalogue_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/categorie/' + categorie_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {

        },
        complete: function(result, status) {

        }
    });
}
var _deleteCategorie = function(categorie_id) {
    var successMsg="Votre grain de l'arborescence a été supprimée.";
    var errorMsg="Votre grain de l'arborescence n'a pas été supprimée.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer ce grain de l'arborescence?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimez!"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/delete/categorie/"+ categorie_id,
                type: "GET",
                dataType: "JSON",
                success: function(result, status) {
                    if(result.success){
                        _showResponseMessage("success",successMsg);
                    }else{
                        _showResponseMessage("error",errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error",errorMsg);
                },
                complete: function(result, status) {
                    resfreshJSTreeCatalogues(0);
                    KTApp.unblockPage();
                }
            });
        }
    });
}
var _archiveCategorie = function(categorie_id) {
    var successMsg="Votre grain de l'arborescence a été archivé.";
    var errorMsg="Votre grain de l'arborescence n'a pas été archivé.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir archiver ce grain de l'arborescence?";
    var swalConfirmText = "L'archivage de ce grain de l'arborescence entraine l'archivage de tous les produits de formation et sous arborescences associés!";
    _loadDatasForSelectOptions('selectMotifs','CATEGORY_ARCHIVING_REASON',0);
    Swal.fire({
        title: swalConfirmTitle,
        icon: 'warning',
        html:'<p>'+swalConfirmText+'</p>'+
        `<div class="form-group"><label for="selectMotifs">Motif : </label><select class="form-control" id="selectMotifs"></select></div>`,
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText:'<i class="fa fa-check"></i> Oui, archivez!',
        cancelButtonText:'<i class="fa fa-times"></i>',
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content'); 
            var motif_text = $("#selectMotifs option:selected").text();
            //alert(motif_text);
            $.ajax({
                url: "/api/archive/categorie",
                type: "POST",
                dataType: "JSON",
                data: {_token:CSRF_TOKEN,categorie_id: categorie_id,motif_text:motif_text},
                success: function(result, status) {
                    if(result.success){
                        _showResponseMessage("success",successMsg);
                    }else{
                        _showResponseMessage("error",errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error",errorMsg);
                },
                complete: function(result, status) {
                    resfreshJSTreeCatalogues(0);
                    KTApp.unblockPage();
                }
            });
        }
    });
}
var _unarchiveCategorie = function(categorie_id) {
    var successMsg="Votre grain de l'arborescence a été désarchivé.";
    var errorMsg="Votre grain de l'arborescence n'a pas été désarchivé.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir désarchiver ce grain de l'arborescence?";
    var swalConfirmText = "Vous allez désarchiver ce niveau. mais pas les formations assoicées!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, archivez!"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/unarchive/categorie/"+ categorie_id,
                type: "GET",
                dataType: "JSON",
                success: function(result, status) {
                    if(result.success){
                        _showResponseMessage("success",successMsg);
                    }else{
                        _showResponseMessage("error",errorMsg);
                    }
                },
                error: function(result, status, error) {
                    _showResponseMessage("error",errorMsg);
                },
                complete: function(result, status) {
                    resfreshJSTreeCatalogues(0);
                    KTApp.unblockPage();
                }
            });
        }
    });
}