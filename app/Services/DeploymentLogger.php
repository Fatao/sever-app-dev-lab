<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DeploymentLog;
use App\Services\Interfaces\DeploymentLoggerInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeploymentLogger implements DeploymentLoggerInterface
{
    /**
     * Log the start of a deployment and return the log ID.
     */
    public function logStart(string $ipAddress): int
    {
        $log = DeploymentLog::create([
            'status'     => 'started',
            'ip_address' => $ipAddress,
            'message'    => 'Deployment started.',
            'started_at' => Carbon::now(),
        ]);

        Log::channel('single')->info('Deployment started', [
            'ip'         => $ipAddress,
            'started_at' => Carbon::now()->toDateTimeString(),
        ]);

        return $log->id;
    }

    /**
     * Log a successful deployment completion.
     */
    public function logSuccess(int $logId, string $output): void
    {
        DeploymentLog::where('id', $logId)->update([
            'status'      => 'success',
            'message'     => 'Deployment completed successfully.',
            'output'      => $output,
            'finished_at' => Carbon::now(),
        ]);

        Log::channel('single')->info('Deployment succeeded', [
            'log_id'      => $logId,
            'finished_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * Log a failed deployment.
     */
    public function logFailure(int $logId, string $message): void
    {
        DeploymentLog::where('id', $logId)->update([
            'status'      => 'failed',
            'message'     => $message,
            'finished_at' => Carbon::now(),
        ]);

        Log::channel('single')->error('Deployment failed', [
            'log_id'  => $logId,
            'message' => $message,
        ]);
    }
}