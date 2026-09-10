@extends('layouts.portal')

@section('title', 'Teacher home')

@section('content')
    <h1 class="h3 mb-1">Welcome, {{ auth()->user()->name }}</h1>
    <p class="text-muted">Homeroom overview. Face check-in is only on the school kiosk tablet.</p>
    <p class="mb-4"><a href="{{ route('teacher.courses.index') }}" class="btn btn-primary btn-sm">My courses &amp; lessons</a></p>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">My classes</div>
            <div class="fs-3 fw-semibold">{{ $classes->count() }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">Students</div>
            <div class="fs-3 fw-semibold">{{ $studentCount }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">Present today</div>
            <div class="fs-3 fw-semibold text-success">{{ $todayPresent }}</div>
        </div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><strong>My classes</strong></div>
                <ul class="list-group list-group-flush">
                    @forelse($classes as $class)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $class->name }}</span>
                            <span class="text-muted">{{ $class->students_count }} students</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No homeroom class assigned yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between">
                    <strong>Notices</strong>
                    <a href="{{ route('teacher.notices') }}">Manage</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($notices as $notice)
                        <li class="list-group-item">
                            <div class="fw-medium">{{ $notice->title }}</div>
                            <div class="small text-muted">{{ $notice->published_at?->diffForHumans() }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No notices.</li>
                    @endforelse
                </ul>
            </div>
            <a href="{{ route('messages.index') }}" class="btn btn-outline-primary w-100">Messages @if($unreadMessages) ({{ $unreadMessages }}) @endif</a>
        </div>
    </div>
@endsection
