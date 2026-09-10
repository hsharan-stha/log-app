<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AttendanceTestingSeeder extends Seeder
{
    /**
     * Creates demo staff (if missing) and attendance for the last three full calendar months
     * (excluding the current month), Monday–Friday, with occasional absences or incomplete days.
     */
    public function run(): void
    {
        $password = Hash::make(config('staff.default_password'));

        $demoStaff = [
            ['name' => 'Alex Rivera', 'email' => 'alex.rivera@example.test'],
            ['name' => 'Jordan Kim', 'email' => 'jordan.kim@example.test'],
            ['name' => 'Sam Patel', 'email' => 'sam.patel@example.test'],
            ['name' => 'Taylor Chen', 'email' => 'taylor.chen@example.test'],
            ['name' => 'Riley Morgan', 'email' => 'riley.morgan@example.test'],
        ];

        $staffIds = [];
        foreach ($demoStaff as $row) {
            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => $password,
                    'role' => 'teacher',
                ]
            );
            $staffIds[] = $user->id;
        }

        $endMonth = Carbon::now()->subMonthNoOverflow()->endOfMonth();
        $startMonth = Carbon::now()->subMonthsNoOverflow(3)->startOfMonth();

        foreach ($staffIds as $userId) {
            for ($day = $startMonth->copy(); $day->lte($endMonth); $day->addDay()) {
                if ($day->isWeekend()) {
                    continue;
                }

                $roll = random_int(1, 100);
                if ($roll <= 6) {
                    continue;
                }

                $dateStr = $day->toDateString();
                $checkin = $day->copy()->setTimeFromTimeString(
                    sprintf('%02d:%02d:00', random_int(8, 9), random_int(0, 55))
                );
                $checkout = null;
                if ($roll > 10) {
                    $checkout = $day->copy()->setTimeFromTimeString(
                        sprintf('%02d:%02d:00', random_int(16, 18), random_int(0, 59))
                    );
                }

                Attendance::query()->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'attendance_date' => $dateStr,
                    ],
                    [
                        'checkin_time' => $checkin,
                        'checkout_time' => $checkout,
                        'checkin_photo_path' => null,
                        'checkout_photo_path' => null,
                    ]
                );
            }
        }
    }
}
