<?php

$catchAllUsername = fn (string $domain): string => str_replace(
    '{domain}',
    $domain,
    env('CATCHALL_MAIL_USERNAME', 'catchall-temp@{domain}'),
);
$catchAllPassword = env('CATCHALL_MAIL_PASSWORD');
$hrPassword = env('HR_MAIL_PASSWORD', $catchAllPassword);

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
        'rnb_hr' => [
            'host' => env('RNB_HR_IMAP_HOST', env('RNB_IMAP_HOST', env('RNB_MAIL_HOST', 'mail.rnb.co.id'))),
            'port' => env('RNB_HR_IMAP_PORT', env('RNB_IMAP_PORT', 993)),
            'encryption' => env('RNB_HR_IMAP_ENCRYPTION', env('RNB_IMAP_ENCRYPTION', 'ssl')),
            'username' => env('RNB_HR_IMAP_USERNAME', 'hr@rnb.co.id'),
            'password' => env('RNB_HR_IMAP_PASSWORD', $hrPassword),
            'verify_ssl' => env('RNB_HR_IMAP_VERIFY_SSL', env('RNB_IMAP_VERIFY_SSL', true)),
        ],
        'andalanku' => [
            'host' => env('ANDALANKU_IMAP_HOST', env('ANDALANKU_MAIL_HOST', 'mail.andalanku.com')),
            'port' => env('ANDALANKU_IMAP_PORT', 993),
            'encryption' => env('ANDALANKU_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('ANDALANKU_IMAP_USERNAME', $catchAllUsername('andalanku.com')),
            'password' => env('ANDALANKU_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('ANDALANKU_IMAP_VERIFY_SSL', true),
        ],
        'niskala' => [
            'host' => env('NISKALA_IMAP_HOST', env('NISKALA_MAIL_HOST', 'mail.coffeeniskala.com')),
            'port' => env('NISKALA_IMAP_PORT', 993),
            'encryption' => env('NISKALA_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('NISKALA_IMAP_USERNAME', $catchAllUsername('coffeeniskala.com')),
            'password' => env('NISKALA_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('NISKALA_IMAP_VERIFY_SSL', true),
        ],
        'niskala_hr' => [
            'host' => env('NISKALA_HR_IMAP_HOST', env('NISKALA_IMAP_HOST', env('NISKALA_MAIL_HOST', 'mail.coffeeniskala.com'))),
            'port' => env('NISKALA_HR_IMAP_PORT', env('NISKALA_IMAP_PORT', 993)),
            'encryption' => env('NISKALA_HR_IMAP_ENCRYPTION', env('NISKALA_IMAP_ENCRYPTION', 'ssl')),
            'username' => env('NISKALA_HR_IMAP_USERNAME', 'hr@coffeeniskala.com'),
            'password' => env('NISKALA_HR_IMAP_PASSWORD', $hrPassword),
            'verify_ssl' => env('NISKALA_HR_IMAP_VERIFY_SSL', env('NISKALA_IMAP_VERIFY_SSL', true)),
        ],
        'tms' => [
            'host' => env('TMS_IMAP_HOST', env('TMS_MAIL_HOST', 'mail.tims.co.id')),
            'port' => env('TMS_IMAP_PORT', 993),
            'encryption' => env('TMS_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('TMS_IMAP_USERNAME', $catchAllUsername('tims.co.id')),
            'password' => env('TMS_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('TMS_IMAP_VERIFY_SSL', true),
        ],
        'tms_hr' => [
            'host' => env('TMS_HR_IMAP_HOST', env('TMS_IMAP_HOST', env('TMS_MAIL_HOST', 'mail.tims.co.id'))),
            'port' => env('TMS_HR_IMAP_PORT', env('TMS_IMAP_PORT', 993)),
            'encryption' => env('TMS_HR_IMAP_ENCRYPTION', env('TMS_IMAP_ENCRYPTION', 'ssl')),
            'username' => env('TMS_HR_IMAP_USERNAME', 'hr@tims.co.id'),
            'password' => env('TMS_HR_IMAP_PASSWORD', $hrPassword),
            'verify_ssl' => env('TMS_HR_IMAP_VERIFY_SSL', env('TMS_IMAP_VERIFY_SSL', true)),
        ],
        'rne' => [
            'host' => env('RNE_IMAP_HOST', env('RNE_MAIL_HOST', 'mail.rne.co.id')),
            'port' => env('RNE_IMAP_PORT', 993),
            'encryption' => env('RNE_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('RNE_IMAP_USERNAME', $catchAllUsername('rne.co.id')),
            'password' => env('RNE_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('RNE_IMAP_VERIFY_SSL', true),
        ],
        'rne_hr' => [
            'host' => env('RNE_HR_IMAP_HOST', env('RNE_IMAP_HOST', env('RNE_MAIL_HOST', 'mail.rne.co.id'))),
            'port' => env('RNE_HR_IMAP_PORT', env('RNE_IMAP_PORT', 993)),
            'encryption' => env('RNE_HR_IMAP_ENCRYPTION', env('RNE_IMAP_ENCRYPTION', 'ssl')),
            'username' => env('RNE_HR_IMAP_USERNAME', 'hr@rne.co.id'),
            'password' => env('RNE_HR_IMAP_PASSWORD', $hrPassword),
            'verify_ssl' => env('RNE_HR_IMAP_VERIFY_SSL', env('RNE_IMAP_VERIFY_SSL', true)),
        ],
        'trah' => [
            'host' => env('TRAH_IMAP_HOST', env('TRAH_MAIL_HOST', 'mail.trah.co.id')),
            'port' => env('TRAH_IMAP_PORT', 993),
            'encryption' => env('TRAH_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('TRAH_IMAP_USERNAME', $catchAllUsername('trah.co.id')),
            'password' => env('TRAH_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('TRAH_IMAP_VERIFY_SSL', true),
        ],
        'trah_hr' => [
            'host' => env('TRAH_HR_IMAP_HOST', env('TRAH_IMAP_HOST', env('TRAH_MAIL_HOST', 'mail.trah.co.id'))),
            'port' => env('TRAH_HR_IMAP_PORT', env('TRAH_IMAP_PORT', 993)),
            'encryption' => env('TRAH_HR_IMAP_ENCRYPTION', env('TRAH_IMAP_ENCRYPTION', 'ssl')),
            'username' => env('TRAH_HR_IMAP_USERNAME', 'hr@trah.co.id'),
            'password' => env('TRAH_HR_IMAP_PASSWORD', $hrPassword),
            'verify_ssl' => env('TRAH_HR_IMAP_VERIFY_SSL', env('TRAH_IMAP_VERIFY_SSL', true)),
        ],
        'kma' => [
            'host' => env('KMA_IMAP_HOST', env('KMA_MAIL_HOST', 'mail.karpetmerah.id')),
            'port' => env('KMA_IMAP_PORT', 993),
            'encryption' => env('KMA_IMAP_ENCRYPTION', 'ssl'),
            'username' => env('KMA_IMAP_USERNAME', $catchAllUsername('karpetmerah.id')),
            'password' => env('KMA_IMAP_PASSWORD', $catchAllPassword),
            'verify_ssl' => env('KMA_IMAP_VERIFY_SSL', true),
        ],
        'kma_hr' => [
            'host' => env('KMA_HR_IMAP_HOST', env('KMA_IMAP_HOST', env('KMA_MAIL_HOST', 'mail.karpetmerah.id'))),
            'port' => env('KMA_HR_IMAP_PORT', env('KMA_IMAP_PORT', 993)),
            'encryption' => env('KMA_HR_IMAP_ENCRYPTION', env('KMA_IMAP_ENCRYPTION', 'ssl')),
            'username' => env('KMA_HR_IMAP_USERNAME', 'hr@karpetmerah.id'),
            'password' => env('KMA_HR_IMAP_PASSWORD', $hrPassword),
            'verify_ssl' => env('KMA_HR_IMAP_VERIFY_SSL', env('KMA_IMAP_VERIFY_SSL', true)),
        ],
    ],
];
