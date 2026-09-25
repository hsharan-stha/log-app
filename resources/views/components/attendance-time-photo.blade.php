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
            <button type="button" class="att-snap__link d-block w-100 h-100" data-att-photo="{{ $photoUrl }}" title="View full image">
                <img src="{{ $photoUrl }}" alt="Attendance snapshot" class="att-snap__img" loading="lazy">
            </button>
        @else
            <div class="att-snap__placeholder">No photo</div>
        @endif
    </div>
    <span class="{{ $timeClass }}">{{ $time ?: '—' }}</span>
</div>
@once
    <div id="att-photo-dialog" class="att-photo-dialog" hidden>
        <button type="button" class="att-photo-dialog__close" data-att-photo-close aria-label="Close">Close</button>
        <img id="att-photo-dialog-img" alt="Attendance snapshot">
    </div>
    <style>
        .att-snap__link {
            border: 0; padding: 0; background: transparent; cursor: zoom-in;
        }
        .att-photo-dialog {
            position: fixed; inset: 0; z-index: 2000;
            display: flex; align-items: center; justify-content: center;
            background: rgba(0, 0, 0, 0.92);
            padding: 0.75rem;
        }
        .att-photo-dialog[hidden] { display: none !important; }
        .att-photo-dialog img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        .att-photo-dialog__close {
            position: absolute; top: 0.75rem; right: 0.75rem;
            border: 0; border-radius: 999px;
            background: #fff; color: #111;
            padding: 0.4rem 0.9rem; font-weight: 600;
        }
    </style>
    <script>
        document.addEventListener('click', (event) => {
            const opener = event.target.closest('[data-att-photo]');
            const dialog = document.getElementById('att-photo-dialog');
            const image = document.getElementById('att-photo-dialog-img');
            if (!dialog || !image) return;

            if (opener) {
                image.src = opener.getAttribute('data-att-photo');
                dialog.hidden = false;
                document.body.style.overflow = 'hidden';
                return;
            }

            if (event.target === dialog || event.target.closest('[data-att-photo-close]')) {
                dialog.hidden = true;
                image.removeAttribute('src');
                document.body.style.overflow = '';
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            const dialog = document.getElementById('att-photo-dialog');
            const image = document.getElementById('att-photo-dialog-img');
            if (!dialog || dialog.hidden) return;
            dialog.hidden = true;
            image.removeAttribute('src');
            document.body.style.overflow = '';
        });
    </script>
@endonce
