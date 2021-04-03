<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SppResource;

class SiswaResource extends JsonResource
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
            'kelas_id' => $this->kelas_id,
            'kelas_jurusan' => $this->kelas()->first()->kelas . " " . $this->kelas()->first()->kompetensi_keahlian,
            'spp' => SppResource::collection($this->whenLoaded('spp'))
        ];
    }
}
