<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimpleAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // We get the username & password From the request then we compare them with the one from the .env file
        if ($request->getUser()!=env('API_AUTH_USER')||$request->getPassword()!=env('API_AUTH_PASSWORD'))
        {
            // If they do not match we return Unauthorized User message with unauthenticated status code "401"
            return response(['success'=>'Unauthorized User'],401);
        }
        return $next($request);
    }
}
