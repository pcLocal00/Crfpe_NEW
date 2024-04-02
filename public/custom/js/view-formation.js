$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var _viewDefaultSheet = function(formation_id, content_id) {
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#'+content_id).html(spinner);
    $.ajax({
        url: "/api/default/sheet/"+ formation_id,
        type: "GET",
        dataType: "JSON",
        success: function(result, status) {
                _viewSheet(formation_id,result.default_sheet_id, content_id);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {
        }
    });
};
var _viewSheet = function(formation_id,id_sheet, content_id) {
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#'+content_id).html(spinner);
    //KTApp.block("#" + content_id, {});
    $.ajax({
        url: "/view/sheet/"+formation_id+"/"+id_sheet,
        type: "GET",
        dataType: "html",
        success: function(html, status) {
            $("#" + content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {
            //KTApp.unblock("#" + content_id);
        }
    });
};
var _viewSheets = function(formation_id, content_id) {
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#'+content_id).html(spinner);
    $.ajax({
        url: "/list/sheets/" + formation_id,
        type: "GET",
        dataType: "html",
        success: function(html, status) {
            $("#" + content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {
        }
    });
};
var _formSheet = function(formation_id,id_sheet) {
    var modal_id='modal_sheet_formation';
    var modal_content_id='modal_sheet_formation_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#'+modal_id).modal('show');
    $('#'+modal_content_id).html(spinner);
    $.ajax({
        url : '/form/sheet/'+formation_id+'/'+id_sheet,
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

var _loadContent = function(viewtype) {
    var block_id = 'BLOCK_CONTENT_NAVIGATION';
    var row_id = $('#VIEW_INPUT_PF_ID_HELPER').val();
    var spinner = '<div class="card card-custom"><div class="card-body"><div class="spinner spinner-primary spinner-lg"></div></div></div>';
    $('#' + block_id).html(spinner);
    $.ajax({
        url: '/view/content/construct/pf/' + viewtype + '/' + row_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + block_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
    $(".css-af").each(function() {
        $(this).removeClass("active");
    });
    btn_id = 'NAV1';
    if (viewtype == 'ficheTechnique') {
        btn_id = 'NAV2';
    } else if (viewtype == 'ficheExploitation') {
        btn_id = 'NAV3';
    } else if (viewtype == 'versions') {
        btn_id = 'NAV4';
    } else if (viewtype == 'catalogue') {
        btn_id = 'NAV5';
    } else if (viewtype == 'tarification') {
        btn_id = 'NAV6';
    } else if (viewtype == 'historique') {
        btn_id = 'NAV7';
    } else if (viewtype == 'structure') {
        btn_id = 'NAV8';
    }
    $('#' + btn_id).addClass("active");
}
_loadContent('ficheTechnique');


var _deletePfRelPrice = function(price_id, formation_id) {
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    var urlDelete = "/api/delete/pfrelprice/" + price_id+'/'+formation_id;
    var successMsg = "Le tarif a été supprimé.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer ce tarif?";
    var errorMsg = "Le tarif n\'a pas été supprimée.";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimez-le!"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: urlDelete,
                type: "GET",
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
_update_pf_stats();
function _update_pf_stats(){
    var spinner = '<div class="spinner spinner-primary spinner-sm"></div>';
    $('#PF_NB_VERSIONS').html(spinner);
    var pf_id=$('#VIEW_INPUT_PF_ID_HELPER').val();
    if(pf_id>0){
        $.ajax({
            url : '/api/statistics/pf/'+pf_id,
            type : 'GET',
            dataType : 'JSON',
            success:function(result,status){
                $('#PF_NB_VERSIONS').html(result.nb_versions);
            },
            error:function(result,status){

            },
            complete:function(result,status){

            }
        });
    }
}
