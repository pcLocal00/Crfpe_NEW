var _formEstimate = function(estimate_id,af_id,entity_id) {
    var modal_id = 'modal_form_estimate';
    var modal_content_id = 'modal_form_estimate_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/estimate/' + estimate_id+'/'+af_id+'/'+entity_id,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function(result, status, error) {},
        complete: function(result, status) {}
    });
}