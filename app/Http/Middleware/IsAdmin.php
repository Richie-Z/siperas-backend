<?php

namespace App\Http\Middleware;

use Closure;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth('siswa')->check())
            return response()->json(['status' => false, 'message' => 'Akses ditolak'], 401);
        return $next($request);
    }
}
