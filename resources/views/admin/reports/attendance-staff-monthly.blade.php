@extends('layouts.admin')

@section('title', ($staff->isTeacher() ? 'Teacher' : 'Student').' monthly attendance')

@section('content')
    @include('admin.partials.reports-flow', [
        'current' => $staff->isTeacher() ? 'teachers' : 'students',
    ])

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $staff->name }}</h1>
            <p class="text-muted mb-0">
                <span class="badge text-bg-light border text-capitalize me-1">{{ $staff->role }}</span>
                Day-by-day attendance for {{ $monthLabel }}.
                <span class="text-body fw-semibold ms-1">Total time: {{ $summary['total_work_label'] }}</span>
                <span class="text-muted">({{ $summary['total_work_decimal_hours'] }} h)</span>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary rounded-3" href="{{ route('admin.reports.attendance', ['month' => $month, 'role' => $staff->isTeacher() ? 'teacher' : 'student']) }}">← Back to report</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body py-3 px-4 bg-white border-bottom" style="background: linear-gradient(180deg, #fafbfc 0%, #fff 100%) !important;">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.attendance.staff', $staff) }}">
                <div class="col-sm-auto">
                    <label for="month" class="form-label small text-muted mb-1 fw-semibold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.08em;">Month</label>
                    <input type="month" class="form-control form-control-lg shadow-sm" id="month" name="month" value="{{ $month }}"
                           style="min-width: 200px; border-radius: 10px;">
                </div>
                <div class="col-sm-auto">
                    <button class="btn btn-primary btn-lg px-4 rounded-3 shadow-sm" type="submit">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.06em;">Weekdays</div>
                    <div class="h4 mb-0 mt-1">{{ $summary['working_weekdays'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.06em;">With record</div>
                    <div class="h4 mb-0 mt-1">{{ $summary['days_with_record'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.06em;">Complete</div>
                    <div class="h4 mb-0 mt-1 text-success">{{ $summary['complete_days'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.06em;">Incomplete</div>
                    <div class="h4 mb-0 mt-1 text-warning">{{ $summary['incomplete_days'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.06em;">No record (weekdays)</div>
                    <div class="h4 mb-0 mt-1 text-muted">{{ $summary['no_record_weekdays'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-primary-subtle" style="border-width: 1px !important;">
                <div class="card-body">
                    <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.06em;">Total hours (check-in → check-out)</div>
                    <div class="h4 mb-0 mt-1 text-primary">{{ $summary['total_work_label'] }}</div>
                    <div class="small text-muted mb-0">{{ $summary['total_work_decimal_hours'] }} decimal hours</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-attendance table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Weekday</th>
                    <th class="text-center">Check-in</th>
                    <th class="text-center">Check-out</th>
                    <th class="text-end pe-4">Duration</th>
                </tr>
                </thead>
                <tbody>
                @foreach($calendar as $cell)
                    @php
                        $d = $cell['date'];
                        $row = $cell['attendance'];
                    @endphp
                    <tr class="{{ $d->isWeekend() ? 'table-light' : '' }}">
                        <td class="ps-4 py-3">
                            <span class="date-pill">{{ $d->toFormattedDateString() }}</span>
                        </td>
                        <td class="py-3">
                            {{ $d->translatedFormat('l') }}
                            @if($d->isWeekend())
                                <span class="badge bg-secondary-subtle text-secondary border ms-1">Weekend</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if($row && $row->checkin_time)
                                <x-attendance-time-photo
                                    :time="$row->checkin_time->format('H:i')"
                                    :photo-url="$row->checkinPhotoUrl()"
                                    variant="in"
                                    size="sm"
                                />
                            @else
                                <span class="small text-muted">—</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if($row && $row->checkout_time)
                                <x-attendance-time-photo
                                    :time="$row->checkout_time->format('H:i')"
                                    :photo-url="$row->checkoutPhotoUrl()"
                                    variant="out"
                                    size="sm"
                                />
                            @else
                                <span class="small text-muted">—</span>
                            @endif
                        </td>
                        <td class="py-3 text-end pe-4 font-monospace small fw-semibold text-body-secondary">
                            {{ $cell['worked_label'] }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
