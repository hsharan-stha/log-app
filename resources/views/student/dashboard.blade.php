@extends('layouts.portal')

@section('title', 'Student home')

@section('content')
    <h1 class="h3 mb-1">Hello, {{ $user->name }}</h1>
    <p class="text-muted">{{ $user->schoolClass?->name ?? 'No class' }} · Face check-in only at school kiosk</p>
    <p class="mb-4"><a href="{{ route('student.courses.index') }}" class="btn btn-primary btn-sm">Open my subjects</a></p>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h6 text-muted">Today</h2>
            @if($today?->checkin_time)
                <div>Checked in: <strong>{{ $today->checkin_time->format('H:i') }}</strong></div>
                <div>Checked out: <strong>{{ $today->checkout_time?->format('H:i') ?? '—' }}</strong></div>
            @else
                <div class="text-muted">Not checked in yet today.</div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <h2 class="h5">Notices</h2>
            <div class="list-group shadow-sm">
                @forelse($notices as $notice)
                    <div class="list-group-item">
                        <div class="fw-semibold">{{ $notice->title }}</div>
                        <div class="small text-muted">{{ $notice->published_at?->diffForHumans() }}</div>
                        <div class="mt-1" style="white-space:pre-wrap">{{ \Illuminate\Support\Str::limit($notice->body, 180) }}</div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">No notices.</div>
                @endforelse
            </div>
        </div>
        <div class="col-md-6">
            <h2 class="h5">Recent attendance</h2>
            <div class="card border-0 shadow-sm">
                <table class="table mb-0">
                    <thead><tr><th>Date</th><th>In</th><th>Out</th></tr></thead>
                    <tbody>
                    @foreach($history as $row)
                        <tr>
                            <td>{{ $row->attendance_date?->format('M j') }}</td>
                            <td>{{ $row->checkin_time?->format('H:i') ?? '—' }}</td>
                            <td>{{ $row->checkout_time?->format('H:i') ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
