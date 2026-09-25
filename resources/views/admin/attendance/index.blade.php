@extends('layouts.admin')

@section('title', $groupLabel.' calendar')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $groupLabel }} calendar</h1>
            <p class="text-muted mb-0">
                {{ $groupLabel }} · {{ $eligibleTotal }} people on roll.
                Click a day for photos and absences.
            </p>
        </div>
        <div class="calendar-nav d-flex align-items-center gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('admin.attendance.index', ['group' => $group, 'month' => $prevMonth]) }}">← Prev</a>
            <form method="GET" action="{{ route('admin.attendance.index', $group) }}" class="d-flex gap-2">
                <input type="month" name="month" value="{{ $month }}" class="form-control calendar-month" onchange="this.form.submit()">
            </form>
            <a class="btn btn-outline-secondary" href="{{ route('admin.attendance.index', ['group' => $group, 'month' => $nextMonth]) }}">Next →</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h2 class="h5 mb-0">{{ $monthLabel }}</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center align-middle attendance-calendar">
                <thead class="table-light">
                <tr>
                    <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th>
                </tr>
                </thead>
                <tbody>
                @foreach($weeks as $week)
                    <tr>
                        @foreach($week as $cell)
                            <td class="p-0 calendar-cell {{ $cell['inMonth'] ? '' : 'bg-light' }} {{ $cell['isToday'] ? 'today-cell' : '' }}">
                                @if($cell['inMonth'] && $cell['date'])
                                    <a href="{{ route('admin.attendance.day', ['group' => $group, 'date' => $cell['date']]) }}"
                                       class="d-block h-100 text-decoration-none text-dark p-2 calendar-day-link">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="fw-semibold {{ $cell['isToday'] ? 'text-primary' : '' }}">{{ $cell['day'] }}</span>
                                            @if($cell['isToday'])
                                                <span class="badge text-bg-primary" style="font-size: 0.6rem;">Today</span>
                                            @endif
                                        </div>
                                        @if($cell['isFuture'])
                                            <div class="small text-muted mt-3">—</div>
                                        @else
                                            <div class="mt-1 mt-md-2 small calendar-counts">
                                                <div class="text-success fw-semibold"><span class="cal-word">Attend </span>{{ $cell['present'] }}</div>
                                                <div class="text-danger"><span class="cal-word">Unattend </span>{{ $cell['absent'] }}</div>
                                            </div>
                                        @endif
                                    </a>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="small text-muted mb-0">
        <span class="text-success fw-semibold">Attend</span> = checked in that day ·
        <span class="text-danger fw-semibold">Unattend</span> = no check-in among {{ strtolower($groupLabel) }}.
    </p>
@endsection

@push('styles')
<style>
    .attendance-calendar .calendar-day-link:hover {
        background: rgba(13, 110, 253, 0.06);
    }
    .attendance-calendar .today-cell {
        box-shadow: inset 0 0 0 2px rgba(13, 110, 253, 0.45);
    }
    .calendar-month { width: 11rem; }
    .calendar-cell { height: 110px; min-width: 0; vertical-align: top; }
    @media (max-width: 767.98px) {
        .calendar-nav { width: 100%; }
        .calendar-nav form { flex: 1 1 auto; }
        .calendar-month { width: 100%; }
        .calendar-cell { height: 72px; }
        .calendar-cell .calendar-day-link { padding: 0.3rem !important; }
        .cal-word, .calendar-cell .badge { display: none; }
        .calendar-counts { font-size: 0.68rem; line-height: 1.15; }
    }
    @media (min-width: 768px) and (max-width: 1199.98px) {
        .calendar-cell { height: 96px; }
    }
    @media (min-width: 1400px) {
        .calendar-cell { height: 132px; }
    }
</style>
@endpush
