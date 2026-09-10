@extends('layouts.admin')

@section('title', 'People setup')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">People setup</h1>
            <p class="text-muted mb-0">Complete in order: Students → Guardians (link children).</p>
        </div>
        @if($canLinkGuardians)
            <a href="{{ route('admin.guardians.create') }}" class="btn btn-primary btn-lg rounded-3">Add guardian</a>
        @endif
    </div>

    <div class="row g-3 mb-4">
        @foreach($steps as $step)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 academic-step-card {{ !empty($step['locked']) ? 'opacity-75' : '' }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge rounded-pill {{ $step['ready'] ? 'text-bg-success' : 'text-bg-secondary' }}">
                                Step {{ $step['step'] }}
                            </span>
                            <span class="fs-4 fw-semibold lh-1">{{ $step['count'] }}</span>
                        </div>
                        <h2 class="h5 mb-1">{{ $step['title'] }}</h2>
                        <p class="small text-muted mb-3">{{ $step['blurb'] }}</p>
                        <div class="mt-auto d-flex flex-wrap gap-2">
                            <a href="{{ $step['index'] }}" class="btn btn-sm btn-outline-secondary">Open</a>
                            @if(empty($step['locked']))
                                <a href="{{ $step['create'] }}" class="btn btn-sm btn-primary">{{ $step['create_label'] }}</a>
                            @else
                                <span class="btn btn-sm btn-secondary disabled">Add students first</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
                <span class="fw-semibold me-1">Quick flow</span>
                <span class="badge text-bg-light border">1 Student + roll no.</span>
                <span class="text-muted">→</span>
                <span class="badge text-bg-primary">2 Guardian linked</span>
                <span class="small text-muted ms-md-2">
                    {{ $linkedStudents }} of {{ $studentCount }} students have a guardian · {{ $guardianCount }} guardians
                </span>
            </div>
        </div>
    </div>
@endsection
