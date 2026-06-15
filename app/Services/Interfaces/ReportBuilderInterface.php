<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

interface ReportBuilderInterface
{
    /**
     * Build a report file and return its storage path.
     *
     * @param array<string, mixed> $reportData
     */
    public function build(array $reportData): string;
}
