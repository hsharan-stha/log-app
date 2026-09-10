@extends('layouts.admin')

@section('title', 'Edit student')

@section('content')
    @include('admin.partials.people-flow', [
        'current' => 'students',
        'nextHref' => route('admin.guardians.index'),
        'nextLabel' => 'Guardians',
    ])

    <h1 class="h3 mb-4">Edit {{ $student->name }}</h1>
    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="card border-0 shadow-sm" style="max-width:640px">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="{{ old('name', $student->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Roll number</label>
                <input name="roll_number" class="form-control" value="{{ old('roll_number', $student->roll_number) }}" required maxlength="40">
            </div>
            <div class="mb-3">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select" required>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('class_id', $student->class_id) == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input name="phone" class="form-control" value="{{ old('phone', $student->phone) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Password (optional)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Linked guardians</label>
                <select name="guardian_ids[]" class="form-select" multiple size="6">
                    @foreach($guardians as $g)
                        <option value="{{ $g->id }}" @selected($student->guardians->contains('id', $g->id) || collect(old('guardian_ids', []))->contains($g->id))>{{ $g->name }} ({{ $g->email }})</option>
                    @endforeach
                </select>
                <div class="form-text">Hold Ctrl/Cmd to select multiple. Prefer creating guardians in step 2 if none listed.</div>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
