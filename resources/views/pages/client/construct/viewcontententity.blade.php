@if ($viewtype == 'overview')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Fiche de suivi</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1"></span>
            </div>
            <div class="card-toolbar">

            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">

            <input type="hidden" id="HIDDEN_INPUT_ENTITY_ID" value="{{ $row->id }}">
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_documents_entity">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Document</th>
                        <th>AF</th>
                        <th>Dates</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <!--end: Datatable-->

        </div>
    </div>
    <script>
        var dtUrlEd = '/api/sdt/entitydocuments/' + $('#HIDDEN_INPUT_ENTITY_ID').val();
        var table_documents_entity = $('#dt_documents_entity');
        // begin first table
        table_documents_entity.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            processing: true,
            paging: true,
            ordering: false,
            serverSide: false,
            ajax: {
                url: dtUrlEd,
                type: 'POST',
                data: {
                    pagination: {
                        perpage: 50,
                    },
                },
            },
            lengthMenu: [5, 10, 25, 50],
            pageLength: 10,
        });
    </script>
@endif

@if ($viewtype == 'entity')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Fiche client</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1">Informations générales</span>
            </div>
            <div class="card-toolbar">
                <button type="button" onclick="_formEntityView({{ $row->id }})"
                    class="btn btn-sm btn-icon btn-light-primary" data-toggle="tooltip" title="Modifier le client"><i
                        class="flaticon-edit"></i></button>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">

            @php
                $createdAt = $row->created_at ? $row->created_at->format('d/m/Y H:i') : '';
                $updatedAt = $row->updated_at ? $row->updated_at->format('d/m/Y H:i') : '';
                $deletedAt = $row->deleted_at ? $row->deleted_at->format('d/m/Y H:i') : '';
            @endphp
            <!-- Infos date : begin -->
            <div class="form-group row mb-0">
                <div class="col-lg-12">
                    @if ($createdAt)
                        <span class="label label-inline label-outline-info mr-2">Crée le :
                            {{ $createdAt }}</span>
                    @endif
                    @if ($updatedAt)
                        <span class="label label-inline label-outline-info mr-2">Modifié le :
                            {{ $updatedAt }}</span>
                    @endif
                </div>
                @if ($deletedAt)
                    <div class="col-lg-12 mt-5">
                        <div class="alert alert-custom alert-outline-info fade show mb-0" role="alert">
                            <div class="alert-icon"><i class="flaticon-warning"></i></div>
                            <div class="alert-text">Archivé le : {{ $deletedAt }}</div>
                        </div>
                    </div>
                @endif
            </div>
            <!-- Infos date : end -->

            <div class="form-group row mb-0">
                <div class="col-lg-6">
                    <div class="form-group row my-2">
                        <label class="col-4 col-form-label">Référence:</label>
                        <div class="col-8">
                            <span class="form-control-plaintext font-weight-bolder">{{ $row->ref }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row my-2 mb-0">
                        <label class="col-4 col-form-label">Activé:</label>
                        <div class="col-8">
                            <span class="form-control-plaintext"><span
                                    class="label label-inline label-{{ $row->is_active === 1 ? 'success' : 'danger' }} label-bold">{{ $row->is_active === 1 ? 'Oui' : 'Non' }}</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-lg-6">
                    <div class="form-group row my-2">
                        <label class="col-4 col-form-label">Nom complet:</label>
                        <div class="col-8">
                            <span class="form-control-plaintext font-weight-bolder">{{ $row->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 form-group">
                    @if ($row->is_client == 1)
                        <span class="label font-weight-bold label label-inline label-light-info">Client</span>
                    @endif
                    @if ($row->is_funder == 1)
                        <span class="label font-weight-bold label label-inline label-light-info">Financeur</span>
                    @endif
                    @if ($row->is_former == 1)
                        <span class="label font-weight-bold label label-inline label-light-info">Formateur</span>
                    @endif
                    @if ($row->is_stage_site == 1)
                        <span class="label font-weight-bold label label-inline label-light-info">Lieu
                            de stage</span>
                    @endif
                    @if ($row->is_prospect == 1)
                        <span class="label font-weight-bold label label-inline label-light-info">Prospect</span>
                    @endif
                </div>
            </div>

            <!--begin::CAS SOCIETE-->
            @if ($row->entity_type == 'S')

                <!--BEGIN::GROUPE-->
                @if ($row->parent)
                    @php
                        $parent = $row->parent->name . ' (' . $row->parent->ref . ')';
                    @endphp
                    <div class="form-group row mb-0">
                        <div class="col-lg-12">
                            <div class="form-group row my-2">
                                <label class="col-4 col-form-label text-warning"><i class="flaticon-home"></i> Société
                                    parente:</label>
                                <div class="col-8">
                                    <span class="form-control-plaintext font-weight-bolder text-warning"><a
                                            href="/view/entity/{{ $row->parent->id }}">{{ $parent }}</a></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <!--END::GROUPE-->

                <div class="form-group row mb-0">
                    <div class="col-lg-6">
                        <div class="form-group row my-2">
                            <label class="col-4 col-form-label">Forme:</label>
                            <div class="col-8">
                                <span class="form-control-plaintext font-weight-bolder">{{ $row->type }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row my-2">
                            <label class="col-4 col-form-label">Sigle:</label>
                            <div class="col-8">
                                <span
                                    class="form-control-plaintext font-weight-bolder">{{ $row->acronym ? $row->acronym : '--' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-lg-6">
                        <div class="form-group row my-2">
                            <label class="col-4 col-form-label">Siret:</label>
                            <div class="col-8">
                                <span
                                    class="form-control-plaintext font-weight-bolder">{{ $row->siret ? $row->siret : '--' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row my-2">
                            <label class="col-4 col-form-label">Siren:</label>
                            <div class="col-8">
                                <span
                                    class="form-control-plaintext font-weight-bolder">{{ $row->siren ? $row->siren : '--' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-lg-6">
                        <div class="form-group row my-2">
                            <label class="col-4 col-form-label">Type d’établissement:</label>
                            <div class="col-8">
                                <span
                                    class="form-control-plaintext font-weight-bolder">{{ $type_establishment }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row my-2">
                            <label class="col-4 col-form-label">Code NAF:</label>
                            <div class="col-8">
                                <span
                                    class="form-control-plaintext font-weight-bolder">{{ $row->naf_code ? $row->naf_code : '--' }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="col-lg-6">
                <div class="form-group row my-2">
                    <label class="col-4 col-form-label">Numéro TVA:</label>
                    <div class="col-8">
                        <span class="form-control-plaintext font-weight-bolder">{{ $row->tva ? $row->tva : '--' }}</span>
                    </div>
                </div>
            </div> -->
                </div>
                <div class="form-group row mb-0">
                    <div class="col-lg-6">
                        <div class="form-group row my-2">
                            <label class="col-4 col-form-label">Code Matricule:</label>
                            <div class="col-8">
                                <span
                                    class="form-control-plaintext font-weight-bolder">{{ $row->matricule_code ? $row->matricule_code : '--' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row my-2">
                            <label class="col-4 col-form-label">Code Tiers personnel:</label>
                            <div class="col-8">
                                <span
                                    class="form-control-plaintext font-weight-bolder">{{ $row->personal_thirdparty_code ? $row->personal_thirdparty_code : '--' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row my-2">
                            <label class="col-4 col-form-label">Code Fournisseur:</label>
                            <div class="col-8">
                                <span
                                    class="form-control-plaintext font-weight-bolder">{{ $row->vendor_code ? $row->vendor_code : '--' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <!--end::CAS SOCIETE-->

            <div class="form-group row mb-0">
                <div class="col-lg-6">
                    <div class="form-group row my-2">
                        <label class="col-4 col-form-label">Téléphone:</label>
                        <div class="col-8">
                            <span
                                class="form-control-plaintext font-weight-bolder">{{ $row->pro_phone ? $row->pro_phone : '--' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row my-2">
                        <label class="col-4 col-form-label">Portable:</label>
                        <div class="col-8">
                            <span
                                class="form-control-plaintext font-weight-bolder">{{ $row->pro_mobile ? $row->pro_mobile : '--' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-lg-6">
                    <div class="form-group row my-2">
                        <label class="col-4 col-form-label">Email:</label>
                        <div class="col-8">
                            <span
                                class="form-control-plaintext font-weight-bolder">{{ $row->email ? $row->email : '--' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row my-2">
                        <label class="col-4 col-form-label">Fax:</label>
                        <div class="col-8">
                            <span
                                class="form-control-plaintext font-weight-bolder">{{ $row->fax ? $row->fax : '--' }}</span>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-group row mb-0">
                <div class="col-lg-6">
                    <div class="form-group row my-2">
                        <label class="col-4 col-form-label">Zone de prospection:</label>
                        <div class="col-8">
                            <span
                                class="form-control-plaintext font-weight-bolder">{{ $row->prospecting_area ? $row->prospecting_area : '--' }}</span>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-lg-6">
                <div class="form-group row my-2">
                    <label class="col-4 col-form-label">Chargé d'affaire:</label>
                    <div class="col-8">
                        <span
                            class="form-control-plaintext font-weight-bolder">{{ $row->rep_id ? $row->rep_id : '--' }}</span>
                    </div>
                </div>
            </div> -->
            </div>

            <div class="separator separator-dashed my-5"></div>
            <p class="text-primary">Coordonnées banquaires :</p>
            <div class="form-group row mb-0">
                <div class="col-lg-6">
                    <div class="form-group row my-2">
                        <label class="col-4 col-form-label">IBAN:</label>
                        <div class="col-8">
                            <span
                                class="form-control-plaintext font-weight-bolder">{{ $row->iban ? $row->iban : '--' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row my-2">
                        <label class="col-4 col-form-label">BIC – Code SWIFT:</label>
                        <div class="col-8">
                            <span
                                class="form-control-plaintext font-weight-bolder">{{ $row->bic ? $row->bic : '--' }}</span>
                        </div>
                    </div>
                </div>
            </div>




            <!-- Groupe -->
            @if ($row->entity_type == 'S')
                @php
                    $nb_childs = $row->children()->count();
                @endphp
                @if ($nb_childs > 0)
                    <div class="form-group row mb-0">
                        <div class="col-lg-12">
                            <div class="accordion accordion-light  accordion-toggle-arrow" id="accordionExample5">
                                <div class="card">
                                    <div class="card-header" id="headingOne5">
                                        <div class="card-title" data-toggle="collapse" data-target="#collapseOne5">
                                            <i class="flaticon-list"></i> Groupe de {{ $nb_childs }} société(s)
                                        </div>
                                    </div>
                                    <div id="collapseOne5" class="collapse show" data-parent="#accordionExample5">
                                        <div class="card-body">
                                            <!--  -->
                                            <table class="table">
                                                <tbody>
                                                    @foreach ($row->children()->get() as $ch)
                                                        <tr>
                                                            <td> <a href="/view/entity/{{ $ch->id }}">{{ $ch->name }}
                                                                    ({{ $ch->ref }})
                                                                </a></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <!--  -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endif
            @endif
            <!-- Groupe -->

        </div>
        <!--end::Card-body-->
    </div>
    <!--end::Card-->
@endif

@if ($viewtype == 'contacts')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Contacts</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1">Liste des contacts</span>
            </div>
            <div class="card-toolbar">
                @if ($row->entity_type == 'S')
                    <button onclick="_formContact(0,{{ $row->id }})"
                        class="btn btn-sm btn-icon btn-light-primary mr-2">
                        <i class="flaticon2-add-1"></i>
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
            <input type="hidden" id="HIDDEN_INPUT_ENTITY_ID" value="{{ $row->id }}">
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_contacts">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Infos</th>
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
    <x-modal id="modal_form_contact" content="modal_form_contact_content" />
    <script>
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
            ajax: {
                url: '/api/sdt/contacts/' + $('#HIDDEN_INPUT_ENTITY_ID').val(),
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

        table_contacts.on('change', '.group-checkable', function() {
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

        table_contacts.on('change', 'tbody tr .checkbox', function() {
            $(this).parents('tr').toggleClass('active');
        });

        var _reload_dt_contacts = function() {
            $('#dt_contacts').DataTable().ajax.reload();
        }
        
        var _formContact = function(contact_id, entity_id) {
            var modal_id = 'modal_form_contact';
            var modal_content_id = 'modal_form_contact_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/contact/' + contact_id + '/' + entity_id,
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
    </script>
@endif

@if ($viewtype == 'adresses')
    <!--begin::Card-->
    <div class="card card-custom">
        <!--begin::Header-->
        <div class="card-header py-3">
            <div class="card-title align-items-start flex-column">
                <h3 class="card-label font-weight-bolder text-dark">Adresses</h3>
                <span class="text-muted font-weight-bold font-size-sm mt-1">Liste des adresses</span>
            </div>
            <div class="card-toolbar">
                <button onclick="_formAdresse(0,{{ $row->id }})"
                    class="btn btn-sm btn-icon btn-light-primary mr-2">
                    <i class="flaticon2-add-1"></i>
                </button>
                <button onclick="_reload_dt_adresses()" class="btn btn-sm btn-icon btn-light-info mr-2">
                    <i class="flaticon-refresh"></i>
                </button>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Card-body-->
        <div class="card-body">
            <input type="hidden" id="HIDDEN_INPUT_ENTITY_ID" value="{{ $row->id }}">
            <!--begin: Datatable-->
            <table class="table table-bordered table-checkable" id="dt_adresses">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Adresse</th>
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
    <x-modal id="modal_form_adresse" content="modal_form_adresse_content" />

    <script>
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var dt_adresses = $('#dt_adresses');
        // begin first table
        dt_adresses.DataTable({
            language: {
                url: "/custom/plugins/datatable/fr.json"
            },
            responsive: true,
            paging: true,
            ordering: false,
            processing: true,
            ajax: {
                url: '/api/sdt/adresses/' + $('#HIDDEN_INPUT_ENTITY_ID').val(),
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

        dt_adresses.on('change', '.group-checkable', function() {
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

        dt_adresses.on('change', 'tbody tr .checkbox', function() {
            $(this).parents('tr').toggleClass('active');
        });

        var _reload_dt_adresses = function() {
            $('#dt_adresses').DataTable().ajax.reload();
        }
        var _formAdresse = function(adresse_id, entity_id) {
            var modal_id = 'modal_form_adresse';
            var modal_content_id = 'modal_form_adresse_content';
            var spinner = '<div class="modal-body"><div class="spinner spinner-primary spinner-lg"></div></div>';
            $('#' + modal_id).modal('show');
            $('#' + modal_content_id).html(spinner);
            $.ajax({
                url: '/form/adresse/' + adresse_id + '/' + entity_id,
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
    </script>
@endif
