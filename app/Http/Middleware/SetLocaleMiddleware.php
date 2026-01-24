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
        $locale = null;

        // 1. Check Session (Explicit User Choice)
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } 
        
        // 2. Check User Preference (Authenticated Fallback)
        elseif (Auth::check() && Auth::user()->locale) {
             $locale = Auth::user()->locale;
             Session::put('locale', $locale);
        }

        // 3. Check Browser Header (Guest / First Visit)
        if (!$locale) {
            $locale = $request->getPreferredLanguage(['en', 'id']);
        }

        // 4. Default Fallback
        if (!$locale || !in_array($locale, ['en', 'id'])) {
            $locale = config('app.locale', 'id');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
