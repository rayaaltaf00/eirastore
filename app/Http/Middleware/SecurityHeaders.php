<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk menambahkan HTTP security headers pada setiap response.
 *
 * Header yang ditambahkan:
 * - X-Content-Type-Options: Mencegah MIME-type sniffing
 * - X-Frame-Options: Mencegah clickjacking (halaman tidak bisa di-embed iframe)
 * - X-XSS-Protection: Lapisan tambahan perlindungan XSS di browser lama
 * - Referrer-Policy: Batasi informasi referrer yang dikirim ke situs lain
 * - Permissions-Policy: Batasi akses fitur browser (kamera, mikrofon, dll)
 * - Strict-Transport-Security: Paksa HTTPS di produksi
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS hanya di produksi (HTTPS)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
