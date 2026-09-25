<?php

return [
    'accounts' => [
        'rnb' => [
            'host' => env('RNB_MAIL_HOST', 'mail.rnb.co.id'),
            'port' => env('RNB_IMAP_PORT', 993),
            'encryption' => env('RNB_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('RNB_MAIL_USERNAME', 'hr@rnb.co.id'),
            'password' => env('RNB_MAIL_PASSWORD'),
        ],
        'niskala' => [
            'host' => env('NISKALA_MAIL_HOST', 'mail.coffeeniskala.com'),
            'port' => env('NISKALA_IMAP_PORT', 993),
            'encryption' => env('NISKALA_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('NISKALA_MAIL_USERNAME', 'hr@coffeeniskala.com'),
            'password' => env('NISKALA_MAIL_PASSWORD'),
        ],
        'tms' => [
            'host' => env('TMS_MAIL_HOST', 'mail.tims.co.id'),
            'port' => env('TMS_IMAP_PORT', 993),
            'encryption' => env('TMS_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('TMS_MAIL_USERNAME', 'hr@tims.co.id'),
            'password' => env('TMS_MAIL_PASSWORD'),
        ],
        'rne' => [
            'host' => env('RNE_MAIL_HOST', 'mail.rne.co.id'),
            'port' => env('RNE_IMAP_PORT', 993),
            'encryption' => env('RNE_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('RNE_MAIL_USERNAME', 'hr@rne.co.id'),
            'password' => env('RNE_MAIL_PASSWORD'),
        ],
        'trah' => [
            'host' => env('TRAH_MAIL_HOST', 'mail.trah.co.id'),
            'port' => env('TRAH_IMAP_PORT', 993),
            'encryption' => env('TRAH_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('TRAH_MAIL_USERNAME', 'hr@trah.co.id'),
            'password' => env('TRAH_MAIL_PASSWORD'),
        ],
        'kma' => [
            'host' => env('KMA_MAIL_HOST', 'mail.karpetmerah.id'),
            'port' => env('KMA_IMAP_PORT', 993),
            'encryption' => env('KMA_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('KMA_MAIL_USERNAME', 'hr@karpetmerah.id'),
            'password' => env('KMA_MAIL_PASSWORD'),
        ],
    ],
];
