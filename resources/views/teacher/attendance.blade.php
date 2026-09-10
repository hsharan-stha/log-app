@extends('layouts.portal')

@section('title', 'My attendance')

@section('content')
    <h1 class="h3 mb-3">My attendance</h1>
    <p class="text-muted">Recorded at the school face kiosk (view only here).</p>
    <div class="card border-0 shadow-sm">
        <table class="table mb-0">
            <thead><tr><th>Date</th><th>Check-in</th><th>Check-out</th></tr></thead>
            <tbody>
            @forelse($records as $row)
                <tr>
                    <td>{{ $row->attendance_date?->toFormattedDateString() }}</td>
                    <td>{{ $row->checkin_time?->format('H:i') ?? '—' }}</td>
                    <td>{{ $row->checkout_time?->format('H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-muted text-center py-4">No records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($records->hasPages())
            <div class="card-footer">{{ $records->links() }}</div>
        @endif
    </div>
@endsection
