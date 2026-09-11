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

    <form method="GET" action="{{ route('admin.courses.index') }}" class="mb-3 d-flex flex-wrap align-items-end gap-2">
        <div>
            <label class="form-label mb-1" for="class_id">Class</label>
            <select id="class_id" name="class_id" class="form-select" style="min-width: 140px;">
                <option value="all" @selected($classId === 'all')>All classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected((string) $classId === (string) $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label mb-1" for="subject_id">Subject</label>
            <select id="subject_id" name="subject_id" class="form-select" style="min-width: 140px;">
                <option value="all" @selected($subjectId === 'all')>All subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string) $subjectId === (string) $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label mb-1" for="teacher_id">Teacher</label>
            <select id="teacher_id" name="teacher_id" class="form-select" style="min-width: 160px;">
                <option value="all" @selected($teacherId === 'all')>All teachers</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected((string) $teacherId === (string) $teacher->id)>{{ $teacher->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label mb-1" for="academic_year">Year</label>
            <select id="academic_year" name="academic_year" class="form-select" style="min-width: 140px;">
                <option value="" @selected($year === '')>All years</option>
                @foreach($years as $optionYear)
                    <option value="{{ $optionYear }}" @selected($year === $optionYear)>{{ $optionYear }}</option>
                @endforeach
                @if($year !== '' && ! $years->contains($year))
                    <option value="{{ $year }}" selected>{{ $year }}</option>
                @endif
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Filter</button>
        @if($classId !== 'all' || $subjectId !== 'all' || $teacherId !== 'all' || $year !== '')
            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </form>

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
                <tr><td colspan="6" class="text-center text-muted py-4">No courses found for this filter.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($courses->hasPages())
            <div class="card-footer">{{ $courses->links() }}</div>
        @endif
    </div>
@endsection
