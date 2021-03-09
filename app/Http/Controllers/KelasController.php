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
        $this->middleware('level:admin', ['only' => ['store', 'update', 'destroy']]);
    }
    public function index()
    {
        try {
            $kelas = Kelas::all();
            return $this->sendResponse(null,  KelasResource::collection($kelas), 200);
        } catch (\Throwable $th) {
            return $this->sendResponse($th, null, 500);
        }
    }
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'kelas' => 'required|integer|in:10,11,12',
            'kompetensi_keahlian' => 'required|string',
        ]);
        if ($validate->fails()) return $this->sendResponse('Validasi gagal', $validate->messages(), 401);
        try {
            $kelas = new Kelas;
            $kelas->kelas = $request->kelas;
            $kelas->kompetensi_keahlian = $request->kompetensi_keahlian;
            $kelas->save();
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
