<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan user yang sedang login berstatus akun 'aktif'.
 * Jika user telah dinonaktifkan oleh Admin (status_akun != 'aktif'),
 * maka session langsung diakhiri (force logout) dan dialihkan ke login.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = $request->user();

            if ($user && $user->status_akun !== 'aktif') {
                Auth::guard('web')->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Hubungi admin untuk informasi lebih lanjut.',
                ]);
            }
        }

        return $next($request);
    }
}
