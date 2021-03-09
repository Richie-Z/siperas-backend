<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SppResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tahun' => $this->tahun,
            'history_pembayaran' => json_decode($this->history_pembayaran),
            'nominal' => $this->nominal,
        ];
    }
}
