<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('level:admin', ['only' => 'store']);
    }
    public function index()
    {
        try {
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'no_telp' => 'required|string',
            'nisn' => 'required|string|unique:siswa,nisn',
            'nis' => 'required|string|unique:siswa,nis',
            'kelas_id' => 'required|integer',
        ]);
        if ($validate->fails()) return $this->sendResponse('Validasi gagal', $validate->messages(), 401);
        DB::beginTransaction();
        try {
            $siswa = new Siswa;
            $siswa->nama = $request->nama;
            $siswa->alamat = $request->alamat;
            $siswa->no_telp = $request->no_telp;
            $siswa->nisn = $request->nisn;
            $siswa->nis = $request->nis;
            $siswa->kelas_id = $request->kelas_id;
            $siswa->save();
            $siswa->spp()->create([
                'nominal' => 200000
            ]);
            DB::commit();
            return $this->sendResponse('Sukses menambah siswa', null, 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendResponse('Gagal menambah siswa', $th, 500);
        }
    }
}
