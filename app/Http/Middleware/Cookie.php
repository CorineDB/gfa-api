<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Cookie
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    public function handle(Request $request, Closure $next)
    {
        $cookie = $request->cookie('X-XSRF-TOKEN');

        if($cookie != $request->header('Authorization'))
        {
            return response()->json(['statut' => 'error', 'message' => "Vous n'avez pas les droits d'accès à cette resource", 'errors' => [], 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
