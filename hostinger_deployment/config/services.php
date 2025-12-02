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

    'resend' => [
        'key' => env('RESEND_KEY'),
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

        // Stripe / Razorpay service credentials used by the app.
        // Keep env() here so credentials are centralized in config files (phpstan-friendly).
        'stripe' => [
            'secret' => env('STRIPE_SECRET'),
            'public' => env('STRIPE_PUBLIC'),
            'price_id' => env('STRIPE_PRICE_ID'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],

        'razorpay' => [
            'key_id' => env('RAZORPAY_KEY_ID'),
            'key_secret' => env('RAZORPAY_KEY_SECRET'),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        ],

        // Background check service configuration
        'background_check' => [
            'api_key' => env('BACKGROUND_CHECK_API_KEY'),
            'endpoint' => env('BACKGROUND_CHECK_ENDPOINT'),
            'check_types' => [
                'criminal_history',
                'identity_verification',
                'watchlist_screening',
                'employment_verification',
            ],
        ],

        // Facial recognition service
        'facial_recognition' => [
            'api_key' => env('FACIAL_RECOGNITION_API_KEY'),
            'endpoint' => env('FACIAL_RECOGNITION_ENDPOINT'),
            'confidence_threshold' => env('FACIAL_RECOGNITION_THRESHOLD', 0.8),
        ],

];
