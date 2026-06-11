<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyVkSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.vk.secret');

        if ($request->input('secret') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
