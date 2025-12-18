<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role, $permission = null)
    {

        $roles = explode("|", $role);
        
        if($role != null && ( !$request->user()->hasRole(array_values($roles)) && !in_array($request->user()->type, $roles) ) ) {
            return response()->json(['statut' => 'error', 'message' => "Vous n'avez pas les droits d'accès à cette resource", 'errors' => [], 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
        }

        if($permission != null && !$request->user()->can($permission)) {
            return response()->json(['statut' => 'error', 'message' => "Vous n'avez pas la permission d'accès à cette resource", 'errors' => [], 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
