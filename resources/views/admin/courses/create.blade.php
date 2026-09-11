@extends('layouts.admin')

@section('title', 'Assign course')

@section('content')
    @include('admin.partials.academics-flow', [
        'current' => 'courses',
    ])

    <div class="mb-4">
        <h1 class="h3 mb-1">Assign course</h1>
        <p class="text-muted mb-0">Pick a class, one or more subjects, and a teacher in one step.</p>
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
                        <select name="class_id" class="form-select form-select-lg @error('class_id') is-invalid @enderror" required>
                            <option value="">Select class…</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-semibold">2. Subjects</label>
                            <a href="{{ route('admin.subjects.create') }}" class="small">+ New subject</a>
                        </div>
                        <p class="text-muted small mb-2">Select all subjects to assign to this class (same teacher).</p>
                        <div class="border rounded-3 p-3 @error('subject_ids') border-danger @enderror" style="max-height: 260px; overflow-y: auto;">
                            @forelse($subjects as $subject)
                                <div class="form-check py-1">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="subject_ids[]"
                                        id="subject_{{ $subject->id }}"
                                        value="{{ $subject->id }}"
                                        @checked(collect(old('subject_ids', []))->contains($subject->id))
                                    >
                                    <label class="form-check-label" for="subject_{{ $subject->id }}">{{ $subject->name }}</label>
                                </div>
                            @empty
                                <p class="text-muted mb-0 small">No subjects yet. Create a subject first.</p>
                            @endforelse
                        </div>
                        @error('subject_ids')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        @error('subject_ids.*')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-semibold">3. Teacher</label>
                            <a href="{{ route('admin.staff.create') }}" class="small">+ New teacher</a>
                        </div>
                        <select name="teacher_id" class="form-select form-select-lg @error('teacher_id') is-invalid @enderror" required>
                            <option value="">Select teacher…</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Academic year</label>
                        <input name="academic_year" class="form-control form-control-lg @error('academic_year') is-invalid @enderror" value="{{ old('academic_year', '2026/2027') }}" placeholder="2026/2027">
                        @error('academic_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-lg px-4">Save courses</button>
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
                        <li class="mb-2">Subjects exist (e.g. English, Math)</li>
                        <li>Assign one or more subjects here as courses</li>
                    </ol>
                    <hr>
                    <a href="{{ route('admin.academics.index') }}" class="btn btn-outline-dark w-100">Open academics hub</a>
                </div>
            </div>
        </div>
    </div>
@endsection
