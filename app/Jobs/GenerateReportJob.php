<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\AnalyticsReportMail;
use App\Services\Interfaces\ReportBuilderInterface;
use App\Services\Interfaces\ReportDataCollectorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries;

    /**
     * Maximum execution time in seconds.
     */
    public int $timeout;

    public function __construct()
    {
        $this->tries   = config('report.job_max_attempts', 3);
        $this->timeout = config('report.job_timeout_minutes', 5) * 60;
    }

    /**
     * Calculate the delay (in seconds) before each retry attempt.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        $delaySeconds = config('report.job_retry_delay_minutes', 2) * 60;

        return array_fill(0, $this->tries, $delaySeconds);
    }

    /**
     * Execute the job: collect data, build report, send and clean up.
     */
    public function handle(
        ReportDataCollectorInterface $collector,
        ReportBuilderInterface       $builder,
    ): void {
        Log::info('GenerateReportJob: started');

        $intervalHours = config('report.time_interval_hours', 24);
        $since         = Carbon::now()->subHours($intervalHours);

        $reportData = [
            'report_type' => 'Analytics report',
            'period'      => [
                'from' => $since->toDateTimeString(),
                'to'   => Carbon::now()->toDateTimeString(),
            ],
            'method_ranking' => $collector->getMethodRanking($since),
            'entity_ranking' => $collector->getEntityRanking($since),
            'user_ranking'   => $collector->getUserRanking($since),
        ];

        $reportPath = $builder->build($reportData);

        try {
            $this->sendReport($reportPath, $since);
        } finally {
            Storage::delete($reportPath);
        }

        Log::info('GenerateReportJob: finished successfully');
    }

    /**
     * Send the generated report file to configured admin emails.
     */
    private function sendReport(string $reportPath, Carbon $since): void
    {
        $absolutePath = Storage::path($reportPath);
        $period       = $since->toDateString() . ' — ' . Carbon::now()->toDateString();
        $recipients   = array_map('trim', explode(',', config('report.admin_email')));

        Mail::to($recipients)->send(new AnalyticsReportMail($absolutePath, $period));
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('GenerateReportJob: failed permanently', [
            'error' => $exception->getMessage(),
        ]);
    }
}
