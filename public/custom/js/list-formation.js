$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var dt_formation = $('#kt_dt_formations');
var dtUrl = '/api/sdt/formations';
// begin first dt_formation
dt_formation.DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
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
    },{
        targets: -1,
		title: 'Actions',
		orderable: false,
		width: '140px',
    },{
        targets: 2,
		width: '140px',
    },{
        targets: 3,
		width: '130px',
    }
    ],
    lengthMenu: [5, 10, 25, 50],
    pageLength: 5,
});

dt_formation.on('change', '.group-checkable', function() {
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

dt_formation.on('change', 'tbody tr .checkbox', function() {
    $(this).parents('tr').toggleClass('active');
});

var _reload_dt_formation = function(){
    $('#kt_dt_formations').DataTable().ajax.reload();
}





var _viewFormation = function(formation_id) {
    window.location.href = "/view/formation/" + formation_id;
}
var _deleteFormation = function(formation_id) {
    var successMsg="Votre produit de formation a été supprimé.";
    var errorMsg="Votre produit de formation n\'a pas été supprimé.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir supprimer le produit de formation?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimez-le!",
        cancelButtonText: "Annuler"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/delete/formation/"+ formation_id,
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
                    _reload_dt_formation();
                    KTApp.unblockPage();
                }
            });
        }
    });
}
var _archiveFormation = function(formation_id) {
    var successMsg="Votre produit de formation a été archivé.";
    var errorMsg="Votre produit de formation n\'a pas été archivé.";
    var swalConfirmTitle = "Êtes-vous sûr de bien vouloir archiver le produit de formation?";
    var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
    Swal.fire({
        title: swalConfirmTitle,
        text: swalConfirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, archivez-le!",
        cancelButtonText: "Annuler"
    }).then(function(result) {
        if (result.value) {
            KTApp.blockPage();
            $.ajax({
                url: "/api/archive/formation/"+ formation_id,
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
                    _reload_dt_formation();
                    KTApp.unblockPage();
                }
            });
        }
    });
}
