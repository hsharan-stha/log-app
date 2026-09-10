@extends('layouts.admin')

@section('title', 'Add student')

@section('content')
    @include('admin.partials.people-flow', [
        'current' => 'students',
        'nextHref' => route('admin.guardians.index'),
        'nextLabel' => 'Guardians',
    ])

    <h1 class="h3 mb-4">Add student</h1>
    <form method="POST" action="{{ route('admin.students.store') }}" class="card border-0 shadow-sm" style="max-width:560px">
        @csrf
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Roll number</label>
                <input name="roll_number" class="form-control" value="{{ old('roll_number') }}" required maxlength="40" placeholder="e.g. UKG-01">
                <div class="form-text">Unique within the selected class. Used for search and ID.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select" required>
                    <option value="">Select…</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Email (for portal login)</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <p class="small text-muted">Default password: from config (usually <code>password</code>)</p>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
