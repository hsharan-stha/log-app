<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianAttendanceCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_guardian_can_view_child_calendar_and_day_photos(): void
    {
        $guardian = User::factory()->create(['role' => 'guardian']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Demo Child']);
        $guardian->wards()->attach($student->id, ['relationship' => 'parent']);

        Attendance::query()->create([
            'user_id' => $student->id,
            'attendance_date' => now()->toDateString(),
            'checkin_time' => now()->setTime(8, 15),
            'checkout_time' => now()->setTime(15, 0),
        ]);

        $this->actingAs($guardian)
            ->get(route('guardian.attendance', ['student' => $student->id]))
            ->assertOk()
            ->assertSee('Attendance calendar')
            ->assertSee('Present');

        $this->actingAs($guardian)
            ->get(route('guardian.attendance.day', ['date' => now()->toDateString(), 'student' => $student->id]))
            ->assertOk()
            ->assertSee('Demo Child')
            ->assertSee('Check-in')
            ->assertSee('Check-out');
    }
}
