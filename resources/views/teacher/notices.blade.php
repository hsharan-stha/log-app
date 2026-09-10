@extends('layouts.portal')

@section('title', 'Notices')

@section('content')
    <h1 class="h3 mb-4">Notices</h1>

    @if($classes->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6">Post to my class</h2>
                <form method="POST" action="{{ route('teacher.notices.store') }}">
                    @csrf
                    <div class="mb-2">
                        <select name="class_id" class="form-select" required>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2"><input name="title" class="form-control" placeholder="Title" required></div>
                    <div class="mb-2"><textarea name="body" class="form-control" rows="3" placeholder="Message" required></textarea></div>
                    <button class="btn btn-primary btn-sm">Post</button>
                </form>
            </div>
        </div>
    @endif

    <div class="list-group shadow-sm">
        @forelse($notices as $notice)
            <div class="list-group-item">
                <div class="fw-semibold">{{ $notice->title }}</div>
                <div class="small text-muted mb-1">{{ $notice->published_at?->toDayDateTimeString() ?? 'Draft' }} · {{ $notice->audience }}</div>
                <div style="white-space: pre-wrap">{{ $notice->body }}</div>
            </div>
        @empty
            <div class="list-group-item text-muted">No notices.</div>
        @endforelse
    </div>
    @if($notices->hasPages())
        <div class="mt-3">{{ $notices->links() }}</div>
    @endif
@endsection
