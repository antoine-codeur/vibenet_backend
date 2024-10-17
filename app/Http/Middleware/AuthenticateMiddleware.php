<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure;

class AuthenticateMiddleware extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Call the parent handle method to authenticate the user
        $this->authenticate($request, $guards);

        // Check if the user is authenticated and the token is expired
        $user = $request->user(); // Get the authenticated user
        
        if ($user && method_exists($user, 'tokenIsExpired') && $user->tokenIsExpired()) {
            return response()->json(['message' => 'Token expired'], 401);
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
