<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SiswaResource;

class KelasResource extends JsonResource
{
    public function toArray($request)
    {
        $array = [
            'id' => $this->id,
            'kelas' => $this->kelas,
            'kompetensi_keahlian' => $this->kompetensi_keahlian,
            'siswa' => SiswaResource::collection($this->whenLoaded('siswa'))
        ];
        return $array;
    }
}
