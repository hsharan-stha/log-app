@extends('layouts.admin')

@section('title', 'Guardians')

@section('content')
    @include('admin.partials.people-flow', [
        'current' => 'guardians',
        'primaryHref' => route('admin.guardians.create'),
        'primaryLabel' => 'Add guardian',
    ])

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Guardians</h1>
            <p class="text-muted mb-0">Step 2: link parents to students (by name / roll number).</p>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Phone</th><th>Children</th><th></th></tr></thead>
            <tbody>
            @forelse($guardians as $g)
                <tr>
                    <td class="fw-medium">{{ $g->name }}</td>
                    <td>{{ $g->email }}</td>
                    <td>{{ $g->phone ?? '—' }}</td>
                    <td>{{ $g->wards_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.guardians.edit', $g) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('admin.guardians.destroy', $g) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No guardians yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($guardians->hasPages())
            <div class="card-footer">{{ $guardians->links() }}</div>
        @endif
    </div>
@endsection
