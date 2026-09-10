@extends('layouts.admin')

@section('title', 'Subjects')

@section('content')
    @include('admin.partials.academics-flow', [
        'current' => 'subjects',
        'primaryHref' => route('admin.subjects.create'),
        'primaryLabel' => 'Add subject',
        'nextHref' => route('admin.courses.index'),
        'nextLabel' => 'Courses',
    ])
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Subjects</h1>
            <p class="text-muted mb-0">English, Math, Japanese, etc.</p>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Name</th><th>Code</th><th>Order</th><th></th></tr></thead>
            <tbody>
            @forelse($subjects as $subject)
                <tr>
                    <td class="fw-medium">{{ $subject->name }}</td>
                    <td><code>{{ $subject->code }}</code></td>
                    <td>{{ $subject->sort_order }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No subjects yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($subjects->hasPages())
            <div class="card-footer">{{ $subjects->links() }}</div>
        @endif
    </div>
@endsection
