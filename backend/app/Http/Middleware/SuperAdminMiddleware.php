<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($request->user()->user_type !== 'super_admin') {
            return response()->json([
                'message' => 'Unauthorized. Super Admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
