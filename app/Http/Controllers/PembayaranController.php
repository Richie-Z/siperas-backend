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
        $this->middleware('isAdmin', ['only' => 'store']);
    }
    public function index()
    {
        return "hello world";
    }
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:App\Models\Siswa,id',
            'spp_id' => 'required|exists:App\Models\Spp,id',
            'jumlah_bayar' => 'required|integer'
        ]);
        if ($validate->fails()) return $this->sendResponse('Validasi gagal', $validate->messages(), 401);
        $array_bulan = [6, 7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5];
        $pembayaran = $request->jumlah_bayar;
        DB::beginTransaction();
        try {
            $spp = Spp::where('id', $request->spp_id)->where('siswa_id', $request->siswa_id)->first();
            $history_pembayaran  = json_decode($spp->history_pembayaran);

            foreach ($history_pembayaran as $key => $h) {
                if ($spp->nominal <= $h && $h != 'kosong') {
                    $index = array_search($key, $array_bulan);
                    unset($array_bulan[$index]);
                }
            }
            $pembayaran /= $spp->nominal;
            $jumlah = 0;
            for ($i = 0; $i < explode(".", $pembayaran)[0]; $i++) {
                $jumlah_pembayaran[] = $spp->nominal;
                $jumlah += $spp->nominal;
            }
            if (is_double($pembayaran)) {
                $sisa = $request->jumlah_bayar - $jumlah;
                $jumlah_pembayaran[] = $sisa;
            }
            if ($request->has('is_specific')) {
                if (count($request->pembayaran_untuk) != count($jumlah_pembayaran)) {
                    return $this->sendResponse('Upps, Pembayaran untuk harus diisi semua', null, 500);
                }
                foreach ($request->pembayaran_untuk as $key => $pk) {
                    if ($history_pembayaran->$pk != 'kosong') {
                        $history_pembayaran->$pk += $jumlah_pembayaran[$key];
                    } else {
                        $history_pembayaran->$pk = $jumlah_pembayaran[$key];
                    }
                }
            } else {
                $month = number_format(Carbon::now()->format('m'));
                $month = $month == 12 ? 1 : $month;
                if (!in_array($month, $array_bulan)) {
                    $month += 1;
                }
                foreach ($jumlah_pembayaran as $jp) {
                    if (!in_array($month, $array_bulan)) {
                        $month += 1;
                    }
                    $history_pembayaran->$month = $jp;
                    $month++;
                }
            }
            $spp->history_pembayaran = json_encode($history_pembayaran);
            $spp->save();
            $spp->pembayaran()->create([
                'petugas_id' => auth('petugas')->user()->id,
                'siswa_id' => $request->siswa_id,
                'jumlah_bayar' => $request->jumlah_bayar,
            ]);
            DB::commit();
            return $this->sendResponse('Pembayaran spp sukses', null, 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendResponse('Pembayaran spp gagal', $th, 500);
        }
    }
}
