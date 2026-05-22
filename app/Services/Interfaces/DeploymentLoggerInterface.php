<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

interface DeploymentLoggerInterface
{
    /**
     * Log the start of a deployment.
     */
    public function logStart(string $ipAddress): int;

    /**
     * Log a successful deployment completion.
     */
    public function logSuccess(int $logId, string $output): void;

    /**
     * Log a failed deployment.
     */
    public function logFailure(int $logId, string $message): void;
}