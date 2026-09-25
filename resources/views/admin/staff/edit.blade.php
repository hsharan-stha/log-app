@extends('layouts.admin')

@section('title', 'Edit teacher')

@section('content')
    <a href="{{ route('admin.staff.index') }}" class="people-back">← Teachers</a>
    <div class="people-head">
        <div>
            <h1>Edit {{ $staff->name }}</h1>
            <p>Leave the password blank to keep the current one.</p>
        </div>
    </div>

    <div class="card people-form-card">
        <div class="card-body">
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
                    <label class="form-label" for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $staff->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">New password</label>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                </div>
                <div class="people-form__footer">
                    <button class="btn btn-primary" type="submit">Save changes</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.staff.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
