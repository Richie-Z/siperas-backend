<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->guardArray = ['petugas', 'siswa'];
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);
        if ($validate->fails()) return $this->sendResponse('Validasi gagal', $validate->messages(), 401);
        $credential = request(['username', 'password']);
        $token = Auth::guard('petugas')->attempt($credential);
        return $token ? $this->sendToken($token, 'petugas') :  $this->sendResponse('Gagal,Username/Password salah', null, 401);
    }
    public function loginSiswa(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'nisn' => 'required',
        ]);
        if ($validate->fails()) return $this->sendResponse('Validasi gagal', $validate->messages(), 401);
        $siswa = Siswa::where('nisn', $request->nisn)->first();
        if (!$siswaToken = auth('siswa')->login($siswa))
            return $this->sendResponse('Gagal,NISN Salah/Belum Terdaftar', null, 401);
        return  $this->sendToken($siswaToken, 'siswa');
    }
    public function logout()
    {
        try {
            foreach ($this->guardArray as $g) {
                if (auth($g)->check()) auth($g)->logout();
            }
            return $this->sendResponse('Logout Berhasil', null, 200);
        } catch (\Throwable $th) {
            return $this->sendResponse('Error saat melakukan logout', $th, 401);
        }
    }
    public function profile()
    {
        try {
            foreach ($this->guardArray as $g) {
                if (auth($g)->check()) $user = auth($g)->user();
            }
            return $this->sendResponse(null, $user, 200);
        } catch (\Exception $ex) {
            return $this->sendResponse(null, $ex, 500);
        }
    }
    public function updateSiswa(Request $request)
    {
        if (!auth('siswa')->check())
            return $this->sendResponse('Endpoint khusus untuk siswa', null, 401);
        try {
            $user = auth('siswa')->user();
            $user->update($request->all());
            return $this->sendResponse('Update profile berhasil', null, 200);
        } catch (\Throwable $th) {
            return $this->sendResponse('Update profile gagal', $th, 200);
        }
    }
    public function update(Request $request)
    {
        if (!auth('petugas')->check())
            return $this->sendResponse('Endpoint khusus untuk administrator', null, 401);
        try {
            $user = auth('petugas')->user();
            $user->update($request->all());
            return $this->sendResponse('Update profile berhasil', null, 200);
        } catch (\Throwable $th) {
            return $this->sendResponse('Update profile gagal', $th, 200);
        }
    }
}
