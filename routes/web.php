<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\BarangController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes - SI-MONI (PT Camperniq Solusi Intermedia)
|--------------------------------------------------------------------------
*/

// --- DASHBOARD UTAMA ---
Route::get('/', [BarangController::class, 'index']);


// --- AUTHENTICATION ROUTES (LOGIN & LOGOUT) ---
Route::get('/login', function () {
    if (view()->exists('auth.login')) {
        return view('auth.login');
    } elseif (view()->exists('login')) {
        return view('login');
    }
    return redirect('/');
})->name('login');

// Route Login Dinamis & Penataan Session Sesuai User DB
Route::post('/login', function (Request $request) {
    $username = $request->input('username') ?? $request->input('email') ?? $request->input('nama') ?? $request->input('nama_lengkap');

    $tabel = Schema::hasTable('users') ? 'users' : 'user';
    $query = DB::table($tabel);
    
    if (Schema::hasColumn($tabel, 'username')) {
        $query->where('username', $username);
    } elseif (Schema::hasColumn($tabel, 'nama_lengkap')) {
        $query->where('nama_lengkap', $username);
    }

    if (Schema::hasColumn($tabel, 'email')) {
        $query->orWhere('email', $username);
    }

    $user = $query->first();

    // Wajib ada di database
    if (!$user) {
        return redirect('/login')->with('error', 'Username atau Akun tidak ditemukan/sudah dihapus!');
    }

    // Ambil role dan nama spesifik milik user yang sedang login
    $role = strtoupper($user->role ?? $user->hak_akses ?? $user->level ?? 'TEKNISI');
    $nama = $user->nama_lengkap ?? $user->nama ?? $user->name ?? $user->username ?? $username;

    // Simpan ke Session
    session([
        'user_role' => $role, 
        'user_name' => $nama
    ]);

    return redirect('/')->with('success', 'Berhasil login sebagai ' . $role . '!');
});

Route::get('/logout', function () {
    session()->forget(['user_role', 'user_name']);
    return redirect('/login')->with('success', 'Berhasil keluar dari sistem.');
});


// --- FITUR KHUSUS ADMIN (DIPROTEKSI MIDDLEWARE ADMINONLY) ---
Route::middleware([\App\Http\Middleware\AdminOnly::class])->group(function () {
    
    // Kelola User
    Route::get('/kelola-user', function () {
        $tabel = Schema::hasTable('users') ? 'users' : 'user';
        $users = DB::table($tabel)->get();

        if (view()->exists('users')) {
            return view('users', compact('users'));
        } elseif (view()->exists('user.index')) {
            return view('user.index', compact('users'));
        } elseif (view()->exists('kelola_user')) {
            return view('kelola_user', compact('users'));
        } elseif (view()->exists('kelola-user')) {
            return view('kelola-user', compact('users'));
        }

        return redirect('/')->with('error', 'View kelola user tidak ditemukan.');
    });

    Route::post('/kelola-user/store', function (Request $request) {
        $tabel = Schema::hasTable('users') ? 'users' : 'user';
        
        $namaInput = $request->input('nama_lengkap') ?? $request->input('nama') ?? $request->input('name') ?? 'Karyawan Baru';
        $usernameInput = $request->input('username') ?? strtolower(str_replace(' ', '', $namaInput));
        $roleInput = strtoupper($request->input('role') ?? $request->input('hak_akses') ?? $request->input('level') ?? 'TEKNISI');

        $cekUsername = DB::table($tabel)->where('username', $usernameInput)->first();
        if ($cekUsername) {
            return redirect('/kelola-user')->with('error', 'Username "' . $usernameInput . '" sudah digunakan!');
        }

        $dataInsert = [];

        if (Schema::hasColumn($tabel, 'nama_lengkap')) $dataInsert['nama_lengkap'] = $namaInput;
        if (Schema::hasColumn($tabel, 'username')) $dataInsert['username'] = $usernameInput;
        if (Schema::hasColumn($tabel, 'email')) $dataInsert['email'] = $request->input('email') ?? ($usernameInput . '@camperniq.com');
        if (Schema::hasColumn($tabel, 'password')) $dataInsert['password'] = bcrypt($request->input('password') ?? '123456');
        
        if (Schema::hasColumn($tabel, 'role')) $dataInsert['role'] = $roleInput;
        if (Schema::hasColumn($tabel, 'hak_akses')) $dataInsert['hak_akses'] = $roleInput;
        if (Schema::hasColumn($tabel, 'level')) $dataInsert['level'] = $roleInput;

        if (Schema::hasColumn($tabel, 'created_at')) $dataInsert['created_at'] = now();
        if (Schema::hasColumn($tabel, 'updated_at')) $dataInsert['updated_at'] = now();

        DB::table($tabel)->insert($dataInsert);

        return redirect('/kelola-user')->with('success', 'Akun ' . $namaInput . ' berhasil ditambahkan!');
    });

    Route::get('/kelola-user/delete/{id}', function ($id) {
        $tabel = Schema::hasTable('users') ? 'users' : 'user';
        DB::table($tabel)->where('id', $id)->delete();
        return redirect('/kelola-user')->with('success', 'Akun pengguna berhasil dihapus!');
    });

    // Master Barang (Tambah, Edit, Hapus)
    Route::get('/master-barang/create', [BarangController::class, 'createMasterBarang']);
    Route::post('/master-barang/store', [BarangController::class, 'storeMasterBarang']);
    Route::get('/master-barang/edit/{id}', [BarangController::class, 'editMasterBarang']);
    Route::post('/master-barang/update/{id}', [BarangController::class, 'updateMasterBarang']);
    Route::get('/master-barang/delete/{id}', [BarangController::class, 'destroyMasterBarang']);

});


// --- TRANSAKSI BARANG (BISA DIAKSES ADMIN & TEKNISI) ---
Route::get('/transaksi/create', [BarangController::class, 'createTransaksi']);
Route::post('/transaksi/store', [BarangController::class, 'storeTransaksi']);
Route::get('/transaksi/delete/{id}', [BarangController::class, 'destroyTransaksi']);


// --- RIWAYAT MUTASI & REKAPITULASI BULANAN ---
Route::get('/riwayat', [BarangController::class, 'riwayat']);


// --- FITUR EKSPOR SERVER-SIDE MURNI (PDF, WORD, EXCEL) ---
Route::get('/riwayat/export-pdf', [BarangController::class, 'exportPdf']);
Route::get('/riwayat/export-excel', [BarangController::class, 'exportExcel']);
Route::get('/riwayat/export-word', [BarangController::class, 'exportWord']);