<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale'); // Default to config (usually 'id')

        // 1. Check Session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } 
        // 2. Check User Preference (if authenticated and nothing in session yet?)
        // Actually, we should probably sync session with user pref on login. 
        // But here, if session is empty, we check user.
        elseif (Auth::check() && Auth::user()->locale) {
             $locale = Auth::user()->locale;
             Session::put('locale', $locale);
        }

        // Validate locale is supported
        if (!in_array($locale, ['en', 'id'])) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
