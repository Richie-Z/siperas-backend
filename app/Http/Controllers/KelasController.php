<?php

namespace App\Http\Controllers;

use App\Http\Resources\KelasResource;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('level:admin', ['only' => ['store', 'update', 'destroy']]);
    }
    public function index()
    {
        try {
            $kelas = Kelas::orderBy('kompetensi_keahlian', 'asc')->orderBy('kelas', 'asc')->get();
            return $this->sendResponse(null,  KelasResource::collection($kelas), 200);
        } catch (\Throwable $th) {
            return $this->sendResponse($th, null, 500);
        }
    }
    public function store($auto = null, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'kelas' => 'integer|in:10,11,12',
            'kompetensi_keahlian' => 'required|string',
        ]);
        if ($validate->fails()) return $this->sendResponse('Validasi gagal', $validate->messages(), 422);
        $kelas_jurusan = Kelas::where('kompetensi_keahlian', $request->kompetensi_keahlian);
        try {
            if ($kelas_jurusan->count() === 3)
                return $this->sendResponse('Error, max kelas perjurusan adalah 3', null, 422);
            $kelas = new Kelas;
            if ($auto) {
                $array_kelas = [10, 11, 12];
                if ($kelas_jurusan->count() != 0) {
                    foreach ($kelas_jurusan->get() as $kj) {
                        $key = array_search($kj->kelas, $array_kelas);
                        unset($array_kelas[$key]);
                    }
                }
                foreach ($array_kelas as $k) {
                    $kelas->create([
                        'kelas' => $k,
                        'kompetensi_keahlian' => $request->kompetensi_keahlian
                    ]);
                }
            } else {
                $kelas->kelas = $request->kelas;
                $kelas->kompetensi_keahlian = $request->kompetensi_keahlian;
                $kelas->save();
            }
            return $this->sendResponse('Sukses menambah kelas', null, 200);
        } catch (\Throwable $th) {
            return $this->sendResponse($th, null, 500);
        }
    }
    public function show($id)
    {
        $kelas = Kelas::findOrFail($id);
        return $this->sendResponse(null, new KelasResource($kelas->load(['siswa' => function ($query) {
            $query->orderBy('nama', 'ASC');
        }])), 200);
    }
    public function update($id, Request $request)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->update($request->all());
        return $this->sendResponse('Sukses mengupdate Kelas', null, 200);
    }
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();
        return $this->sendResponse('Sukses menghapus Kelas', null, 200);
    }
}
