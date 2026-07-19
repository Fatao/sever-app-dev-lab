<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Interfaces\MessengerServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService implements MessengerServiceInterface
{
    private string $apiUrl;

    public function __construct()
    {
        $token        = config('messenger.telegram.token');
        $this->apiUrl = config('messenger.telegram.api_url') . $token;
    }

    /**
     * Send a text message via Telegram Bot API.
     */
    public function sendMessage(string $chatId, string $message): bool
    {
        try {
            $response = Http::timeout(10)->post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text'    => $message,
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('TelegramService: unreachable', ['error' => $e->getMessage()]);
            return app()->environment(['local', 'dev']);
        }
    }

    /**
     * Send a document/file via Telegram Bot API.
     */
    public function sendDocument(string $chatId, string $filePath, string $caption = ''): bool
    {
        try {
            $response = Http::attach('document', file_get_contents($filePath), basename($filePath))
                ->post("{$this->apiUrl}/sendDocument", [
                    'chat_id' => $chatId,
                    'caption' => $caption,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('TelegramService::sendDocument failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
