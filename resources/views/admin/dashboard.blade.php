@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Dashboard</h1>
            <p class="text-muted mb-0">Snapshot for {{ \Illuminate\Support\Carbon::parse($today)->toFormattedDateString() }}</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Total staff</div>
                    <div class="display-6 fw-semibold">{{ $totalStaff }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Today present</div>
                    <div class="display-6 fw-semibold text-success">{{ $todayPresent }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-3">Scans today</div>
                    <div class="row g-3 align-items-end">
                        <div class="col-6">
                            <div class="display-6 fw-semibold text-primary lh-1">{{ $checkinsToday }}</div>
                            <div class="small text-muted mt-2">Check-ins</div>
                        </div>
                        <div class="col-6">
                            <div class="display-6 fw-semibold text-success lh-1">{{ $checkoutsToday }}</div>
                            <div class="small text-muted mt-2">Check-outs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
            <h2 class="h5 mb-0 fw-semibold">Recent attendance</h2>
            <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">View all</a>
        </div>
        <div class="table-responsive">
            <table class="table table-attendance table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                <tr>
                    <th class="ps-4">Staff</th>
                    <th>Date</th>
                    <th class="text-center">Check-in</th>
                    <th class="text-center pe-4">Check-out</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentAttendances as $row)
                    @php
                        $name = $row->user?->name ?? 'Unknown';
                        $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1));
                    @endphp
                    <tr>
                        <td class="ps-4 py-3 staff-cell">
                            <div class="d-flex align-items-center gap-2">
                                <div class="staff-avatar is-compact" aria-hidden="true">{{ $initial }}</div>
                                <span class="staff-name">{{ $name }}</span>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="date-pill" style="font-size: 0.8rem;">{{ $row->attendance_date?->toFormattedDateString() }}</span>
                        </td>
                        <td class="py-3 text-center">
                            <x-attendance-time-photo
                                :time="optional($row->checkin_time)?->format('H:i')"
                                :photo-url="$row->checkinPhotoUrl()"
                                variant="in"
                                size="sm"
                            />
                        </td>
                        <td class="py-3 text-center pe-4">
                            <x-attendance-time-photo
                                :time="optional($row->checkout_time)?->format('H:i')"
                                :photo-url="$row->checkoutPhotoUrl()"
                                variant="out"
                                size="sm"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No attendance records yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
