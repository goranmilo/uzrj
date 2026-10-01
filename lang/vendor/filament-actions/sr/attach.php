<?php

return [

    'single' => [
        'label' => 'Priključi',
        'modal' => [
            'heading' => 'Priključivanje: :label',
            'fields' => [
                'record_id' => [
                    'label' => 'Zapis',
                ],
            ],

            'actions' => [
                'attach' => [
                    'label' => 'Priključi',
                ],

                'attach_another' => [
                    'label' => 'Priključi i nastavi',
                ],
            ],
        ],

        'notifications' => [
            'attached' => [
                'title' => 'Priključeno',
            ],
        ],
    ],

];
