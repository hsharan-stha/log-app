@extends('layouts.admin')

@section('title', 'Students')

@section('content')
    @include('admin.partials.people-flow', [
        'current' => 'students',
        'primaryHref' => route('admin.students.create'),
        'primaryLabel' => 'Add student',
        'nextHref' => route('admin.guardians.index'),
        'nextLabel' => 'Guardians',
    ])

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Students</h1>
            <p class="text-muted mb-0">Add students with class and roll number, then link guardians.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.students.index') }}" class="mb-3 d-flex flex-wrap align-items-end gap-2">
        <div>
            <label class="form-label mb-1" for="q">Search</label>
            <input id="q" name="q" type="search" class="form-control" style="min-width: 220px;"
                   value="{{ $q }}" placeholder="Name or roll number">
        </div>
        <div>
            <label class="form-label mb-1" for="class_id">Class</label>
            <select id="class_id" name="class_id" class="form-select">
                <option value="all" @selected($classId === 'all')>All classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected((string) $classId === (string) $class->id)>{{ $class->name }}</option>
                @endforeach
                <option value="unassigned" @selected($classId === 'unassigned')>Unassigned</option>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Search</button>
        @if($q !== '' || $classId !== 'all')
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr><th>Roll no.</th><th>Name</th><th>Class</th><th>Email</th><th>Face</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($students as $student)
                    <tr>
                        <td><code>{{ $student->roll_number ?? '—' }}</code></td>
                        <td class="fw-medium">{{ $student->name }}</td>
                        <td>{{ $student->schoolClass?->name ?? '—' }}</td>
                        <td>{{ $student->email ?? '—' }}</td>
                        <td>
                            @if($student->face_descriptor)
                                <span class="badge text-bg-success">Registered</span>
                            @else
                                <span class="badge text-bg-warning text-dark">Missing</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.students.register-face', $student) }}" class="btn btn-sm btn-outline-primary">Face</a>
                            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form class="d-inline" method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Delete student?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No students found for this filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
            <div class="card-footer">{{ $students->links() }}</div>
        @endif
    </div>
@endsection
