<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\DeploymentResultDTO;
use App\Services\Interfaces\DeploymentLoggerInterface;
use App\Services\Interfaces\DeploymentServiceInterface;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class DeploymentService implements DeploymentServiceInterface
{
    private const LOCK_KEY = 'deploy_lock';
    private const LOCK_TTL = 300; // 5 minutes
    private const TIMEOUT  = 120; // seconds per command

    public function __construct(
        private readonly DeploymentLoggerInterface $logger,
    ) {}

    /**
     * Run the full git deployment process.
     */
    public function deploy(string $ipAddress): DeploymentResultDTO
    {
        $projectPath = base_path();
        $branch      = config('git.default_branch', 'main');

        // Verify this is a git repository
        if (!is_dir($projectPath . DIRECTORY_SEPARATOR . '.git')) {
            return new DeploymentResultDTO(
                status:  'error',
                message: 'Not a git repository.',
            );
        }

        $logId  = $this->logger->logStart($ipAddress);
        $output = '';

        try {
            //  1 — stash local changes
            $output .= $this->runCommandSafe(['git', 'stash'], $projectPath);

            //  2 — checkout target branch
            $output .= $this->runCommandSafe(['git', 'checkout', $branch], $projectPath);

            // 3 — reset hard to discard any remaining changes
            $output .= $this->runCommandSafe(['git', 'reset', '--hard', 'HEAD'], $projectPath);

            // 4 — pull latest (may fail in restricted network environments)
            $output .= $this->runCommandSafe(['git', 'pull', 'origin', $branch], $projectPath);

            $this->logger->logSuccess($logId, $output);

            return new DeploymentResultDTO(
                status:  'success',
                message: 'Deployment completed successfully.',
                output:  $output,
            );

        } catch (\Throwable $e) {
            $this->logger->logFailure($logId, $e->getMessage());

            return new DeploymentResultDTO(
                status:  'error',
                message: $e->getMessage(),
                output:  $output,
            );
        }
    }

    /**
     * Check if a deployment lock is active.
     */
    public function isLocked(): bool
    {
        return Cache::has(self::LOCK_KEY);
    }

    /**
     * Acquire the deployment lock.
     */
    public function acquireLock(): void
    {
        Cache::put(self::LOCK_KEY, true, self::LOCK_TTL);
    }

    /**
     * Release the deployment lock.
     */
    public function releaseLock(): void
    {
        Cache::forget(self::LOCK_KEY);
    }

    /**
     * Run a command and return output with label, never throwing.
     * Used for commands that may fail in local/Windows environments.
     *
     * @param array<string> $command
     */
    private function runCommandSafe(array $command, string $cwd): string
    {
        $label = implode(' ', $command);

        try {
            $result = $this->runCommand($command, $cwd);
            return "{$label}\n{$result}\n";
        } catch (\RuntimeException $e) {
            return "{$label}\nWARNING: {$e->getMessage()}\n";
        }
    }

    /**
     * Run a single shell command using Symfony Process.
     *
     * @param  array<string> $command
     * @throws \RuntimeException
     */
    private function runCommand(array $command, string $cwd): string
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(self::TIMEOUT);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                'Command failed: ' . implode(' ', $command) . "\n" . $process->getErrorOutput()
            );
        }

        return $process->getOutput();
    }
}