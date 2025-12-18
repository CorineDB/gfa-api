<?php

namespace App\Http\Middleware;

use App\Traits\Helpers\ConfigueTrait;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PasswordValidity
{
    use ConfigueTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Carbon::parse($request->user()->password_update_at)->addDays($this->periodeValiditerMotDePasse)->lte(Carbon::now()))
        {
            $request->user()->update(["statut" => -1]);

            $request->user()->tokens()->delete();
            
            return response()->json(['statut' => 'error', 'message' => "Durée de validiter du mot de passe expirer", 'errors' => [], 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
