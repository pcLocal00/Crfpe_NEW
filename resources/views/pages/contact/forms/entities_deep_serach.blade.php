<!--begin::Card-->
<div class="modal-header">
    <span class="modal-title">Recherche avancée (Entitiés)</span>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="modal-body">
    <table class="table table-bordered table-checkable" id="dt_serach_entities">
        <thead>
            <tr>
                {{-- <th></th> --}}
                <th>Type</th>
                <th>Nom</th>
                <th>Infos</th>
                <th>Rôles</th>
                <th>Contacts</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>


<script>
    $(document).ready(function() {
        var entities_deep_search_dt = $('#dt_serach_entities').dataTable({
            ajax: {
                url: '/api/sdt/deepsearch/entities/load',
                type: 'GET',
                data: {
                    pagination: {
                        perpage: 50,
                    },
                },
            },
            // responsive: {
            //     breakpoints: [{name: 'contacts', width: 0}]
            //     details: {
            //         targets: [4]
            //     }
            // },
            // "columnDefs": [
            //     {
            //         "name": "contacts",
            //         "targets": 4
            //     },
            // ],
            processing: true,
            paging: true,
            serverSide: true,
            lengthMenu: [5, 10, 25, 50],
            pageLength: 5,
            columnDefs: [{
                    targets: 0,
                    width: '20px',
                    // orderable: false,
                    render: function(data, type, row, meta) {
                        return `<span class="text-primary">${row.entity_type == 'P' ? 'Personne' : 'Société'}</span>`;
                    }
                },
                {
                    targets: 1,
                    width: '100px',
                    render: function(data, type, row, meta) {
                        var name = row.name;
                        if (row.entity_type === 'P') {
                            const contacts = row.contacts ? row.contacts.split('##') ?? [] : [];
                            name = contacts.length > 0 ? contacts[0].split('*')[1] : row.name;
                        }
                        return `<div class="text-dark-75 mb-2">${row.ref}</div>
                        <div class="text-dark-75 font-weight-bolder mb-2">${name}</div>`;
                    }
                },
                {
                    targets: 2,
                    width: '100px',
                    render: function (data, type, row, meta) {
                        const addresses = row.addresses ? row.addresses.split('##') ?? [] : [];
                        const adrSet = new Set(addresses);
                        var column = '';
                        [...adrSet].map((a) => {
                            column += `
                            <div class="text-body mb-2">
                                <i class="fa fa-map-marker text-primary"></i> ${ a }
                            </div>
                            `;
                        });
                        return column;
                    }
                },
                {
                    targets: 3,
                    width: '65px',
                    orderable: false,
                    render: function(data, type, row, meta) {
                        if (row.is_client)
                            return `<p class="font-size-xs"><span class="label label-lg label-dot label-primary"></span>Client</p>`;

                        if (row.is_funder)
                            return `<p class="font-size-xs"><span class="label label-lg label-dot label-primary"></span>Financeur</p>`;

                        if (row.is_former)
                            return `<p class="font-size-xs"><span class="label label-lg label-dot label-primary"></span>Formateur</p>`;

                        if (row.is_stage_site)
                            return `<p class="font-size-xs"><span class="label label-lg label-dot label-primary"></span>Terrain</p>`;

                        if (row.is_prospect)
                            return `<p class="font-size-xs"><span class="label label-lg label-dot label-primary"></span>Prospect</p>`;
                        return '';
                    }
                },
                {
                    targets: 4,
                    width: '100px',
                    render: function(data, type, row, meta) {
                        // if (row.entity_type == 'P') return '';
                        const contacts = row.contacts ? row.contacts.split('##') ?? [] : [];
                        var column = '';
                        contacts.map((contact) => {
                            const parts = contact.split('*');
                            // if (parts[0] == 'null') return true;
                            column += `
                            <p class="font-size-xs">
                                <span class="label label-lg label-dot label-primary"></span>
                                ${ parts[1] } (${ parts[2] ?? 'Mail non renseigné' })
                                <button class="btn btn-primary btn-xs py-0 pl-1 pr-0"
                                 onclick="_selectContact(event, ${ parts[0] }, ${ row.id })"><i class="fa fa-check"></i></button>
                            </p>
                            `;
                        });
                        return column;
                    }
                },
                {
                    targets: 5,
                    orderable: false,
                    width: '40px',
                    render: function(data, type, row, meta) {
                        var contact = 0;
                        if (row.entity_type == 'P') {
                            const contacts = row.contacts ? row.contacts.split('##') ?? [] : [];
                            if (contacts.length > 0) {
                                contact = contacts[0].split('*')[0];
                            }
                        }
                        return `<button class="btn btn-success btn-sm"
                            onclick="_selectEntity(event, ${ row.id }, ${contact})">Sélectionner</button>`;
                    }
                },
            ],
        });
    });


    $(document).ajaxSend(function(event, jqxhr, settings) {
        // console.log(event, jqxhr, settings);
        if (
            typeof entities_deep_search_dt !== 'undefined' &&
            settings.url.indexOf('/api/sdt/deepsearch/entities/load') >= 0
        ) {
            entities_deep_search_dt.DataTable().settings().map((setting) => {
                setting.jqXHR.abort();
            });
        }
    });

    function _selectEntity(e, e_id, c_id) {
        e.preventDefault();
        $('select.entity-deep-search').select2().val(e_id).trigger("change", [c_id]);
        $('#modal_dt_entities').modal('hide');
    }

    function _selectContact(e, c_id, e_id) {
        e.preventDefault();
        $('select.entity-deep-search').select2().val(e_id).trigger("change", [c_id]);
        $('#modal_dt_entities').modal('hide');
    }
</script>
