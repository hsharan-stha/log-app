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
            ({{ $existingDevice->locationLabel() }} kiosk · {{ $existingDevice->token_prefix }}…).
        </div>
    @elseif($blockingDevice)
        <div class="alert alert-warning">
            Another kiosk is already registered (<strong>{{ $blockingDevice->name }}</strong>).
            Ask an admin to revoke it before this tablet can be registered.
        </div>
    @else
        <div class="alert alert-warning">
            This browser is not a registered kiosk yet. Register it once before opening face attendance.
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2">
        @if($existingDevice)
            <a href="{{ route('attendance.scan') }}" class="btn btn-primary">Open face attendance</a>
        @elseif(! $blockingDevice)
            <a href="{{ route('attendance.setup') }}" class="btn btn-outline-primary">Setup kiosk device</a>
        @endif
    </div>
@endsection
