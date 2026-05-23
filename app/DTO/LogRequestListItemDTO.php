<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\LogRequest;

class LogRequestListItemDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $fullUrl,
        public readonly ?string $controllerPath,
        public readonly ?string $controllerMethod,
        public readonly int     $responseStatus,
        public readonly string  $calledAt,
    ) {}

    /**
     * Build list item DTO from model instance.
     */
    public static function fromModel(LogRequest $log): self
    {
        return new self(
            id:               $log->id,
            fullUrl:          $log->full_url,
            controllerPath:   $log->controller_path,
            controllerMethod: $log->controller_method,
            responseStatus:   $log->response_status,
            calledAt:         $log->called_at->toDateTimeString(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'full_url'          => $this->fullUrl,
            'controller_path'   => $this->controllerPath,
            'controller_method' => $this->controllerMethod,
            'response_status'   => $this->responseStatus,
            'called_at'         => $this->calledAt,
        ];
    }
}