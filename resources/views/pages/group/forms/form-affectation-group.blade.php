@php
    $modal_title = "Affectation contacts au groupe";
$tools = new \App\Library\Services\PublicTools();
@endphp
<div class="modal-header">
    <h5 class="modal-title" id="modal_form_group_title"><i
            class="{{$tools->getIconeByAction('VIEW')}}"></i> {{ $modal_title }} </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>

<!-- Form group : begin -->
<form id="formAffectationContactGroup" class="form">
    <div class="modal-body" id="modal_form_group_body">
        <div data-scroll="true" data-height="600">
            <input type="hidden" id="af_id" name="AF_ID" value="{{$af_id}}">
            <input type="hidden" id="grp_id" name="GROUP_ID" value="{{$group->id}}">
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-lg-12">
                        <label class="font-weight-bold ">Groupe : </label>
                        <label class="text-primary">{{$group->title}}</label>
                    </div>
                </div>
                <table class="table table-bordered table-checkable _tm" id="dt_affectation_contacts">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Contact</th>
                        <th>Client</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <!--end: Datatable-->
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-light-danger" data-dismiss="modal"><i class="fa fa-times"></i>
            Annuler
        </button>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Valider <span
                id="BTN_SAVE_GROUP"></span></button>
    </div>


</form>

<!-- Form group : end -->


<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var af_id = $('#af_id').val();
    var grp_id = $('#grp_id').val();
    var table_contacts = $('#dt_affectation_contacts');
    // begin first table
    table_contacts.DataTable({
        language: {
            url: "/custom/plugins/datatable/fr.json"
        },
        responsive: true,
        paging: true,
        ordering: false,
        processing: true,
        ajax: {
            url: '/api/sdt/member/contact/' + af_id+'/'+grp_id,
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
        lengthMenu: [[-1],["Tout"]],//[5, 10, 25, 50],
        pageLength: -1,
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
            orderable: false
        }],
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
        var selectedRows = $('table._tm').find('tbody') // select table body and
            .find('tr') // select all rows that has
            .has('input[type=checkbox]:checked') // checked checkbox element
        console.log('selectedRows:', selectedRows[0]
        );

    });

    $('[data-scroll="true"]').each(function () {
        var el = $(this);
        KTUtil.scrollInit(this, {
            mobileNativeScroll: true,
            handleWindowResize: true,
            rememberPosition: (el.data('remember-position') == 'true' ? true : false)
        });
    });

    $("#formAffectationContactGroup").validate({
        rules: {},
        messages: {},
        submitHandler: function(form) {
            _showLoader('BTN_SAVE_GROUP');
            var formData = $(form).serializeArray();
            $.ajax({
                type: 'POST',
                url: '/form/affectation/group',
                data: formData,
                dataType: 'JSON',
                success: function(result) {
                    _hideLoader('BTN_SAVE_GROUP');
                    if (result.success) {
                        _showResponseMessage('success', result.msg);
                        $('#modal_affectation_group').modal('hide');
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                    _reload_dt_groups();
                }
            });
            return false;
        }
    });

</script>
