var _loadContent = function(viewtype) {
    var block_id = 'BLOCK_CONTENT_NAVIGATION';
    var entity_id = $('#VIEW_INPUT_ENTITY_ID_HELPER').val();
    KTApp.block('#'+block_id, {
        overlayColor: '#000000',
        state: 'danger',
        message: 'Please wait...'
    });
    $.ajax({
        url: '/view/content/construct/'+viewtype+'/' + entity_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + block_id).html(html);
        },
        error: function(result, status, error) {

        },
        complete: function(result, status) {
            KTApp.unblock('#'+block_id);
        }
    });
    $( ".css-entity" ).each(function() {
        $( this ).removeClass( "active" );
    });
    btn_id = 'NAV1';
    if(viewtype=='entity'){
        btn_id = 'NAV2';
    }else if(viewtype=='contacts'){
        btn_id = 'NAV3';
    }else if(viewtype=='adresses'){
        btn_id = 'NAV4';
    }
    $('#'+btn_id).addClass("active");   
}
_loadContent('entity');
/* 
FORM EDIT ENTITY
*/
var _formEntityView = function(entity_id) {
    var modal_id = 'modal_form_entitie';
    var modal_content_id = 'modal_form_entitie_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/entitie/' + entity_id,
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