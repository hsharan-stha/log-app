@extends('layouts.admin')

@section('title', 'Add staff')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.staff.index') }}" class="text-decoration-none small">← Back to staff</a>
        <h1 class="h3 mt-2 mb-0">Add staff</h1>
        <p class="text-muted mb-0">
            A password is set automatically to your configured default
            (<code>STAFF_DEFAULT_PASSWORD</code> → currently <strong class="text-body">{{ config('staff.default_password') }}</strong>).
            Staff can sign in at <strong>/login</strong> to register their face at <strong>/my-face</strong>.
        </p>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.staff.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email (optional)</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary" type="submit">Save staff</button>
            </form>
        </div>
    </div>
@endsection
