@extends('layouts.portal')

@section('title', 'My subjects')

@section('content')
    <h1 class="h3 mb-1">My subjects</h1>
    <p class="text-muted mb-4">
        {{ $user->schoolClass?->name ?? 'No class assigned' }}
        — open a subject to see daily lessons.
    </p>

    @if(!$user->class_id)
        <div class="alert alert-warning">You are not assigned to a class yet.</div>
    @else
        <div class="row g-3">
            @forelse($courses as $course)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('student.courses.show', $course) }}" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                        <div class="card-body">
                            <div class="fs-5 fw-semibold">{{ $course->subject?->name }}</div>
                            <div class="small text-muted mt-1">Teacher: {{ $course->teacher?->name ?? '—' }}</div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info mb-0">No subjects for your class yet.</div></div>
            @endforelse
        </div>
        @if($courses->hasPages())
            <div class="mt-3">{{ $courses->links() }}</div>
        @endif
    @endif
@endsection
