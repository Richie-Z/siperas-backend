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
        if (auth('siswa')->check())
            return response()->json(['status' => false, 'message' => 'Akses ditolak'], 401);
        else if (auth('petugas')->user()->level != $level)
            return response()->json(['status' => false, 'message' => 'Endpoint khusus untuk ' . $level], 401);
        else
            return $next($request);
    }
}
