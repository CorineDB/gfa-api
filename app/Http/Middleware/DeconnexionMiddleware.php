<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeconnexionMiddleware
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
        $user = $request->user();
        $user = User::find($user->id);

        if($user)
        {
            if($user->lastRequest)
            {
                if((strtotime(date('Y-m-d h:i:s')) - strtotime($user->lastRequest))/3600 >= 4)
                {
                    $user->tokens()->delete();
                    //$user->token = null;
                }
            }

            $user->lastRequest = date('Y-m-d h:i:s');
            $user->save();
        }

        return $next($request);
    }
}
