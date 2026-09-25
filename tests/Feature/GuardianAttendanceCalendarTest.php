<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use App\Notifications\StudentAttendanceAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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
            ->assertSee('School in')
            ->assertSee('School out');
    }

    public function test_guardian_dashboard_paginates_alerts_server_side(): void
    {
        $guardian = User::factory()->create(['role' => 'guardian']);
        $student = User::factory()->create(['role' => 'student']);
        $guardian->wards()->attach($student->id, ['relationship' => 'parent']);

        foreach (range(0, 15) as $i) {
            $guardian->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => StudentAttendanceAlert::class,
                'data' => ['message' => "Attendance alert {$i}"],
                'created_at' => now()->subMinutes($i),
                'updated_at' => now()->subMinutes($i),
            ]);
        }

        $this->actingAs($guardian)
            ->get(route('guardian.dashboard'))
            ->assertOk()
            ->assertSee('Attendance alert 0')
            ->assertSee('Attendance alert 14')
            ->assertDontSee('Attendance alert 15')
            ->assertSee('alerts=2');

        $this->actingAs($guardian)
            ->get(route('guardian.dashboard', ['alerts' => 2]))
            ->assertOk()
            ->assertSee('Attendance alert 15')
            ->assertDontSee('Attendance alert 0');
    }
}
