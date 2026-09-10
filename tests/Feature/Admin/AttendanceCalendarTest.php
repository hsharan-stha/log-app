<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_calendar_and_day_detail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Present Teacher']);
        $absentStudent = User::factory()->create(['role' => 'student', 'name' => 'Absent Student']);

        Attendance::query()->create([
            'user_id' => $teacher->id,
            'attendance_date' => now()->toDateString(),
            'checkin_time' => now(),
            'checkin_photo_path' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.index'))
            ->assertOk()
            ->assertSee('Attendance calendar')
            ->assertSee('Attend');

        $this->actingAs($admin)
            ->get(route('admin.attendance.day', now()->toDateString()))
            ->assertOk()
            ->assertSee('Present Teacher')
            ->assertSee('Absent Student')
            ->assertSee('Attended')
            ->assertSee('Unattended');
    }
}
