<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_report_hides_attendance_login_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'staff', 'name' => 'Office Staff']);
        User::factory()->create(['role' => 'attendance', 'name' => 'Kiosk Operator']);

        $this->actingAs($admin)
            ->get(route('admin.reports.attendance.teachers'))
            ->assertOk()
            ->assertSee('Staff report')
            ->assertSee('Office Staff')
            ->assertDontSee('Kiosk Operator');
    }
}
