<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes - SI-MONI (PT Camperniq Solusi Intermedia)
|--------------------------------------------------------------------------
*/

// --- DASHBOARD UTAMA (BISA DIAKSES ADMIN & TEKNISI) ---
Route::get('/', [BarangController::class, 'index']);


// --- AUTHENTICATION ROUTES (LOGIN & LOGOUT) ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);


// --- FITUR KHUSUS ADMIN (DIPROTEKSI MIDDLEWARE ADMINONLY) ---
Route::middleware([\App\Http\Middleware\AdminOnly::class])->group(function () {
    
    // 1. Kelola User
    Route::get('/kelola-user', [AuthController::class, 'indexUser']);
    Route::post('/kelola-user/store', [AuthController::class, 'storeUser']);
    Route::get('/kelola-user/delete/{id}', [AuthController::class, 'destroyUser']);

    // 2. Master Barang (Tambah, Edit, Hapus)
    Route::get('/master-barang/create', [BarangController::class, 'createMasterBarang']);
    Route::post('/master-barang/store', [BarangController::class, 'storeMasterBarang']);
    Route::get('/master-barang/edit/{id}', [BarangController::class, 'editMasterBarang']);
    Route::post('/master-barang/update/{id}', [BarangController::class, 'updateMasterBarang']);
    Route::get('/master-barang/delete/{id}', [BarangController::class, 'destroyMasterBarang']);

    // 3. Transaksi Barang (Khusus Admin)
    Route::get('/transaksi/create', [BarangController::class, 'createTransaksi']);
    Route::post('/transaksi/store', [BarangController::class, 'storeTransaksi']);
    Route::get('/transaksi/delete/{id}', [BarangController::class, 'destroyTransaksi']);

    // 4. Riwayat Mutasi & Rekapitulasi Bulanan (Khusus Admin)
    Route::get('/riwayat', [BarangController::class, 'riwayat']);

    // 5. Fitur Ekspor Server-Side (PDF, Word, Excel - Khusus Admin)
    Route::get('/riwayat/export-pdf', [BarangController::class, 'exportPdf']);
    Route::get('/riwayat/export-excel', [BarangController::class, 'exportExcel']);
    Route::get('/riwayat/export-word', [BarangController::class, 'exportWord']);

});