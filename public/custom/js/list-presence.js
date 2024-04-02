$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$('[data-toggle="tooltip"]').tooltip();
var af_id=$('#VIEW_INPUT_AF_ID_HELPER').val();
var dtUrl = '/api/sdt/stages/'+af_id; 
var table = $('#dt_stages');
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
        render: function(data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        },
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

function _reload_dt_stages() {
    $('#dt_stages').DataTable().ajax.reload();
}
function _formJustification() {
    // schedules_data = {'state': state ,'member_id': $('#membersSelectFilter').val(), 'schedules': []};
    var modal_id = 'modal_form_stage';
    var modal_content_id = 'modal_form_stage_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    schedulecontact_id = $('#membersSelectFilter').val();
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/presence/' + schedulecontact_id,
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
    return false;
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
/*var form_id = 'formFilterStages';
$("#"+form_id).submit(function(event) {
    event.preventDefault();
    KTApp.blockPage();
    var formData = $(this).serializeArray();
    var table = 'dt_stages';
    $.ajax({
        type: "POST",
        dataType: 'json',
        data: formData,
        url: dtUrl,
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
});*/
// var _reset = function() {
//     _reload_dt_stages();
// }