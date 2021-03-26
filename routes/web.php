<?php


/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () {
    return response()->json(['status' => true, 'message' => 'Selamat datang di Siperas Endpoint'], 200);
});
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('login', 'AuthController@login');
        $router->post('siswa/login', 'AuthController@loginSiswa');
        $router->group(['middleware' => 'auth'], function () use ($router) {
            $router->put('update', 'AuthController@update');
            $router->get('profile', 'AuthController@profile');
            $router->delete('logout', 'AuthController@logout');
            $router->put('siswa/update', 'AuthController@updateSiswa');
        });
    });
    $router->group(['middleware' => ['auth', 'level:admin'], 'prefix' => 'petugas'], function () use ($router) {
        $router->post('', 'PetugasController@store');
        $router->get('', 'PetugasController@index');
        $router->get('/{id:[0-9]+}', 'PetugasController@show');
        $router->put('/{id:[0-9]+}', 'PetugasController@update');
        $router->delete('/{id:[0-9]+}', 'PetugasController@destroy');
    });
    $router->post('kelas[/{auto}]', 'KelasController@store');
    $router->group(['prefix' => 'kelas'], function () use ($router) {
        $router->get('', 'KelasController@index');
        $router->get('/{id:[0-9]+}', 'KelasController@show');
        $router->put('/{id:[0-9]+}', 'KelasController@update');
        $router->delete('/{id:[0-9]+}', 'KelasController@destroy');
    });
    $router->group(['prefix' => 'siswa', 'middleware' => 'auth'], function () use ($router) {
        $router->post('', 'SiswaController@store');
        $router->post('/{siswa_id:[0-9]+}/spp/', 'SppController@store');
        $router->get('', 'SiswaController@index');
        $router->get('/{id:[0-9]+}', 'SiswaController@show');
        $router->get('/{siswa_id:[0-9]+}/spp/{id:[0-9]+}', 'SppController@show');
        $router->put('/{id:[0-9]+}', 'SiswaController@update');
        $router->put('/{siswa_id:[0-9]+}/spp/{id:[0-9]+}', 'SppController@update');
        $router->delete('/{id:[0-9]+}', 'SiswaController@destroy');
        $router->delete('/{siswa_id:[0-9]+}/spp/{id:[0-9]+}', 'SppController@destroy');
    });
    $router->group(['prefix' => 'pembayaran'], function () use ($router) {
        $router->post('', 'PembayaranController@store');
        $router->get('', 'PembayaranController@index');
        $router->get('/{id:[0-9]+}', 'PembayaranController@show');
    });
    $router->group(['prefix' => 'rekap'], function () use ($router) {
        $router->get('', 'RekapController@index');
        $router->get('per_minggu', 'RekapController@perMinggu');
        $router->get('per_petugas', 'RekapController@perPetugas');
    });
});
