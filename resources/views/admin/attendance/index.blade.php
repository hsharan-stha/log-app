@extends('layouts.admin')

@section('title', 'Attendance logs')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance</h1>
            <p class="text-muted mb-0">Daily check-in and check-out with kiosk snapshots.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body py-3 px-4 bg-white border-bottom" style="background: linear-gradient(180deg, #fafbfc 0%, #fff 100%) !important;">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.attendance.index') }}">
                <div class="col-sm-auto">
                    <label for="date" class="form-label small text-muted mb-1 fw-semibold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.08em;">Filter by date</label>
                    <input type="date" class="form-control form-control-lg shadow-sm" id="date" name="date" value="{{ $date }}"
                           style="min-width: 200px; border-radius: 10px;">
                </div>
                <div class="col-sm-auto d-flex gap-2">
                    <button class="btn btn-primary btn-lg px-4 rounded-3 shadow-sm" type="submit">Apply</button>
                    @if($date)
                        <a class="btn btn-outline-secondary btn-lg rounded-3" href="{{ route('admin.attendance.index') }}">Reset</a>
                    @endif
                </div>
                @if($date)
                    <div class="col-sm d-flex align-items-end">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                            Showing {{ \Illuminate\Support\Carbon::parse($date)->toFormattedDateString() }}
                        </span>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow rounded-4 overflow-hidden">
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
                @forelse($attendances as $row)
                    @php
                        $name = $row->user?->name ?? 'Unknown';
                        $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1));
                    @endphp
                    <tr>
                        <td class="ps-4 py-4 staff-cell">
                            <div class="d-flex align-items-center gap-3">
                                <div class="staff-avatar" aria-hidden="true">{{ $initial }}</div>
                                <div>
                                    <div class="staff-name">{{ $name }}</div>
                                    @if($row->user?->email)
                                        <div class="small text-muted text-truncate" style="max-width: 220px;">{{ $row->user->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-4">
                            <span class="date-pill">{{ $row->attendance_date?->toFormattedDateString() }}</span>
                        </td>
                        <td class="py-4 text-center">
                            <x-attendance-time-photo
                                :time="optional($row->checkin_time)?->format('H:i')"
                                :photo-url="$row->checkinPhotoUrl()"
                                variant="in"
                                size="lg"
                            />
                        </td>
                        <td class="py-4 text-center pe-4">
                            <x-attendance-time-photo
                                :time="optional($row->checkout_time)?->format('H:i')"
                                :photo-url="$row->checkoutPhotoUrl()"
                                variant="out"
                                size="lg"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <div class="py-3">
                                <div class="fs-4 mb-2 opacity-50">📋</div>
                                <p class="mb-0 fw-medium">No attendance records for this filter.</p>
                                <p class="small mb-0 mt-1">Try another date or clear the filter.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
            <div class="card-body border-top bg-light py-3">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
@endsection
