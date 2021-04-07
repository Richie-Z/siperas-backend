<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Spp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PembayaranController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('isAdmin', ['only' => ['store', 'index']]);
        $this->user = auth('petugas')->user();
    }
    public function index()
    {
        $pembayaran = new Pembayaran;
        if (!$this->user->isSuperAdmin()) {
            $pembayaran->where('petugas_id', $this->user->id);
        }
        return $pembayaran->paginate(10);
    }
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:App\Models\Siswa,id',
            'spp_id' => 'required|exists:App\Models\Spp,id',
            'jumlah_bayar' => 'required|integer'
        ]);
        if ($validate->fails()) return $this->sendResponse('Validasi gagal', $validate->messages(), 422);
        $array_bulan = [6, 7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5];
        $pembayaran = $request->jumlah_bayar;
        DB::beginTransaction();
        try {
            $spp = Spp::where('id', $request->spp_id)->where('siswa_id', $request->siswa_id)->first();
            $history_pembayaran  = json_decode($spp->history_pembayaran);
            $jumlah_lunas = 0;
            foreach ($history_pembayaran as $key => $h) {
                if ($spp->nominal > $h && $h != 'kosong' && $request->is_specific == 0) {
                    $kurang = $spp->nominal - $h;
                    if ($pembayaran > $kurang) {
                        $history_pembayaran->$key = $h + $kurang;
                        $pembayaran -= $kurang;
                    } else {
                        $history_pembayaran->$key = $h + $pembayaran;
                        $pembayaran -= $pembayaran;
                    }
                }
                if ($h != 'kosong') {
                    $index = array_search($key, $array_bulan);
                    unset($array_bulan[$index]);
                }
                if ($h == $spp->nominal) $jumlah_lunas++;
            }

            if ($jumlah_lunas == 12)
                return $this->sendResponse('Error, spp sudah lunas', null, 500);
            $kembalian = $pembayaran || 0;
            $pembayaran /= $spp->nominal;
            $jumlah = 0;
            if ($pembayaran >= 1) {
                for ($i = 0; $i < explode(".", $pembayaran)[0]; $i++) {
                    $jumlah_pembayaran[] = $spp->nominal;
                    $jumlah += $spp->nominal;
                }
                if (is_double($pembayaran)) {
                    $sisa = $request->jumlah_bayar - $jumlah;
                    $jumlah_pembayaran[] = $sisa;
                }
                $pembayaran *= $spp->nominal;
            } else {
                $pembayaran *= $spp->nominal;
                $jumlah_pembayaran[] = intval($pembayaran);
            }
            if ($request->is_specific == 1) {
                $kembalian = 0;
                if (!$request->has('pembayaran_untuk') || count($request->pembayaran_untuk) != count($jumlah_pembayaran)) {
                    return $this->sendResponse("Upps, Pilihan Bulan tidak valid , dengan nominal $request->jumlah_bayar bisa untuk membayar " . count($jumlah_pembayaran) . " bulan", null, 500);
                }
                foreach ($request->pembayaran_untuk as $key => $pk) {
                    if (!in_array($pk, $array_bulan) && $spp->nominal == $history_pembayaran->$pk)
                        return $this->sendResponse("Gagal, bulan ke $pk sudah lunas", null, 500);
                    if ($history_pembayaran->$pk != 'kosong') {
                        if ($history_pembayaran->$pk + $jumlah_pembayaran[0] > $spp->nominal) {
                            $kurang_p = $spp->nominal - $history_pembayaran->$pk;
                            $history_pembayaran->$pk = $history_pembayaran->$pk + $kurang_p;
                            $jumlah_pembayaran[0] -= $kurang_p;
                            if ($history_pembayaran->$pk == $spp->nominal) $jumlah_lunas++;
                            $key = $pk;
                            if ($jumlah_lunas < 12) {
                                foreach ([6, 7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5] as $ab) {
                                    if (!in_array($key, $array_bulan) && $history_pembayaran->$key == $spp->nominal) {
                                        $key++;
                                    }
                                    $key = $key == 13 ? 1 : $key;
                                }
                                if (is_int($history_pembayaran->$key)) {
                                    if ($history_pembayaran->$key + $jumlah_pembayaran[0] > $spp->nominal) {
                                        $minus = $spp->nominal - $history_pembayaran->$key;
                                        $history_pembayaran->$key += $minus;
                                        $jumlah_pembayaran[0] -= $minus;
                                        $key++;
                                    }
                                    while ($jumlah_pembayaran[0] != 0) {
                                        if ($history_pembayaran->$key == 'kosong') {
                                            $history_pembayaran->$key = $jumlah_pembayaran[0];
                                            $jumlah_pembayaran[0] = 0;
                                        } else if ($history_pembayaran->$key != $spp->nominal && $history_pembayaran->$key + $jumlah_pembayaran[0] > $spp->nominal) {
                                            $minus = $spp->nominal - $history_pembayaran->$key;
                                            $history_pembayaran->$key += $minus;
                                            $jumlah_pembayaran[0] -= $minus;
                                        } else {
                                            $kembalian = $jumlah_pembayaran[0] + 10000;
                                            $jumlah_pembayaran[0] = 0;
                                        }
                                        $key++;
                                        $key = $key == 13 ? 1 : $key;
                                    }
                                } else {
                                    $history_pembayaran->$key = $jumlah_pembayaran[0];
                                }
                            }
                        } else {
                            $history_pembayaran->$pk += $jumlah_pembayaran[$key];
                        }
                    } else {
                        $history_pembayaran->$pk = $jumlah_pembayaran[$key];
                    }
                }
            } else {
                $month = intval(Carbon::now()->format('m'));
                foreach ($jumlah_pembayaran as $key => $jp) {
                    if ($history_pembayaran->$month == $spp->nominal) $jumlah_lunas++;
                    if ($jumlah_lunas < 12) {
                        foreach ([6, 7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5] as $ab) {
                            if (!in_array($month, $array_bulan)) {
                                $month++;
                            }
                            $month = $month == 13 ? 1 : $month;
                        }
                        $history_pembayaran->$month = $jp == 0 ? "kosong" : $jp;
                        $month += 1;
                        $month = $month == 13 ? 1 : $month;
                    }
                }
            }
            if ($request->is_specific == 0 && $kembalian != 0) {
                $kembalian = 0;
                if (is_int($request->jumlah_bayar) ? $request->jumlah_bayar : intval($request->jumlah_bayar) > $spp->nominal * 12) {
                    $kembalian = array_slice($jumlah_pembayaran, 0, count($jumlah_pembayaran) - count($array_bulan));
                    $kembalian = collect($kembalian)->sum();
                } else {
                    $jumlah = 0;
                    foreach (json_decode($spp->history_pembayaran) as $sh) {
                        if ($spp->nominal == $sh) $jumlah += 1;
                        if (is_int($sh) && $spp->nominal >= $sh && $jumlah >= 11) {
                            $kurang = $spp->nominal - $sh;
                            $kembalian = collect($jumlah_pembayaran)->sum() - $kurang;
                        }
                    }
                }
            }
            $spp->history_pembayaran = json_encode($history_pembayaran);
            $spp->save();
            $arr_create = [
                'petugas_id' => auth('petugas')->user()->id,
                'siswa_id' => $request->siswa_id,
                'jumlah_bayar' => $request->jumlah_bayar,
            ];
            $message = 'Pembayaran spp sukses';
            if ($kembalian != 0) {
                $arr_create['kembalian'] = $kembalian;
                $message = "Pembayaran spp sukses,kembalian $kembalian";
            }
            $spp->pembayaran()->create($arr_create);
            DB::commit();
            return $this->sendResponse($message, null, 200);
        } catch (\App\Exceptions\InvalidOrderException $th) {
            DB::rollBack();
            return $this->sendResponse('Pembayaran spp gagal', $th, 500);
        }
    }
    public function show($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $data = [
            'id' => $pembayaran->id,
            'nama_petugas' => $pembayaran->petugas()->first()->nama_petugas,
            'nama_siswa' => $pembayaran->siswa()->first()->nama,
            'jumlah_bayar' => $pembayaran->jumlah_bayar,
            'tgl_bayar' => $pembayaran->tgl_bayar,
            'kembalian' => $pembayaran->kembalian ?? '0'
        ];
        return $this->sendResponse(null, $data, 200);
    }
    public function history()
    {
        $data = auth('petugas')->user()->pembayaran()->leftJoin('siswa', 'siswa.id', 'pembayaran.siswa_id')
            ->select('pembayaran.id', 'siswa.nama as nama_siswa', 'pembayaran.siswa_id', 'pembayaran.jumlah_bayar', 'pembayaran.kembalian', 'pembayaran.tgl_bayar')
            ->paginate(10);
        return $this->sendResponse(null, $data, 200);
    }
}
