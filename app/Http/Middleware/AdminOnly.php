<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah user sudah login dan rolenya adalah ADMIN
        if (strtoupper(session('user_role')) !== 'ADMIN') {
            return redirect('/')->with('error', 'Akses ditolak! Fitur ini khusus untuk ADMIN.');
        }

        return $next($request);
    }
}