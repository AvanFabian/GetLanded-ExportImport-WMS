<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Tambahkan ini
use Illuminate\Support\Facades\Http; // Add this line
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
// Paksa semua URL menggunakan skema HTTPS jika di production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
