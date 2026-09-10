@extends('layouts.portal')

@section('title', $course->subject?->name)

@section('content')
    <a href="{{ route('student.courses.index') }}" class="small text-decoration-none">← All subjects</a>
    <h1 class="h3 mt-2 mb-1">{{ $course->subject?->name }}</h1>
    <p class="text-muted mb-4">{{ $course->schoolClass?->name }} · {{ $course->teacher?->name }}</p>

    <div class="list-group shadow-sm">
        @forelse($lessons as $lesson)
            <a href="{{ route('student.lessons.show', [$course, $lesson]) }}" class="list-group-item list-group-item-action">
                <div class="d-flex justify-content-between">
                    <strong>{{ $lesson->title }}</strong>
                    <span class="small text-muted">{{ $lesson->lesson_date?->toFormattedDateString() }}</span>
                </div>
                <div class="small text-muted">{{ $lesson->attachments_count }} file(s)
                    @if($lesson->recording_url)
                        · {{ $lesson->livePlatformLabel() }}
                    @endif
                </div>
            </a>
        @empty
            <div class="list-group-item text-muted">No published lessons yet.</div>
        @endforelse
    </div>
    <div class="mt-3">{{ $lessons->links() }}</div>
@endsection
