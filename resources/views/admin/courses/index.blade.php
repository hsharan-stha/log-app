@extends('layouts.admin')

@section('title', 'Courses')

@section('content')
    @include('admin.partials.academics-flow', [
        'current' => 'courses',
        'primaryHref' => route('admin.courses.create'),
        'primaryLabel' => 'Assign course',
        'nextHref' => route('admin.academics.index'),
        'nextLabel' => 'Setup hub',
    ])

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Courses</h1>
            <p class="text-muted mb-0">Final step: class + subject + teacher.</p>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
            <tr><th>Class</th><th>Subject</th><th>Teacher</th><th>Year</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($courses as $course)
                <tr>
                    <td>{{ $course->schoolClass?->name }}</td>
                    <td class="fw-medium">{{ $course->subject?->name }}</td>
                    <td>{{ $course->teacher?->name }}</td>
                    <td>{{ $course->academic_year ?? '—' }}</td>
                    <td>
                        @if($course->is_active)
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('Delete course and its lessons?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No courses yet. Create class, teacher, and subject first, then assign.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($courses->hasPages())
            <div class="card-footer">{{ $courses->links() }}</div>
        @endif
    </div>
@endsection
