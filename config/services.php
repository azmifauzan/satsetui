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

    'llm' => [
        'api_key' => env('LLM_API_KEY', 'sk-Cx00n-G2-g8__tXS44WljA'),
        'base_url' => env('LLM_BASE_URL', 'https://ai.sumopod.com/v1'),
    ],

    // Legacy Gemini config (deprecated, use 'llm' instead)
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'free_model' => env('GEMINI_FREE_MODEL', 'gemini-2.0-flash-exp'),
        'premium_model' => env('GEMINI_PREMIUM_MODEL', 'gemini-1.5-pro-latest'),
        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    ],

];
