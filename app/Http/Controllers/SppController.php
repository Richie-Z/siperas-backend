<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SppController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('level:admin', ['except' => 'show']);
    }
    public function store($siswa_id, Request $request)
    {
        $siswa = Siswa::findOrFail($siswa_id);
        if (count(Siswa::with('spp')->findOrFail($siswa_id)->spp->toArray()) === 3)
            return $this->sendResponse('Error, satu siswa hanya boleh memiliki max 3 buah buku spp', null, 422);
        DB::beginTransaction();
        try {
            $siswa->findOrFail($siswa_id);
            $siswa->spp()->create([
                'tahun_ajaran' => $request->tahun_ajaran,
                'nominal' => $request->nominal,
            ]);
            DB::commit();
            return $this->sendResponse('Sukses membuat buku SPP', null, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->sendResponse('Gagal saat membuat buku SPP', $th, 500);
        }
    }
    public function show($siswa_id, $id)
    {
        $siswa = Siswa::findOrFail($siswa_id);
        $spp = $siswa->spp()->findOrFail($id);
        return $this->sendResponse(null, $spp, 200);
    }
    public function update($siswa_id, $id, Request $request)
    {
        $siswa = Siswa::findOrFail($siswa_id);
        $spp = $siswa->spp()->findOrFail($id);
        $spp->update($request->all());
        return $this->sendResponse('Sukses mengupdate buku SPP', null, 200);
    }
    public function destroy($siswa_id, $id)
    {
        $siswa = Siswa::findOrFail($siswa_id);
        $spp = $siswa->spp()->findOrFail($id);
        $spp->delete();
        return $this->sendResponse('Sukses menghapus buku SPP', null, 200);
    }
}
