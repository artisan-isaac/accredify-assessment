<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [\App\Http\Controllers\UserController::class, 'login']);

Route::get('/upload', function () {
    $token = session('token');

    if (!$token) {
        return redirect()->route('login');
    }

    return view('upload', [
        'token' => session('token')]
    );
})->name('upload');

