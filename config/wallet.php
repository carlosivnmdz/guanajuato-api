<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Apple Wallet
    |--------------------------------------------------------------------------
    |
    | Requiere una cuenta de Apple Developer Program y un certificado
    | Pass Type ID para firmar el .pkpass. Mientras 'enabled' sea false,
    | el endpoint responde 503 sin intentar generar nada.
    |
    */

    'apple' => [
        'enabled' => env('APPLE_WALLET_ENABLED', false),
        'pass_type_identifier' => env('APPLE_PASS_TYPE_IDENTIFIER'),
        'team_identifier' => env('APPLE_TEAM_IDENTIFIER'),
        'certificate_path' => env('APPLE_PASS_CERTIFICATE_PATH'),
        'certificate_password' => env('APPLE_PASS_CERTIFICATE_PASSWORD'),
        'wwdr_certificate_path' => env('APPLE_WWDR_CERTIFICATE_PATH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Wallet
    |--------------------------------------------------------------------------
    |
    | Requiere una cuenta de "issuer" aprobada por Google en la Wallet
    | API y un service account para firmar el JWT del pase.
    |
    */

    'google' => [
        'enabled' => env('GOOGLE_WALLET_ENABLED', false),
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID'),
        'service_account_path' => env('GOOGLE_WALLET_SERVICE_ACCOUNT_PATH'),
    ],

];
