
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var table_contacts = $('#dt_contacts');
// begin first table
table_contacts.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    paging: true,
    ordering: false,
    processing: true,
    serverSide: false,
    ajax: {
        url: '/api/sdt/contacts/0',
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
                            <input type="checkbox" value="`+data+`" class="checkable"/>
                            <span></span>
                        </label>`;
        },
    }],
    "initComplete": function( settings, json ) {
        var entity_id=$('#INPUT_ENTITY_ID').val();
        var contact_id=$('#INPUT_CONTACT_ID').val();
        if(contact_id>0 && entity_id>0){
            _formContact (contact_id, entity_id);
        }
    }
});

table_contacts.on('change', '.group-checkable', function () {
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

table_contacts.on('change', 'tbody tr .checkbox', function () {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_contacts = function () {
    $('#dt_contacts').DataTable().ajax.reload();
}
function _formContact (contact_id, entity_id) {
    var modal_id = 'modal_form_contact';
    var modal_content_id = 'modal_form_contact_content';
    var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
    $('#' + modal_id).modal('show');
    $('#' + modal_content_id).html(spinner);
    $.ajax({
        url: '/form/contact/' + contact_id + '/' + entity_id,
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

var dtUrl = '/api/sdt/contacts/0';

//formFilterContacts
var form_id = 'formFilterContacts';
$("#"+form_id).submit(function(event) {
    event.preventDefault();
    KTApp.blockPage();
    var formData = $(this).serializeArray();
    var table = 'dt_contacts';
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
});

