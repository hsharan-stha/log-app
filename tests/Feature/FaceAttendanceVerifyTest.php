<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FaceAttendanceVerifyTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, float> */
    private function matchingDescriptor(): array
    {
        return [0.0, 0.0, 0.0];
    }

    private function createStaffWithMatchingFace(): User
    {
        return User::query()->create([
            'name' => 'Test Staff',
            'email' => 'staff-face-verify@example.test',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'face_descriptor' => $this->matchingDescriptor(),
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Kolkata']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_check_in_allowed_again_on_the_next_calendar_day_in_app_timezone(): void
    {
        config(['app.timezone' => 'Asia/Kolkata']);

        $user = $this->createStaffWithMatchingFace();
        $descriptor = $this->matchingDescriptor();

        $this->travelTo(Carbon::parse('2026-05-13 18:00:00', 'Asia/Kolkata'));

        $this->postJson(route('attendance.verify'), ['descriptor' => $descriptor])
            ->assertOk()
            ->assertJsonPath('action', 'checkin');

        $this->travelTo(Carbon::parse('2026-05-13 19:00:00', 'Asia/Kolkata'));

        $this->postJson(route('attendance.verify'), ['descriptor' => $descriptor])
            ->assertOk()
            ->assertJsonPath('action', 'checkout');

        $this->travelTo(Carbon::parse('2026-05-13 20:00:00', 'Asia/Kolkata'));

        $this->postJson(route('attendance.verify'), ['descriptor' => $descriptor])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Attendance already completed for today.');

        $this->travelTo(Carbon::parse('2026-05-14 09:00:00', 'Asia/Kolkata'));

        $this->postJson(route('attendance.verify'), ['descriptor' => $descriptor])
            ->assertOk()
            ->assertJsonPath('action', 'checkin');

        $rows = Attendance::query()
            ->where('user_id', $user->id)
            ->orderBy('attendance_date')
            ->get();

        $this->assertCount(2, $rows);
        $this->assertSame('2026-05-13', $rows[0]->attendance_date->format('Y-m-d'));
        $this->assertSame('2026-05-14', $rows[1]->attendance_date->format('Y-m-d'));
    }
}
