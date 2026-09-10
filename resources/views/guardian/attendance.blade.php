@extends('layouts.portal')

@section('title', 'Attendance calendar')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance calendar</h1>
            <p class="text-muted mb-0">{{ $student->name }} — tap a day to see check-in / check-out photos.</p>
        </div>
        <form method="GET" action="{{ route('guardian.attendance') }}" class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label small text-muted mb-1">Child</label>
                <select name="student" class="form-select" onchange="this.form.submit()">
                    @foreach($wards as $w)
                        <option value="{{ $w->id }}" @selected($studentId === $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Month</label>
                <input type="month" name="month" value="{{ $month }}" class="form-control" onchange="this.form.submit()">
            </div>
        </form>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('guardian.attendance', ['student' => $studentId, 'month' => $prevMonth]) }}">← Prev</a>
        <span class="align-self-center fw-semibold px-2">{{ $monthLabel }}</span>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('guardian.attendance', ['student' => $studentId, 'month' => $nextMonth]) }}">Next →</a>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center align-middle">
                <thead class="table-light">
                <tr>
                    <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th>
                </tr>
                </thead>
                <tbody>
                @foreach($weeks as $week)
                    <tr>
                        @foreach($week as $cell)
                            <td class="p-0 {{ $cell['inMonth'] ? '' : 'bg-light' }} {{ $cell['isToday'] ? 'border border-primary border-2' : '' }}"
                                style="height: 96px; min-width: 96px; vertical-align: top;">
                                @if($cell['inMonth'] && $cell['date'])
                                    <a href="{{ route('guardian.attendance.day', ['date' => $cell['date'], 'student' => $studentId]) }}"
                                       class="d-block h-100 text-decoration-none text-dark p-2">
                                        <div class="fw-semibold {{ $cell['isToday'] ? 'text-primary' : '' }}">{{ $cell['day'] }}</div>
                                        @if($cell['isFuture'])
                                            <div class="small text-muted mt-2">—</div>
                                        @elseif($cell['present'])
                                            <div class="small text-success fw-semibold mt-2">Present</div>
                                            @if($cell['hasCheckout'])
                                                <div class="small text-muted">In + Out</div>
                                            @else
                                                <div class="small text-muted">Checked in</div>
                                            @endif
                                        @else
                                            <div class="small text-danger mt-2">Absent</div>
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
@endsection
