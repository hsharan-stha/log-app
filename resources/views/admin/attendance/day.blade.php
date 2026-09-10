@extends('layouts.admin')

@section('title', 'Attendance '.$dateLabel)

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <a href="{{ route('admin.attendance.index', ['month' => $month]) }}" class="small text-decoration-none">← Back to calendar</a>
            <h1 class="h3 mt-2 mb-1">{{ $dateLabel }}</h1>
            <p class="text-muted mb-0">
                Attend <strong class="text-success">{{ $presentCount }}</strong>
                · Unattend <strong class="text-danger">{{ $absentCount }}</strong>
                · Roll {{ $eligibleTotal }}
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong class="text-success">Attended ({{ $presentCount }})</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-attendance table-hover align-middle mb-0">
                        <thead class="bg-body-secondary">
                        <tr>
                            <th class="ps-4">Person</th>
                            <th>Role</th>
                            <th class="text-center">Check-in photo</th>
                            <th class="text-center pe-4">Check-out photo</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($present as $row)
                            @php
                                $user = $row['user'];
                                $att = $row['attendance'];
                                $name = $user->name;
                                $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1));
                            @endphp
                            <tr>
                                <td class="ps-4 py-3 staff-cell">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="staff-avatar is-compact" aria-hidden="true">{{ $initial }}</div>
                                        <div>
                                            <div class="staff-name">{{ $name }}</div>
                                            <div class="small text-muted">{{ $user->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-capitalize">{{ $user->role }}</td>
                                <td class="text-center py-3">
                                    <x-attendance-time-photo
                                        :time="optional($att->checkin_time)?->format('H:i')"
                                        :photo-url="$att->checkinPhotoUrl()"
                                        variant="in"
                                        size="lg"
                                    />
                                </td>
                                <td class="text-center pe-4 py-3">
                                    <x-attendance-time-photo
                                        :time="optional($att->checkout_time)?->format('H:i')"
                                        :photo-url="$att->checkoutPhotoUrl()"
                                        variant="out"
                                        size="lg"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">Nobody checked in this day.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white">
                    <strong class="text-danger">Unattended ({{ $absentCount }})</strong>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($absent as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-medium">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $user->email ?? '—' }}</div>
                            </div>
                            <span class="badge text-bg-light text-capitalize border">{{ $user->role }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-4">Everyone attended.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
