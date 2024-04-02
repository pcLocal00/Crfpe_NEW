$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$('[data-toggle="tooltip"]').tooltip();
var af_id=$('#VIEW_INPUT_AF_ID_HELPER').val();
var dtUrl = '/api/sdt/stages/'+af_id; 
var tableStages = $('#dt_stages');
// begin first table
tableStages.DataTable({
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
        render: function(data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        },
    }],
});

tableStages.on('change', '.group-checkable', function() {
    var set = $(this).closest('tableStages').find('td:first-child .checkable');
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

tableStages.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

function _reload_dt_stages() {
    $('#dt_stages').DataTable().ajax.reload();
}
function _formStage(session_id,af_id) {
    var modal_id = 'modal_form_stage';
    var modal_content_id = 'modal_form_stage_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/stage/' + session_id+'/'+af_id,
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
var form_id = 'formFilterStages';
$("#"+form_id).submit(function(event) {
    event.preventDefault();
    KTApp.blockPage();
    var formData = $(this).serializeArray();
    var tableStages = 'dt_stages';
    $.ajax({
        type: "POST",
        dataType: 'json',
        data: formData,
        url: dtUrl,
        success: function(response) {
            if (response.data.length == 0) {
                $('#'+tableStages).dataTable().fnClearTable();
                return 0;
            }
            $('#'+tableStages).dataTable().fnClearTable();
            $("#"+tableStages).dataTable().fnAddData(response.data, true);
        },
        error: function() {
            $('#'+tableStages).dataTable().fnClearTable();
        }
    }).done(function(data) {
        KTApp.unblockPage();
    });
    return false;
});
var _reset = function() {
    _reload_dt_stages();
}