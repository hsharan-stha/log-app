@extends('layouts.admin')

@section('title', 'Bulk add staff')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.staff.index') }}" class="text-decoration-none small">← Back to staff</a>
        <h1 class="h3 mt-2 mb-0">Bulk add staff</h1>
        <p class="text-muted mb-0">
            One person per line: <code>Name</code> or <code>Name, email@example.com</code>.
            Each account gets the default password from <code>STAFF_DEFAULT_PASSWORD</code> in <code>.env</code>
            (currently <strong class="text-body">{{ config('staff.default_password') }}</strong>).
        </p>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 720px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.staff.bulk-create.store') }}">
                @csrf
                @if($errors->has('list'))
                    <div class="alert alert-danger small mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->get('list') as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label" for="list">Staff list</label>
                    <textarea id="list" name="list" rows="14" required
                              class="form-control font-monospace {{ $errors->has('list') ? 'is-invalid' : '' }}"
                              placeholder="Ada Lovelace, ada@example.com&#10;Alan Turing&#10;Grace Hopper, grace@example.com">{{ old('list') }}</textarea>
                    <div class="form-text">Up to 500 lines. Emails must be unique in the list and in the database.</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit">Create all</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.staff.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
