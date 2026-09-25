@extends('layouts.portal')

@section('title', 'Attendance '.$dateLabel)

@section('content')
    <a href="{{ route('guardian.attendance', ['student' => $studentId, 'month' => $month]) }}" class="small text-decoration-none">← Back to calendar</a>
    <h1 class="h3 mt-2 mb-1">{{ $student->name }}</h1>
    <p class="text-muted mb-4">
        {{ $dateLabel }}
        @if($student->rides_bus)
            · <span class="badge text-bg-info">Bus student</span>
        @endif
    </p>

    @if($isFuture)
        <div class="alert alert-secondary">This day is in the future.</div>
    @elseif(!$attendance || (! $attendance->checkin_time && ! $attendance->bus_checkin_time))
        <div class="alert alert-warning mb-0">
            <strong>Absent</strong> — no check-in recorded for this day.
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                @if($attendance->checkin_time)
                    <strong class="text-success">Present at school</strong>
                @else
                    <strong class="text-info">On bus (not yet at school)</strong>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-4 justify-content-center text-center">
                    @if($student->rides_bus)
                        <div class="col-sm-6 col-lg-3">
                            <div class="text-muted small text-uppercase mb-2">Bus in</div>
                            <x-attendance-time-photo
                                :time="optional($attendance->bus_checkin_time)?->format('H:i')"
                                :photo-url="$attendance->busCheckinPhotoUrl()"
                                variant="in"
                                size="lg"
                            />
                        </div>
                    @endif
                    <div class="col-sm-6 col-lg-3">
                        <div class="text-muted small text-uppercase mb-2">School in</div>
                        <x-attendance-time-photo
                            :time="optional($attendance->checkin_time)?->format('H:i')"
                            :photo-url="$attendance->checkinPhotoUrl()"
                            variant="in"
                            size="lg"
                        />
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="text-muted small text-uppercase mb-2">School out</div>
                        <x-attendance-time-photo
                            :time="optional($attendance->checkout_time)?->format('H:i')"
                            :photo-url="$attendance->checkoutPhotoUrl()"
                            variant="out"
                            size="lg"
                        />
                    </div>
                    @if($student->rides_bus)
                        <div class="col-sm-6 col-lg-3">
                            <div class="text-muted small text-uppercase mb-2">Bus out</div>
                            <x-attendance-time-photo
                                :time="optional($attendance->bus_checkout_time)?->format('H:i')"
                                :photo-url="$attendance->busCheckoutPhotoUrl()"
                                variant="out"
                                size="lg"
                            />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection
