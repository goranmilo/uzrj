<?php

return [

    'label' => 'Navigacija po stranicama',
    'overview' => '{1} Prikazan je 1 rezultat|[2,*] Prikazano :first–:last od ukupno :total',
    'fields' => [
        'records_per_page' => [
            'label' => 'Po stranici',
            'options' => [
                'all' => 'Sve',
            ],
        ],
    ],

    'actions' => [
        'first' => [
            'label' => 'Prva',
        ],

        'go_to_page' => [
            'label' => 'Idi na stranicu :page',
        ],

        'last' => [
            'label' => 'Poslednja',
        ],

        'next' => [
            'label' => 'Sledeća',
        ],

        'previous' => [
            'label' => 'Prethodna',
        ],
    ],

];
