<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;
use Carbon\Carbon;

class Controller extends BaseController
{
    /**
     * Fungsi buat mereturn response berupa json
     *
     * @param  null|string  $message
     * @param  null|string  $data
     * @param  integer  $code
     * @return json
     */
    public function sendResponse($message, $data, $code)
    {
        $status = $code == 200 ? true : false;
        $response = ['status' => $status];
        empty($message) || $message == null ?: $response['message'] = $message;
        empty($data) || $data == null ?: $response['data'] = $data;
        return response()->json($response, $code);
    }
    /**
     * Fungsi buat mereturn token berupa json
     *
     * @param  null|string  $token
     * @return json
     */
    public function sendToken($token, $guard)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard($guard)->factory()->getTTL() * 60,
            'user' => auth($guard)->user()
        ], 200);
    }
    public function sppAttribute(): array
    {
        $year = Carbon::now()->format('Y');
        return [
            'tahun_ajaran' => $year - 1 . '/' . $year,
            'history_pembayaran' => [
                6 => 'kosong',
                7 => 'kosong',
                8 => 'kosong',
                9 => 'kosong',
                10 => 'kosong',
                11 => 'kosong',
                12 => 'kosong',
                1 => 'kosong',
                2 => 'kosong',
                3 => 'kosong',
                4 => 'kosong',
                5 => 'kosong',
            ]
        ];
    }
}
