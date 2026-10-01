<?php

return [

    'title' => 'Registracija',
    'heading' => 'Registracija',
    'actions' => [
        'login' => [
            'before' => 'ili',
            'label' => 'prijavite se',
        ],
    ],

    'form' => [
        'email' => [
            'label' => 'E-mail adresa',
        ],

        'name' => [
            'label' => 'Ime',
        ],

        'password' => [
            'label' => 'Lozinka',
            'validation_attribute' => 'lozinka',
        ],

        'password_confirmation' => [
            'label' => 'Potvrda lozinke',
        ],

        'actions' => [
            'register' => [
                'label' => 'Registruj se',
            ],
        ],
    ],

    'notifications' => [
        'throttled' => [
            'title' => 'Previše pokušaja registracije',
            'body' => 'Pokušajte ponovo za :seconds s.',
        ],
    ],

];
