<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah yang login adalah admin
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request); // Silakan masuk
        }

        // Jika teknisi yang mencoba masuk, tendang kembali dengan pesan error (403 Forbidden)
        abort(403, 'AKSES DITOLAK! Halaman ini hanya untuk Admin Master.');
    }
}