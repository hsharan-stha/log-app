<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Notice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $wards = $user->wards()->with('schoolClass')->orderBy('name')->get();

        $todayByStudent = Attendance::query()
            ->whereIn('user_id', $wards->pluck('id'))
            ->whereDate('attendance_date', today())
            ->get()
            ->keyBy('user_id');

        $notices = Notice::query()
            ->published()
            ->latest('published_at')
            ->limit(20)
            ->get()
            ->filter(fn (Notice $n) => $n->isVisibleTo($user));

        $alerts = $user->notifications()->latest()->limit(15)->get();

        return view('guardian.dashboard', compact('user', 'wards', 'todayByStudent', 'notices', 'alerts'));
    }

    public function attendance(Request $request): View
    {
        [$wards, $student] = $this->resolveWard($request);

        $monthInput = $request->query('month');
        $month = $monthInput
            ? $request->validate(['month' => ['date_format:Y-m']])['month']
            : now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $byDate = Attendance::query()
            ->where('user_id', $student->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $row) => $row->attendance_date->toDateString());

        $weeks = $this->buildCalendarWeeks($start, $end, $byDate);

        return view('guardian.attendance', [
            'wards' => $wards,
            'student' => $student,
            'studentId' => $student->id,
            'month' => $month,
            'monthLabel' => $start->translatedFormat('F Y'),
            'prevMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
            'weeks' => $weeks,
            'today' => now()->toDateString(),
        ]);
    }

    public function attendanceDay(Request $request, string $date): View
    {
        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1, 404);

        try {
            $day = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            abort(404);
        }

        abort_unless($day->format('Y-m-d') === $date, 404);

        [$wards, $student] = $this->resolveWard($request);

        $attendance = Attendance::query()
            ->where('user_id', $student->id)
            ->whereDate('attendance_date', $day->toDateString())
            ->first();

        return view('guardian.attendance-day', [
            'wards' => $wards,
            'student' => $student,
            'studentId' => $student->id,
            'date' => $day->toDateString(),
            'dateLabel' => $day->toFormattedDateString(),
            'month' => $day->format('Y-m'),
            'attendance' => $attendance,
            'isFuture' => $day->gt(now()->startOfDay()),
        ]);
    }

    /**
     * @return array{0: Collection<int, User>, 1: User}
     */
    private function resolveWard(Request $request): array
    {
        $wards = $request->user()->wards()->orderBy('name')->get();
        abort_if($wards->isEmpty(), 404);

        $studentId = (int) $request->query('student', $wards->first()->id);
        $student = $wards->firstWhere('id', $studentId);
        abort_unless($student instanceof User, 404);

        return [$wards, $student];
    }

    /**
     * @param  Collection<string, Attendance>  $byDate
     * @return list<list<array<string, mixed>>>
     */
    private function buildCalendarWeeks(Carbon $start, Carbon $end, Collection $byDate): array
    {
        $cursor = $start->copy()->startOfWeek(Carbon::MONDAY);
        $last = $end->copy()->endOfWeek(Carbon::SUNDAY);
        $today = now()->startOfDay();

        $weeks = [];
        $week = [];

        while ($cursor->lte($last)) {
            $inMonth = $cursor->month === $start->month;
            $dateKey = $cursor->toDateString();
            $record = $inMonth ? $byDate->get($dateKey) : null;
            $isFuture = $cursor->gt($today);
            $present = $record && $record->checkin_time !== null;

            $week[] = [
                'date' => $inMonth ? $dateKey : null,
                'day' => $cursor->day,
                'inMonth' => $inMonth,
                'present' => $present,
                'absent' => $inMonth && ! $isFuture && ! $present,
                'isToday' => $cursor->isSameDay($today),
                'isFuture' => $isFuture,
                'hasCheckout' => $present && $record?->checkout_time !== null,
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }

            $cursor->addDay();
        }

        return $weeks;
    }
}
