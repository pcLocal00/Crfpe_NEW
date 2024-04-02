<?php if($temoin == 1) {?>
<table class="table table-bordered table-sm" style="width:100%;" id="subtasktable">
    <thead class="thead-light">
        <tr>
            <th></th>
            <th>Résumé</th>
            <th>Type</th>
            <th>Source</th>
            <th>Etat</th>
            <th>Description</th>
            <th>Superviseur</th>
            <th>Responsable</th>
            <th>Dates</th>
            <th>Mode de rappel</th>
            <th>Mode de réponse</th>
            <th>Entité</th>
            <th>Contact</th>
            <th>Concerne</th>
            <th>Commentaire</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var ids=[];

var list = <?php echo $data; ?>;

list.forEach(element => {
    ids.push(element['id']);
});

$('#subtasktable').DataTable({
    language: {
        url: "/custom/plugins/datatable/fr.json"
    },
    "responsive": true,
    "paging": false,
    "info": false,
    "fixedHeader": false,
    "searching": false,
    "deferRender":    true,
    ajax: {
        url: '/api/sdt/select/sdtsubtasks/' + ids,
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
    columnDefs: [
        {
            targets: 0,
            width: '30px',
            className: 'dt-left',
            orderable: false,
            render: function(data, type, full, meta) {
                return `
                <label class="checkbox checkbox-single">
                    <input type="checkbox" value="`+data+`" class="checkable"/>
                    <span></span>
                </label>`;
            },
        }
    ],
});

$(".dataTables_scrollBody thead tr").hide();

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

var _subTabletasks = function(taskid) {
    return '<div id="child_data_subtasks_' + taskid +
        '" class="datatable datatable-default datatable-primary datatable-loaded"></div>';
}

var _getSubTasks = function(taskid) {
    var spinner = '<div class="spinner spinner-primary spinner-lg"></div>';
    $('#child_data_subtasks_' + taskid).parent().addClass('bg-light');
    $('#child_data_subtasks_' + taskid).html(spinner);
    $.ajax({
        url: '/get/subtasks/' + taskid,
        type: 'GET',
        dataType: 'html',
        success: function(html, status) {
            $('#child_data_subtasks_' + taskid).html(html);
        }
    });
}

</script>

<?php } else{
    echo "Aucun sous tâches pour cette tâche";
}?><?php /**PATH C:\Users\pc\OneDrive - Havet Digital\Bureau\src\resources\views/pages/task/subtasks.blade.php ENDPATH**/ ?>