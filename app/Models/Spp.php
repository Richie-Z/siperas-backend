<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spp extends Model
{
    use HasFactory;
    protected $table = 'spp';
    protected $fillable = ['nominal', 'tahun', 'siswa_id', 'history_pembayaran'];
    public function siswa()
    {
        return $this->belongsTo('App\Models\Siswa');
    }
}
