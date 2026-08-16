<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMiAccountsSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('miaccounts.inbound_secret');
        $provided = (string) $request->header('X-Miaccounts-Secret');

        if ($secret === '' || $provided === '' || ! hash_equals($secret, $provided)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
