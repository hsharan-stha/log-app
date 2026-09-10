<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Message;
use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $classes = SchoolClass::query()
            ->where('homeroom_teacher_id', $user->id)
            ->withCount('students')
            ->orderBy('sort_order')
            ->get();

        $classIds = $classes->pluck('id');
        $studentIds = User::query()
            ->where('role', 'student')
            ->whereIn('class_id', $classIds)
            ->pluck('id');

        $todayPresent = Attendance::query()
            ->whereIn('user_id', $studentIds)
            ->whereDate('attendance_date', today())
            ->whereNotNull('checkin_time')
            ->count();

        $notices = Notice::query()
            ->published()
            ->latest('published_at')
            ->limit(8)
            ->get()
            ->filter(fn (Notice $n) => $n->isVisibleTo($user));

        return view('teacher.dashboard', [
            'classes' => $classes,
            'todayPresent' => $todayPresent,
            'studentCount' => $studentIds->count(),
            'notices' => $notices,
            'unreadMessages' => Message::query()
                ->where('recipient_id', $user->id)
                ->whereNull('read_at')
                ->count(),
        ]);
    }
}
