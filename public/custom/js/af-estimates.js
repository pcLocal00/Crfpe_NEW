var af_id = $("input[name='id']").val();
var dtUrlEstimate = '/api/sdt/estimates/'+af_id; 
var tableEstimate = $('#dt_estimates');
// begin first table
tableEstimate.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    ordering: false,
    ajax: {
        url: dtUrlEstimate,
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
        render: function(data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        },
    }],
});

tableEstimate.on('change', '.group-checkable', function() {
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

tableEstimate.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_estimates = function() {
    $('#dt_estimates').DataTable().ajax.reload();
}

var _formSendEstimate = function(estimate_id) {
    var modal_id = 'modal_form_mail';
    var modal_content_id = 'modal_form_mail_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: "/form/mail/estimate/" + estimate_id,
        type: "GET",
        dataType: "html",
        success: function(html, status) {
            $("#" + modal_content_id).html(html);
        },
    });
};

$('#filter_datepicker').datepicker({
    language: 'fr',
    rtl: KTUtil.isRTL(),
    format: 'dd/mm/yyyy',
    todayHighlight: true,
    orientation: "bottom left",
    templates: {
        leftArrow: '<i class="la la-angle-left"></i>',
        rightArrow: '<i class="la la-angle-right"></i>'
    }
});
var form_id = 'formFilterEstimates';
$("#"+form_id).submit(function(event) {
    event.preventDefault();
    KTApp.blockPage();
    var formData = $(this).serializeArray();
    var table = 'dt_estimates';
    $.ajax({
        type: "POST",
        dataType: 'json',
        data: formData,
        url: dtUrlEstimate,
        success: function(response) {
            if (response.data.length == 0) {
                $('#'+table).dataTable().fnClearTable();
                return 0;
            }
            $('#'+table).dataTable().fnClearTable();
            $("#"+table).dataTable().fnAddData(response.data, true);
        },
        error: function() {
            $('#'+table).dataTable().fnClearTable();
        }
    }).done(function(data) {
        KTApp.unblockPage();
    });
    return false;
});
var _reset = function() {
    _reload_dt_estimates();
}