<?php

return [

    'label' => 'Izvoz: :label',
    'modal' => [
        'heading' => 'Izvoz: :label',
        'form' => [
            'columns' => [
                'label' => 'Kolone',
                'form' => [
                    'is_enabled' => [
                        'label' => ':column uključena',
                    ],

                    'label' => [
                        'label' => 'Naziv kolone :column',
                    ],
                ],
            ],
        ],

        'actions' => [
            'export' => [
                'label' => 'Izvezi',
            ],
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Izvoz je završen',
            'actions' => [
                'download_csv' => [
                    'label' => 'Preuzmi .csv',
                ],

                'download_xlsx' => [
                    'label' => 'Preuzmi .xlsx',
                ],
            ],
        ],

        'max_rows' => [
            'title' => 'Izvoz je prevelik',
            'body' => 'Ne možete izvesti više od :count reda odjednom.|Ne možete izvesti više od :count reda odjednom.|Ne možete izvesti više od :count redova odjednom.',
        ],

        'started' => [
            'title' => 'Izvoz je počeo',
            'body' => 'Izvoz je počeo i :count red će biti obrađen u pozadini. Dobićete obaveštenje sa linkom za preuzimanje kada bude gotov.|Izvoz je počeo i :count reda će biti obrađena u pozadini. Dobićete obaveštenje sa linkom za preuzimanje kada bude gotov.|Izvoz je počeo i :count redova će biti obrađeno u pozadini. Dobićete obaveštenje sa linkom za preuzimanje kada bude gotov.',
        ],
    ],

    'file_name' => 'izvoz-:export_id-:model',

];
