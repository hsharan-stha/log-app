@extends('layouts.portal')

@section('title', 'Attendance '.$dateLabel)

@section('content')
    <a href="{{ route('guardian.attendance', ['student' => $studentId, 'month' => $month]) }}" class="small text-decoration-none">← Back to calendar</a>
    <h1 class="h3 mt-2 mb-1">{{ $student->name }}</h1>
    <p class="text-muted mb-4">{{ $dateLabel }}</p>

    @if($isFuture)
        <div class="alert alert-secondary">This day is in the future.</div>
    @elseif(!$attendance || !$attendance->checkin_time)
        <div class="alert alert-warning mb-0">
            <strong>Absent</strong> — no check-in recorded for this day.
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong class="text-success">Present</strong>
            </div>
            <div class="card-body">
                <div class="row g-4 justify-content-center text-center">
                    <div class="col-sm-5">
                        <div class="text-muted small text-uppercase mb-2">Check-in</div>
                        <x-attendance-time-photo
                            :time="optional($attendance->checkin_time)?->format('H:i')"
                            :photo-url="$attendance->checkinPhotoUrl()"
                            variant="in"
                            size="lg"
                        />
                    </div>
                    <div class="col-sm-5">
                        <div class="text-muted small text-uppercase mb-2">Check-out</div>
                        <x-attendance-time-photo
                            :time="optional($attendance->checkout_time)?->format('H:i')"
                            :photo-url="$attendance->checkoutPhotoUrl()"
                            variant="out"
                            size="lg"
                        />
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
