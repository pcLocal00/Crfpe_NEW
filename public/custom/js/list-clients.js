$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var table = $('#dt_entities');
var dtUrl = '/api/sdt/entities'; 
// begin first table
table.DataTable({
    language: {
        url: "custom/plugins/datatable/fr.json"
    },
    responsive: true,
    processing: true,
    paging: true,
    ordering: false,
    serverSide: false,
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
    pageLength: 5,
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
        // render: function(data, type, full, meta) {
        //     return `<label class="checkbox checkbox-single"><input type="checkbox" value="" class="checkable"/><span></span></label>`;
        // },
    },
    {
        targets: 5,
        width: '100px',  
    },
    //dates
    {
        targets: 6,
        width: '130px',  
    },
    //actions
    {
        targets: 7,
        width: '70px',  
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

var _reload_dt_entities = function() {
    $('#dt_entities').DataTable().ajax.reload();
}

var _formEntity = function(entity_id) {
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
var _viewEntity = function(entity_id) {
    window.location.href = "/view/entity/" + entity_id;
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
//formFilterClients
var form_id = 'formFilterClients';
$("#"+form_id).submit(function(event) {
    event.preventDefault();
    KTApp.blockPage();
    var formData = $(this).serializeArray();
    var table = 'dt_entities';
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
var _reset = function() {
    _reload_dt_entities();
}

function _generatePncFile() {
    var params = '?entities=';
    var selections = $('table').find('td:first-child .checkable:checked');
    $(selections).each(function() {
        var inv = $(this);
        params += (inv.val() + ',');
    });
    params = params.slice(0, -1);
    $.ajax({
        url: '/form/generate/pnc/'+ params,
        type: 'GET',
        dataType: 'JSON',
        success: function(resp) {
            if (!resp.success) {
                var error;
                if (!resp.message && resp.already_sync) {
                    error = ': Déjà synchronisée avec Sage.';
                } else {
                    error = 'Informations non saisies: <br/>' + resp.message.join('<br/>')
                }
                _showResponseMessage("error", '['+resp.entity+'] ' + error, 0);
            } else {
                window.open('/form/downloadsagefile/?sageFile=' + resp.file + '.PNC', '_blank');
            }

            _reload_dt_entities();
        }
    });
}
