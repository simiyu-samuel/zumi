<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiGlobalMetadata
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            
            $meta = [
                'api_version' => '1.0.0',
                'timestamp'   => now()->toIso8601String(),
                'request_id'  => (string) \Illuminate\Support\Str::uuid(),
            ];

            if (isset($data['meta'])) {
                $data['meta'] = array_merge($meta, $data['meta']);
            } else {
                $data['meta'] = $meta;
            }

            $response->setData($data);
        }

        return $response;
    }
}
