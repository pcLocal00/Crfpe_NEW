{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Contacts</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1">Liste des contacts</span>
            </div>
            <div class="card-toolbar">
            <!--begin::Dropdown-->
            <div class="dropdown dropdown-inline mr-2">
            @if(auth()->user()->roles[0]->code!='FORMATEUR')   
                <button type="button" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="la la-download"></i></button>
            @endif
                <!--begin::Dropdown Menu-->
                <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                    <!--begin::Navigation-->
                    <ul class="navi flex-column navi-hover py-2">
                        {{-- <li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">Choose an option:</li> --}}
                       <!--  <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-print"></i>
                                </span>
                                <span class="navi-text">Print</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-copy"></i>
                                </span>
                                <span class="navi-text">Copy</span>
                            </a>
                        </li> -->
                        <li class="navi-item">
                            <a href="{{ url('/api/sdt/personne/export') }}" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-excel-o"></i>
                                </span>
                                <span class="navi-text">Excel</span>
                            </a>
                        </li>
                        <!-- <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-text-o"></i>
                                </span>
                                <span class="navi-text">CSV</span>
                            </a>
                        </li>
                        <li class="navi-item">
                            <a href="#" class="navi-link">
                                <span class="navi-icon">
                                    <i class="la la-file-pdf-o"></i>
                                </span>
                                <span class="navi-text">PDF</span>
                            </a>
                        </li> -->
                    </ul>
                    <!--end::Navigation-->
                </div>
                <!--end::Dropdown Menu-->
            </div>
            <!--end::Dropdown-->

            @if(auth()->user()->roles[0]->code!='FORMATEUR')   
                <button onclick="_formContact(0,0)" class="btn btn-sm btn-icon btn-light-primary mr-2">
                    <i class="flaticon2-add-1"></i>
                </button>

                <button onclick="_create_account_personnes()" class="btn btn-sm btn-icon btn-light-info mr-2" title="Créer les comptes">
                    <i class="flaticon-add"></i>
                </button>
            @endif

                <button onclick="_reload_dt_contacts()" class="btn btn-sm btn-icon btn-light-info mr-2">
                    <i class="flaticon-refresh"></i>
                </button>

                
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <input type="hidden" value="{{$contact_id}}" id="INPUT_CONTACT_ID">
            <input type="hidden" value="{{$entity_id}}" id="INPUT_ENTITY_ID">
            <!--begin::filter-->
            <x-filter-form type="Contacts" />
            <!--end::filter-->

            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_contacts">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Infos</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Actions</th>

                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <!--end::Card-->
    <x-modal id="modal_form_contact" content="modal_form_contact_content"/>

@endsection

{{-- Styles Section --}}
@section('styles')
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection


{{-- Scripts Section --}}
@section('scripts')
    {{-- vendors --}}
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/jquery-validation-defaults.js') }}"></script>
    <script src="{{ asset('custom/plugins/jquery-validation/localization/messages_fr.js') }}"></script>

    {{-- page scripts --}}
    <script src="{{ asset('custom/js/general.js?v=0') }}"></script>
    <script src="{{ asset('custom/js/list-contacts.js?v=3') }}"></script>
    <script>

        function getCheckedCheckboxesFor(checkboxName) {
            var checkboxes = document.querySelectorAll('input[name="' + checkboxName + '"]:checked'), values = [];
            Array.prototype.forEach.call(checkboxes, function(el) {
                values.push(el.value);
            });
            return values;
        }

        var _create_account_personnes = function() {

        var table = $('#dt_contacts').DataTable();

        $('#dt_contacts tbody').on('click', 'tr', function () {
            $(this).toggleClass('active');
        });

        var count = table.rows('.active').data().length;
        var dataselected= [];

        $("input:checkbox[class=checkable]:checked").each(function () {
            dataselected.push($(this).val());
        });

        var successMsg = "Les utilisateurs ont été bien créés.";
        var errorMsg = "La création ne marche pas.";
        var swalConfirmTitle = "Créer les utilisateurs!";
        var swalConfirmText ="Êtes-vous sûr de bien créer Les utilisateur? Si oui merci de saisir le rôle!";

        var formData=[];
        if (dataselected.length <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Veuillez sélectionner un ou plusieurs contact(s)!',
            });
            return false;
        }

        Swal.fire({
            title: swalConfirmTitle,
            html:'<span class="mr-4"><input type="radio" id="role" name="role" value="4" class="role"> <label for="role">Formateur</label></span><span><input type="radio" id="role" name="role" value="1" class="role"> <label for="role">Etudiant</label></span>',
            text: swalConfirmText,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Créer",
            cancelButtonText: "Non",
            preConfirm: () => {
                var result = getCheckedCheckboxesFor('role');
                if(result==""){
                    Swal.showValidationMessage(
                        `Merci de sélectionner un rôle!`
                    )
                }
            }
        }).then(function(result) {
            var role = getCheckedCheckboxesFor('role');

            formData = formData.concat([
                {name: "count", value: count},
                {name: "data", value: dataselected},
                {name: "role", value: role},
                {name: "type", value: 'personnes'}
            ]);
            
            if (result.value) {
                $.ajax({
                    type: 'POST',
                    url: '/api/sdt/createAccounts',
                    data: formData,
                    dataType: 'JSON',
                    success: function(result) {
                        result.forEach(elem => {
                            if(elem.success){
                                toastr.success(elem.msg);
                            }else{
                                toastr.error(elem.msg);
                            }
                        });
                    }
                });
            }
        });
        }
    </script>
@endsection
