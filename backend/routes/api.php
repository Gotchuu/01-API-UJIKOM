<?php

use App\Http\Controllers\API\AlatController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KategoriController;
use App\Http\Controllers\API\LaporanController;
use App\Http\Controllers\API\LogAktivitasController;
use App\Http\Controllers\API\PeminjamanController;
use App\Http\Controllers\API\PengembalianController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Dapat diakses tanpa autentikasi / token)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Wajib membawa Bearer Token via Laravel Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profil Pengguna
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route Khusus Role Admin
    Route::middleware('role.admin')->group(function () {
        // apiResource otomatis menyediakan route CRUD (index, store, show, update, destroy) untuk Kategori
        Route::apiResource('kategori', KategoriController::class);
        Route::apiResource('alat', AlatController::class);

        Route::get('/katalog', [KategoriController::class, 'katalog']);

        Route::apiResource('users', UserController::class);

        Route::get('/peminjaman', [PeminjamanController::class, 'index']);
        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show']);
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);
        Route::put('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update']);
        Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy']);

        Route::get('/pengembalian', [PengembalianController::class, 'index']);
        Route::get('/pengembalian/{pengembalian}', [PengembalianController::class, 'show']);
        Route::put('/pengembalian/{pengembalian}', [PengembalianController::class, 'update']);
        Route::delete('/pengembalian/{pengembalian}', [PengembalianController::class, 'destroy']);
        Route::get('/log-aktivitas', [LogAktivitasController::class, 'index']);
        Route::get('/laporan-peminjaman', [LaporanController::class, 'index']);
    });

    // Route Khusus Role Petugas
    Route::middleware('role.petugas')->group(function () {
        // Route Petugas
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);
        Route::post('/pengembalian', [PengembalianController::class, 'store']);
    });

    // Route Khusus Role Peminjam
    Route::middleware('role.peminjam')->group(function () {
        // Route Peminjam
        Route::get('/katalog', [KategoriController::class, 'katalog']);
        Route::post('/peminjaman', [PeminjamanController::class, 'store']);
        Route::get('/riwayat-pinjam', [PeminjamanController::class, 'riwayat']);
    });

});
