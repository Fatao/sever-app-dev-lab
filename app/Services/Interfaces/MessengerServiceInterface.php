<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

interface MessengerServiceInterface
{
    /**
     * Send a text message to the given chat/user ID.
     */
    public function sendMessage(string $chatId, string $message): bool;

    /**
     * Send a document/file to the given chat/user ID.
     */
    public function sendDocument(string $chatId, string $filePath, string $caption = ''): bool;
}
