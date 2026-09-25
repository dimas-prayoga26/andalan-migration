<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'rnb' => [
            'transport' => 'smtp',
            'scheme' => env('RNB_MAIL_SCHEME', 'smtps'),
            'host' => env('RNB_MAIL_HOST', 'mail.rnb.co.id'),
            'port' => env('RNB_MAIL_PORT', 465),
            'username' => env('RNB_MAIL_USERNAME', 'hr@rnb.co.id'),
            'password' => env('RNB_MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', 'rnb.co.id'),
        ],

        'andalanku' => [
            'transport' => 'smtp',
            'scheme' => env('ANDALANKU_MAIL_SCHEME', 'smtps'),
            'host' => env('ANDALANKU_MAIL_HOST', 'mail.andalanku.com'),
            'port' => env('ANDALANKU_MAIL_PORT', 465),
            'username' => env('ANDALANKU_MAIL_USERNAME', 'recruitment@andalanku.com'),
            'password' => env('ANDALANKU_MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', 'andalanku.com'),
        ],

        'kma' => [
            'transport' => 'smtp',
            'scheme' => env('KMA_MAIL_SCHEME', 'smtps'),
            'host' => env('KMA_MAIL_HOST', 'mail.karpetmerah.id'),
            'port' => env('KMA_MAIL_PORT', 465),
            'username' => env('KMA_MAIL_USERNAME', 'hr@karpetmerah.id'),
            'password' => env('KMA_MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', 'karpetmerah.id'),
        ],

        'niskala' => [
            'transport' => 'smtp',
            'scheme' => env('NISKALA_MAIL_SCHEME', 'smtps'),
            'host' => env('NISKALA_MAIL_HOST', 'mail.coffeeniskala.com'),
            'port' => env('NISKALA_MAIL_PORT', 465),
            'username' => env('NISKALA_MAIL_USERNAME', 'hr@coffeeniskala.com'),
            'password' => env('NISKALA_MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', 'coffeeniskala.com'),
        ],

        'rne' => [
            'transport' => 'smtp',
            'scheme' => env('RNE_MAIL_SCHEME', 'smtps'),
            'host' => env('RNE_MAIL_HOST', 'mail.rne.co.id'),
            'port' => env('RNE_MAIL_PORT', 465),
            'username' => env('RNE_MAIL_USERNAME', 'hr@rne.co.id'),
            'password' => env('RNE_MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', 'rne.co.id'),
        ],

        'tms' => [
            'transport' => 'smtp',
            'scheme' => env('TMS_MAIL_SCHEME', 'smtps'),
            'host' => env('TMS_MAIL_HOST', 'mail.tims.co.id'),
            'port' => env('TMS_MAIL_PORT', 465),
            'username' => env('TMS_MAIL_USERNAME', 'hr@tims.co.id'),
            'password' => env('TMS_MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', 'tims.co.id'),
        ],

        'trah' => [
            'transport' => 'smtp',
            'scheme' => env('TRAH_MAIL_SCHEME', 'smtps'),
            'host' => env('TRAH_MAIL_HOST', 'mail.trah.co.id'),
            'port' => env('TRAH_MAIL_PORT', 465),
            'username' => env('TRAH_MAIL_USERNAME', 'hr@trah.co.id'),
            'password' => env('TRAH_MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', 'trah.co.id'),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

];
