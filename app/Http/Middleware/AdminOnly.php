<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        // Ambil role dari session secara fleksibel
        $userRole = strtoupper(session('user_role') ?? session('role') ?? session('hak_akses') ?? session('level') ?? '');

        // Jika role adalah ADMIN, izinkan lewat
        if ($userRole === 'ADMIN') {
            return $next($request);
        }

        // Jika bukan ADMIN (Teknisi/Guest), tendang ke halaman utama dengan pesan error
        return redirect('/')->with('error', 'Akses ditolak! Fitur ini khusus untuk Admin.');
    }
}