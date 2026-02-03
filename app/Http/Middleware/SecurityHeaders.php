<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection in browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Enforce HTTPS (strict transport security)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy (CSP)
        // Allow turning CSP on/off via env, defaulting to enabled
        $cspEnabled = filter_var(env('CSP_ENABLED', true), FILTER_VALIDATE_BOOL);

        if ($cspEnabled) {
            // Extend allowances for Vite dev server (IPv4, IPv6) and HMR websocket only in local env.
            $isLocal = config('app.env') === 'local';

            $scriptSrc      = ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https://cdn.jsdelivr.net', 'https://unpkg.com'];
            $scriptSrcElem  = $scriptSrc; // browsers may differentiate script-src-elem
            $styleSrc       = ["'self'", "'unsafe-inline'", 'https://fonts.bunny.net', 'https://cdn.jsdelivr.net', 'https://unpkg.com'];
            $styleSrcElem   = $styleSrc;  // style-src-elem fallback
            $fontSrc        = ["'self'", 'https://fonts.bunny.net', 'data:'];
            $imgSrc         = ["'self'", 'data:', 'https:', 'http:'];
            $connectSrc     = ["'self'"]; // will add ws:// for HMR

            if ($isLocal) {
                $viteHttpOrigins = ['http://127.0.0.1:5173', 'http://localhost:5173', 'http://[::1]:5173'];
                $viteWsOrigins   = ['ws://127.0.0.1:5173', 'ws://localhost:5173', 'ws://[::1]:5173'];

                // Add blob: for Vite's dynamic style/script updates (CSS HMR can use blob URLs)
                $scriptSrc[] = 'blob:';
                $scriptSrcElem[] = 'blob:';
                $styleSrc[] = 'blob:';
                $styleSrcElem[] = 'blob:';

                $scriptSrc = array_merge($scriptSrc, $viteHttpOrigins);
                $scriptSrcElem = array_merge($scriptSrcElem, $viteHttpOrigins);
                $styleSrc = array_merge($styleSrc, $viteHttpOrigins);
                $styleSrcElem = array_merge($styleSrcElem, $viteHttpOrigins);
                $connectSrc = array_merge($connectSrc, $viteHttpOrigins, $viteWsOrigins);
            }

            // Optional secure websockets if using HTTPS dev server
            if ($isLocal) {
                $connectSrc = array_merge($connectSrc, ['wss://127.0.0.1:5173', 'wss://localhost:5173', 'wss://[::1]:5173']);
            }

            $cspDirectives = [
                "default-src 'self'",
                'script-src ' . implode(' ', $scriptSrc),
                'script-src-elem ' . implode(' ', $scriptSrcElem),
                'style-src ' . implode(' ', $styleSrc),
                'style-src-elem ' . implode(' ', $styleSrcElem),
                'font-src ' . implode(' ', $fontSrc),
                'img-src ' . implode(' ', $imgSrc),
                'connect-src ' . implode(' ', $connectSrc),
                "frame-ancestors 'self'",
            ];
            $csp = implode('; ', $cspDirectives);
            $response->headers->set('Content-Security-Policy', $csp);
        } elseif (config('app.env') === 'local') {
            // Still set minimal dev-safe headers for local without CSP.
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        }

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (formerly Feature Policy)
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}