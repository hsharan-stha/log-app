@extends('layouts.admin')

@section('title', 'Staff')

@section('content')
    @include('admin.partials.academics-flow', [
        'current' => 'teachers',
        'primaryHref' => route('admin.staff.create'),
        'primaryLabel' => 'Add teacher',
        'nextHref' => route('admin.subjects.index'),
        'nextLabel' => 'Subjects',
    ])
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Teachers</h1>
            <p class="text-muted mb-0">Add teachers, register faces for the school kiosk.</p>
        </div>
        <a href="{{ route('admin.staff.bulk-create') }}" class="btn btn-outline-primary">Bulk add</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
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
                        <td class="fw-medium">{{ $member->name }}</td>
                        <td>{{ $member->email ?? '—' }}</td>
                        <td>
                            @if($member->face_descriptor)
                                <span class="badge text-bg-success">Registered</span>
                            @else
                                <span class="badge text-bg-warning text-dark">Missing</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-outline-secondary" href="{{ route('admin.staff.edit', $member) }}">Edit</a>
                                <a class="btn btn-outline-primary" href="{{ route('admin.staff.register-face', $member) }}">Face</a>
                                <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this staff member?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No staff yet. Create the first record.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($staff->hasPages())
            <div class="card-body border-top">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
@endsection
