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

    public function test_attendance_kiosk_without_device_does_not_open(): void
    {
        $this->get('/attendance')->assertRedirect(route('attendance.unauthorized'));
    }

    public function test_registered_device_acts_as_attendance_login_until_admin_revokes_it(): void
    {
        $operator = User::factory()->create(['role' => 'attendance']);
        $admin = User::factory()->create(['role' => 'admin']);

        $registered = $this->actingAs($operator)->post(route('attendance.setup.store'), [
            'name' => 'Entrance Tablet',
            'location' => 'school',
        ]);
        $registered->assertRedirect(route('attendance.scan'));

        $tokenCookie = collect($registered->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === 'kiosk_device_token');
        $this->assertNotNull($tokenCookie);
        $this->assertGreaterThan(60 * 24 * 365, $tokenCookie->getExpiresTime() - time());
        $plainToken = $tokenCookie->getValue();

        $this->actingAs($operator)
            ->withUnencryptedCookie('kiosk_device_token', $plainToken)
            ->post('/logout')
            ->assertRedirect(route('attendance.scan'));

        auth()->logout();

        $this->withUnencryptedCookie('kiosk_device_token', $plainToken)
            ->get('/attendance')
            ->assertOk();

        $this->withUnencryptedCookie('kiosk_device_token', $plainToken)
            ->get('/')
            ->assertRedirect(route('attendance.scan'));

        $device = KioskDevice::query()->whereNull('revoked_at')->first();
        $this->actingAs($admin)->post(route('admin.devices.revoke', $device))->assertRedirect();

        auth()->logout();

        $this->withUnencryptedCookie('kiosk_device_token', $plainToken)
            ->get('/attendance')
            ->assertRedirect(route('attendance.unauthorized'));

        $this->withUnencryptedCookie('kiosk_device_token', $plainToken)
            ->get('/')
            ->assertRedirect(route('login'));

        $this->get('/attendance/setup')->assertRedirect(route('login'));
    }

    public function test_registered_device_opens_attendance_without_login_and_root_redirects_there(): void
    {
        ['plain_token' => $plain] = KioskDevice::register('Gate', 'school');

        $this->withUnencryptedCookie('kiosk_device_token', $plain)
            ->get('/attendance')
            ->assertOk();

        $this->withUnencryptedCookie('kiosk_device_token', $plain)
            ->get('/')
            ->assertRedirect(route('attendance.scan'));
    }

    public function test_attendance_kiosk_requires_registered_device(): void
    {
        $operator = User::factory()->create(['role' => 'attendance']);

        $this->actingAs($operator)
            ->get('/attendance')
            ->assertRedirect(route('attendance.unauthorized'));
    }

    public function test_admin_cannot_setup_kiosk_but_registered_device_can_open_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ['plain_token' => $plain] = KioskDevice::register('Gate', 'school');

        $this->actingAs($admin)->get('/attendance/setup')->assertForbidden();
        $this->withUnencryptedCookie('kiosk_device_token', $plain)
            ->get('/attendance')
            ->assertOk();
    }

    public function test_only_one_kiosk_can_be_registered_until_admin_revokes_it(): void
    {
        $operator = User::factory()->create(['role' => 'attendance']);
        $admin = User::factory()->create(['role' => 'admin']);
        $hr = User::factory()->create(['role' => 'hr']);

        $this->actingAs($operator)->post(route('attendance.setup.store'), [
            'name' => 'Entrance Tablet',
            'location' => 'school',
        ])->assertRedirect(route('attendance.scan'));

        $this->assertSame(1, KioskDevice::query()->whereNull('revoked_at')->count());

        $this->actingAs($operator)->post(route('attendance.setup.store'), [
            'name' => 'Second Tablet',
            'location' => 'bus',
        ])->assertSessionHasErrors('name');

        $this->assertSame(1, KioskDevice::query()->whereNull('revoked_at')->count());

        $device = KioskDevice::query()->whereNull('revoked_at')->first();

        $this->actingAs($hr)
            ->post(route('admin.devices.revoke', $device))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.devices.revoke', $device))
            ->assertRedirect(route('admin.devices.index'));

        $this->assertSame(0, KioskDevice::query()->whereNull('revoked_at')->count());

        $this->actingAs($operator)->post(route('attendance.setup.store'), [
            'name' => 'Replacement Tablet',
            'location' => 'school',
        ])->assertRedirect(route('attendance.scan'));

        $this->assertSame(1, KioskDevice::query()->whereNull('revoked_at')->count());
    }

    public function test_registered_device_can_open_kiosk_when_attendance_user_signed_in(): void
    {
        $operator = User::factory()->create(['role' => 'attendance']);
        ['plain_token' => $plain] = KioskDevice::register('Gate', 'school', $operator);

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
        ['plain_token' => $plain] = KioskDevice::register('Gate', 'school', $operator);

        $descriptor = array_fill(0, 128, 0.1);

        $this->actingAs($operator)
            ->withHeaders([
                'X-Kiosk-Device-Token' => $plain,
            ])
            ->postJson('/attendance/verify', ['descriptor' => $descriptor])
            ->assertOk()
            ->assertJson(['action' => 'school_checkin']);

        Notification::assertSentTo($guardian, StudentAttendanceAlert::class);
        Notification::assertNotSentTo($teacher, StudentAttendanceAlert::class);
    }
}
