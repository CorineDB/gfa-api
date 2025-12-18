<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SwaggerMiddleware
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
        if(config('app.env') == 'production')
        {
            return response()->json(['statut' => 'error', 'message' => "Vous n'avez pas les droits d'accès à cette resource", 'errors' => [], 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
