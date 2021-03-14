<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;
    protected $table = 'pembayaran';
    protected $fillable = ['siswa_id', 'spp_id', 'jumlah_bayar', 'petugas_id', 'kembalian'];
    public function petugas()
    {
        return $this->belongsTo('App\Models\Petugas', 'petugas_id');
    }
    public function siswa()
    {
        return $this->belongsTo('App\Models\Siswa', 'siswa_id');
    }
}
