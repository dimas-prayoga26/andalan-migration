<?php

$catchAllUsername = fn (string $domain): string => str_replace(
    '{domain}',
    $domain,
    env('CATCHALL_MAIL_USERNAME', 'catchall-temp@{domain}'),
);
$catchAllPassword = env('CATCHALL_MAIL_PASSWORD');

return [
    'accounts' => [
        'rnb' => [
            'host' => env('RNB_IMAP_HOST', env('RNB_MAIL_HOST', 'mail.rnb.co.id')),
            'port' => env('RNB_IMAP_PORT', 993),
            'encryption' => env('RNB_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('RNB_IMAP_USERNAME', $catchAllUsername('rnb.co.id')),
            'password' => env('RNB_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('RNB_IMAP_VERIFY_SSL', true),
        ],
        'niskala' => [
            'host' => env('NISKALA_IMAP_HOST', env('NISKALA_MAIL_HOST', 'mail.coffeeniskala.com')),
            'port' => env('NISKALA_IMAP_PORT', 993),
            'encryption' => env('NISKALA_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('NISKALA_IMAP_USERNAME', $catchAllUsername('coffeeniskala.com')),
            'password' => env('NISKALA_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('NISKALA_IMAP_VERIFY_SSL', true),
        ],
        'tms' => [
            'host' => env('TMS_IMAP_HOST', env('TMS_MAIL_HOST', 'mail.tims.co.id')),
            'port' => env('TMS_IMAP_PORT', 993),
            'encryption' => env('TMS_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('TMS_IMAP_USERNAME', $catchAllUsername('tims.co.id')),
            'password' => env('TMS_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('TMS_IMAP_VERIFY_SSL', true),
        ],
        'rne' => [
            'host' => env('RNE_IMAP_HOST', env('RNE_MAIL_HOST', 'mail.rne.co.id')),
            'port' => env('RNE_IMAP_PORT', 993),
            'encryption' => env('RNE_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('RNE_IMAP_USERNAME', $catchAllUsername('rne.co.id')),
            'password' => env('RNE_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('RNE_IMAP_VERIFY_SSL', true),
        ],
        'trah' => [
            'host' => env('TRAH_IMAP_HOST', env('TRAH_MAIL_HOST', 'mail.trah.co.id')),
            'port' => env('TRAH_IMAP_PORT', 993),
            'encryption' => env('TRAH_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('TRAH_IMAP_USERNAME', $catchAllUsername('trah.co.id')),
            'password' => env('TRAH_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('TRAH_IMAP_VERIFY_SSL', true),
        ],
        'kma' => [
            'host' => env('KMA_IMAP_HOST', env('KMA_MAIL_HOST', 'mail.karpetmerah.id')),
            'port' => env('KMA_IMAP_PORT', 993),
            'encryption' => env('KMA_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('KMA_IMAP_USERNAME', $catchAllUsername('karpetmerah.id')),
            'password' => env('KMA_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('KMA_IMAP_VERIFY_SSL', true),
        ],
    ],
];
