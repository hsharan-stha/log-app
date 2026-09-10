@extends('layouts.admin')

@section('title', 'Notices')

@section('content')
    <div class="d-flex justify-content-between mb-4">
        <h1 class="h3 mb-0">Notice board</h1>
        <a href="{{ route('admin.notices.create') }}" class="btn btn-primary">Post notice</a>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Title</th><th>Audience</th><th>Published</th><th>Author</th><th></th></tr></thead>
            <tbody>
            @forelse($notices as $notice)
                <tr>
                    <td>
                        <div class="fw-medium">{{ $notice->title }}</div>
                        <div class="small text-muted text-truncate" style="max-width:360px">{{ $notice->body }}</div>
                    </td>
                    <td class="text-capitalize">{{ $notice->audience }}@if($notice->schoolClass) ({{ $notice->schoolClass->name }})@endif</td>
                    <td>{{ $notice->published_at?->toDayDateTimeString() ?? 'Draft' }}</td>
                    <td>{{ $notice->author?->name }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.notices.destroy', $notice) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No notices yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="card-footer">{{ $notices->links() }}</div>
    </div>
@endsection
