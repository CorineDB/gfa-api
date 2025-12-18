<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // List of allowed origins
        $allowedOrigins = [
            'http://localhost:3000',
            'http://localhost:3001',
            'http://localhost:3002',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:3001',
            'http://127.0.0.1:3002',
            'https://dms-redevabilite.dev',
            'https://ug.dms-redevabilite.dev',
            'https://organisation.dms-redevabilite.dev',
            'https://admin.dms-redevabilite.dev',
            'https://dms-redevabilite.com',
            'https://ug.dms-redevabilite.com',
            'https://organisation.dms-redevabilite.com',
            'https://admin.dms-redevabilite.com',
        ];

        // Get the origin from the request
        $origin = $request->headers->get('Origin');

        // Debug logging
        \Log::info('CORS Middleware - Origin: ' . ($origin ?? 'null'));

        // Check if the origin is in the allowed list
        $allowOrigin = in_array($origin, $allowedOrigins) ? $origin : null;

        \Log::info('CORS Middleware - Allow Origin: ' . ($allowOrigin ?? 'null'));

        // Only set CORS headers if origin is allowed
        if ($allowOrigin) {
            $headers = [
                'Access-Control-Allow-Origin' => $allowOrigin,
                'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '86400',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With'
            ];

            if ($request->isMethod('OPTIONS')) {
                return response()->json('{"method":"OPTIONS"}', 200, $headers);
            }

            $response = $next($request);
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
            return $response;
        }

        // If origin not allowed, just pass through without CORS headers
        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200);
        }

        $response = $next($request);

        return $response;

        /*return $next($request)
        ->header->('Access-Control-Allow-Origin', '*')
        ->header->('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header->('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization');*/
    }
}
