<?php

return [

    'column_toggle' => [
        'heading' => 'Kolone',
    ],

    'columns' => [
        'actions' => [
            'label' => 'Akcija|Akcije|Akcije',
        ],

        'text' => [
            'actions' => [
                'collapse_list' => 'Prikaži :count manje',
                'expand_list' => 'Prikaži još :count',
            ],

            'more_list_items' => 'i još :count',
        ],
    ],

    'fields' => [
        'bulk_select_page' => [
            'label' => 'Izaberi/poništi izbor svih stavki za grupne akcije.',
        ],

        'bulk_select_record' => [
            'label' => 'Izaberi/poništi izbor stavke :key za grupne akcije.',
        ],

        'bulk_select_group' => [
            'label' => 'Izaberi/poništi izbor grupe :title za grupne akcije.',
        ],

        'search' => [
            'label' => 'Pretraga',
            'placeholder' => 'Pretraga',
            'indicator' => 'Pretraga',
        ],
    ],

    'summary' => [
        'heading' => 'Rezime',
        'subheadings' => [
            'all' => 'Ukupno (:label)',
            'group' => 'Rezime: :group',
            'page' => 'Ova stranica',
        ],

        'summarizers' => [
            'average' => [
                'label' => 'Prosek',
            ],

            'count' => [
                'label' => 'Broj',
            ],

            'sum' => [
                'label' => 'Zbir',
            ],
        ],
    ],

    'actions' => [
        'disable_reordering' => [
            'label' => 'Završi promenu redosleda',
        ],

        'enable_reordering' => [
            'label' => 'Promeni redosled',
        ],

        'filter' => [
            'label' => 'Filter',
        ],

        'group' => [
            'label' => 'Grupisanje',
        ],

        'open_bulk_actions' => [
            'label' => 'Grupne akcije',
        ],

        'toggle_columns' => [
            'label' => 'Prikaz kolona',
        ],
    ],

    'empty' => [
        'heading' => 'Nema zapisa',
        'description' => 'Dodajte prvi zapis da biste počeli.',
    ],

    'filters' => [
        'actions' => [
            'apply' => [
                'label' => 'Primeni filtere',
            ],

            'remove' => [
                'label' => 'Ukloni filter',
            ],

            'remove_all' => [
                'label' => 'Ukloni sve filtere',
                'tooltip' => 'Ukloni sve filtere',
            ],

            'reset' => [
                'label' => 'Poništi',
            ],
        ],

        'heading' => 'Filteri',
        'indicator' => 'Aktivni filteri',
        'multi_select' => [
            'placeholder' => 'Sve',
        ],

        'select' => [
            'placeholder' => 'Sve',
        ],

        'trashed' => [
            'label' => 'Obrisani zapisi',
            'only_trashed' => 'Samo obrisani',
            'with_trashed' => 'Sa obrisanima',
            'without_trashed' => 'Bez obrisanih',
        ],
    ],

    'grouping' => [
        'fields' => [
            'group' => [
                'label' => 'Grupiši po',
                'placeholder' => 'Grupiši po',
            ],

            'direction' => [
                'label' => 'Smer grupisanja',
                'options' => [
                    'asc' => 'Rastuće',
                    'desc' => 'Opadajuće',
                ],
            ],
        ],
    ],

    'reorder_indicator' => 'Prevucite zapise da promenite redosled.',
    'selection_indicator' => [
        'selected_count' => 'Izabran :count zapis|Izabrana :count zapisa|Izabrano :count zapisa',
        'actions' => [
            'select_all' => [
                'label' => 'Izaberi svih :count',
            ],

            'deselect_all' => [
                'label' => 'Poništi izbor',
            ],
        ],
    ],

    'sorting' => [
        'fields' => [
            'column' => [
                'label' => 'Sortiraj po',
            ],

            'direction' => [
                'label' => 'Smer sortiranja',
                'options' => [
                    'asc' => 'Rastuće',
                    'desc' => 'Opadajuće',
                ],
            ],
        ],
    ],

];
