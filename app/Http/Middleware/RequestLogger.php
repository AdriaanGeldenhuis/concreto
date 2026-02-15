<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RequestLogger
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = Str::uuid()->toString();
        $request->headers->set('X-Request-ID', $requestId);

        $response = $next($request);

        // Only log API and important routes, skip static assets
        if ($request->is('api/*') || $request->is('webhooks/*') || $request->is('admin/*')) {
            $context = [
                'request_id' => $requestId,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status' => $response->getStatusCode(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'user_role' => $request->user()?->role,
                'duration_ms' => defined('LARAVEL_START')
                    ? round((microtime(true) - LARAVEL_START) * 1000)
                    : null,
            ];

            // Add order_id if in route params
            if ($request->route('order')) {
                $context['order_id'] = $request->route('order')->id ?? $request->route('order');
            }

            if ($response->getStatusCode() >= 400) {
                Log::warning('request.error', $context);
            } else {
                Log::info('request.completed', $context);
            }
        }

        // Set request ID in response header for tracing
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
