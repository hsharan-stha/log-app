@extends('layouts.admin')

@section('title', 'Kiosk devices')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Kiosk devices</h1>
            <p class="text-muted mb-0">Only tablets registered by the attendance operator can open face attendance. You can revoke devices here.</p>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
            <tr><th>Name</th><th>Token</th><th>Registered by</th><th>Last seen</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($devices as $device)
                <tr>
                    <td class="fw-medium">{{ $device->name }}</td>
                    <td><code>{{ $device->token_prefix }}…</code></td>
                    <td>{{ $device->registrar?->name ?? '—' }}</td>
                    <td>{{ $device->last_seen_at?->diffForHumans() ?? '—' }}</td>
                    <td>
                        @if($device->isActive())
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-secondary">Revoked</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($device->isActive())
                            <form method="POST" action="{{ route('admin.devices.revoke', $device) }}" onsubmit="return confirm('Revoke this device?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">Revoke</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No devices yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($devices->hasPages())
            <div class="card-footer">{{ $devices->links() }}</div>
        @endif
    </div>
@endsection
