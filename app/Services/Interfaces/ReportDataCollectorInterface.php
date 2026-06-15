<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use Illuminate\Support\Carbon;

interface ReportDataCollectorInterface
{
    /**
     * Get the most-called controller methods within the period.
     *
     * @return array<int, array{name: string, count: int, last_operation: string|null}>
     */
    public function getMethodRanking(Carbon $since): array;

    /**
     * Get the most-edited entity types within the period.
     *
     * @return array<int, array{name: string, count: int, last_operation: string|null}>
     */
    public function getEntityRanking(Carbon $since): array;

    /**
     * Get user activity ranking within the period.
     *
     * @return array<int, array{name: string, count: int, last_operation: string|null}>
     */
    public function getUserRanking(Carbon $since): array;
}
