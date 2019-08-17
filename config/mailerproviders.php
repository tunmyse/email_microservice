<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default MailerProvider
    |--------------------------------------------------------------------------
    |
    | This option controls the default provider that will be used to send emails
    | It can be set to any of the providers defined in the "providers" array below.
    |
    */

    'default' => env('MAILER_DEFAULT_PROVIDER', 'mailjet'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Providers
    |--------------------------------------------------------------------------
    |
    | Here you may define additional providers that will be used for sending
    | emails.
    |
    */

    'providers' => [

        'mailjet' => [
            'username' => env('MAILER_MJ_USERNAME', ''),
            'password' => env('MAILER_MJ_PASSWORD', '')
        ],

        'sendgrid' => [
            'token' => env('MAILER_SG_TOKEN', ''),
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Sender Email
    |--------------------------------------------------------------------------
    |
    | This option controls the sender email used to send emails.
    |
    */

    'sender' => env('MAILER_SENDER', 'admin@example.com'),

    /*
    |--------------------------------------------------------------------------
    | ReplyTo Email
    |--------------------------------------------------------------------------
    |
    | This option controls the reply_to email specified in the sent emails
    |
    */

    'reply_to' => env('MAILER_REPLY_TO', 'reply@example.com'),

];
