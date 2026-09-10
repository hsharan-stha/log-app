<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('schoolClass');

        $today = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', today())
            ->first();

        $notices = Notice::query()
            ->published()
            ->latest('published_at')
            ->limit(20)
            ->get()
            ->filter(fn (Notice $n) => $n->isVisibleTo($user));

        $history = Attendance::query()
            ->where('user_id', $user->id)
            ->orderByDesc('attendance_date')
            ->limit(15)
            ->get();

        return view('student.dashboard', compact('user', 'today', 'notices', 'history'));
    }
}
