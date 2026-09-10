<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function monthly(Request $request): View
    {
        $defaultMonth = Carbon::now()->subMonth()->format('Y-m');
        $monthInput = $request->query('month');
        $month = $monthInput
            ? $request->validate(['month' => ['date_format:Y-m']])['month']
            : $defaultMonth;

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $workingWeekdays = $this->workingWeekdayCount($start, $end);

        $aggregates = Attendance::query()
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('user_id,
                COUNT(*) as days_with_record,
                SUM(CASE WHEN checkin_time IS NOT NULL AND checkout_time IS NOT NULL THEN 1 ELSE 0 END) as complete_days,
                SUM(CASE WHEN checkin_time IS NOT NULL AND checkout_time IS NULL THEN 1 ELSE 0 END) as incomplete_days')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $people = User::query()
            ->whereIn('role', ['teacher', 'student'])
            ->with('schoolClass')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'class_id', 'roll_number']);

        $mapRow = function (User $user) use ($aggregates, $workingWeekdays): array {
            $a = $aggregates->get($user->id);

            $withRecord = (int) ($a->days_with_record ?? 0);
            $complete = (int) ($a->complete_days ?? 0);
            $incomplete = (int) ($a->incomplete_days ?? 0);

            return [
                'user' => $user,
                'working_weekdays' => $workingWeekdays,
                'days_with_record' => $withRecord,
                'complete_days' => $complete,
                'incomplete_days' => $incomplete,
                'no_record_weekdays' => max(0, $workingWeekdays - $withRecord),
            ];
        };

        $teacherRows = $people->where('role', 'teacher')->values()->map($mapRow);
        $studentRows = $people->where('role', 'student')->values()->map($mapRow);

        $role = $request->query('role', 'all');
        if (! in_array($role, ['all', 'teacher', 'student'], true)) {
            $role = 'all';
        }

        return view('admin.reports.attendance-monthly', [
            'month' => $month,
            'monthLabel' => $start->translatedFormat('F Y'),
            'teacherRows' => $teacherRows,
            'studentRows' => $studentRows,
            'role' => $role,
            'reportKind' => 'all',
        ]);
    }

    public function teachers(Request $request): View
    {
        $request->merge(['role' => 'teacher']);

        return $this->monthly($request)->with([
            'reportKind' => 'teacher',
            'reportTitle' => 'Teacher attendance report',
            'reportBlurb' => 'Monthly weekday summary for teachers.',
        ]);
    }

    public function students(Request $request): View
    {
        $classId = $request->string('class_id')->toString();
        $request->merge(['role' => 'student']);

        $view = $this->monthly($request);

        $data = $view->getData();
        $studentRows = $data['studentRows'];

        if ($classId !== '' && $classId !== 'all') {
            $studentRows = $studentRows
                ->filter(fn (array $row) => (string) ($row['user']->class_id ?? '') === (string) $classId)
                ->values();
        }

        return $view->with([
            'reportKind' => 'student',
            'reportTitle' => 'Student attendance report',
            'reportBlurb' => 'Monthly weekday summary for students.',
            'studentRows' => $studentRows,
            'classId' => $classId !== '' ? $classId : 'all',
            'classes' => SchoolClass::query()->orderBy('sort_order')->orderBy('name')->get(),
            'showStudentMeta' => true,
        ]);
    }

    public function staffMonthly(Request $request, User $staff): View
    {
        $this->ensureStaff($staff);

        $defaultMonth = Carbon::now()->subMonth()->format('Y-m');
        $monthInput = $request->query('month');
        $month = $monthInput
            ? $request->validate(['month' => ['date_format:Y-m']])['month']
            : $defaultMonth;

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $byDate = Attendance::query()
            ->where('user_id', $staff->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $r) => $r->attendance_date->toDateString());

        $totalWorkSeconds = 0;
        $calendar = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->toDateString();
            $att = $byDate->get($key);
            $daySeconds = $this->workedSeconds($att);
            $totalWorkSeconds += $daySeconds;
            $calendar[] = [
                'date' => $d->copy(),
                'attendance' => $att,
                'worked_seconds' => $daySeconds,
                'worked_label' => $this->formatDurationSeconds($daySeconds, emptyAsDash: true),
            ];
        }

        $workingWeekdays = $this->workingWeekdayCount($start, $end);
        $withRecord = $byDate->count();
        $complete = $byDate->filter(fn (Attendance $r) => $r->checkin_time && $r->checkout_time)->count();
        $incomplete = $byDate->filter(fn (Attendance $r) => $r->checkin_time && ! $r->checkout_time)->count();

        return view('admin.reports.attendance-staff-monthly', [
            'staff' => $staff,
            'month' => $month,
            'monthLabel' => $start->translatedFormat('F Y'),
            'calendar' => $calendar,
            'summary' => [
                'working_weekdays' => $workingWeekdays,
                'days_with_record' => $withRecord,
                'complete_days' => $complete,
                'incomplete_days' => $incomplete,
                'no_record_weekdays' => max(0, $workingWeekdays - $withRecord),
                'total_work_seconds' => $totalWorkSeconds,
                'total_work_label' => $this->formatDurationSeconds($totalWorkSeconds, emptyAsDash: false),
                'total_work_decimal_hours' => round($totalWorkSeconds / 3600, 1),
            ],
        ]);
    }

    private function workedSeconds(?Attendance $attendance): int
    {
        if ($attendance === null || $attendance->checkin_time === null || $attendance->checkout_time === null) {
            return 0;
        }

        if ($attendance->checkout_time->lte($attendance->checkin_time)) {
            return 0;
        }

        return (int) $attendance->checkin_time->diffInSeconds($attendance->checkout_time);
    }

    private function formatDurationSeconds(int $seconds, bool $emptyAsDash = true): string
    {
        if ($seconds <= 0) {
            return $emptyAsDash ? '—' : '0h';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($minutes === 0) {
            return "{$hours}h";
        }

        return "{$hours}h {$minutes}m";
    }

    private function workingWeekdayCount(Carbon $start, Carbon $end): int
    {
        $n = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->isWeekday()) {
                $n++;
            }
        }

        return $n;
    }

    private function ensureStaff(User $user): void
    {
        abort_unless(in_array($user->role, ['teacher', 'student'], true), 404);
    }
}
