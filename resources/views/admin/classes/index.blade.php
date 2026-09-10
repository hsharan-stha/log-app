@extends('layouts.admin')

@section('title', 'Classes')

@section('content')
    @include('admin.partials.academics-flow', [
        'current' => 'classes',
        'primaryHref' => route('admin.classes.create'),
        'primaryLabel' => 'Add class',
        'nextHref' => route('admin.staff.index'),
        'nextLabel' => 'Teachers',
    ])
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Classes</h1>
            <p class="text-muted mb-0">Nursery through Grade 5</p>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr><th>Name</th><th>Code</th><th>Homeroom teacher</th><th>Students</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($classes as $class)
                    <tr>
                        <td class="fw-medium">{{ $class->name }}</td>
                        <td><code>{{ $class->code }}</code></td>
                        <td>{{ $class->homeroomTeacher?->name ?? '—' }}</td>
                        <td>{{ $class->students_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form class="d-inline" method="POST" action="{{ route('admin.classes.destroy', $class) }}" onsubmit="return confirm('Delete this class?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No classes yet. Seed or create Nursery–Grade 5.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($classes->hasPages())
            <div class="card-footer">{{ $classes->links() }}</div>
        @endif
    </div>
@endsection
