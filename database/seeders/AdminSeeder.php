<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Petugas;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Petugas::create([
            'username' => 'richie',
            'password' => app('hash')->make('richie'),
            'nama_petugas' => 'Richie-Kun',
            'level' => 'admin'
        ]);
    }
}
