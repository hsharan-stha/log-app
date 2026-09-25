<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_staff_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'role' => 'staff',
            'name' => 'Mina Office',
            'email' => 'mina@school.test',
        ]);
        User::factory()->create([
            'role' => 'hr',
            'name' => 'Ken HR',
            'email' => 'ken@school.test',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.office-users.index', ['name' => 'Mina']))
            ->assertOk()
            ->assertSee('Mina Office')
            ->assertDontSee('Ken HR');
    }

    public function test_admin_can_delete_a_staff_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create([
            'role' => 'staff',
            'name' => 'Office Staff',
            'email' => 'office-staff@school.test',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.office-users.destroy', $staff))
            ->assertRedirect(route('admin.office-users.index'));

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_teachers_cannot_be_deleted_from_staff_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($admin)
            ->delete(route('admin.office-users.destroy', $teacher))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $teacher->id]);
    }

    public function test_signed_in_hr_cannot_delete_their_own_account(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);

        $this->actingAs($hr)
            ->delete(route('admin.office-users.destroy', $hr))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $hr->id]);
    }
}
