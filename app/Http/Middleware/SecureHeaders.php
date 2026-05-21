<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureHeaders
{
    public function handle(Request $request, Closure $next)
    {
        if ((bool) config('security.force_https', env('FORCE_HTTPS', false)) && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        $response = $next($request);

        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera=()");

        $csp = "default-src 'self' https: data: blob:;
                img-src 'self' https: data: blob:;
                script-src 'self' https: 'unsafe-inline' 'unsafe-eval';
                style-src 'self' https: 'unsafe-inline';
                font-src 'self' https: data:;
                connect-src 'self' https:;
                frame-ancestors 'self'";
        $response->headers->set('Content-Security-Policy', preg_replace('/\s+/', ' ', $csp));
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
