<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $provided = $request->header('X-API-Key') ?? $request->header('X-Api-Key');

        $configured = config('services.master_api_keys');
        $keys = [];
        if (is_array($configured)) {
            $keys = $configured;
        } elseif (is_string($configured) && $configured !== '') {
            $keys = array_filter(array_map('trim', explode(',', $configured)));
        }

        if (!$provided || empty($keys) || !in_array($provided, $keys, true)) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        return $next($request);
    }
}
