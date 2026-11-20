@extends('layouts.portal')

@section('title', 'Register kiosk device')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Register attendance kiosk</h1>
        <p class="text-muted mb-0">Three kiosks can be active: one for teachers, one for students at school, and one for students on the bus. Revoke a device before another tablet can take that place.</p>
    </div>

    @if($existingDevice)
        <div class="alert alert-success">
            This browser is already registered as <strong>{{ $existingDevice->name }}</strong>
            ({{ $existingDevice->audienceLabel() }} · {{ $existingDevice->locationLabel() }}, token prefix {{ $existingDevice->token_prefix }}…).
            It does not need to be registered again.
            <a href="{{ route('attendance.scan') }}">Open face attendance</a>
        </div>
    @elseif($openSlots === [])
        <div class="alert alert-warning">
            All three kiosks are already registered. Ask an admin to revoke one before this tablet can be registered.
        </div>
        <ul class="mb-0">
            @foreach($activeDevices as $device)
                <li>{{ $device->name }} — {{ $device->audienceLabel() }} · {{ $device->locationLabel() }}</li>
            @endforeach
        </ul>
    @else
        @if($activeDevices->isNotEmpty())
            <div class="alert alert-info">
                Already registered:
                {{ $activeDevices->map(fn ($device) => $device->name.' ('.$device->audienceLabel().')')->join(', ') }}.
                Choose an open kiosk below.
            </div>
        @endif
        <div class="card border-0 shadow-sm" style="max-width: 520px;">
            <div class="card-body">
                <form method="POST" action="{{ route('attendance.setup.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="slot">Kiosk</label>
                        <select id="slot" name="slot" class="form-select" required>
                            @foreach($openSlots as $slot)
                                <option value="{{ $slot }}" @selected(old('slot') === $slot)>{{ \App\Models\KioskDevice::SLOTS[$slot]['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Students on the bus scan bus, then school, then school, then bus.</div>
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
