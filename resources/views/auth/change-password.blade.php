@extends($layout)

@section('title', 'Change password')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Change password</h1>
        <p class="text-muted mb-0">Update the password for your own account ({{ $user->email ?? $user->name }}).</p>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 480px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="current_password">Current password</label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                        autocomplete="current-password"
                        required
                    >
                    @error('current_password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">New password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                        autocomplete="new-password"
                        required
                    >
                    @error('password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">Confirm new password</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="form-control"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update password</button>
                    <a href="{{ route(auth()->user()->homeRoute()) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
