@extends('layouts.portal')

@section('title', $course->displayName())

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <a href="{{ route('teacher.courses.index') }}" class="small text-decoration-none">← My courses</a>
            <h1 class="h3 mb-0 mt-1">{{ $course->displayName() }}</h1>
        </div>
        <a href="{{ route('teacher.lessons.create', $course) }}" class="btn btn-primary">New lesson</a>
    </div>

    <div class="card border-0 shadow-sm">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
            <tr><th>Date</th><th>Title</th><th>Files</th><th>Live</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($lessons as $lesson)
                <tr>
                    <td>{{ $lesson->lesson_date?->toFormattedDateString() }}</td>
                    <td class="fw-medium">
                        <a href="{{ route('teacher.lessons.show', [$course, $lesson]) }}" class="text-decoration-none text-dark">
                            {{ $lesson->title }}
                        </a>
                    </td>
                    <td>{{ $lesson->attachments_count }}</td>
                    <td>
                        @if($lesson->hasRecording())
                            <a href="{{ $lesson->liveJoinUrl() }}" class="btn btn-sm btn-success" target="_blank" rel="noopener">
                                Start / Join
                            </a>
                            <div class="small text-muted mt-1">{{ $lesson->livePlatformLabel() }}</div>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($lesson->isPublished())
                            <span class="badge text-bg-success">Published</span>
                        @else
                            <span class="badge text-bg-secondary">Draft</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('teacher.lessons.show', [$course, $lesson]) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="{{ route('teacher.lessons.edit', [$course, $lesson]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('teacher.lessons.destroy', [$course, $lesson]) }}" onsubmit="return confirm('Delete lesson?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No lessons yet. Create today’s lesson.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="card-footer">{{ $lessons->links() }}</div>
    </div>
@endsection
