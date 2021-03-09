<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PembayaranResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nisn' => $this->nisn,
            'nis' => $this->nis,
            'nama' => $this->nama,
            'alamat' => $this->alamat,
            'no_telp' => $this->no_telp,
            'nis' => $this->nis,
            'spp' => 'test'
        ];
    }
}
