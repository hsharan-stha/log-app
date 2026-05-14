@props([
    'time' => null,
    'photoUrl' => null,
    'variant' => 'in', // in | out — badge color
    'size' => 'lg',    // lg | sm
])
@php
    $hasPhoto = ! empty($photoUrl);
    $sizeClass = $size === 'sm' ? 'att-snap att-snap--sm' : 'att-snap att-snap--lg';
    $timeClass = 'att-snap__time ';
    if (! $time) {
        $timeClass .= 'att-snap__time--empty';
    } else {
        $timeClass .= $variant === 'out' ? 'att-snap__time--out' : 'att-snap__time--in';
    }
@endphp
<div class="{{ $sizeClass }}">
    <div class="att-snap__frame">
        @if($hasPhoto)
            <a href="{{ $photoUrl }}" target="_blank" rel="noopener" class="att-snap__link d-block w-100 h-100" title="Open full image">
                <img src="{{ $photoUrl }}" alt="Attendance snapshot" class="att-snap__img" loading="lazy">
            </a>
        @else
            <div class="att-snap__placeholder">No photo</div>
        @endif
    </div>
    <span class="{{ $timeClass }}">{{ $time ?: '—' }}</span>
</div>
