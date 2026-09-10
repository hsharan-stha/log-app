@extends('layouts.admin')

@section('title', $reportTitle ?? 'Monthly attendance report')

@section('content')
    @php
        $reportKind = $reportKind ?? 'all';
        $formAction = match ($reportKind) {
            'teacher' => route('admin.reports.attendance.teachers'),
            'student' => route('admin.reports.attendance.students'),
            default => route('admin.reports.attendance'),
        };
        $flowCurrent = match ($reportKind) {
            'teacher' => 'teachers',
            'student' => 'students',
            default => 'hub',
        };
    @endphp

    @include('admin.partials.reports-flow', ['current' => $flowCurrent])

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $reportTitle ?? 'Monthly attendance' }}</h1>
            <p class="text-muted mb-0">{{ $reportBlurb ?? 'Separate summaries for teachers and students (weekdays).' }}</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body py-3 px-4 bg-white border-bottom">
            <form class="row g-3 align-items-end" method="GET" action="{{ $formAction }}">
                <div class="col-sm-auto">
                    <label for="month" class="form-label small text-muted mb-1 fw-semibold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.08em;">Month</label>
                    <input type="month" class="form-control form-control-lg shadow-sm" id="month" name="month" value="{{ $month }}"
                           style="min-width: 200px; border-radius: 10px;">
                </div>
                @if($reportKind === 'student')
                    <div class="col-sm-auto">
                        <label for="class_id" class="form-label small text-muted mb-1 fw-semibold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.08em;">Class</label>
                        <select name="class_id" id="class_id" class="form-select form-select-lg shadow-sm" style="border-radius: 10px;">
                            <option value="all" @selected(($classId ?? 'all') === 'all')>All classes</option>
                            @foreach(($classes ?? []) as $class)
                                <option value="{{ $class->id }}" @selected((string) ($classId ?? '') === (string) $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($reportKind === 'all')
                    <div class="col-sm-auto">
                        <label for="role" class="form-label small text-muted mb-1 fw-semibold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.08em;">Show</label>
                        <select name="role" id="role" class="form-select form-select-lg shadow-sm" style="border-radius: 10px;">
                            <option value="all" @selected($role === 'all')>Teachers &amp; students</option>
                            <option value="teacher" @selected($role === 'teacher')>Teachers only</option>
                            <option value="student" @selected($role === 'student')>Students only</option>
                        </select>
                    </div>
                @endif
                <div class="col-sm-auto">
                    <button class="btn btn-primary btn-lg px-4 rounded-3 shadow-sm" type="submit">Apply</button>
                </div>
                <div class="col-sm d-flex align-items-end">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                        {{ $monthLabel }}
                    </span>
                </div>
            </form>
        </div>
    </div>

    @if($role === 'all' || $role === 'teacher')
        @include('admin.reports.partials.attendance-role-table', [
            'title' => 'Teachers',
            'emptyMessage' => 'No teachers yet.',
            'rows' => $teacherRows,
            'month' => $month,
            'showStudentMeta' => false,
        ])
    @endif

    @if($role === 'all' || $role === 'student')
        @include('admin.reports.partials.attendance-role-table', [
            'title' => 'Students',
            'emptyMessage' => 'No students found for this filter.',
            'rows' => $studentRows,
            'month' => $month,
            'showStudentMeta' => $showStudentMeta ?? ($reportKind === 'student'),
        ])
    @endif
@endsection
