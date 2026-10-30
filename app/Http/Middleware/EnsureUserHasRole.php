<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan user memiliki role yang diizinkan.
 * Pemakaian di route: middleware('role:admin') atau middleware('role:admin,worker')
 */
class EnsureUserHasRole
{
    /**
     * Cek apakah role user termasuk dalam daftar role yang diizinkan.
     * Jika tidak, kembalikan respons 403 Forbidden.
     *
     * @param  string  ...$roles  Daftar role yang diizinkan (dipisah koma di route)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Pastikan user sudah login
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Cek apakah role user ada di daftar yang diizinkan
        $roleValue = $request->user()->role instanceof \BackedEnum ? $request->user()->role->value : (string) $request->user()->role;
        if (! in_array($roleValue, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
