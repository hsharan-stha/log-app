@extends('layouts.portal')

@section('title', 'Guardian home')

@section('content')
    <h1 class="h3 mb-1">Guardian portal</h1>
    <p class="text-muted">You get alerts when your child checks in or out at school.</p>

    <div class="row g-3 mb-4">
        @forelse($wards as $child)
            @php $att = $todayByStudent->get($child->id); @endphp
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="fw-semibold fs-5">{{ $child->name }}</div>
                        <div class="text-muted small mb-3">{{ $child->schoolClass?->name }}</div>
                        @if($att?->checkin_time)
                            <div class="text-success">Checked in {{ $att->checkin_time->format('H:i') }}</div>
                            <div>Checked out {{ $att->checkout_time?->format('H:i') ?? '—' }}</div>
                        @else
                            <div class="text-warning">Not checked in yet today</div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-warning">No children linked. Ask the school office.</div></div>
        @endforelse
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <h2 class="h5">Alerts</h2>
            <div class="list-group shadow-sm">
                @forelse($alerts as $alert)
                    <div class="list-group-item">
                        {{ $alert->data['message'] ?? 'Attendance update' }}
                        <div class="small text-muted">{{ $alert->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">No alerts yet.</div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-6">
            <h2 class="h5">Notices</h2>
            <div class="list-group shadow-sm">
                @forelse($notices as $notice)
                    <div class="list-group-item">
                        <div class="fw-semibold">{{ $notice->title }}</div>
                        <div style="white-space:pre-wrap">{{ \Illuminate\Support\Str::limit($notice->body, 200) }}</div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">No notices.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
