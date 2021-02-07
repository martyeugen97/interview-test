<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if($token == env('API_TOKEN'))
            return $next($request);
        $data = [
            'status' => 'error',
            'code' => 403,
            'message' => 'Invalid token'
        ];

        return response()->json($data, 403);
    }
}
