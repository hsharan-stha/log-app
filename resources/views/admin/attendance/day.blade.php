@extends('layouts.admin')

@section('title', 'Attendance '.$dateLabel)

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <a href="{{ route('admin.attendance.index', ['group' => $group, 'month' => $month]) }}" class="small text-decoration-none">← Back to {{ strtolower($groupLabel) }} calendar</a>
            <h1 class="h3 mt-2 mb-1">{{ $groupLabel }} · {{ $dateLabel }}</h1>
            <p class="text-muted mb-0">
                Attend <strong class="text-success">{{ $presentCount }}</strong>
                · Unattend <strong class="text-danger">{{ $absentCount }}</strong>
                · Roll {{ $eligibleTotal }}
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.attendance.day', ['group' => $group, 'date' => $date]) }}" class="mb-4 d-flex flex-wrap align-items-end gap-2">
        <div>
            <label class="form-label mb-1" for="q">Search person</label>
            <input id="q" name="q" type="search" class="form-control day-search"
                   value="{{ $q }}" placeholder="Name or email" autofocus>
        </div>
        <button class="btn btn-primary" type="submit">Search</button>
        @if($q !== '')
            <a href="{{ route('admin.attendance.day', ['group' => $group, 'date' => $date]) }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </form>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong class="text-success">
                        Attended
                        @if($q !== '')
                            ({{ $present->count() }} of {{ $presentCount }})
                        @else
                            ({{ $presentCount }})
                        @endif
                    </strong>
                </div>
                <div class="table-responsive day-table-wrap">
                    <table class="table table-attendance table-hover align-middle mb-0 day-table">
                        <thead class="bg-body-secondary">
                        <tr>
                            <th class="ps-4">Person</th>
                            <th>Role</th>
                            @if($group === 'students')
                                <th class="text-center">Bus in</th>
                            @endif
                            <th class="text-center">School in</th>
                            <th class="text-center pe-4">School out</th>
                            @if($group === 'students')
                                <th class="text-center pe-4">Bus out</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($present as $row)
                            @php
                                $user = $row['user'];
                                $att = $row['attendance'];
                                $name = $user->name;
                                $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1));
                                $showBus = $user->role === 'student' && $user->rides_bus;
                            @endphp
                            <tr>
                                <td class="ps-4 py-3 staff-cell" data-label="Person">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="staff-avatar is-compact" aria-hidden="true">{{ $initial }}</div>
                                        <div>
                                            <div class="staff-name">{{ $name }}</div>
                                            <div class="small text-muted">{{ $user->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-capitalize role-cell" data-label="Role">
                                    {{ $user->role }}
                                    @if($showBus)
                                        <span class="badge text-bg-info">Bus</span>
                                    @endif
                                </td>
                                @if($group === 'students')
                                <td class="text-center py-3 photo-cell" data-label="Bus in">
                                    @if($showBus)
                                        <x-attendance-time-photo
                                            :time="optional($att->bus_checkin_time)?->format('H:i')"
                                            :photo-url="$att->busCheckinPhotoUrl()"
                                            variant="in"
                                            size="sm"
                                        />
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @endif
                                <td class="text-center py-3 photo-cell" data-label="School in">
                                    <x-attendance-time-photo
                                        :time="optional($att->checkin_time)?->format('H:i')"
                                        :photo-url="$att->checkinPhotoUrl()"
                                        variant="in"
                                        size="sm"
                                    />
                                </td>
                                <td class="text-center py-3 photo-cell" data-label="School out">
                                    <x-attendance-time-photo
                                        :time="optional($att->checkout_time)?->format('H:i')"
                                        :photo-url="$att->checkoutPhotoUrl()"
                                        variant="out"
                                        size="sm"
                                    />
                                </td>
                                @if($group === 'students')
                                <td class="text-center pe-4 py-3 photo-cell" data-label="Bus out">
                                    @if($showBus)
                                        <x-attendance-time-photo
                                            :time="optional($att->bus_checkout_time)?->format('H:i')"
                                            :photo-url="$att->busCheckoutPhotoUrl()"
                                            variant="out"
                                            size="sm"
                                        />
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $group === 'students' ? 6 : 4 }}" class="text-center text-muted py-5">
                                    @if($q !== '')
                                        No attended people match “{{ $q }}”.
                                    @else
                                        Nobody checked in this day.
                                    @endif
                                </td>
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
                    <strong class="text-danger">
                        Unattended
                        @if($q !== '')
                            ({{ $absent->count() }} of {{ $absentCount }})
                        @else
                            ({{ $absentCount }})
                        @endif
                    </strong>
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
                        <li class="list-group-item text-muted text-center py-4">
                            @if($q !== '')
                                No unattended people match “{{ $q }}”.
                            @else
                                Everyone attended.
                            @endif
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .day-search { width: min(100%, 280px); min-width: 0; }
    @media (max-width: 767.98px) {
        .day-search { width: 100%; }
        .day-table-wrap { overflow: visible; }
        .day-table thead { display: none; }
        .day-table,
        .day-table tbody,
        .day-table tr,
        .day-table td { display: block; width: 100%; }
        .day-table tr {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.35rem 0.75rem;
            padding: 0.85rem 0.85rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }
        .day-table td { border: 0; padding: 0 !important; text-align: left !important; }
        .day-table td.staff-cell,
        .day-table td.role-cell,
        .day-table td[colspan] { grid-column: 1 / -1; }
        .day-table .staff-cell { min-width: 0; }
        .day-table td.photo-cell::before {
            content: attr(data-label);
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        .day-table .att-snap { max-width: none; }
        .day-table .att-snap--sm .att-snap__frame {
            width: min(42vw, 148px);
            height: min(42vw, 148px);
        }
    }
</style>
@endpush
