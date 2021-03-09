<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;
    protected $fillable = ['kelas', 'kompetensi_keahlian'];
    public function siswa()
    {
        return $this->hasMany('App\Models\Siswa');
    }
}
