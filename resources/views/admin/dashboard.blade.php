@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Dashboard</h1>
            <p class="text-muted mb-0">Snapshot for {{ \Illuminate\Support\Carbon::parse($today)->toFormattedDateString() }}</p>
        </div>
        <a href="{{ route('admin.academics.index') }}" class="btn btn-primary">Academics setup</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Teachers</div>
                <div class="display-6 fw-semibold">{{ $totalTeachers }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Students</div>
                <div class="display-6 fw-semibold">{{ $totalStudents }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Guardians</div>
                <div class="display-6 fw-semibold">{{ $totalGuardians }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Active kiosks</div>
                <div class="display-6 fw-semibold">{{ $activeDevices }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase">Present today</div>
                <div class="display-6 fw-semibold text-success">{{ $todayPresent }}</div>
                <div class="small text-muted">Teachers {{ $todayPresentTeachers }} · Students {{ $todayPresentStudents }}</div>
            </div></div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small text-uppercase mb-3">Scans today</div>
                <div class="row">
                    <div class="col-6">
                        <div class="display-6 fw-semibold text-primary">{{ $checkinsToday }}</div>
                        <div class="small text-muted">Check-ins</div>
                    </div>
                    <div class="col-6">
                        <div class="display-6 fw-semibold text-success">{{ $checkoutsToday }}</div>
                        <div class="small text-muted">Check-outs</div>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-primary">Open attendance calendar</a>
@endsection
