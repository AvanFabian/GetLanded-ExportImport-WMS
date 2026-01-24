<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class GuestLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_starts_with_default_locale()
    {
        // 1. Visit root without session
        $response = $this->get('/');
        
        $response->assertStatus(200);
        // Assuming default is 'id' (from config)
        $this->assertEquals('id', App::getLocale());
    }

    public function test_guest_can_switch_language()
    {
        // 1. Visit language switch route
        $response = $this->get(route('lang.switch', 'en'));
        
        $response->assertRedirect();
        
        // 2. Check session has locale
        $response->assertSessionHas('locale', 'en');
        
        // 3. Visit root again
        $this->get('/');
        $this->assertEquals('en', App::getLocale());
    }

    public function test_guest_browser_language_detection()
    {
        // Clear session if any
        Session::forget('locale');

        // 1. Visit root with Accept-Language header 'en-US'
        $response = $this->withHeaders([
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->get('/');

        $response->assertStatus(200);
        
        // Should detect 'en'
        $this->assertEquals('en', App::getLocale());
    }

    public function test_locale_persists_after_registration()
    {
        // 1. Set locale as guest
        $this->get(route('lang.switch', 'en'));
        $this->assertSessionHas('locale', 'en');

        // 2. Register
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        // 3. User should be created with 'en' locale
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'locale' => 'en',
        ]);
    }
}
