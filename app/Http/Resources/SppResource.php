<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SppResource extends JsonResource
{
    public function toArray($request)
    {
        $jumlah = 0;
        $total_bayar = 0;
        foreach (json_decode($this->history_pembayaran) as $j) {
            if ($j == "kosong") $jumlah++;
            else $total_bayar += $j;
        }
        $array = [
            'id' => $this->id,
            'tahun_ajaran' => $this->tahun_ajaran,
            'history_pembayaran' => json_decode($this->history_pembayaran),
            'nominal' => $this->nominal,
            'editable' => $jumlah == 12 ? true : false,
            'is_lunas' => $total_bayar == $this->nominal * 12 ? true : false
        ];
        return $array;
    }
}
