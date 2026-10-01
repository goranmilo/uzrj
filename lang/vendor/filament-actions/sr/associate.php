<?php

return [

    'single' => [
        'label' => 'Poveži',
        'modal' => [
            'heading' => 'Povezivanje: :label',
            'fields' => [
                'record_id' => [
                    'label' => 'Zapis',
                ],
            ],

            'actions' => [
                'associate' => [
                    'label' => 'Poveži',
                ],

                'associate_another' => [
                    'label' => 'Poveži i nastavi',
                ],
            ],
        ],

        'notifications' => [
            'associated' => [
                'title' => 'Povezano',
            ],
        ],
    ],

];
