<?php
// Aside menu
return [

    'items' => [
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
                    'title' => 'Personnes',
                    'page' => 'contacts'
                ],
            ]
        ],
        [
            'title' => 'AF & Sessions',
            'icon' => 'media/svg/icons/Shopping/Barcode-read.svg',
            'bullet' => 'dot',
            'root' => true,
            'page' => '/agenda',
            'submenu' => [
                [
                    'title' => 'AF',
                    'page' => 'afs'
                ],
                [
                    'title' => 'Agenda',
                    'page' => 'agenda'
                ],
            ]
        ],
    ]

];
