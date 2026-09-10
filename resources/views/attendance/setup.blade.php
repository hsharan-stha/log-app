@extends('layouts.portal')

@section('title', 'Register kiosk device')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Register attendance kiosk</h1>
        <p class="text-muted mb-0">Run this once on each school tablet while signed in as the attendance operator. After registration, only this browser can open face attendance (while you stay signed in).</p>
    </div>

    @if($existingDevice)
        <div class="alert alert-success">
            This browser is already registered as <strong>{{ $existingDevice->name }}</strong>
            (token prefix {{ $existingDevice->token_prefix }}…).
            <a href="{{ route('attendance.scan') }}">Open face attendance</a>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="max-width: 520px;">
        <div class="card-body">
            <form method="POST" action="{{ route('attendance.setup.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Device name</label>
                    <input id="name" name="name" class="form-control" value="{{ old('name', 'Entrance Tablet') }}" required maxlength="120">
                </div>
                <button class="btn btn-primary" type="submit">Register this device</button>
            </form>
        </div>
    </div>
@endsection
