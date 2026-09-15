<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Session::has('user_id')) {
            return redirect('/');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $username = strtolower(trim($request->input('username')));
        $password = trim($request->input('password'));

        // 1. Auto Bypass untuk Akun ADMIN Default
        if ($username === 'admin' && $password === 'admin123') {
            $user = DB::table('users')->where('username', 'admin')->first();
            
            if (!$user) {
                $dataInsert = [
                    'username' => 'admin',
                    'password' => 'admin123',
                ];

                if (Schema::hasColumn('users', 'nama_lengkap')) {
                    $dataInsert['nama_lengkap'] = 'Admin PT Camperniq';
                } elseif (Schema::hasColumn('users', 'nama')) {
                    $dataInsert['nama'] = 'Admin PT Camperniq';
                } elseif (Schema::hasColumn('users', 'name')) {
                    $dataInsert['name'] = 'Admin PT Camperniq';
                }

                if (Schema::hasColumn('users', 'role')) {
                    $dataInsert['role'] = 'ADMIN';
                }

                $userId = DB::table('users')->insertGetId($dataInsert);
            } else {
                $userId = $user->id;
                if (Schema::hasColumn('users', 'role')) {
                    DB::table('users')->where('id', $userId)->update(['role' => 'ADMIN']);
                }
            }

            $namaTampil = $user->nama_lengkap ?? $user->nama ?? $user->name ?? 'Admin PT Camperniq';

            Session::put('user_id', $userId);
            Session::put('user_nama', $namaTampil);
            Session::put('user_role', 'ADMIN');

            return redirect('/')->with('success', 'Selamat datang kembali, Administrator!');
        }

        // 2. Auto Bypass untuk Akun TEKNISI Default
        if ($username === 'teknisi' && ($password === 'teknisi123' || $password === 'teknisi')) {
            $user = DB::table('users')->where('username', 'teknisi')->first();
            
            if (!$user) {
                $dataInsert = [
                    'username' => 'teknisi',
                    'password' => 'teknisi123',
                ];

                if (Schema::hasColumn('users', 'nama_lengkap')) {
                    $dataInsert['nama_lengkap'] = 'Teknisi Lapangan';
                } elseif (Schema::hasColumn('users', 'nama')) {
                    $dataInsert['nama'] = 'Teknisi Lapangan';
                } elseif (Schema::hasColumn('users', 'name')) {
                    $dataInsert['name'] = 'Teknisi Lapangan';
                }

                if (Schema::hasColumn('users', 'role')) {
                    $dataInsert['role'] = 'TEKNISI';
                }

                $userId = DB::table('users')->insertGetId($dataInsert);
            } else {
                $userId = $user->id;
            }

            $namaTampil = $user->nama_lengkap ?? $user->nama ?? $user->name ?? 'Teknisi Lapangan';

            Session::put('user_id', $userId);
            Session::put('user_nama', $namaTampil);
            Session::put('user_role', 'TEKNISI');

            return redirect('/')->with('success', 'Selamat datang, Teknisi Lapangan!');
        }

        // 3. Autentikasi User Lain dari Database
        $user = DB::table('users')->where('username', $username)->first();

        if ($user && ($user->password === $password || password_verify($password, $user->password))) {
            $namaUser = $user->nama_lengkap ?? $user->nama ?? $user->name ?? $user->username;
            $roleUser = strtoupper($user->role ?? 'TEKNISI');

            Session::put('user_id', $user->id);
            Session::put('user_nama', $namaUser);
            Session::put('user_role', $roleUser);

            return redirect('/')->with('success', 'Selamat datang kembali, ' . $namaUser . '!');
        }

        return back()->with('error', 'Username atau Password salah!')->withInput();
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    // --- FITUR KELOLA USER (KHUSUS ADMIN) ---

    public function indexUser()
    {
        if (Session::get('user_role') !== 'ADMIN') {
            return redirect('/')->with('error', 'Akses ditolak! Fitur ini khusus Admin.');
        }

        // PAKSA UPDATE ROLE USER 'admin' MENJADI 'ADMIN' DI DATABASE
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('username', 'admin')->update(['role' => 'ADMIN']);
        }

        $users = DB::table('users')->orderBy('id', 'ASC')->get();

        // Standardisasi huruf kapital untuk role pada semua data user
        foreach ($users as $u) {
            if (isset($u->role)) {
                $u->role = strtoupper($u->role);
            }
        }
        
        return view('users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        if (Session::get('user_role') !== 'ADMIN') {
            return redirect('/')->with('error', 'Akses ditolak!');
        }

        $request->validate([
            'username' => 'required|unique:users,username',
            'password' => 'required|min:4',
            'role' => 'required',
        ]);

        $dataInsert = [
            'username' => strtolower(trim($request->username)),
            'password' => trim($request->password),
            'role' => strtoupper($request->role),
        ];

        if (Schema::hasColumn('users', 'nama_lengkap')) {
            $dataInsert['nama_lengkap'] = trim($request->nama);
        } elseif (Schema::hasColumn('users', 'nama')) {
            $dataInsert['nama'] = trim($request->nama);
        } elseif (Schema::hasColumn('users', 'name')) {
            $dataInsert['name'] = trim($request->nama);
        }

        DB::table('users')->insert($dataInsert);

        return redirect('/kelola-user')->with('success', 'Akun pengguna baru berhasil dibuat!');
    }

    public function destroyUser($id)
    {
        if (Session::get('user_role') !== 'ADMIN') {
            return redirect('/')->with('error', 'Akses ditolak!');
        }

        if ($id == Session::get('user_id')) {
            return redirect('/kelola-user')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan!');
        }

        DB::table('users')->where('id', $id)->delete();
        return redirect('/kelola-user')->with('success', 'Akun pengguna berhasil dihapus!');
    }
}