<?php

namespace App\Http\Middleware;

use Closure;

class CekLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $level)
    {
        if (auth('petugas')->user()->level != $level)
            return response()->json(['status' => false, 'message' => 'Endpoint khusus untuk ' . $level], 401);
        else
            return $next($request);
    }
}
