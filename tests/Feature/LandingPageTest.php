<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Know what’s left to spend')
            ->assertSee('Log in')
            ->assertSee('Create free account')
            ->assertSee('Features')
            ->assertSee('How it works');
    }

    public function test_authenticated_user_is_redirected_from_the_landing_page_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('expenses.index'));
    }

    public function test_authenticated_user_is_redirected_from_guest_pages_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect(route('expenses.index'));

        $this->actingAs($user)
            ->get('/register')
            ->assertRedirect(route('expenses.index'));
    }

    public function test_guest_is_redirected_from_the_dashboard_to_login(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }
}
