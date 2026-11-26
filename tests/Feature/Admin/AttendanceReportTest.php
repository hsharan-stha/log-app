<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
            ->assertDontSee('Kiosk Operator')
            ->assertDontSee('Reports hub')
            ->assertDontSee('Student attendance')
            ->assertSee('value="'.now()->format('Y-m').'"', false)
            ->assertSee('Export Excel');
    }

    public function test_staff_export_is_one_workbook_for_the_selected_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Export Teacher']);
        User::factory()->create(['role' => 'staff', 'name' => 'Office Staff']);
        $operator = User::factory()->create(['role' => 'attendance', 'name' => 'Kiosk Operator']);

        Attendance::query()->create([
            'user_id' => $teacher->id,
            'attendance_date' => '2026-11-18',
            'checkin_time' => '2026-11-18 08:15:00',
            'checkout_time' => '2026-11-18 17:00:00',
        ]);
        Attendance::query()->create([
            'user_id' => $operator->id,
            'attendance_date' => '2026-11-18',
            'checkin_time' => '2026-11-18 08:00:00',
            'checkout_time' => '2026-11-18 17:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.attendance.teachers.export', ['month' => '2026-11']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());

        $book = IOFactory::load($path);
        @unlink($path);

        $this->assertSame(['Summary', 'Export Teacher', 'Office Staff'], $book->getSheetNames());

        $summary = $book->getSheetByName('Summary');
        $this->assertSame('November 2026', $summary->getCell('B1')->getValue());
        $this->assertSame('Export Teacher', $summary->getCell('A4')->getValue());
        $this->assertSame('Teacher', $summary->getCell('B4')->getValue());
        $this->assertSame('8h 45m', $summary->getCell('H4')->getValue());

        $sheet = $book->getSheetByName('Export Teacher');
        $this->assertSame('2026-11-18', $sheet->getCell('A21')->getValue());
        $this->assertSame('08:15', $sheet->getCell('C21')->getValue());
        $this->assertSame('17:00', $sheet->getCell('D21')->getValue());
        $this->assertSame('8h 45m', $sheet->getCell('E21')->getValue());
    }

    public function test_student_report_is_in_the_sidebar_for_the_current_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'student', 'name' => 'Demo Student']);
        User::factory()->create(['role' => 'teacher', 'name' => 'Demo Teacher']);

        $this->actingAs($admin)
            ->get(route('admin.reports.attendance.students'))
            ->assertOk()
            ->assertSee('Students')
            ->assertSee('Student report')
            ->assertSee('Student calendar')
            ->assertSee('Student attendance report')
            ->assertSee('Demo Student')
            ->assertDontSee('Demo Teacher')
            ->assertDontSee('Reports hub')
            ->assertSee('value="'.now()->format('Y-m').'"', false);
    }

    public function test_student_month_shows_bus_and_school_steps(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Bus Student',
            'rides_bus' => true,
        ]);

        Attendance::query()->create([
            'user_id' => $student->id,
            'attendance_date' => now()->toDateString(),
            'bus_checkin_time' => now()->setTime(7, 30),
            'checkin_time' => now()->setTime(8, 15),
            'checkout_time' => now()->setTime(15, 0),
            'bus_checkout_time' => now()->setTime(16, 10),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.attendance.staff', ['staff' => $student, 'month' => now()->format('Y-m')]))
            ->assertOk()
            ->assertSee('Bus in')
            ->assertSee('School in')
            ->assertSee('School out')
            ->assertSee('Bus out')
            ->assertSee('07:30')
            ->assertSee('08:15')
            ->assertSee('15:00')
            ->assertSee('16:10');
    }

    public function test_admin_can_save_a_manual_checkout_that_counts_like_a_kiosk_checkout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Manual Teacher']);

        $attendance = Attendance::query()->create([
            'user_id' => $teacher->id,
            'attendance_date' => '2026-11-18',
            'checkin_time' => '2026-11-18 08:15:00',
        ]);

        $page = route('admin.reports.attendance.staff', ['staff' => $teacher, 'month' => '2026-11']);

        $this->actingAs($admin)
            ->get($page)
            ->assertOk()
            ->assertDontSee('name="checkout_time"', false)
            ->assertDontSee('Manual checkout time for', false);

        $this->actingAs($admin)
            ->from($page)
            ->post(route('admin.reports.attendance.staff.checkout', $teacher), [
                'date' => '2026-11-18',
                'checkout_time' => '08:30',
            ])
            ->assertRedirect($page)
            ->assertSessionHasErrors('checkout_time');

        $this->assertNull($attendance->fresh()->checkout_time);

        $this->actingAs($admin)
            ->from($page)
            ->post(route('admin.reports.attendance.staff.checkout', $teacher), [
                'date' => '2026-11-18',
                'checkout_time' => '17:00',
            ])
            ->assertRedirect($page)
            ->assertSessionHas('success');

        $attendance->refresh();
        $this->assertTrue($attendance->manual_checkout);
        $this->assertSame('17:00', $attendance->checkout_time->format('H:i'));
        $this->assertNull($attendance->checkout_photo_path);

        $this->actingAs($admin)
            ->get($page)
            ->assertOk()
            ->assertSee('17:00')
            ->assertSee('Manual')
            ->assertSee('8h 45m')
            ->assertDontSee('name="checkout_time"', false);

        $this->actingAs($admin)
            ->from($page)
            ->post(route('admin.reports.attendance.staff.checkout', $teacher), [
                'date' => '2026-11-18',
                'checkout_time' => '18:00',
            ])
            ->assertSessionHasErrors('checkout_time');

        $this->assertSame('17:00', $attendance->fresh()->checkout_time->format('H:i'));
    }

    public function test_manual_checkout_is_not_available_for_a_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        Attendance::query()->create([
            'user_id' => $student->id,
            'attendance_date' => '2026-11-18',
            'checkin_time' => '2026-11-18 08:15:00',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reports.attendance.staff.checkout', $student), [
                'date' => '2026-11-18',
                'checkout_time' => '17:00',
            ])
            ->assertNotFound();
    }
}
