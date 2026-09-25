@extends('layouts.admin')

@section('title', 'Staff users')

@section('content')
    <div class="people-head">
        <div>
            <h1>Staff users</h1>
            <p>HR, finance, attendance, staff, and other non-teaching accounts.</p>
        </div>
        <div class="people-head__actions">
            <a href="{{ route('admin.office-users.create') }}" class="btn btn-primary">Add staff user</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.office-users.index') }}" class="card people-filters">
        <div class="card-body">
            <div class="people-filters__grid">
                <div>
                    <label class="form-label" for="name">Name</label>
                    <input id="name" name="name" type="search" class="form-control" value="{{ $name }}" placeholder="Search by name">
                </div>
                <div>
                    <label class="form-label" for="email">Email</label>
                    <input id="email" name="email" type="search" class="form-control" value="{{ $email }}" placeholder="Search by email">
                </div>
                <div>
                    <label class="form-label" for="role">Role</label>
                    <select id="role" name="role" class="form-select">
                        <option value="">All roles</option>
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption }}" @selected($role === $roleOption)>{{ ucfirst($roleOption) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="people-filters__actions">
                    <button class="btn btn-primary" type="submit">Search</button>
                    @if($name !== '' || $email !== '' || $role !== '')
                        <a href="{{ route('admin.office-users.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="card people-table-card">
        <table class="table table-hover align-middle people-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Face</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td data-label="Name">
                        <div class="people-person">
                            <span class="people-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
                            <span class="fw-medium">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="Role"><span class="badge text-bg-secondary">{{ $user->roleLabel() }}</span></td>
                    <td data-label="Face">
                        @if($user->face_descriptor)
                            <span class="badge text-bg-success">Registered</span>
                        @else
                            <span class="badge text-bg-warning text-dark">Not registered</span>
                        @endif
                    </td>
                    <td class="people-actions-cell">
                        <div class="people-actions">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.office-users.edit', $user) }}">Edit</a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.office-users.register-face', $user) }}">Face</a>
                            @unless($user->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.office-users.destroy', $user) }}" onsubmit="return confirm('Delete this staff user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No staff users found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="card-body border-top">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
