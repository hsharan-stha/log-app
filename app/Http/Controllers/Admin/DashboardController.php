<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $totalStaff = User::query()->where('role', 'staff')->count();

        $todayPresent = User::query()
            ->where('role', 'staff')
            ->whereHas('attendances', function ($query) use ($today) {
                $query->whereDate('attendance_date', $today)
                    ->whereNotNull('checkin_time');
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

        $recentAttendances = Attendance::query()
            ->with('user:id,name')
            ->orderByDesc('attendance_date')
            ->orderByDesc('checkin_time')
            ->limit(15)
            ->get();

        return view('admin.dashboard', compact(
            'totalStaff',
            'todayPresent',
            'checkinsToday',
            'checkoutsToday',
            'recentAttendances',
            'today'
        ));
    }
}
