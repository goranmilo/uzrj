<?php

return [

    'title' => 'Promena lozinke',
    'heading' => 'Zaboravljena lozinka?',
    'actions' => [
        'login' => [
            'label' => 'nazad na prijavu',
        ],
    ],

    'form' => [
        'email' => [
            'label' => 'E-mail adresa',
        ],

        'actions' => [
            'request' => [
                'label' => 'Pošalji e-mail',
            ],
        ],
    ],

    'notifications' => [
        'sent' => [
            'body' => 'Ako nalog sa tom adresom ne postoji, e-mail neće biti poslat.',
        ],

        'throttled' => [
            'title' => 'Previše zahteva',
            'body' => 'Pokušajte ponovo za :seconds s.',
        ],
    ],

];
