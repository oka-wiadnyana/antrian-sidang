<?php

use App\Http\Controllers\AntrianUmumController;
use App\Http\Controllers\Auth\LoginBypassController;
use App\Http\Controllers\CheckinPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [CheckinPublicController::class, 'showForm'])->name('lapor-hadir.form');
Route::post('/lapor-hadir', [CheckinPublicController::class, 'store'])->name('lapor-hadir.store');

// Untuk autocomplete nomor perkara
Route::get('/api/perkara/search', [CheckinPublicController::class, 'searchPerkara']);

// Untuk load pihak setelah pilih perkara
Route::get('/api/perkara/{id}/pihak', [CheckinPublicController::class, 'getPihak']);

Route::post('/api/checkin', [CheckinPublicController::class, 'store'])->name('api.checkin');

// routes/web.php
Route::get('/antrian-umum', [AntrianUmumController::class, 'index'])->name('antrian.umum');

Route::get('/login-bypass/{email}', [LoginBypassController::class, 'login'])
    ->name('login.bypass')
    ->middleware('signed', 'throttle:10,1');
