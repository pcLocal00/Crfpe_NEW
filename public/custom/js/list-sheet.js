var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
var formation_id = $('#hidden_input_formation_id').val();
var table = $('#kt_sheets_datatable');
// begin first table
table.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    responsive: true,
    paging: true,
    ajax: {
        url: '/api/sdt/sheets/' + formation_id,
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
    columns: [{
            data: 'ID'
        },
        {
            data: 'Code'
        },
        {
            data: 'Version'
        },
        {
            data: 'Infos'
        },
        {
            data: 'Actions',
            responsivePriority: -1
        },
    ],
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

var _reload_dt_sheets = function(){
    $('#kt_sheets_datatable').DataTable().ajax.reload();
}

var _deleteSheet = function(sheet_id) {
    var successMsg="Votre fiche technique a été supprimée.";
    var errorMsg="Votre fiche technique n\'a pas été supprimée.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer la fiche technique?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
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
                url: "/api/delete/sheet/"+ sheet_id,
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
                    _reload_dt_sheets();
                    KTApp.unblockPage();
                }
            });
        }
    });
}