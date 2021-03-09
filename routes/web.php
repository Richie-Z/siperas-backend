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

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('login', 'AuthController@login');
        $router->post('siswa/login', 'AuthController@loginSiswa');
        $router->group(['middleware' => 'auth'], function () use ($router) {
            $router->get('profile', 'AuthController@profile');
            $router->delete('logout', 'AuthController@logout');
        });
    });
    $router->group(['middleware' => ['auth', 'level:admin'], 'prefix' => 'petugas'], function () use ($router) {
        $router->post('', 'PetugasController@store');
        $router->get('', 'PetugasController@index');
        $router->get('/{id:[0-9]+}', 'PetugasController@show');
        $router->post('/{id:[0-9]+}', 'PetugasController@update');
        $router->delete('/{id:[0-9]+}', 'PetugasController@destroy');
    });
    $router->group(['prefix' => 'kelas', 'middleware' => 'auth'], function () use ($router) {
        $router->post('', 'KelasController@store');
        $router->get('', 'KelasController@index');
        $router->get('/{id:[0-9]+}', 'KelasController@show');
        $router->post('/{id:[0-9]+}', 'KelasController@update');
        $router->delete('/{id:[0-9]+}', 'KelasController@destroy');
    });
    $router->group(['prefix' => 'siswa', 'middleware' => 'auth'], function () use ($router) {
        $router->post('', 'SiswaController@store');
        $router->get('', 'SiswaController@index');
    });
});
