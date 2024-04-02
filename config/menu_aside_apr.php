<?php
// Aside menu
return [

    'items' => [

        // Custom
        [
            'section' => 'CRFPE',
        ],
        [
            'title' => 'AF & Sessions',
            'icon' => 'media/svg/icons/Shopping/Barcode-read.svg',
            'bullet' => 'dot',
            'root' => true,
            'page' => '/agenda',
            'submenu' => [
                [
                    'title' => 'Agenda',
                    'page' => 'agenda'
                ],
            ]
        ],
    ]

];
