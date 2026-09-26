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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'indexnow' => [
        'enabled' => (bool) env('INDEXNOW_ENABLED', env('APP_ENV') === 'production'),
    ],

    'google' => [
        'site_verification' => env('GOOGLE_SITE_VERIFICATION'),
    ],

    'bing' => [
        'site_verification' => env('BING_SITE_VERIFICATION'),
    ],

    /*
     * Google Analytics 4. The id is printed (in production only) for
     * resources/js/consent.js, which loads gtag.js after the visitor accepts
     * analytics cookies, never before.
     */
    'google_analytics' => [
        'id' => env('GOOGLE_ANALYTICS_ID', 'G-1PQQSTPYZC'),
    ],

    /*
     * Where the notification of each demo sent through /demos goes.
     */
    'demos' => [
        'notify_email' => env('DEMO_NOTIFY_EMAIL', 'demo@1namm.com'),
    ],

];
