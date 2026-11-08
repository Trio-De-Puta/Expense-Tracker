<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'secret-password',
        ]);

        $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'secret-password',
        ])->assertRedirect(route('expenses.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_log_in_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'secret-password',
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'login@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_email_is_case_insensitive(): void
    {
        $user = User::factory()->create([
            'email' => 'Login@Example.com',
            'password' => 'secret-password',
        ]);

        $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'secret-password',
        ])->assertRedirect(route('expenses.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_to_the_intended_page(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'secret-password',
        ]);

        $this->get('/profile')->assertRedirect('/login');

        $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'secret-password',
        ])->assertRedirect('/profile');

        $this->assertAuthenticatedAs($user);
    }

    public function test_remember_me_sets_the_remember_cookie(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'secret-password',
        ]);

        $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'secret-password',
            'remember' => '1',
        ])
            ->assertRedirect(route('expenses.index'))
            ->assertCookie(Auth::guard()->getRecallerName());

        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_stores_a_lowercase_email(): void
    {
        $this->post('/register', [
            'name' => 'Juan Dela Cruz',
            'email' => 'Juan@Example.com',
            'profile_type' => 'working',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
        ]);
    }

    public function test_registration_rejects_an_email_that_only_differs_in_case(): void
    {
        User::factory()->create(['email' => 'juan@example.com']);

        $this->post('/register', [
            'name' => 'Another Juan',
            'email' => 'JUAN@example.com',
            'profile_type' => 'working',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(1, User::whereRaw('LOWER(email) = ?', ['juan@example.com'])->count());
    }

    public function test_profile_update_stores_a_lowercase_email(): void
    {
        $user = User::factory()->create([
            'email' => 'juan@example.com',
            'profile_type' => 'working',
        ]);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => 'NewAddress@Example.com',
                'profile_type' => 'working',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'newaddress@example.com',
        ]);
    }
}
