<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_staff_with_face_descriptor(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $descriptor = [0.1, 0.2, 0.3];

        $response = $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Photo Ready Staff',
            'email' => 'photo-ready@example.com',
            'descriptor' => $descriptor,
        ]);

        $response->assertRedirect(route('admin.staff.index'));

        $staff = User::query()->where('email', 'photo-ready@example.com')->first();

        $this->assertNotNull($staff);
        $this->assertSame('staff', $staff->role);
        $this->assertSame($descriptor, $staff->face_descriptor);
    }

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
        $this->assertDatabaseHas('users', ['email' => 'one@example.com', 'role' => 'staff', 'name' => 'Person One']);
        $this->assertDatabaseHas('users', ['email' => null, 'role' => 'staff', 'name' => 'Person Two']);

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
        $this->assertEquals(0, User::query()->where('role', 'staff')->count());
    }
}
