<?php

return [

    'single' => [
        'label' => 'Raskini vezu',
        'modal' => [
            'heading' => 'Raskidanje veze: :label',
            'actions' => [
                'dissociate' => [
                    'label' => 'Raskini vezu',
                ],
            ],
        ],

        'notifications' => [
            'dissociated' => [
                'title' => 'Veza raskinuta',
            ],
        ],
    ],

    'multiple' => [
        'label' => 'Raskini vezu za izabrano',
        'modal' => [
            'heading' => 'Raskidanje veze za izabrano: :label',
            'actions' => [
                'dissociate' => [
                    'label' => 'Raskini vezu',
                ],
            ],
        ],

        'notifications' => [
            'dissociated' => [
                'title' => 'Veza raskinuta',
            ],
        ],
    ],

];
