<?php

namespace Tests\Feature;

use App\Enums\ProfileType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_user_can_view_their_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'profile_type' => 'working',
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Edit profile')
            ->assertSee('Juan Dela Cruz')
            ->assertSee('juan@example.com')
            ->assertSee('Salary, paydays, and everyday bills.');
    }

    public function test_user_can_update_their_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'profile_type' => 'working',
        ]);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'profile_type' => 'student_allowance',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Profile updated.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'profile_type' => 'student_allowance',
        ]);

        $this->assertSame(
            ProfileType::StudentAllowance,
            $user->fresh()->profile()
        );
    }

    public function test_user_can_keep_their_existing_email(): void
    {
        $user = User::factory()->create([
            'email' => 'same@example.com',
            'profile_type' => 'working',
        ]);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => 'same@example.com',
                'profile_type' => 'working',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasNoErrors();
    }

    public function test_user_cannot_use_another_users_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $user = User::factory()->create([
            'profile_type' => 'working',
        ]);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => 'taken@example.com',
                'profile_type' => 'working',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'email' => 'taken@example.com',
        ]);
    }

    public function test_profile_update_rejects_an_invalid_profile_type(): void
    {
        $user = User::factory()->create([
            'profile_type' => 'working',
        ]);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'profile_type' => 'invalid',
            ])
            ->assertSessionHasErrors('profile_type');
    }
}
