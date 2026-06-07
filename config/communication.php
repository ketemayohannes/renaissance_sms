<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Settings
    |--------------------------------------------------------------------------
    | Default values — overridden at runtime by CommunicationSetting::applyConfigurations()
    | which reads from the communication_settings DB table.
    */
    'sms' => [
        'enabled' => env('SMS_ENABLED', false),
        'provider' => env('SMS_PROVIDER', 'africastalking'),

        'africastalking' => [
            'username' => env('AT_USERNAME', 'sandbox'),
            'api_key'  => env('AT_API_KEY', ''),
            'from'     => env('AT_FROM', null),
            'sandbox'  => env('AT_SANDBOX', true),
        ],

        'smsethiopia' => [
            'api_key' => env('SMSETHIOPIA_API_KEY', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    */
    'email' => [
        'enabled' => env('MAIL_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-Event Channel Routing Defaults
    |--------------------------------------------------------------------------
    | Controls which events trigger SMS and/or email by default.
    | Admin can override via the Communication Settings page.
    */
    'events' => [
        'notice'  => ['sms' => false, 'email' => true],
        'absence' => ['sms' => true,  'email' => true],
        'message' => ['sms' => false, 'email' => true],
        'export'  => ['sms' => false, 'email' => true],
    ],

];
