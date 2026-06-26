<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\UserMessenger;

class UserMessengerDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $userId,
        public readonly int     $messengerId,
        public readonly string  $messengerUserId,
        public readonly bool    $isVerified,
        public readonly ?string $verifiedAt,
        public readonly bool    $notificationsEnabled,
    ) {}

    /**
     * Build DTO from model instance.
     */
    public static function fromModel(UserMessenger $um): self
    {
        return new self(
            id:                   $um->id,
            userId:               $um->user_id,
            messengerId:          $um->messenger_id,
            messengerUserId:      $um->messenger_user_id,
            isVerified:           $um->is_verified,
            verifiedAt:           $um->verified_at?->toDateTimeString(),
            notificationsEnabled: $um->notifications_enabled,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'user_id'              => $this->userId,
            'messenger_id'         => $this->messengerId,
            'messenger_user_id'    => $this->messengerUserId,
            'is_verified'          => $this->isVerified,
            'verified_at'          => $this->verifiedAt,
            'notifications_enabled'=> $this->notificationsEnabled,
        ];
    }
}
