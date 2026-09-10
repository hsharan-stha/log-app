@extends('layouts.admin')

@section('title', 'Assign course')

@section('content')
    @include('admin.partials.academics-flow', [
        'current' => 'courses',
    ])

    <div class="mb-4">
        <h1 class="h3 mb-1">Assign course</h1>
        <p class="text-muted mb-0">Pick class, subject, and teacher in one step.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('admin.courses.store') }}" class="card border-0 shadow-sm">
                @csrf
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-semibold">1. Class</label>
                            <a href="{{ route('admin.classes.create') }}" class="small">+ New class</a>
                        </div>
                        <select name="class_id" class="form-select form-select-lg" required>
                            <option value="">Select class…</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-semibold">2. Subject</label>
                            <a href="{{ route('admin.subjects.create') }}" class="small">+ New subject</a>
                        </div>
                        <select name="subject_id" class="form-select form-select-lg" required>
                            <option value="">Select subject…</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-semibold">3. Teacher</label>
                            <a href="{{ route('admin.staff.create') }}" class="small">+ New teacher</a>
                        </div>
                        <select name="teacher_id" class="form-select form-select-lg" required>
                            <option value="">Select teacher…</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Academic year</label>
                        <input name="academic_year" class="form-control form-control-lg" value="{{ old('academic_year', '2026/2027') }}" placeholder="2026/2027">
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-lg px-4">Save course</button>
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted">Reminder</h2>
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">Class exists (e.g. UKG)</li>
                        <li class="mb-2">Teacher account exists</li>
                        <li class="mb-2">Subject exists (e.g. English)</li>
                        <li>Assign them here as a course</li>
                    </ol>
                    <hr>
                    <a href="{{ route('admin.academics.index') }}" class="btn btn-outline-dark w-100">Open academics hub</a>
                </div>
            </div>
        </div>
    </div>
@endsection
