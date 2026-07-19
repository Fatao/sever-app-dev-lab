<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\UserMessenger;
use App\Services\TelegramService;
use App\Services\Interfaces\MessengerServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries;

    /**
     * @param int      $userId            Target user ID.
     * @param string   $message           Notification text.
     * @param string   $eventType         Event type for logging.
     * @param int[]    $failedMessengerIds Messenger IDs that failed on previous attempts.
     */
    public function __construct(
        public readonly int    $userId,
        public readonly string $message,
        public readonly string $eventType,
        public array           $failedMessengerIds = [],
    ) {
        $this->tries = config('messenger.retry_attempts', 3);
    }

    /**
     * Calculate retry delays in seconds.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return array_fill(0, $this->tries, 60);
    }

    /**
     * Execute the job: send notification to all verified messengers.
     */
    public function handle(): void
    {
        $userMessengers = UserMessenger::where('user_id', $this->userId)
            ->where('is_verified', true)
            ->where('notifications_enabled', true)
            ->with('messenger')
            ->get();

        $stillFailed = [];

        foreach ($userMessengers as $um) {
            // On retry, only process previously failed messengers
            if (!empty($this->failedMessengerIds) && !in_array($um->messenger_id, $this->failedMessengerIds, true)) {
                continue;
            }

            $service = $this->resolveService($um->messenger->name);
            $attempt = $this->attempts();

            try {
                $sent = $service->sendMessage($um->messenger_user_id, $this->message);

                NotificationLog::create([
                    'user_id'      => $this->userId,
                    'messenger_id' => $um->messenger_id,
                    'message'      => $this->message,
                    'status'       => $sent ? 'sent' : 'failed',
                    'attempt'      => $attempt,
                    'response'     => $sent ? 'OK' : 'API returned false',
                ]);

                if (!$sent) {
                    $stillFailed[] = $um->messenger_id;
                }
            } catch (Throwable $e) {
                NotificationLog::create([
                    'user_id'      => $this->userId,
                    'messenger_id' => $um->messenger_id,
                    'message'      => $this->message,
                    'status'       => 'failed',
                    'attempt'      => $attempt,
                    'response'     => $e->getMessage(),
                ]);

                $stillFailed[] = $um->messenger_id;
            }
        }

        if (!empty($stillFailed)) {
            // Re-queue with only failed messengers for retry
            $this->failedMessengerIds = $stillFailed;
            $this->release(60);
        }
    }

    /**
     * Handle final job failure after all retries exhausted.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('SendNotificationJob failed permanently', [
            'user_id'    => $this->userId,
            'event_type' => $this->eventType,
            'error'      => $exception->getMessage(),
        ]);
    }

    /**
     * Resolve the messenger service by name.
     *
     * @throws \RuntimeException
     */
    private function resolveService(string $messengerName): MessengerServiceInterface
    {
        return match ($messengerName) {
            'telegram' => app(TelegramService::class),
            default    => throw new \RuntimeException("Unsupported messenger: {$messengerName}"),
        };
    }
}
