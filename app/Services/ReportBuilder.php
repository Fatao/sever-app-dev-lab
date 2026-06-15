<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Interfaces\ReportBuilderInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportBuilder implements ReportBuilderInterface
{
    private const REPORTS_DIRECTORY = 'reports';

    /**
     * Build a JSON report file and return its storage path.
     *
     * @param array<string, mixed> $reportData
     */
    public function build(array $reportData): string
    {
        $fileName = self::REPORTS_DIRECTORY . '/report_' . Str::random(12) . '.json';

        $content = json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        Storage::put($fileName, $content);

        return $fileName;
    }
}
