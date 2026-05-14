<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->filled('date') ? $request->query('date') : null;

        $query = Attendance::query()
            ->with('user:id,name')
            ->orderByDesc('attendance_date')
            ->orderByDesc('checkin_time');

        if ($date) {
            $request->merge(['date' => $date]);
            $request->validate([
                'date' => ['date'],
            ]);
            $query->whereDate('attendance_date', $date);
        }

        $attendances = $query->paginate(20)->withQueryString();

        return view('admin.attendance.index', compact('attendances', 'date'));
    }
}
