@extends($layout)

@section('title', 'Messages')

@section('content')
    <h1 class="h3 mb-4">Messages</h1>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6">New message</h2>
                    <form method="POST" action="{{ route('messages.store') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">To</label>
                            <select name="recipient_id" class="form-select" required>
                                <option value="">Select…</option>
                                @foreach($recipients as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">About student (optional)</label>
                            <input type="number" name="student_id" class="form-control" placeholder="Student user id">
                        </div>
                        <div class="mb-2">
                            <textarea name="body" class="form-control" rows="4" required></textarea>
                        </div>
                        <button class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <h2 class="h6">Inbox</h2>
            <div class="list-group mb-4 shadow-sm">
                @forelse($inbox as $msg)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>From {{ $msg->sender?->name }}</strong>
                            <span class="small text-muted">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <div style="white-space:pre-wrap">{{ $msg->body }}</div>
                        @if(!$msg->read_at)
                            <form method="POST" action="{{ route('messages.read', $msg) }}" class="mt-2">@csrf<button class="btn btn-sm btn-outline-secondary">Mark read</button></form>
                        @endif
                    </div>
                @empty
                    <div class="list-group-item text-muted">Inbox empty.</div>
                @endforelse
            </div>
            {{ $inbox->links() }}
        </div>
    </div>
@endsection
