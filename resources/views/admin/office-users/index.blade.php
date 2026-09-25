@extends('layouts.admin')

@section('title', 'Office users')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Office users</h1>
            <p class="text-muted mb-0">HR, finance, attendance, staff, and other non-teaching accounts.</p>
        </div>
        <a href="{{ route('admin.office-users.create') }}" class="btn btn-primary">Add user</a>
    </div>
    <div class="card border-0 shadow-sm">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Face</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td class="fw-medium">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge text-bg-secondary">{{ $user->roleLabel() }}</span></td>
                    <td>
                        @if($user->face_descriptor)
                            <span class="badge text-bg-success">Registered</span>
                        @else
                            <span class="badge text-bg-warning text-dark">Not registered</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.office-users.register-face', $user) }}">Face</a>
                        <a href="{{ route('admin.office-users.edit', $user) }}">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No office users yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="card-footer">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
