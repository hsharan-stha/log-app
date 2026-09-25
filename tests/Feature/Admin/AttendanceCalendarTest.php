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

        $staff = User::factory()->create(['role' => 'staff', 'name' => 'Present Staff']);
        Attendance::query()->create([
            'user_id' => $staff->id,
            'attendance_date' => now()->toDateString(),
            'checkin_time' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', 'staff'))
            ->assertOk()
            ->assertSee('Staff & teachers calendar')
            ->assertSee('Attend');

        $this->actingAs($admin)
            ->get(route('admin.attendance.day', ['group' => 'staff', 'date' => now()->toDateString()]))
            ->assertOk()
            ->assertSee('Present Teacher')
            ->assertSee('Present Staff')
            ->assertDontSee('Absent Student')
            ->assertSee('Attended')
            ->assertSee('Unattended');

        $this->actingAs($admin)
            ->get(route('admin.attendance.day', ['group' => 'students', 'date' => now()->toDateString()]))
            ->assertOk()
            ->assertSee('Absent Student')
            ->assertDontSee('Present Teacher')
            ->assertDontSee('Present Staff');
    }

    public function test_admin_can_search_day_attendance_by_person(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $presentTeacher = User::factory()->create([
            'role' => 'teacher',
            'name' => 'Present Teacher',
            'email' => 'present.teacher@example.com',
        ]);
        $absentStudent = User::factory()->create([
            'role' => 'student',
            'name' => 'Absent Student',
            'email' => 'absent.student@example.com',
        ]);
        $otherPresent = User::factory()->create([
            'role' => 'student',
            'name' => 'Other Present',
            'email' => 'other.present@example.com',
        ]);

        Attendance::query()->create([
            'user_id' => $presentTeacher->id,
            'attendance_date' => now()->toDateString(),
            'checkin_time' => now(),
        ]);
        Attendance::query()->create([
            'user_id' => $otherPresent->id,
            'attendance_date' => now()->toDateString(),
            'checkin_time' => now(),
        ]);

        $day = now()->toDateString();

        $this->actingAs($admin)
            ->get(route('admin.attendance.day', ['group' => 'staff', 'date' => $day, 'q' => 'Present Teacher']))
            ->assertOk()
            ->assertSee('Present Teacher')
            ->assertDontSee('Other Present')
            ->assertDontSee('Absent Student')
            ->assertSee('1 of 1');

        $this->actingAs($admin)
            ->get(route('admin.attendance.day', ['group' => 'students', 'date' => $day, 'q' => 'absent.student']))
            ->assertOk()
            ->assertSee('Absent Student')
            ->assertDontSee('Present Teacher')
            ->assertDontSee('Other Present')
            ->assertSee('1 of 1');
    }
}
