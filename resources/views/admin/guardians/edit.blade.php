@extends('layouts.admin')

@section('title', 'Edit guardian')

@section('content')
    @include('admin.partials.people-flow', ['current' => 'guardians'])

    <h1 class="h3 mb-4">Edit {{ $guardian->name }}</h1>
    <form method="POST" action="{{ route('admin.guardians.update', $guardian) }}" class="card border-0 shadow-sm" style="max-width:640px">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $guardian->name) }}" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $guardian->email) }}" required></div>
            <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $guardian->phone) }}"></div>
            <div class="mb-3"><label class="form-label">Password (optional)</label><input type="password" name="password" class="form-control"></div>
            @include('admin.partials.student-linker', [
                'students' => $students,
                'classes' => $classes,
                'selectedIds' => old('student_ids', $guardian->wards->pluck('id')->all()),
            ])
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.guardians.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
