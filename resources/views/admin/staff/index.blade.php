@extends('layouts.admin')

@section('title', 'Teachers')

@section('content')
    @include('admin.partials.academics-flow', [
        'current' => 'teachers',
        'primaryHref' => route('admin.staff.create'),
        'primaryLabel' => 'Add teacher',
        'nextHref' => route('admin.subjects.index'),
        'nextLabel' => 'Subjects',
        'hideFlowSteps' => true,
    ])

    <div class="people-head">
        <div>
            <h1>Teachers</h1>
            <p>Add teachers and register faces for the school kiosk.</p>
        </div>
        <div class="people-head__actions">
            <a href="{{ route('admin.staff.bulk-create') }}" class="btn btn-outline-primary">Bulk add</a>
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">Add teacher</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.staff.index') }}" class="card people-filters">
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
                <div class="people-filters__actions">
                    <button class="btn btn-primary" type="submit">Search</button>
                    @if($name !== '' || $email !== '')
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">Clear</a>
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
                <th>Face</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($staff as $member)
                <tr>
                    <td data-label="Name">
                        <div class="people-person">
                            <span class="people-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($member->name, 0, 1)) }}</span>
                            <span class="fw-medium">{{ $member->name }}</span>
                        </div>
                    </td>
                    <td data-label="Email">{{ $member->email ?? '—' }}</td>
                    <td data-label="Face">
                        @if($member->face_descriptor)
                            <span class="badge text-bg-success">Registered</span>
                        @else
                            <span class="badge text-bg-warning text-dark">Not registered</span>
                        @endif
                    </td>
                    <td class="people-actions-cell">
                        <div class="people-actions">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.staff.edit', $member) }}">Edit</a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.staff.register-face', $member) }}">Face</a>
                            <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" onsubmit="return confirm('Delete this teacher?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No teachers found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        @if($staff->hasPages())
            <div class="card-body border-top">{{ $staff->links() }}</div>
        @endif
    </div>
@endsection
