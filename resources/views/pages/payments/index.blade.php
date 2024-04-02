{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <div class="card-header flex-wrap border-0 ">
            <div class="card-title">
                <h3 class="card-label">Contrôle de paie
                </h3>
            </div>
            <div class="card-toolbar">
                <!--end::Dropdown-->
                <!--begin::Button-->
                <button class="btn btn-sm btn-icon btn-light-primary mr-2" data-toggle="tooltip" title="Rafraîchir"
                    onclick="_reload_dt_contracts()"><i class="flaticon-refresh"></i></button>
                <button class="btn btn-sm btn-icon btn-light-success" data-toggle="tooltip"
                    title="Envoyer les informations validées sur la période" onclick="_sendValidatedInfosByMail()"><i
                        class="flaticon-mail"></i></button>
                <!--end::Button-->
            </div>
        </div>
        <div class="card-body">
            <!--begin::filter-->
            <x-filter-form type="ControlePay" />
            <!--end::filter-->
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_contracts">
                <thead>
                    <tr>
                        <th></th>
                        <th>Name (Etat)</th>
                        <th>CDD</th>
                        <th>CDD < 60j</th>
                        <th>Bulletin C</th>
                        <th>DSN/FCDD</th>
                        <th>Sommeil</th>
                        <th>Nb Jours</th>
                        <th>Nb Heures</th>
                        <th>Détails</th>
                        <th>Validé ?</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <!--end::Card-->
    <x-modal id="modal_schedule_details" content="modal_schedule_details_content" />
    <x-modal id="modal_form_pointage" content="modal_form_pointage_content" />
@endsection

{{-- Styles Section --}}
@section('styles')
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection


{{-- Scripts Section --}}
@section('scripts')
    {{-- vendors --}}
    <script src="{{ asset('plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>


    {{-- page scripts --}}
    <script src="{{ asset('custom/js/general.js?v=2') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('[data-toggle="tooltip"]').tooltip();

        var dtUrl = '/api/sdt/controle/contracts';
        var table = $('#dt_contracts');
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
                    filter: 1,
                    filter_start: $('#filter_start').val(),
                    filter_end: $('#filter_end').val(),
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

        function _reload_dt_contracts() {
            $('#dt_contracts').DataTable().ajax.reload();
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
        var form_id = 'formFilterControlePay';
        $("#" + form_id).submit(function(event) {
            event.preventDefault();
            KTApp.blockPage();
            var formData = $(this).serializeArray();
            var table = 'dt_contracts';
            $.ajax({
                type: "POST",
                dataType: 'json',
                data: formData,
                url: dtUrl,
                success: function(response) {
                    if (response.data.length == 0) {
                        $('#' + table).dataTable().fnClearTable();
                        return 0;
                    }
                    $('#' + table).dataTable().fnClearTable();
                    $("#" + table).dataTable().fnAddData(response.data, true);
                },
                error: function() {
                    $('#' + table).dataTable().fnClearTable();
                }
            }).done(function(data) {
                KTApp.unblockPage();
            });
            return false;
        });

        function _showFormerScheduleDetails(contract_id, start_date, end_date) {
            var modal_id = 'modal_schedule_details';
            var modal_content_id = 'modal_schedule_details_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/get/schedule/contract/details/periods/' + contract_id + '/' + start_date + '/' + end_date,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                }
            });
        }

        function _validateContractScheduleContacts(contract_id) {

            var filter_start = $('#filter_start').val();
            var filter_end = $('#filter_end').val();

            var swalConfirmText = "Vous ne pourrez pas revenir en arrière!";
            var swalConfirmTitle = "Êtes-vous sûr de bien vouloir valider la ligne?";
            Swal.fire({
                title: swalConfirmTitle,
                text: swalConfirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Oui, valider!"
            }).then(function(result) {
                if (result.value) {
                    KTApp.blockPage();
                    $.ajax({
                        url: '/api/validate/contract/schedulecontacts',
                        type: 'post',
                        data: {
                            contract_id: contract_id,
                            filter_start: filter_start,
                            filter_end: filter_end,
                        },
                        dataType: 'json',
                        success: function(result, status) {
                            if (result.success) {
                                _showResponseMessage('success', result.msg);
                            } else {
                                _showResponseMessage('error', result.msg);
                            }
                        }
                    }).done(function(data) {
                        KTApp.unblockPage();
                    });
                }
            });
        }

        function _formPointage(schedulecontact_id) {
            var modal_id = 'modal_form_pointage';
            var modal_content_id = 'modal_form_pointage_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/pointage/' + schedulecontact_id,
                type: 'GET',
                dataType: 'html',
                success: function(html, status) {
                    $('#' + modal_content_id).html(html);
                }
            });
        }

        function _sendValidatedInfosByMail() {
            var filter_start = $('#filter_start').val();
            var filter_end = $('#filter_end').val();
            KTApp.blockPage();
            $.ajax({
                url: '/api/sendemail/validated/contracts/schedulecontacts',
                type: 'post',
                data: {
                    filter_start: filter_start,
                    filter_end: filter_end,
                },
                dataType: 'json',
                success: function(result, status) {
                    if (result.success) {
                        _showResponseMessage('success', result.msg);
                    } else {
                        _showResponseMessage('error', result.msg);
                    }
                }
            }).done(function(data) {
                KTApp.unblockPage();
            });
        }
    </script>
@endsection
