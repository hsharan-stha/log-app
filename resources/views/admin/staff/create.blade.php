@extends('layouts.admin')

@section('title', 'Add teacher')

@section('content')
    <a href="{{ route('admin.staff.index') }}" class="people-back">← Teachers</a>
    <div class="people-head">
        <div>
            <h1>Add teacher</h1>
            <p>The account uses the school default password until you change it.</p>
        </div>
    </div>

    <div class="card people-form-card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.staff.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Optional. Needed if this teacher will sign in.</div>
                </div>
                <p class="small text-muted">Default password: <strong class="text-body">{{ config('staff.default_password') }}</strong></p>
                <div class="people-form__footer">
                    <button class="btn btn-primary" type="submit">Save teacher</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.staff.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
