<?php

declare(strict_types=1);

return [
    'time_interval_hours'     => (int) env('REPORT_TIME_INTERVAL_HOURS', 24),
    'job_timeout_minutes'     => (int) env('REPORT_JOB_TIMEOUT_MINUTES', 5),
    'job_retry_delay_minutes' => (int) env('REPORT_JOB_RETRY_DELAY_MINUTES', 2),
    'job_max_attempts'        => (int) env('REPORT_JOB_MAX_ATTEMPTS', 3),
    'admin_email'             => env('REPORT_ADMIN_EMAIL', 'admin@example.com'),
];
