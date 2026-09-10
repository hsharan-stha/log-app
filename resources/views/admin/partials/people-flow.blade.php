@php
    $current = $current ?? null;
    $steps = [
        ['key' => 'students', 'label' => '1. Students', 'route' => 'admin.students.index'],
        ['key' => 'guardians', 'label' => '2. Guardians', 'route' => 'admin.guardians.index'],
    ];
@endphp
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.people.index') }}" class="btn btn-sm btn-outline-dark">People hub</a>
                @foreach($steps as $step)
                    <a href="{{ route($step['route']) }}"
                       class="btn btn-sm {{ $current === $step['key'] ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $step['label'] }}
                    </a>
                @endforeach
            </div>
            <div class="d-flex flex-wrap gap-2">
                @isset($primaryHref)
                    <a href="{{ $primaryHref }}" class="btn btn-sm btn-primary">{{ $primaryLabel ?? 'Add' }}</a>
                @endisset
                @isset($nextHref)
                    <a href="{{ $nextHref }}" class="btn btn-sm btn-outline-primary">{{ $nextLabel ?? 'Next' }} →</a>
                @endisset
            </div>
        </div>
    </div>
</div>
