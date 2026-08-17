<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Allow the request through only for an authenticated admin user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (empty($user)) {
            abort(403, 'Unauthorized.');
        }

        if (! $user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
