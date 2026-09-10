@extends('layouts.admin')

@section('title', 'Academics setup')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Academics setup</h1>
            <p class="text-muted mb-0">Complete these steps in order: Class → Teacher → Subject → Course.</p>
        </div>
        @if($canAssignCourse)
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-lg rounded-3">Assign course</a>
        @endif
    </div>

    <div class="row g-3 mb-4">
        @foreach($steps as $step)
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 academic-step-card {{ !empty($step['locked']) ? 'opacity-75' : '' }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge rounded-pill {{ $step['ready'] ? 'text-bg-success' : 'text-bg-secondary' }}">
                                Step {{ $step['step'] }}
                            </span>
                            <span class="fs-4 fw-semibold lh-1">{{ $step['count'] }}</span>
                        </div>
                        <h2 class="h5 mb-1">{{ $step['title'] }}</h2>
                        <p class="small text-muted mb-3">{{ $step['blurb'] }}</p>
                        <div class="mt-auto d-flex flex-wrap gap-2">
                            <a href="{{ $step['index'] }}" class="btn btn-sm btn-outline-secondary">Open</a>
                            @if(empty($step['locked']))
                                <a href="{{ $step['create'] }}" class="btn btn-sm btn-primary">{{ $step['create_label'] }}</a>
                            @else
                                <span class="btn btn-sm btn-secondary disabled">Finish steps 1–3 first</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
                <span class="fw-semibold me-1">Quick flow</span>
                <span class="badge text-bg-light border">1 Class</span>
                <span class="text-muted">→</span>
                <span class="badge text-bg-light border">2 Teacher</span>
                <span class="text-muted">→</span>
                <span class="badge text-bg-light border">3 Subject</span>
                <span class="text-muted">→</span>
                <span class="badge text-bg-primary">4 Course</span>
                <span class="small text-muted ms-md-2">A course is one subject taught by one teacher in one class.</span>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Recent courses</strong>
            <a href="{{ route('admin.courses.index') }}" class="small">View all</a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                <tr><th>Class</th><th>Subject</th><th>Teacher</th><th>Year</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($recentCourses as $course)
                    <tr>
                        <td>{{ $course->schoolClass?->name }}</td>
                        <td class="fw-medium">{{ $course->subject?->name }}</td>
                        <td>{{ $course->teacher?->name }}</td>
                        <td>{{ $course->academic_year ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No courses yet. Finish the steps above, then assign a course.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
