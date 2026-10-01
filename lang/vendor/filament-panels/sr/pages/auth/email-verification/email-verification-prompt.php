<?php

return [

    'title' => 'Potvrdite e-mail adresu',
    'heading' => 'Potvrdite e-mail adresu',
    'actions' => [
        'resend_notification' => [
            'label' => 'Pošalji ponovo',
        ],
    ],

    'messages' => [
        'notification_not_received' => 'Niste dobili e-mail?',
        'notification_sent' => 'Poslali smo e-mail na :email sa uputstvom za potvrdu adrese.',
    ],

    'notifications' => [
        'notification_resent' => [
            'title' => 'E-mail je ponovo poslat.',
        ],

        'notification_resend_throttled' => [
            'title' => 'Previše pokušaja ponovnog slanja',
            'body' => 'Pokušajte ponovo za :seconds s.',
        ],
    ],

];
