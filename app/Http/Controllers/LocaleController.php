<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Switch application language
     */
    public function switch(Request $request, $locale)
    {
        // Validate locale
        if (!in_array($locale, ['en', 'id'])) {
            abort(400, 'Invalid locale');
        }

        // Store locale in session
        Session::put('locale', $locale);

        // Store locale in cookie (1 year)
        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::make('locale', $locale, 60 * 24 * 365));

        // If user is logged in, save preference to database
        if (Auth::check()) {
            // We use forceFill to bypass fillable protection if 'locale' isn't in fillable yet.
            // Or we should add it to fillable. Let's use forceFill/save for safety or update if fillable.
            // Best practice: add to fillable in User model too.
            $user = Auth::user();
            $user->locale = $locale;
            $user->save();
        }

        // Set application locale (for immediately subsequent logic in this request, though redirect handles next)
        App::setLocale($locale);

        // Redirect back to previous page
        return redirect()->back();
    }
}
