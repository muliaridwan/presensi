<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/useravatar', [RegisterController::class, 'useravatar'])->middleware('auth:api');
Route::put('/user/{id}', [RegisterController::class, 'update'])->middleware('auth:api');
Route::get('/user/{id}', [RegisterController::class, 'detail'])->middleware('auth:api');
Route::post('/ubah-password', [LoginController::class, 'ubahPassword'])->middleware('auth:api');
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:api');

Route::get('/laporanuser',[AbsenController::class, 'laporanuser'])->middleware('auth:api');
Route::get('/detaillaporanuser/{id}',[AbsenController::class, 'detaillaporanuser'])->middleware('auth:api');
Route::get('/grafikpresensiuser/{id}',[AbsenController::class, 'grafikpresensiuser'])->middleware('auth:api');
Route::get('/adminabsen',[AbsenController::class, 'indexadmin'])->middleware('auth:api');
Route::get('/absen',[AbsenController::class, 'index'])->middleware('auth:api');
Route::post('/punchin',[AbsenController::class, 'punchin'])->middleware('auth:api');
Route::put('/punchout/{id}',[AbsenController::class, 'punchout'])->middleware('auth:api');
Route::get('/absen/{id}', [AbsenController::class, 'detail'])->middleware('auth:api');
Route::delete('/absen/{id}', [AbsenController::class, 'delete'])->middleware('auth:api');
Route::get('/statusabsenmasuk', [AbsenController::class, 'statusabsenmasuk'])->middleware('auth:api');
//
// Route::get('/getalamat', [AbsenController::class, 'getalamat']);
Route::get('/getalamat/{lat}/{lng}', [AbsenController::class, 'getalamat']);
Route::get('/getalamatkeluar/{lat}/{lng}', [AbsenController::class, 'getalamatkeluar']);
Route::get('/carbon', [AbsenController::class, 'carbon']);
Route::get('/grafik', [AbsenController::class, 'grafik'])->middleware('auth:api');
Route::get('/grafikbulananuser', [AbsenController::class, 'grafikbulananuser'])->middleware('auth:api');
Route::get('/grafikbulananadmin/{id}', [AbsenController::class, 'grafikbulananadmin'])->middleware('auth:api');