<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class UserLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_language()
    {
        $user = User::factory()->create(['locale' => 'id']);

        $this->actingAs($user)
            ->get(route('lang.switch', 'en'))
            ->assertRedirect();

        $this->assertEquals('en', Session::get('locale'));
        $this->assertEquals('en', $user->fresh()->locale);
    }

    public function test_middleware_defaults_to_id()
    {
        $this->get(route('login')) // Any public route
            ->assertStatus(200);
        
        $this->assertEquals('id', app()->getLocale());
    }

    public function test_middleware_uses_session_locale()
    {
        $this->withSession(['locale' => 'en'])
             ->get(route('login')) // Any route
             ->assertStatus(200);

        $this->assertEquals('en', app()->getLocale());
    }

    public function test_middleware_uses_authenticated_user_locale()
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
             ->get(route('dashboard'))
             ->assertStatus(200);

        $this->assertEquals('en', app()->getLocale());
    }
}
