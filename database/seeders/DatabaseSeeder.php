<?php

namespace Database\Seeders;

use App\Models\Petugas;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
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
