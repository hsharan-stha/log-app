@extends('layouts.portal')

@section('title', 'Register kiosk device')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Register attendance kiosk</h1>
        <p class="text-muted mb-0">Register this tablet once. Only one kiosk can be active. An admin must revoke it before another tablet can register.</p>
    </div>

    @if($existingDevice)
        <div class="alert alert-success">
            This browser is already registered as <strong>{{ $existingDevice->name }}</strong>
            ({{ $existingDevice->locationLabel() }} kiosk, token prefix {{ $existingDevice->token_prefix }}…).
            It does not need to be registered again.
            <a href="{{ route('attendance.scan') }}">Open face attendance</a>
        </div>
    @elseif($blockingDevice)
        <div class="alert alert-warning">
            A kiosk is already registered as <strong>{{ $blockingDevice->name }}</strong>
            ({{ $blockingDevice->locationLabel() }}, token prefix {{ $blockingDevice->token_prefix }}…).
            Ask an admin to revoke that device before registering this one.
        </div>
    @endif

    @if(! $existingDevice && ! $blockingDevice)
    <div class="card border-0 shadow-sm" style="max-width: 520px;">
        <div class="card-body">
            <form method="POST" action="{{ route('attendance.setup.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="location">Kiosk location</label>
                    <select id="location" name="location" class="form-select" required>
                        <option value="school" @selected(old('location', 'school') === 'school')>School ground</option>
                        <option value="bus" class="d-none" hidden @selected(old('location') === 'bus')>School bus</option>
                    </select>
                    <div class="form-text d-none">Fixed per tablet. Bus students must scan bus → school → school → bus.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="name">Device name</label>
                    <input id="name" name="name" class="form-control" value="{{ old('name', 'Entrance Tablet') }}" required maxlength="120">
                </div>
                <button class="btn btn-primary" type="submit">Register this device</button>
            </form>
        </div>
    </div>
    @endif
@endsection
