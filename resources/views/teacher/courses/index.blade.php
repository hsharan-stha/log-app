@extends('layouts.portal')

@section('title', 'My courses')

@section('content')
    <h1 class="h3 mb-1">My courses</h1>
    <p class="text-muted mb-4">Upload a daily lesson for each class subject you teach.</p>

    <div class="row g-3">
        @forelse($courses as $course)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('teacher.courses.show', $course) }}" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                    <div class="card-body">
                        <div class="text-muted small">{{ $course->schoolClass?->name }}</div>
                        <div class="fs-5 fw-semibold">{{ $course->subject?->name }}</div>
                        <div class="small text-muted mt-2">{{ $course->lessons_count }} lesson(s)</div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning mb-0">No courses assigned yet. Ask the principal to assign you a class + subject.</div>
            </div>
        @endforelse
    </div>
    @if($courses->hasPages())
        <div class="mt-3">{{ $courses->links() }}</div>
    @endif
@endsection
