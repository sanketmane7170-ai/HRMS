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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
    ],

    'flowversal' => [
        'api_key' => env('FLOWVERSAL_API_KEY'),
        'model' => env('FLOWVERSAL_MODEL', 'qwen2.5:14b'),
        'base_url' => env('FLOWVERSAL_BASE_URL', 'http://139.84.155.227:3000/api/tanchat'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
    ],

    // AI provider configuration
    'ai' => [
        'primary_provider' => env('AI_PRIMARY_PROVIDER', 'openai'),
        'rag_enabled' => env('AI_RAG_ENABLED', true),
    ],

    'wp_ai' => [
        'widget_url' => env('WP_AI_WIDGET_URL', 'https://widget.WorkPilot.in/?organizationId=org_39WUpTf49G0uGcdb899QYgJGCkm'),
        'org_id' => env('WP_AI_ORG_ID', 'org_39WUpTf49G0uGcdb899QYgJGCkm'),
    ],

];
