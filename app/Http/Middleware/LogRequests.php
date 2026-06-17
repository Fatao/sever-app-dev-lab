<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\LogRequest;
use App\Services\DataSanitizer;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    public function __construct(
        private readonly DataSanitizer $sanitizer,
    ) {}

    /**
     * Store request data before passing to controller.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('log_called_at', Carbon::now());
        $request->attributes->set('log_request_body', $this->sanitizer->sanitize($request->all()));
        $request->attributes->set('log_request_headers', $this->sanitizer->sanitize($request->headers->all()));

        return $next($request);
    }

    /**
     * Write the log entry after the response has been sent.
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $action           = $request->route()?->getActionName() ?? '';
            $controllerPath   = null;
            $controllerMethod = null;

            if (str_contains($action, '@')) {
                [$controllerPath, $controllerMethod] = explode('@', $action, 2);
            }

            $responseBody = null;
            $decoded      = null;
            $content      = $response->getContent();

            if ($content) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $responseBody = $this->sanitizer->sanitize($decoded ?? []);
                }
            }

            // authentication user first, then fall back to login response body
            $userId = $request->user()?->id
                ?? ($request->is('api/auth/login') ? ($decoded['user']['id'] ?? null) : null);

            LogRequest::create([
                'full_url'          => $request->fullUrl(),
                'method'            => $request->method(),
                'controller_path'   => $controllerPath,
                'controller_method' => $controllerMethod,
                'request_body'      => $request->attributes->get('log_request_body'),
                'request_headers'   => $request->attributes->get('log_request_headers'),
                'user_id'           => $userId,
                'ip_address'        => $request->ip(),
                'user_agent'        => $request->userAgent(),
                'response_status'   => $response->getStatusCode(),
                'response_body'     => $responseBody,
                'response_headers'  => $response->headers->all(),
                'called_at'         => $request->attributes->get('log_called_at'),
            ]);
        } catch (\Throwable) {
            // Never let logging break the application
        }
    }
}