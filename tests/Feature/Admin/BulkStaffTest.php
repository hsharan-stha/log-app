<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_create_staff_with_default_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $list = "Person One, one@example.com\nPerson Two\n";

        $response = $this->actingAs($admin)->post(route('admin.staff.bulk-create.store'), [
            'list' => $list,
        ]);

        $response->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseHas('users', ['email' => 'one@example.com', 'role' => 'teacher', 'name' => 'Person One']);
        $this->assertDatabaseHas('users', ['email' => null, 'role' => 'teacher', 'name' => 'Person Two']);

        $one = User::query()->where('email', 'one@example.com')->first();
        $this->assertNotNull($one->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check(config('staff.default_password'), $one->password));
    }

    public function test_bulk_create_rejects_duplicate_email_in_list(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $list = "A, dup@example.com\nB, dup@example.com\n";

        $response = $this->actingAs($admin)->post(route('admin.staff.bulk-create.store'), [
            'list' => $list,
        ]);

        $response->assertSessionHasErrors('list');
        $this->assertEquals(0, User::query()->where('role', 'teacher')->count());
    }
}
