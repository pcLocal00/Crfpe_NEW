var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var table_sessions = $('#dt_sessions');
// begin first table
table_sessions.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    paging: true,
    ordering: false,
    processing: true,
    ajax: {
        url: '/api/sdt/sessions/0',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        type: 'POST',
        data: {
            pagination: {
                perpage: 50,
            },
        },
    },
    lengthMenu: [5, 10, 25, 50],
    pageLength: 5,
    headerCallback: function (thead, data, start, end, display) {
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
        render: function (data, type, full, meta) {
            return `
                        <label class="checkbox checkbox-single">
                            <input type="checkbox" value="" class="checkable"/>
                            <span></span>
                        </label>`;
        },
    }],
});

table_sessions.on('change', '.group-checkable', function () {
    var set = $(this).closest('table').find('td:first-child .checkable');
    var checked = $(this).is(':checked');

    $(set).each(function () {
        if (checked) {
            $(this).prop('checked', true);
            $(this).closest('tr').addClass('active');
        } else {
            $(this).prop('checked', false);
            $(this).closest('tr').removeClass('active');
        }
    });
});

table_sessions.on('change', 'tbody tr .checkbox', function () {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_sessions = function () {
    $('#dt_sessions').DataTable().ajax.reload();
}
//form:

var _formSession = function (session_id, action_id) {
    var modal_id = 'modal_form_session';
    var modal_content_id = 'modal_form_session_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/session/test/' + session_id + '/' + action_id,
        type: 'GET',
        dataType: 'html',
        success: function (html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function (result, status, error) {

        },
        complete: function (result, status) {

        }
    });
}

var _infosSession = function (session_id) {

    var modal_id = 'modal_date_session';
    var modal_content_id = 'modal_date_session_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';

    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/get/session/infos/' + session_id ,
        type: 'GET',
        dataType: 'html',
        success: function (html, status) {
            $('#' + modal_content_id).html(html);
        },
        error: function (result, status, error) {

        },
        complete: function (result, status) {

        }
    });

}


var dtUrl = '/api/sdt/sessions/0';

//formFilterClients
var form_id = 'formFilterSessions';
$("#" + form_id).submit(function (event) {
    event.preventDefault();
    KTApp.blockPage();
    var formData = $(this).serializeArray();
    var table = 'dt_sessions';
    $.ajax({
        type: "POST",
        dataType: 'json',
        data: formData,
        url: dtUrl,
        success: function (response) {
            if (response.data.length == 0) {
                $('#' + table).dataTable().fnClearTable();
                return 0;
            }
            $('#' + table).dataTable().fnClearTable();
            $("#" + table).dataTable().fnAddData(response.data, true);
        },
        error: function () {
            $('#' + table).dataTable().fnClearTable();
        }
    }).done(function (data) {
        KTApp.unblockPage();
    });
    return false;
});

