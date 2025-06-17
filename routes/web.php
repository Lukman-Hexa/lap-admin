<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\KonsumenController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LapanganController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\TopupController;
use App\Http\Controllers\Api\TopupApiController;
use App\Http\Controllers\PembayaranPageController;




Route::get('/', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/', [UserController::class, 'login'])->name('auth.login');
Route::get('forgot-password', [UserController::class, 'ShowPass'])->name('ShowPass');

// Harus login dulu baru kebuka
Route::middleware(['auth'])->group(function() {

Route::get('dashboard', [UserController::class, 'showHome'])->name('dashboard');
Route::get('logout', [UserController::class, 'logout'])->name('logout');

Route::get('informasi', [UserController::class, 'showInformasi'])->name('informasi');
// Route::get('pembayaran', [UserController::class, 'showPembayaran'])->name('pembayaran');
Route::get('konsumen', [KonsumenController::class, 'index'])->name('konsumen');

// Pastikan ini memanggil LapanganController::index yang mengembalikan view lapangan/index.blade.php
Route::get('/lapangan', [LapanganController::class, 'index'])->name('lapangan.index');

// Rute untuk halaman Data Fasilitas
Route::get('/fasilitas', [FasilitasController::class, 'index'])->name('fasilitas.index');

Route::get('/pemesanan', [PemesananController::class, 'index'])->name('pemesanan');


// Rute untuk menampilkan halaman daftar top up
Route::get('/topup', [TopupController::class, 'index'])->name('topup');
// TAMBAHKAN INI DI routes/web.php
Route::patch('/topup/{id}/confirm', [TopupApiController::class, 'confirm'])->middleware('auth');

// Rute untuk menampilkan halaman pembayaran
Route::get('/pembayaran', [PembayaranPageController::class, 'index'])->name('pembayaran');

});