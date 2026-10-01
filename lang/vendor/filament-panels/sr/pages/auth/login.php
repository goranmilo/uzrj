<?php

return [

    'title' => 'Prijava',
    'heading' => 'Prijava',
    'actions' => [
        'register' => [
            'before' => 'ili',
            'label' => 'napravite nalog',
        ],

        'request_password_reset' => [
            'label' => 'Zaboravljena lozinka?',
        ],
    ],

    'form' => [
        'email' => [
            'label' => 'E-mail adresa',
        ],

        'password' => [
            'label' => 'Lozinka',
        ],

        'remember' => [
            'label' => 'Zapamti me',
        ],

        'actions' => [
            'authenticate' => [
                'label' => 'Prijavi se',
            ],
        ],
    ],

    'messages' => [
        'failed' => 'Pogrešan e-mail ili lozinka.',
    ],

    'notifications' => [
        'throttled' => [
            'title' => 'Previše pokušaja prijave',
            'body' => 'Pokušajte ponovo za :seconds s.',
        ],
    ],

];
