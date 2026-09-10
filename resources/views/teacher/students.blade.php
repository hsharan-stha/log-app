@extends('layouts.portal')

@section('title', 'My class')

@section('content')
    <h1 class="h3 mb-4">My class</h1>
    @if(! $hasHomeroom)
        <p class="text-muted">No homeroom class assigned.</p>
    @else
        <div class="card border-0 shadow-sm">
            <ul class="list-group list-group-flush">
                @forelse($students as $student)
                    <li class="list-group-item d-flex justify-content-between">
                        <div>
                            <span class="fw-medium">{{ $student->name }}</span>
                            <span class="small text-muted ms-2">{{ $student->schoolClass?->name }}</span>
                        </div>
                        <span class="small text-muted">{{ $student->email ?? '' }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No students.</li>
                @endforelse
            </ul>
            @if($students->hasPages())
                <div class="card-footer">{{ $students->links() }}</div>
            @endif
        </div>
    @endif
@endsection
