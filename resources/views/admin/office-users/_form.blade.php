@php
    $officeUser = $officeUser ?? null;
    $roleLabels = [
        'hr' => 'HR',
        'finance' => 'Finance',
        'staff' => 'Staff',
        'other' => 'Other',
        'attendance' => 'Attendance',
    ];
@endphp
<div class="mb-3">
    <label class="form-label" for="name">Name</label>
    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $officeUser?->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="email">Email</label>
    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $officeUser?->email) }}" required>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="phone">Phone</label>
    <input id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $officeUser?->phone) }}">
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="role">Role</label>
    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
        @foreach($roles as $role)
            <option value="{{ $role }}" @selected(old('role', $officeUser?->role) === $role)>{{ $roleLabels[$role] ?? ucfirst($role) }}</option>
        @endforeach
    </select>
    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@if($officeUser)
    <div class="mb-3">
        <label class="form-label" for="password">New password</label>
        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Leave blank to keep the current password.</div>
    </div>
@else
    <p class="small text-muted">Default password: <strong class="text-body">{{ config('staff.default_password') }}</strong></p>
@endif
