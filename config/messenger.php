<?php

declare(strict_types=1);

return [
    'retry_attempts' => (int) env('MESSENGER_NOTIFICATION_RETRY_ATTEMPTS', 3),
    'telegram' => [
        'token'   => env('MESSENGER_TELEGRAM_TOKEN', ''),
        'api_url' => 'https://api.telegram.org/bot',
    ],
    'slack' => [
        'webhook' => env('MESSENGER_SLACK_WEBHOOK', ''),
    ],
];
