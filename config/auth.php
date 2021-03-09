<?php

return [
    'defaults' => [
        'guard' => 'api',
    ],
    'guards' => [
        'api' => ['driver' => 'jwt', 'provider' => 'petugas'],
        'petugas' => ['driver' => 'jwt', 'provider' => 'petugas'],
        'siswa' => ['driver' => 'jwt', 'provider' => 'siswa']
    ],

    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => \App\Models\User::class],
        'petugas' => ['driver' => 'eloquent', 'model' => \App\Models\Petugas::class],
        'siswa' => ['driver' => 'eloquent', 'model' => \App\Models\Siswa::class],
    ],
];
