<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\KioskDevice;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $totalTeachers = User::query()->where('role', 'teacher')->count();
        $totalStudents = User::query()->where('role', 'student')->count();
        $totalGuardians = User::query()->where('role', 'guardian')->count();
        $activeDevices = KioskDevice::query()->whereNull('revoked_at')->count();

        $todayPresentTeachers = User::query()
            ->where('role', 'teacher')
            ->whereHas('attendances', function ($query) use ($today) {
                $query->whereDate('attendance_date', $today)->whereNotNull('checkin_time');
            })
            ->count();

        $todayPresentStudents = User::query()
            ->where('role', 'student')
            ->whereHas('attendances', function ($query) use ($today) {
                $query->whereDate('attendance_date', $today)->whereNotNull('checkin_time');
            })
            ->count();

        $checkinsToday = Attendance::query()
            ->whereDate('attendance_date', $today)
            ->whereNotNull('checkin_time')
            ->count();

        $checkoutsToday = Attendance::query()
            ->whereDate('attendance_date', $today)
            ->whereNotNull('checkout_time')
            ->count();

        $todayPresent = $todayPresentTeachers + $todayPresentStudents;

        return view('admin.dashboard', compact(
            'totalTeachers',
            'totalStudents',
            'totalGuardians',
            'activeDevices',
            'todayPresent',
            'todayPresentTeachers',
            'todayPresentStudents',
            'checkinsToday',
            'checkoutsToday',
            'today'
        ));
    }
}
