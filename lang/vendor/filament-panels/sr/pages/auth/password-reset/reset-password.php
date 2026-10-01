<?php

return [

    'title' => 'Promena lozinke',
    'heading' => 'Promena lozinke',
    'form' => [
        'email' => [
            'label' => 'E-mail adresa',
        ],

        'password' => [
            'label' => 'Lozinka',
            'validation_attribute' => 'lozinka',
        ],

        'password_confirmation' => [
            'label' => 'Potvrda lozinke',
        ],

        'actions' => [
            'reset' => [
                'label' => 'Promeni lozinku',
            ],
        ],
    ],

    'notifications' => [
        'throttled' => [
            'title' => 'Previše pokušaja',
            'body' => 'Pokušajte ponovo za :seconds s.',
        ],
    ],

];
