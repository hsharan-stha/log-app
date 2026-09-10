@extends('layouts.admin')

@section('title', 'Edit course')

@section('content')
    <h1 class="h3 mb-4">Edit course</h1>
    <form method="POST" action="{{ route('admin.courses.update', $course) }}" class="card border-0 shadow-sm" style="max-width:560px">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select" required>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('class_id', $course->class_id) == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select" required>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id', $course->subject_id) == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select" required>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(old('teacher_id', $course->teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Academic year</label>
                <input name="academic_year" class="form-control" value="{{ old('academic_year', $course->academic_year) }}">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $course->is_active))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
