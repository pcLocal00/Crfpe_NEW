<?php
// Aside menu
return [

    'items' => [
        // Dashboard
        [
            'title' => 'Tableau de bord',
            'root' => true,
            'icon' => 'flaticon-dashboard',
            'page' => '/',
            'new-tab' => false,
        ],

        // Custom
        [
            'section' => 'CRFPE',
        ],
        [
            'title' => 'Référentiels',
            'icon' => 'media/svg/icons/Layout/Layout-4-blocks.svg',
            'bullet' => 'line',
            'root' => true,
            'submenu' => [
                [
                    'title' => 'Produits de formation',
                    'page' => 'formations',
                ],
                [
                    'title' => 'Clients',
                    'page' => 'clients'
                ],
                [
                    'title' => 'Personnes',
                    'page' => 'contacts'
                ],
                [
                    'title' => 'Import',
                    'page' => 'import'
                ],
            ]
        ],
        [
            'title' => 'AF & Sessions',
            'icon' => 'media/svg/icons/Shopping/Barcode-read.svg',
            'bullet' => 'dot',
            'root' => true,
            'submenu' => [
                // [
                //     'title' => 'Pré planifications',
                //     'page' => 'planifications'
                // ],
                [
                    'title' => 'Pré planifications',
                    'page' => 'list/planifications'
                ],
                [
                    'title' => 'AF',
                    'page' => 'afs'
                ],
                [
                    'title' => 'Sessions',
                    'page' => 'sessions'
                ],
                [
                    'title' => 'Stages',
                    'page' => 'stages'
                ],
                [
                    'title' => 'Agenda',
                    'page' => 'agenda'
                ],
                [
                    'title' => 'Presences',
                    'page' => 'presences'
                ],
                [
                    'title' => 'Planning journalier',
                    'page' => 'dailyschedule'
                ]
            ]
        ],
        [
            'title' => 'Certifications',
            'desc' => '',
            'icon' => 'media/svg/icons/Design/Bucket.svg',
            'bullet' => 'dot',
            'root' => true,
            'submenu' => [
                [
                    'title' => 'Certifications',
                    'page' => '#'
                ]
            ]
        ],
        [
            'title' => 'Commerce',
            'desc' => '',
            'icon' => 'media/svg/icons/Code/Compiling.svg',
            'bullet' => 'dot',
            'root' => true,
            'submenu' => [
                [
                    'title' => 'Devis',
                    'page' => 'devis'
                ],
                [
                    'title' => 'Conventions et contrats',
                    'page' => 'agreements'
                ],
                [
                    'title' => 'Factures',
                    'page' => 'invoices'
                ],
                [
                    'title' => 'Avoirs',
                    'page' => 'avoirs'
                ],
                [
                    'title' => 'Convocations',
                    'page' => 'convocations'
                ],
            ]
        ],
        [
            'title' => 'RH',
            'icon' => 'media/svg/icons/General/Settings-1.svg',
            'bullet' => 'dot',
            'root' => true,
            'submenu' => [
                [
                    'title' => 'Contrôle de paie',
                    'page' => 'payments'
                ],
                [
                    'title' => 'Contrats des intervenants',
                    'page' => 'contrats-intervenants'
                ],

            ]
        ],
        [
            'title' => 'Finances',
            'icon' => 'media/svg/icons/Design/PenAndRuller.svg',
            'root' => true,
            'bullet' => 'dot',
            'submenu' => [
                [
                    'title' => 'Contrôle de factures',
                    'page' => '/control/invoices'
                ],
            ]
        ],
        [
            'title' => 'TDB',
            'icon' => 'media/svg/icons/Layout/Layout-left-panel-2.svg',
            'bullet' => 'dot',
            'root' => true,
            'submenu' => [
                [
                    'title' => 'Exportation TDB',
                    'page' => '/tdb'
                ],
            ]
        ],
        [
            'title' => 'CRM',
            'icon' => 'media/svg/icons/Layout/Layout-left-panel-3.svg',
            'bullet' => 'dot',
            'root' => true,
            'submenu' => [
                [
                    'title' => 'Statistiques des tâches',
                    'page' => '/getstatustasks'
                ],
                [
                    'title' => 'Mes tâches',
                    'page' => '/gettasks'
                ],
                [
                    'title' => 'Ajouter une tâche',
                    'page' => '/addtask'
                ],
            ]
        ],
        [
            'section' => 'ADMINISTRATION',
        ],
        //Compte
        [
            'title' => 'Comptes',
            'root' => true,
            'icon' => 'flaticon-users',
            'page' => 'users',
            'new-tab' => false,
        ],
        // catalogues
        [
            'title' => 'Arborescence des produits',
            'root' => true,
            'icon' => 'flaticon-map',
            'page' => 'admin/catalogues',
            'new-tab' => false,
        ],
        // Structure Temps
        [
            'title' => 'Structure temporelle',
            'root' => true,
            'icon' => 'flaticon-map',
            'page' => 'admin/structure_temps',
            'new-tab' => false,
        ],

        // params
        [
            'title' => 'Paramétrages',
            'root' => true,
            'icon' => 'flaticon-settings-1',
            'page' => 'admin/parametrages',
            'new-tab' => false,
        ],
        [
            'title' => 'Modèles de plannification',
            'root' => true,
            'icon' => 'flaticon-settings-1',
            'page' => 'admin/ptemplates',
            'new-tab' => false,
        ],
        //prices
        [
            'title' => 'Gestion des tarifs',
            'root' => true,
            'icon' => 'flaticon-settings-1',
            'page' => 'admin/prices',
            'new-tab' => false,
        ],
        //ressources
        [
            'title' => 'Gestion des ressources',
            'root' => true,
            'icon' => 'flaticon-settings-1',
            'page' => 'admin/ressources',
            'new-tab' => false,
        ],
        //documents
        [
            'title' => 'Gestion des documents',
            'root' => true,
            'icon' => 'flaticon-settings-1',
            'page' => 'admin/documents',
            'new-tab' => false,
        ],
        //email
        [
            'title' => 'Gestion des emails',
            'root' => true,
            'icon' => 'flaticon-settings-1',
            'page' => 'admin/emails',
            'new-tab' => false,
        ],
        //indexes
        [
            'title' => 'Gestion des indexes',
            'root' => true,
            'icon' => 'flaticon-settings-1',
            'page' => 'admin/indexes',
            'new-tab' => false,
        ],
        // logs
        [
            'title' => 'Logs',
            'root' => true,
            'icon' => 'fas fa-history',
            'page' => 'admin/logs',
            'new-tab' => false,
        ],
    ]

];
