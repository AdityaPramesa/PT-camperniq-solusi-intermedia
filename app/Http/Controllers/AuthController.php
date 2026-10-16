<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Session::has('user_role')) {
            return redirect('/');
        }
        
        if (view()->exists('auth.login')) {
            return view('auth.login');
        } elseif (view()->exists('login')) {
            return view('login');
        }

        return redirect('/');
    }

    public function login(Request $request)
    {
        // 1. Ambil input username dan password persis (tanpa strtolower)
        $inputUsername = trim($request->input('username'));
        $inputPassword = $request->input('password');

        // Tentukan nama tabel users/user
        $tabel = Schema::hasTable('users') ? 'users' : 'user';

        // Cari user berdasarkan username di database
        $user = DB::table($tabel)->where('username', $inputUsername)->first();

        if ($user) {
            // 2. Validasi Username Harus Tepat Persis (Case-Sensitive Exact Match)
            if (strcmp((string)$user->username, (string)$inputUsername) !== 0) {
                return back()->with('error', 'Username atau Password salah!')->withInput();
            }

            $isValidPassword = false;

            // 3. Validasi Password (Hash / Case-Sensitive Plain-Text)
            if (Hash::check($inputPassword, $user->password)) {
                $isValidPassword = true;
            } elseif (strcmp((string)$user->password, (string)$inputPassword) === 0) {
                $isValidPassword = true;
                
                // Otomatis update password ke Hash Bcrypt resmi demi keamanan
                DB::table($tabel)->where('id', $user->id)->update([
                    'password' => Hash::make($inputPassword)
                ]);
            }

            // Jika Username DAN Password 100% Cocok Persis
            if ($isValidPassword) {
                $namaUser = $user->nama_lengkap ?? $user->nama ?? $user->name ?? $user->username;
                $roleUser = strtoupper($user->role ?? $user->hak_akses ?? $user->level ?? 'TEKNISI');

                Session::put('user_id', $user->id);
                Session::put('user_nama', $namaUser);
                Session::put('user_role', $roleUser);

                return redirect('/')->with('success', 'Berhasil login sebagai ' . $roleUser . '!');
            }
        }

        // Jika Username/Password tidak cocok persis
        return back()->with('error', 'Username atau Password salah!')->withInput();
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Berhasil keluar dari sistem.');
    }

    // --- FITUR KELOLA USER (KHUSUS ADMIN) ---

    public function indexUser()
    {
        if (Session::get('user_role') !== 'ADMIN') {
            return redirect('/')->with('error', 'Akses ditolak!');
        }

        $tabel = Schema::hasTable('users') ? 'users' : 'user';
        $users = DB::table($tabel)->get();

        foreach ($users as $u) {
            if (isset($u->role)) {
                $u->role = strtoupper($u->role);
            }
        }

        if (view()->exists('users')) {
            return view('users', compact('users'));
        } elseif (view()->exists('user.index')) {
            return view('user.index', compact('users'));
        }

        return view('users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        if (Session::get('user_role') !== 'ADMIN') {
            return redirect('/')->with('error', 'Akses ditolak!');
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required|min:4',
            'role'     => 'required',
        ]);

        $tabel = Schema::hasTable('users') ? 'users' : 'user';

        $namaInput = $request->input('nama_lengkap') ?? $request->input('nama') ?? $request->input('name') ?? 'Karyawan Baru';
        $usernameInput = trim($request->input('username'));
        $roleInput = strtoupper($request->input('role'));

        $cek = DB::table($tabel)->where('username', $usernameInput)->first();
        if ($cek) {
            return redirect('/kelola-user')->with('error', 'Username "' . $usernameInput . '" sudah digunakan!');
        }

        $dataInsert = [
            'username' => $usernameInput,
            'password' => Hash::make(trim($request->input('password'))),
        ];

        if (Schema::hasColumn($tabel, 'nama_lengkap')) $dataInsert['nama_lengkap'] = $namaInput;
        elseif (Schema::hasColumn($tabel, 'nama')) $dataInsert['nama'] = $namaInput;
        elseif (Schema::hasColumn($tabel, 'name')) $dataInsert['name'] = $namaInput;

        if (Schema::hasColumn($tabel, 'role')) $dataInsert['role'] = $roleInput;
        elseif (Schema::hasColumn($tabel, 'hak_akses')) $dataInsert['hak_akses'] = $roleInput;

        if (Schema::hasColumn($tabel, 'created_at')) $dataInsert['created_at'] = now();
        if (Schema::hasColumn($tabel, 'updated_at')) $dataInsert['updated_at'] = now();

        DB::table($tabel)->insert($dataInsert);

        return redirect('/kelola-user')->with('success', 'Akun ' . $namaInput . ' berhasil ditambahkan!');
    }

    public function destroyUser($id)
    {
        if (Session::get('user_role') !== 'ADMIN') {
            return redirect('/')->with('error', 'Akses ditolak!');
        }

        if ($id == Session::get('user_id')) {
            return redirect('/kelola-user')->with('error', 'Tidak dapat menghapus akun Anda sendiri yang sedang aktif!');
        }

        $tabel = Schema::hasTable('users') ? 'users' : 'user';
        DB::table($tabel)->where('id', $id)->delete();

        return redirect('/kelola-user')->with('success', 'Akun pengguna berhasil dihapus!');
    }
}