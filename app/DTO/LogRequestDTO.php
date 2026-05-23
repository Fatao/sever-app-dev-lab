<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\LogRequest;

class LogRequestDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $fullUrl,
        public readonly string  $method,
        public readonly ?string $controllerPath,
        public readonly ?string $controllerMethod,
        public readonly ?array  $requestBody,
        public readonly ?array  $requestHeaders,
        public readonly ?int    $userId,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly int     $responseStatus,
        public readonly ?array  $responseBody,
        public readonly ?array  $responseHeaders,
        public readonly string  $calledAt,
    ) {}

    /**
     * Build DTO from model instance.
     */
    public static function fromModel(LogRequest $log): self
    {
        return new self(
            id:               $log->id,
            fullUrl:          $log->full_url,
            method:           $log->method,
            controllerPath:   $log->controller_path,
            controllerMethod: $log->controller_method,
            requestBody:      $log->request_body,
            requestHeaders:   $log->request_headers,
            userId:           $log->user_id,
            ipAddress:        $log->ip_address,
            userAgent:        $log->user_agent,
            responseStatus:   $log->response_status,
            responseBody:     $log->response_body,
            responseHeaders:  $log->response_headers,
            calledAt:         $log->called_at->toDateTimeString(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'full_url'          => $this->fullUrl,
            'method'            => $this->method,
            'controller_path'   => $this->controllerPath,
            'controller_method' => $this->controllerMethod,
            'request_body'      => $this->requestBody,
            'request_headers'   => $this->requestHeaders,
            'user_id'           => $this->userId,
            'ip_address'        => $this->ipAddress,
            'user_agent'        => $this->userAgent,
            'response_status'   => $this->responseStatus,
            'response_body'     => $this->responseBody,
            'response_headers'  => $this->responseHeaders,
            'called_at'         => $this->calledAt,
        ];
    }
}