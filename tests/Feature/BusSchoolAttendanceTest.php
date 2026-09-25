<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\KioskDevice;
use App\Models\SchoolClass;
use App\Models\User;
use App\Notifications\StudentAttendanceAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BusSchoolAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_bus_student_can_check_in_at_school_without_bus(): void
    {
        [$operator, $schoolToken, $student] = $this->setupOperatorAndStudent(ridesBus: false);

        $this->verifyAs($operator, $schoolToken, $student)
            ->assertOk()
            ->assertJson(['action' => 'school_checkin']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'checkin_time' => now()->toDateTimeString(),
        ]);
    }

    public function test_bus_student_blocked_at_school_until_bus_checkin(): void
    {
        [$operator, $schoolToken, $student] = $this->setupOperatorAndStudent(ridesBus: true);

        $this->verifyAs($operator, $schoolToken, $student)
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'This student rides the bus. Check in at the bus kiosk first.']);
    }

    public function test_bus_student_full_day_sequence_notifies_on_each_step(): void
    {
        Notification::fake();

        [$operator, $schoolToken, $student, $guardian] = $this->setupOperatorAndStudent(ridesBus: true, withGuardian: true);
        ['plain_token' => $busToken] = KioskDevice::register('Bus Tablet', 'bus', $operator);

        $this->verifyAs($operator, $busToken, $student)
            ->assertOk()
            ->assertJson(['action' => 'bus_checkin']);

        $this->verifyAs($operator, $schoolToken, $student)
            ->assertOk()
            ->assertJson(['action' => 'school_checkin']);

        $this->verifyAs($operator, $schoolToken, $student)
            ->assertOk()
            ->assertJson(['action' => 'school_checkout']);

        $this->verifyAs($operator, $busToken, $student)
            ->assertOk()
            ->assertJson(['action' => 'bus_checkout']);

        $row = Attendance::query()->where('user_id', $student->id)->first();
        $this->assertNotNull($row->bus_checkin_time);
        $this->assertNotNull($row->checkin_time);
        $this->assertNotNull($row->checkout_time);
        $this->assertNotNull($row->bus_checkout_time);

        Notification::assertSentTo($guardian, StudentAttendanceAlert::class, function ($notification) {
            return $notification->action === 'bus_checkin';
        });
        Notification::assertSentTo($guardian, StudentAttendanceAlert::class, function ($notification) {
            return $notification->action === 'school_checkin';
        });
        Notification::assertSentTo($guardian, StudentAttendanceAlert::class, function ($notification) {
            return $notification->action === 'school_checkout';
        });
        Notification::assertSentTo($guardian, StudentAttendanceAlert::class, function ($notification) {
            return $notification->action === 'bus_checkout';
        });
    }

    public function test_staff_school_check_in_and_out_without_bus_or_guardian_alert(): void
    {
        Notification::fake();

        $guardian = User::factory()->create(['role' => 'guardian']);
        $operator = User::factory()->create(['role' => 'attendance']);
        ['plain_token' => $schoolToken] = KioskDevice::register('School Gate', 'school', $operator);
        ['plain_token' => $busToken] = KioskDevice::register('Bus Tablet', 'bus', $operator);

        foreach (['hr', 'finance', 'staff', 'other', 'attendance'] as $index => $role) {
            $person = User::factory()->create([
                'role' => $role,
                'rides_bus' => true,
                'face_descriptor' => array_fill(0, 128, 0.15 * ($index + 1)),
            ]);

            $this->verifyAs($operator, $busToken, $person)
                ->assertOk()
                ->assertJson(['action' => 'school_checkin', 'role' => $role]);

            $this->verifyAs($operator, $schoolToken, $person)
                ->assertOk()
                ->assertJson(['action' => 'school_checkout', 'role' => $role]);

            $row = Attendance::query()->where('user_id', $person->id)->first();
            $this->assertNotNull($row->checkin_time);
            $this->assertNotNull($row->checkout_time);
            $this->assertNull($row->bus_checkin_time);
            $this->assertNull($row->bus_checkout_time);
        }

        Notification::assertNothingSent();
        $this->assertFalse($guardian->notifications()->exists());
    }

    public function test_non_bus_student_rejected_at_bus_kiosk(): void
    {
        [$operator, , $student] = $this->setupOperatorAndStudent(ridesBus: false);
        ['plain_token' => $busToken] = KioskDevice::register('Bus Tablet', 'bus', $operator);

        $this->verifyAs($operator, $busToken, $student)
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'This student is not marked for the school bus. Use the school kiosk, or ask the office to enable bus for this student.']);
    }

    /**
     * @return array{0: User, 1: string, 2: User, 3?: User}
     */
    private function setupOperatorAndStudent(bool $ridesBus, bool $withGuardian = false): array
    {
        $class = SchoolClass::query()->create([
            'name' => 'UKG',
            'code' => 'ukg-'.uniqid(),
            'sort_order' => 1,
        ]);

        $descriptor = array_fill(0, 128, 0.2);
        $student = User::factory()->create([
            'role' => 'student',
            'class_id' => $class->id,
            'rides_bus' => $ridesBus,
            'face_descriptor' => $descriptor,
        ]);

        $operator = User::factory()->create(['role' => 'attendance']);
        ['plain_token' => $schoolToken] = KioskDevice::register('School Gate', 'school', $operator);

        if ($withGuardian) {
            $guardian = User::factory()->create(['role' => 'guardian']);
            $guardian->wards()->attach($student->id, ['relationship' => 'parent']);

            return [$operator, $schoolToken, $student, $guardian];
        }

        return [$operator, $schoolToken, $student];
    }

    private function verifyAs(User $operator, string $token, User $student)
    {
        return $this->actingAs($operator)
            ->withHeaders(['X-Kiosk-Device-Token' => $token])
            ->postJson('/attendance/verify', [
                'descriptor' => $student->face_descriptor,
            ]);
    }
}
