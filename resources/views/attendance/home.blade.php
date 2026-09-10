@extends('layouts.portal')

@section('title', 'Attendance kiosk')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Attendance operator</h1>
        <p class="text-muted mb-0">Set up school tablets and run face check-in/out. Only this account can open the kiosk.</p>
    </div>

    @if($existingDevice)
        <div class="alert alert-success">
            This browser is registered as <strong>{{ $existingDevice->name }}</strong>
            ({{ $existingDevice->token_prefix }}…).
        </div>
    @else
        <div class="alert alert-warning">
            This browser is not a registered kiosk yet. Register it before opening face attendance.
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('attendance.setup') }}" class="btn btn-outline-primary">
            {{ $existingDevice ? 'Re-register device' : 'Setup kiosk device' }}
        </a>
        @if($existingDevice)
            <a href="{{ route('attendance.scan') }}" class="btn btn-primary">Open face attendance</a>
        @endif
    </div>
@endsection
