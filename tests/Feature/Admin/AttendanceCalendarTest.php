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

        $operator = User::factory()->create(['role' => 'attendance', 'name' => 'Kiosk Operator']);
        Attendance::query()->create([
            'user_id' => $operator->id,
            'attendance_date' => now()->toDateString(),
            'checkin_time' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', 'staff'))
            ->assertOk()
            ->assertSee('Staff calendar')
            ->assertSee('Attend');

        $this->actingAs($admin)
            ->get(route('admin.attendance.day', ['group' => 'staff', 'date' => now()->toDateString()]))
            ->assertOk()
            ->assertSee('Present Teacher')
            ->assertSee('Present Staff')
            ->assertDontSee('Kiosk Operator')
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

    public function test_admin_can_update_checkout_from_the_staff_day_page(): void
    {
        $this->travelTo('2026-11-26 18:30:00');

        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Day Teacher']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Day Student']);
        $today = now()->toDateString();

        $attendance = Attendance::query()->create([
            'user_id' => $teacher->id,
            'attendance_date' => $today,
            'checkin_time' => $today.' 08:15:00',
            'checkout_time' => $today.' 15:00:00',
        ]);
        Attendance::query()->create([
            'user_id' => $student->id,
            'attendance_date' => $today,
            'checkin_time' => $today.' 08:15:00',
        ]);

        $day = route('admin.attendance.day', ['group' => 'staff', 'date' => $today]);

        $this->actingAs($admin)
            ->get($day)
            ->assertOk()
            ->assertSee('Checkout time for Day Teacher', false)
            ->assertSee('value="15:00"', false)
            ->assertSee('Update', false);

        $this->actingAs($admin)
            ->from($day)
            ->post(route('admin.reports.attendance.staff.checkout', $teacher), [
                'date' => $today,
                'checkout_time' => '17:00',
                'replace' => '1',
                'person' => $teacher->id,
            ])
            ->assertRedirect($day)
            ->assertSessionHas('success');

        $attendance->refresh();
        $this->assertTrue($attendance->manual_checkout);
        $this->assertSame('17:00', $attendance->checkout_time->format('H:i'));

        $past = Attendance::query()->create([
            'user_id' => $teacher->id,
            'attendance_date' => '2026-11-18',
            'checkin_time' => '2026-11-18 08:15:00',
            'checkout_time' => '2026-11-18 15:00:00',
        ]);

        $pastDay = route('admin.attendance.day', ['group' => 'staff', 'date' => '2026-11-18']);

        $this->actingAs($admin)
            ->get($pastDay)
            ->assertOk()
            ->assertSee('Manual checkout')
            ->assertSee('Checkout time for Day Teacher', false);

        $this->actingAs($admin)
            ->from($pastDay)
            ->post(route('admin.reports.attendance.staff.checkout', $teacher), [
                'date' => '2026-11-18',
                'checkout_time' => '18:00',
                'replace' => '1',
            ])
            ->assertRedirect($pastDay)
            ->assertSessionHas('success');

        $past->refresh();
        $this->assertSame('18:00', $past->checkout_time->format('H:i'));
        $this->assertTrue($past->manual_checkout);

        $this->actingAs($admin)
            ->get(route('admin.attendance.day', ['group' => 'students', 'date' => $today]))
            ->assertOk()
            ->assertDontSee('Checkout time for', false)
            ->assertDontSee('Check-in time for', false);
    }

    public function test_admin_can_save_a_manual_checkin_for_unattended_staff(): void
    {
        $this->travelTo('2026-11-26 18:30:00');

        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Missing Teacher']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Missing Student']);
        $today = now()->toDateString();
        $day = route('admin.attendance.day', ['group' => 'staff', 'date' => $today]);

        $this->actingAs($admin)
            ->get($day)
            ->assertOk()
            ->assertSee('Check-in time for Missing Teacher', false)
            ->assertDontSee('Check-in time for Missing Student', false);

        $this->actingAs($admin)
            ->from($day)
            ->post(route('admin.reports.attendance.staff.checkin', $teacher), [
                'date' => $today,
                'checkin_time' => '19:00',
                'person' => $teacher->id,
            ])
            ->assertRedirect($day)
            ->assertSessionHasErrors('checkin_time');

        $this->assertNull(Attendance::query()->where('user_id', $teacher->id)->first());

        $this->actingAs($admin)
            ->from($day)
            ->post(route('admin.reports.attendance.staff.checkin', $teacher), [
                'date' => $today,
                'checkin_time' => '08:15',
                'person' => $teacher->id,
            ])
            ->assertRedirect($day)
            ->assertSessionHas('success');

        $attendance = Attendance::query()->where('user_id', $teacher->id)->whereDate('attendance_date', $today)->first();
        $this->assertNotNull($attendance);
        $this->assertTrue($attendance->manual_checkin);
        $this->assertSame('08:15', $attendance->checkin_time->format('H:i'));
        $this->assertNull($attendance->checkin_photo_path);

        $this->actingAs($admin)
            ->get($day)
            ->assertOk()
            ->assertSee('08:15')
            ->assertSee('Manual')
            ->assertDontSee('Check-in time for Missing Teacher', false);

        $this->actingAs($admin)
            ->post(route('admin.reports.attendance.staff.checkin', $student), [
                'date' => $today,
                'checkin_time' => '08:15',
            ])
            ->assertNotFound();
    }
}
