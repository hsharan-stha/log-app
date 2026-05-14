@extends('layouts.admin')

@section('title', 'Edit staff')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.staff.index') }}" class="text-decoration-none small">← Back to staff</a>
        <h1 class="h3 mt-2 mb-0">Edit {{ $staff->name }}</h1>
        <p class="text-muted mb-0">New staff get the default password from config until you set one here. Staff may sign in to register their face at <strong>/my-face</strong> only (not the admin console).</p>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.staff.update', $staff) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $staff->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email (optional)</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $staff->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">New password (optional)</label>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control">
                </div>
                <button class="btn btn-primary" type="submit">Save changes</button>
            </form>
        </div>
    </div>
@endsection
