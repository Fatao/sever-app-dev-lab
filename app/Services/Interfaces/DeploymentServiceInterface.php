<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\DTO\DeploymentResultDTO;

interface DeploymentServiceInterface
{
    /**
     * Run the full deployment process.
     */
    public function deploy(string $ipAddress): DeploymentResultDTO;

    /**
     * Check if a deployment is already in progress.
     */
    public function isLocked(): bool;

    /**
     * Acquire the deployment lock.
     */
    public function acquireLock(): void;

    /**
     * Release the deployment lock.
     */
    public function releaseLock(): void;
}