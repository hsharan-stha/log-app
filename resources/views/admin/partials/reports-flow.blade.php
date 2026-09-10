@php
    $current = $current ?? null;
    $links = [
        ['key' => 'hub', 'label' => 'Reports hub', 'route' => 'admin.reports.index'],
        ['key' => 'billing', 'label' => 'Billing', 'route' => 'admin.reports.billing'],
        ['key' => 'students', 'label' => 'Student attendance', 'route' => 'admin.reports.attendance.students'],
        ['key' => 'teachers', 'label' => 'Teacher attendance', 'route' => 'admin.reports.attendance.teachers'],
    ];
@endphp
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}"
                   class="btn btn-sm {{ $current === $link['key'] ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
