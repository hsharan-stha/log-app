<?php

namespace Tests\Feature;

use App\Models\KioskDevice;
use App\Models\SchoolClass;
use App\Models\User;
use App\Notifications\StudentAttendanceAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SchoolPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_kiosk_requires_login(): void
    {
        $this->get('/attendance')->assertRedirect(route('login'));
    }

    public function test_attendance_kiosk_requires_registered_device(): void
    {
        $operator = User::factory()->create(['role' => 'attendance']);

        $this->actingAs($operator)
            ->get('/attendance')
            ->assertRedirect(route('attendance.unauthorized'));
    }

    public function test_admin_cannot_setup_or_open_kiosk(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ['plain_token' => $plain] = KioskDevice::register('Gate');

        $this->actingAs($admin)->get('/attendance/setup')->assertForbidden();
        $this->actingAs($admin)
            ->withUnencryptedCookie('kiosk_device_token', $plain)
            ->get('/attendance')
            ->assertForbidden();
    }

    public function test_registered_device_can_open_kiosk_when_attendance_user_signed_in(): void
    {
        $operator = User::factory()->create(['role' => 'attendance']);
        ['plain_token' => $plain] = KioskDevice::register('Gate', $operator);

        $this->actingAs($operator)
            ->withUnencryptedCookie('kiosk_device_token', $plain)
            ->get('/attendance')
            ->assertOk();
    }

    public function test_roles_land_on_correct_portals(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $guardian = User::factory()->create(['role' => 'guardian']);
        $attendance = User::factory()->create(['role' => 'attendance']);

        $this->actingAs($teacher)->get('/dashboard')->assertRedirect(route('teacher.dashboard'));
        $this->actingAs($student)->get('/dashboard')->assertRedirect(route('student.dashboard'));
        $this->actingAs($guardian)->get('/dashboard')->assertRedirect(route('guardian.dashboard'));
        $this->actingAs($attendance)->get('/dashboard')->assertRedirect(route('attendance.home'));
    }

    public function test_student_checkin_notifies_guardians_only(): void
    {
        Notification::fake();

        $class = SchoolClass::query()->create([
            'name' => 'UKG',
            'code' => 'ukg',
            'sort_order' => 1,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'class_id' => $class->id,
            'face_descriptor' => array_fill(0, 128, 0.1),
        ]);
        $guardian = User::factory()->create(['role' => 'guardian']);
        $guardian->wards()->attach($student->id, ['relationship' => 'parent']);

        $teacher = User::factory()->create([
            'role' => 'teacher',
            'face_descriptor' => array_fill(0, 128, 0.9),
        ]);

        $operator = User::factory()->create(['role' => 'attendance']);
        ['plain_token' => $plain] = KioskDevice::register('Gate', $operator);

        $descriptor = array_fill(0, 128, 0.1);

        $this->actingAs($operator)
            ->withHeaders([
                'X-Kiosk-Device-Token' => $plain,
            ])
            ->postJson('/attendance/verify', ['descriptor' => $descriptor])
            ->assertOk()
            ->assertJson(['action' => 'checkin']);

        Notification::assertSentTo($guardian, StudentAttendanceAlert::class);
        Notification::assertNotSentTo($teacher, StudentAttendanceAlert::class);
    }
}
