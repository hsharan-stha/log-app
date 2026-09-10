@php($officeUser = $officeUser ?? null)
<div class="mb-3">
    <label class="form-label" for="name">Name</label>
    <input id="name" name="name" class="form-control" value="{{ old('name', $officeUser?->name) }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="email">Email</label>
    <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $officeUser?->email) }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="phone">Phone</label>
    <input id="phone" name="phone" class="form-control" value="{{ old('phone', $officeUser?->phone) }}">
</div>
<div class="mb-3">
    <label class="form-label" for="role">Role</label>
    <select id="role" name="role" class="form-select" required>
        @foreach($roles as $role)
            <option value="{{ $role }}" @selected(old('role', $officeUser?->role) === $role)>{{ ucfirst($role) }}</option>
        @endforeach
    </select>
</div>
@if($officeUser)
<div class="mb-3">
    <label class="form-label" for="password">New password (optional)</label>
    <input id="password" name="password" type="password" class="form-control" minlength="8">
</div>
@else
<p class="small text-muted">Default password: <code>{{ config('staff.default_password') }}</code></p>
@endif
