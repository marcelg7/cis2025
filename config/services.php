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
        'bug_report_webhook' => env('BUG_REPORT_SLACK_WEBHOOK'),
        'bot_token' => env('SLACK_BOT_TOKEN'),
        'channel_id' => env('SLACK_CHANNEL_ID'),
    ],
	
	'customer_api' => [
        'url' => env('CUSTOMER_API_URL'),
        'token' => env('CUSTOMER_API_TOKEN'),
    ],

    'github' => [
        'owner' => env('GITHUB_OWNER'),
        'repo' => env('GITHUB_REPO'),
        'token' => env('GITHUB_TOKEN'),
    ],

];
