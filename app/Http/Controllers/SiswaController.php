<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\SiswaResource;

class SiswaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('level:admin', ['only' => 'store', 'update', 'destory']);
    }
    public function index()
    {
        try {
            return $this->sendResponse(null, SiswaResource::collection(Siswa::all()), 200);
        } catch (\Throwable $th) {
            return $this->sendResponse('Gagal', $th, 500);
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
                'tahun_ajaran' => $this->sppAttribute()['tahun_ajaran'],
                'history_pembayaran' => json_encode($this->sppAttribute()['history_pembayaran'])
            ]);
            DB::commit();
            return $this->sendResponse('Sukses menambah siswa', null, 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendResponse('Gagal menambah siswa', $th, 500);
        }
    }
    public function show($id)
    {
        $siswa = Siswa::findOrFail($id);
        return $this->sendResponse(null, new SiswaResource($siswa->load(['spp' => function ($query) {
            $query->orderBy('tahun_ajaran', 'ASC');
        }])), 200);
    }
    public function update($id, Request $request)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->update($request->all());
        return $this->sendResponse('Sukses update siswa', null, 200);
    }
    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->delete();
        return $this->sendResponse('Sukses delete siswa', null, 200);
    }
}
