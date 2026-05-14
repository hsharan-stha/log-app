@extends('layouts.admin')

@section('title', 'Monthly attendance report')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Monthly attendance</h1>
            <p class="text-muted mb-0">Summary for all staff for a calendar month (weekdays).</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body py-3 px-4 bg-white border-bottom" style="background: linear-gradient(180deg, #fafbfc 0%, #fff 100%) !important;">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.attendance') }}">
                <div class="col-sm-auto">
                    <label for="month" class="form-label small text-muted mb-1 fw-semibold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.08em;">Month</label>
                    <input type="month" class="form-control form-control-lg shadow-sm" id="month" name="month" value="{{ $month }}"
                           style="min-width: 200px; border-radius: 10px;">
                </div>
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

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-attendance table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                <tr>
                    <th class="ps-4">Staff</th>
                    <th class="text-end">Weekdays</th>
                    <th class="text-end">Days with record</th>
                    <th class="text-end">Complete (in &amp; out)</th>
                    <th class="text-end">Incomplete (no out)</th>
                    <th class="text-end pe-4">No record (weekdays)</th>
                    <th class="pe-4" style="width: 1%;"></th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    @php
                        $u = $row['user'];
                        $name = $u->name;
                        $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1));
                    @endphp
                    <tr>
                        <td class="ps-4 py-3 staff-cell">
                            <div class="d-flex align-items-center gap-3">
                                <div class="staff-avatar is-compact" aria-hidden="true">{{ $initial }}</div>
                                <div>
                                    <div class="staff-name">{{ $name }}</div>
                                    @if($u->email)
                                        <div class="small text-muted text-truncate" style="max-width: 220px;">{{ $u->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-end py-3 font-monospace">{{ $row['working_weekdays'] }}</td>
                        <td class="text-end py-3 font-monospace">{{ $row['days_with_record'] }}</td>
                        <td class="text-end py-3 font-monospace text-success">{{ $row['complete_days'] }}</td>
                        <td class="text-end py-3 font-monospace text-warning">{{ $row['incomplete_days'] }}</td>
                        <td class="text-end py-3 font-monospace text-muted">{{ $row['no_record_weekdays'] }}</td>
                        <td class="pe-4 py-3 text-end">
                            <a class="btn btn-sm btn-outline-primary rounded-3"
                               href="{{ route('admin.reports.attendance.staff', ['staff' => $u->id, 'month' => $month]) }}">Individual</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No staff members yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
