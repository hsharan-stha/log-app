<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /** @var array<string, list<string>> */
    private const GROUPS = [
        'staff' => User::FACE_STAFF_ROLES,
        'students' => ['student'],
    ];

    public function index(Request $request, string $group): View
    {
        $roles = $this->rolesForGroup($group);

        $monthInput = $request->query('month');
        $month = $monthInput
            ? $request->validate(['month' => ['date_format:Y-m']])['month']
            : now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $eligibleTotal = $this->eligibleUsers($roles)->count();

        $presentByDate = Attendance::query()
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('checkin_time')
            ->whereHas('user', fn ($q) => $q->whereIn('role', $roles))
            ->selectRaw('DATE(attendance_date) as day_date, COUNT(DISTINCT user_id) as present_count')
            ->groupBy('day_date')
            ->pluck('present_count', 'day_date')
            ->mapWithKeys(fn ($count, $day) => [
                Carbon::parse((string) $day)->toDateString() => (int) $count,
            ]);

        $weeks = $this->buildCalendarWeeks($start, $end, $presentByDate, $eligibleTotal);

        return view('admin.attendance.index', [
            'group' => $group,
            'groupLabel' => $this->groupLabel($group),
            'month' => $month,
            'monthLabel' => $start->translatedFormat('F Y'),
            'prevMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
            'weeks' => $weeks,
            'eligibleTotal' => $eligibleTotal,
            'today' => now()->toDateString(),
        ]);
    }

    public function day(Request $request, string $group, string $date): View
    {
        $roles = $this->rolesForGroup($group);

        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1, 404);

        try {
            $day = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            abort(404);
        }

        abort_unless($day->format('Y-m-d') === $date, 404);

        $q = trim($request->string('q')->toString());

        $eligible = $this->eligibleUsers($roles)->get();

        $attendances = Attendance::query()
            ->with('user:id,name,email,role,rides_bus')
            ->whereDate('attendance_date', $day->toDateString())
            ->whereNotNull('checkin_time')
            ->whereIn('user_id', $eligible->pluck('id'))
            ->orderBy('checkin_time')
            ->get()
            ->keyBy('user_id');

        $present = $eligible
            ->filter(fn (User $user) => $attendances->has($user->id))
            ->map(fn (User $user) => [
                'user' => $user,
                'attendance' => $attendances->get($user->id),
            ])
            ->values();

        $absent = $eligible
            ->reject(fn (User $user) => $attendances->has($user->id))
            ->values();

        $presentCount = $present->count();
        $absentCount = $absent->count();

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $matchesPerson = function (User $user) use ($needle): bool {
                return str_contains(mb_strtolower($user->name), $needle)
                    || ($user->email !== null && str_contains(mb_strtolower($user->email), $needle));
            };

            $present = $present
                ->filter(fn (array $row) => $matchesPerson($row['user']))
                ->values();
            $absent = $absent
                ->filter(fn (User $user) => $matchesPerson($user))
                ->values();
        }

        return view('admin.attendance.day', [
            'group' => $group,
            'groupLabel' => $this->groupLabel($group),
            'date' => $day->toDateString(),
            'dateLabel' => $day->toFormattedDateString(),
            'month' => $day->format('Y-m'),
            'q' => $q,
            'present' => $present,
            'absent' => $absent,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'eligibleTotal' => $eligible->count(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function rolesForGroup(string $group): array
    {
        abort_unless(isset(self::GROUPS[$group]), 404);

        return self::GROUPS[$group];
    }

    private function groupLabel(string $group): string
    {
        return $group === 'staff' ? 'Staff & teachers' : 'Students';
    }

    /**
     * @param  list<string>  $roles
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    private function eligibleUsers(array $roles)
    {
        return User::query()
            ->whereIn('role', $roles)
            ->orderBy('role')
            ->orderBy('name');
    }

    /**
     * @param  Collection<string, int|string>  $presentByDate
     * @return list<list<array{date: ?string, inMonth: bool, present: int, absent: int, isToday: bool, isFuture: bool}>>
     */
    private function buildCalendarWeeks(Carbon $start, Carbon $end, Collection $presentByDate, int $eligibleTotal): array
    {
        $cursor = $start->copy()->startOfWeek(Carbon::MONDAY);
        $last = $end->copy()->endOfWeek(Carbon::SUNDAY);
        $today = now()->startOfDay();

        $weeks = [];
        $week = [];

        while ($cursor->lte($last)) {
            $inMonth = $cursor->month === $start->month;
            $dateKey = $cursor->toDateString();
            $present = $inMonth ? (int) ($presentByDate[$dateKey] ?? 0) : 0;

            $isFuture = $cursor->gt($today);
            $absent = ($inMonth && ! $isFuture) ? max(0, $eligibleTotal - $present) : 0;

            $week[] = [
                'date' => $inMonth ? $dateKey : null,
                'day' => $cursor->day,
                'inMonth' => $inMonth,
                'present' => $present,
                'absent' => $absent,
                'isToday' => $cursor->isSameDay($today),
                'isFuture' => $isFuture,
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
