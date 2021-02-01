<?php
return [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_url' => env('GOOGLE_REDIRECT_URI', '/'),
    'scopes' => [
        'all',
    ],
    'access_type' => 'offline',
    'approval_prompt' => 'force',
];
