<?php

namespace App\Http\Controllers;

use App\Http\Resources\SppResource;
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
            $siswa->spp()->create([
                'tahun_ajaran' => $request->tahun_ajaran,
                'nominal' => $request->nominal,
                'history_pembayaran' => json_encode($this->sppAttribute()['history_pembayaran']),
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
        return $this->sendResponse(null, new SppResource($spp), 200);
    }
    public function update($siswa_id, $id, Request $request)
    {
        if ($request->has('history_pembayaran'))
            return $this->sendResponse('Upps, History pembayaran tidak boleh diedit', null, 500);
        $siswa = Siswa::findOrFail($siswa_id);
        $spp = $siswa->spp()->findOrFail($id);
        $jumlah = 0;
        $array_req = ['tahun_ajaran' => $request->tahun_ajaran];
        foreach (json_decode($spp->history_pembayaran) as $j) {
            if ($j == 'kosong') $jumlah++;
        }
        if ($jumlah == 12) $array_req['nominal'] = $request->nominal;
        $spp->update($array_req);
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
