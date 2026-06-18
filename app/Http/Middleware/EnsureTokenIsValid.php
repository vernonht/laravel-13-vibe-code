<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!hash_equals((string) config('auth.secret_token', ''), (string) $request->header('auth_token', ''))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
 
        return $next($request);
    }
}
