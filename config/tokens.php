<?php

return [
    'access_ttl' => env('ACCESS_TOKEN_TTL', 60),
    'refresh_ttl' => env('REFRESH_TOKEN_TTL', 10080),
    'max_active' => env('MAX_ACTIVE_TOKENS', 5),
    'secret' => env('TOKEN_SECRET'),
];