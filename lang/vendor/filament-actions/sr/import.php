<?php

return [

    'label' => 'Uvoz: :label',
    'modal' => [
        'heading' => 'Uvoz: :label',
        'form' => [
            'file' => [
                'label' => 'Fajl',
                'placeholder' => 'Otpremite CSV fajl',
                'rules' => [
                    'duplicate_columns' => '{0} Fajl ne sme imati više od jednog praznog zaglavlja kolone.|{1,*} Fajl ne sme imati duplirana zaglavlja kolona: :columns.',
                ],
            ],

            'columns' => [
                'label' => 'Kolone',
                'placeholder' => 'Izaberite kolonu',
            ],
        ],

        'actions' => [
            'download_example' => [
                'label' => 'Preuzmi primer CSV fajla',
            ],

            'import' => [
                'label' => 'Uvezi',
            ],
        ],
    ],

    'notifications' => [
        'completed' => [
            'title' => 'Uvoz je završen',
            'actions' => [
                'download_failed_rows_csv' => [
                    'label' => 'Preuzmi podatke o neuspelom redu|Preuzmi podatke o neuspelim redovima|Preuzmi podatke o neuspelim redovima',
                ],
            ],
        ],

        'max_rows' => [
            'title' => 'CSV fajl je prevelik',
            'body' => 'Ne možete uvesti više od :count reda odjednom.|Ne možete uvesti više od :count reda odjednom.|Ne možete uvesti više od :count redova odjednom.',
        ],

        'started' => [
            'title' => 'Uvoz je počeo',
            'body' => 'Uvoz je počeo i :count red će biti obrađen u pozadini.|Uvoz je počeo i :count reda će biti obrađena u pozadini.|Uvoz je počeo i :count redova će biti obrađeno u pozadini.',
        ],
    ],

    'example_csv' => [
        'file_name' => ':importer-primer',
    ],

    'failure_csv' => [
        'file_name' => 'uvoz-:import_id-:csv_name-neuspeli-redovi',
        'error_header' => 'greška',
        'system_error' => 'Sistemska greška, obratite se podršci.',
        'column_mapping_required_for_new_record' => 'Kolona :attribute nije povezana ni sa jednom kolonom u fajlu, a obavezna je za kreiranje novih zapisa.',
    ],

];
