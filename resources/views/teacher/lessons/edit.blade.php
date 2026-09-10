@extends('layouts.portal')

@section('title', 'Edit lesson')

@section('content')
    <a href="{{ route('teacher.courses.show', $course) }}" class="small text-decoration-none">← {{ $course->displayName() }}</a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Edit lesson</h1>
            <p class="text-muted mb-0">{{ $course->displayName() }}</p>
        </div>
        <a href="{{ route('teacher.lessons.show', [$course, $lesson]) }}" class="btn btn-outline-secondary btn-sm">Preview</a>
    </div>

    <form id="lesson-edit-form" method="POST" action="{{ route('teacher.lessons.update', [$course, $lesson]) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @if($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Could not save. Please check:</div>
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @include('teacher.lessons._form', ['lesson' => $lesson, 'course' => $course])
        <button class="btn btn-primary btn-lg px-4" type="submit">Update lesson</button>
        <a href="{{ route('teacher.lessons.show', [$course, $lesson]) }}" class="btn btn-link">Cancel</a>
    </form>

    {{-- Must stay outside the lesson form: nested forms break HTML and turn Update into DELETE --}}
    @foreach($lesson->attachments as $file)
        <form id="delete-attachment-{{ $file->id }}" method="POST"
              action="{{ route('teacher.attachments.destroy', [$course, $lesson, $file]) }}" class="d-none">
            @csrf @method('DELETE')
        </form>
    @endforeach
    @foreach($lesson->whiteboards as $board)
        <form id="delete-whiteboard-{{ $board->id }}" method="POST"
              action="{{ route('teacher.whiteboards.destroy', [$course, $lesson, $board]) }}" class="d-none">
            @csrf @method('DELETE')
        </form>
    @endforeach
@endsection
