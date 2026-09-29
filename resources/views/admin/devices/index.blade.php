@extends('layouts.admin')

@section('title', 'Kiosk devices')

@section('content')
    <div class="people-head">
        <div>
            <h1>Kiosk devices</h1>
            <p>Only one kiosk can be active. An admin must revoke it before another tablet can register.</p>
        </div>
    </div>
    <div class="card people-table-card">
        <table class="table mb-0 align-middle people-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Token</th>
                <th>Registered by</th>
                <th>Last seen</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($devices as $device)
                <tr>
                    <td data-label="Name" class="fw-medium">{{ $device->name }}</td>
                    <td data-label="Location">
                        <span class="badge {{ $device->isBusKiosk() ? 'text-bg-warning' : 'text-bg-primary' }}">
                            {{ $device->locationLabel() }}
                        </span>
                    </td>
                    <td data-label="Token"><code>{{ $device->token_prefix }}…</code></td>
                    <td data-label="Registered by">{{ $device->registrar?->name ?? '—' }}</td>
                    <td data-label="Last seen">{{ $device->last_seen_at?->diffForHumans() ?? '—' }}</td>
                    <td data-label="Status">
                        @if($device->isActive())
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-secondary">Revoked</span>
                        @endif
                    </td>
                    <td class="people-actions-cell">
                        @if($device->isActive() && auth()->user()?->isAdmin())
                            <div class="people-actions">
                                <form method="POST" action="{{ route('admin.devices.revoke', $device) }}" onsubmit="return confirm('Revoke this device?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Revoke</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No devices yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($devices->hasPages())
            <div class="card-footer">{{ $devices->links() }}</div>
        @endif
    </div>
@endsection
