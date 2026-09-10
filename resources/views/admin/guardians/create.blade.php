@extends('layouts.admin')

@section('title', 'Add guardian')

@section('content')
    @include('admin.partials.people-flow', ['current' => 'guardians'])

    <h1 class="h3 mb-4">Add guardian</h1>
    <form method="POST" action="{{ route('admin.guardians.store') }}" class="card border-0 shadow-sm" style="max-width:640px">
        @csrf
        <div class="card-body">
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
            <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone') }}"></div>
            @include('admin.partials.student-linker', [
                'students' => $students,
                'classes' => $classes,
                'selectedIds' => old('student_ids', []),
            ])
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.guardians.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
