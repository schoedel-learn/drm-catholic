<?php

return [
    'enabled' => (bool) env('DEMO_MODE', false),
    'git_sha' => env('GIT_SHA'),
    'user_email' => 'demo@example.invalid',
    'user_password' => env('DEMO_USER_PASSWORD'),

    'protected_account' => [
        'prevent_email_change' => true,
        'prevent_password_change' => true,
        'prevent_deletion' => true,
    ],
];
