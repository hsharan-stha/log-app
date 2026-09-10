@extends('layouts.portal')

@section('title', $lesson->title)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <a href="{{ route('teacher.courses.show', $course) }}" class="small text-decoration-none">← {{ $course->displayName() }}</a>
            <h1 class="h3 mt-2 mb-1">{{ $lesson->title }}</h1>
            <p class="text-muted mb-0">
                {{ $lesson->lesson_date?->toFormattedDateString() }}
                ·
                @if($lesson->isPublished())
                    <span class="badge text-bg-success">Published</span>
                @else
                    <span class="badge text-bg-secondary">Draft</span>
                @endif
            </p>
        </div>
        <a href="{{ route('teacher.lessons.edit', [$course, $lesson]) }}" class="btn btn-outline-primary">Edit lesson</a>
    </div>

    @if($lesson->hasRecording())
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong>{{ $lesson->livePlatformLabel() }}</strong>
                <a href="{{ $lesson->liveJoinUrl() }}" class="btn btn-success" target="_blank" rel="noopener">
                    Start / Join
                </a>
            </div>
            <div class="card-body p-0">
                @if($lesson->youtubeEmbedUrl())
                    <div class="ratio ratio-16x9 bg-dark">
                        <iframe
                            src="{{ $lesson->youtubeEmbedUrl() }}"
                            title="{{ $lesson->title }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                        ></iframe>
                    </div>
                    <div class="p-3 small text-muted">
                        Or <a href="{{ $lesson->liveJoinUrl() }}" target="_blank" rel="noopener">open on YouTube</a>.
                    </div>
                @else
                    <div class="p-4 text-center">
                        <p class="mb-3 text-muted">Click below to open {{ $lesson->livePlatformLabel() }} in a new tab.</p>
                        <a href="{{ $lesson->liveJoinUrl() }}" target="_blank" rel="noopener" class="btn btn-lg btn-success px-4">
                            Start / Join
                        </a>
                        <div class="mt-3">
                            <a href="{{ $lesson->liveJoinUrl() }}" class="small text-break" target="_blank" rel="noopener">{{ $lesson->recording_url }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($lesson->body)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><strong>Lesson</strong></div>
            <div class="card-body" style="white-space: pre-wrap;">{{ $lesson->body }}</div>
        </div>
    @endif

    @if($lesson->test_content)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><strong>Practice / test</strong></div>
            <div class="card-body" style="white-space: pre-wrap;">{{ $lesson->test_content }}</div>
        </div>
    @endif

    @if($lesson->whiteboards->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><strong>Whiteboard boards</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($lesson->whiteboards as $board)
                        <div class="col-md-6">
                            <div class="small text-muted mb-1">{{ $board->title }}</div>
                            <a href="{{ $board->url() }}" target="_blank" rel="noopener">
                                <img src="{{ $board->url() }}" alt="{{ $board->title }}" class="img-fluid rounded border bg-white">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($lesson->attachments->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><strong>Files</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($lesson->attachments as $file)
                        <div class="col-md-4">
                            @if($file->isImage())
                                <a href="{{ $file->url() }}" target="_blank" rel="noopener">
                                    <img src="{{ $file->url() }}" alt="{{ $file->original_name }}" class="img-fluid rounded border">
                                </a>
                                <div class="small text-muted mt-1">{{ $file->original_name }}</div>
                            @else
                                <a href="{{ $file->url() }}" target="_blank" rel="noopener" class="btn btn-outline-secondary w-100">
                                    {{ $file->isPdf() ? 'PDF' : 'File' }}: {{ $file->original_name }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(! $lesson->body && ! $lesson->test_content && $lesson->attachments->isEmpty() && ! $lesson->hasRecording() && $lesson->whiteboards->isEmpty())
        <div class="alert alert-secondary">This lesson has no content yet. Use Edit to add text, files, whiteboard, or a live link.</div>
    @endif
@endsection
