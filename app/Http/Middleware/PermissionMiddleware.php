<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!$request->user()->hasPermissionTo($permission)) {
            return response()->json(['statut' => 'error', 'message' => "Vous n'avez pas la permission d'accès à cette ressource", 'errors' => [], 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
