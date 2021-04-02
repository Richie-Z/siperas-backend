<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Petugas;

class PetugasController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function index()
    {
        try {
            $query = Petugas::where('level', '!=', 'admin')->get();
            return $this->sendResponse(null, $query, 200);
        } catch (\Throwable $th) {
            return $this->sendResponse($th, null, 200);
        }
    }
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|string|unique:petugas,username',
            'password' => 'required|string',
            'nama_petugas' => 'required|string',
        ]);
        if ($validate->fails()) return $this->sendResponse('Validasi gagal', $validate->messages(), 422);
        try {
            $petugas = new Petugas;
            $petugas->username = $request->username;
            $petugas->password = app('hash')->make($request->password);
            $petugas->nama_petugas = $request->nama_petugas;
            $petugas->level = 'petugas';
            $petugas->save();
            return $this->sendResponse('Sukses menambah petugas', null, 200);
        } catch (\Throwable $th) {
            return $this->sendResponse('Gagal menambah petugas', $th, 200);
        }
    }
    public function show($id)
    {
        $petugas = Petugas::findOrFail($id);
        return $this->sendResponse(null, $petugas->load(['pembayaran' => function ($query) {
            $query->join('siswa', 'siswa.id', '=', 'pembayaran.siswa_id')
                ->select('pembayaran.petugas_id', 'siswa.nama as nama_siswa', 'pembayaran.tgl_bayar', 'pembayaran.spp_id', 'pembayaran.jumlah_bayar', 'pembayaran.kembalian');
        }]), 200);
    }
    public function update($id, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|string|unique:petugas,username',
            'nama_petugas' => 'required|string',
        ]);
        if ($validate->fails()) return $this->sendResponse('Validasi gagal', $validate->messages(), 422);
        $petugas = Petugas::findOrFail($id);
        if ($request->has('level'))
            return $this->sendResponse('Upss, tidak boleh ada Level didalam request', null, 422);
        $req = $request->all();
        if ($request->has('password'))  $req['password'] = app('hash')->make($req['password']);
        $petugas->update($req);
        return $this->sendResponse('Sukses mengupdate petugas', $petugas, 200);
    }
    public function destroy($id)
    {
        $petugas = Petugas::findOrFail($id);
        $petugas->delete();
        return $this->sendResponse('Sukses menghapus petugas ', null, 200);
    }
}
