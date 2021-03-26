<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Pembayaran;
use App\Models\Petugas;

class RekapController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->carbon = new Carbon;
    }

    public function index()
    {
        $array = [
            'today' => Pembayaran::whereDay('created_at', $this->carbon->day)->selectRaw('SUM(jumlah_bayar) as jumlah')->first()->jumlah,
            'week' => Pembayaran::whereRaw("WEEK(created_at) =" . $this->carbon->week)->selectRaw('SUM(jumlah_bayar) as jumlah')->first()->jumlah,
            'month' => Pembayaran::whereMonth('created_at', $this->carbon->month)->selectRaw('SUM(jumlah_bayar) as jumlah')->first()->jumlah,
            'petugas' => Petugas::where('level', '!=', 'admin')->count()
        ];
        return $this->sendResponse(null, $array, 200);
    }
    public function perMinggu()
    {
        $array_days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jum'at", "Sabtu"];
        $days = [];
        foreach ($array_days as $key => $day) {
            $val = Pembayaran::whereRaw("WEEK(created_at) =" . $this->carbon->week)
                ->whereRaw("DAYOFWEEK(created_at) =" . ($key + 1))
                ->selectRaw("SUM(jumlah_bayar) as jumlah")
                ->first()->jumlah;
            $days[$key]['day'] = $day;
            $days[$key]['value'] = $val ?? 0;
        }
        $res = [
            "week" => $this->carbon->week,
            "days" => $days
        ];
        return $this->sendResponse(null, $res, 200);
    }
    public function perPetugas()
    {
        $array_petugas = Petugas::select('id', 'nama_petugas')->get();
        $res = [];
        foreach ($array_petugas as $key => $p) {
            $val = $p->pembayaran()->count();
            $res[$key]['name'] = $p->nama_petugas;
            $res[$key]['value'] = $val;
        }
        return $this->sendResponse(null, $res, 200);
    }
}
