<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Git Webhook Secret Key
    |--------------------------------------------------------------------------
    | This secret key is used to authenticate webhook requests.
    | It must be exactly 36 characters and stored only in .env.
    */
    'webhook_secret' => env('GIT_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Git Branch
    |--------------------------------------------------------------------------
    | The branch to pull from when a webhook is received.
    */
    'default_branch' => env('GIT_DEFAULT_BRANCH', 'main'),
];