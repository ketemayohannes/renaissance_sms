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
    | In-App (Database) Notification Settings
    |--------------------------------------------------------------------------
    */
    'in_app' => [
        'enabled' => env('IN_APP_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-Event Channel Routing Defaults
    |--------------------------------------------------------------------------
    | Controls which events trigger SMS, email, and in-app notifications.
    | Admin can override via the General Settings page.
    | Keys: sms, email, in_app
    */
    'events' => [
        'notice'            => ['sms' => false, 'email' => true,  'in_app' => true],
        'absence'           => ['sms' => true,  'email' => true,  'in_app' => true],
        'message'           => ['sms' => false, 'email' => true,  'in_app' => true],
        'export'            => ['sms' => false, 'email' => true,  'in_app' => true],
        'promotion'         => ['sms' => false, 'email' => true,  'in_app' => true],
        'disciplinary'      => ['sms' => true,  'email' => true,  'in_app' => true],
        'report_card_ready' => ['sms' => false, 'email' => true,  'in_app' => true],
        'leave_status'      => ['sms' => false, 'email' => true,  'in_app' => true],
        'inventory_request' => ['sms' => false, 'email' => true,  'in_app' => true],
        'inventory_purchase'=> ['sms' => false, 'email' => true,  'in_app' => true],
    ],

];
