<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_change_page_is_available_to_logged_in_user(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($user)
            ->get(route('password.edit'))
            ->assertOk()
            ->assertSee('Change password')
            ->assertSee('Current password');
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('password.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('password.edit'))
            ->put(route('password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect(route('password.edit'));
    }

    public function test_user_can_only_change_own_password(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'student-new-pass',
                'password_confirmation' => 'student-new-pass',
            ])
            ->assertRedirect(route('password.edit'));

        $this->assertTrue(Hash::check('student-new-pass', $user->refresh()->password));
    }
}
