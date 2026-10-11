<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Página de WordPress que envía las citas que reservan los clientes.
    'wordpress' => [
        'token' => env('WORDPRESS_API_TOKEN'),
    ],

    // Datos del negocio para los correos: nombre que ve el cliente y correo
    // donde llegan los avisos de nuevas reservas (si no se indica, se usa
    // el mismo correo que envía: MAIL_FROM_ADDRESS).
    'negocio' => [
        'nombre' => env('NEGOCIO_NOMBRE', 'July Salon'),
        'email' => env('NEGOCIO_EMAIL', env('MAIL_FROM_ADDRESS')),
        // Número de contacto que aparece al pie de los correos al cliente.
        'telefono' => env('NEGOCIO_TELEFONO', '7474-8761'),
    ],

];
