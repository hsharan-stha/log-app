<div class="card border-0 shadow rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0 fw-semibold">{{ $title }}</h2>
        <span class="badge text-bg-light border">{{ $rows->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-attendance table-hover align-middle mb-0">
            <thead class="bg-body-secondary">
            <tr>
                <th class="ps-4">Name</th>
                @if(!empty($showStudentMeta))
                    <th>Roll no.</th>
                    <th>Class</th>
                @endif
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
                    $colspan = !empty($showStudentMeta) ? 9 : 7;
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
                    @if(!empty($showStudentMeta))
                        <td class="py-3"><code>{{ $u->roll_number ?? '—' }}</code></td>
                        <td class="py-3">{{ $u->schoolClass?->name ?? '—' }}</td>
                    @endif
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
                    <td colspan="{{ $colspan ?? 7 }}" class="text-center text-muted py-5">{{ $emptyMessage }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
