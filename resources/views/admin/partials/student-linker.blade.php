@php
    $selectedIds = collect($selectedIds ?? [])->map(fn ($id) => (int) $id)->all();
@endphp
<div class="mb-3" id="student-linker">
    <label class="form-label">Linked students</label>

    <div class="row g-2 mb-2">
        <div class="col-md-5">
            <select id="student-filter-class" class="form-select">
                <option value="">All classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-7">
            <input id="student-filter-q" type="search" class="form-control" placeholder="Search name or roll number">
        </div>
    </div>

    <div id="student-selected" class="d-flex flex-wrap gap-2 mb-2 @if(empty($selectedIds)) d-none @endif"></div>

    <div class="border rounded bg-white" style="max-height: 280px; overflow: auto;">
        <div id="student-empty" class="text-muted small p-3 d-none">No students match this class / search.</div>
        <ul class="list-group list-group-flush" id="student-results">
            @foreach($students as $s)
                @php
                    $label = trim(($s->roll_number ? $s->roll_number.' · ' : '').$s->name);
                    if ($s->schoolClass) {
                        $label .= ' ('.$s->schoolClass->name.')';
                    }
                @endphp
                <li class="list-group-item student-option py-2"
                    data-id="{{ $s->id }}"
                    data-class-id="{{ $s->class_id ?? '' }}"
                    data-name="{{ \Illuminate\Support\Str::lower($s->name) }}"
                    data-roll="{{ \Illuminate\Support\Str::lower($s->roll_number ?? '') }}"
                    data-label="{{ $label }}">
                    <label class="d-flex align-items-center gap-2 mb-0 w-100" style="cursor:pointer">
                        <input type="checkbox"
                               class="form-check-input student-check m-0"
                               name="student_ids[]"
                               value="{{ $s->id }}"
                               @checked(in_array((int) $s->id, $selectedIds, true))>
                        <span>
                            @if($s->roll_number)<code class="me-1">{{ $s->roll_number }}</code>@endif
                            <span class="fw-medium">{{ $s->name }}</span>
                            <span class="small text-muted">{{ $s->schoolClass?->name }}</span>
                        </span>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="form-text">Filter by class and name/roll, then tick students to link.</div>
</div>

@once
@push('scripts')
<script>
(() => {
    const root = document.getElementById('student-linker');
    if (!root) return;

    const classFilter = document.getElementById('student-filter-class');
    const qFilter = document.getElementById('student-filter-q');
    const empty = document.getElementById('student-empty');
    const selectedBox = document.getElementById('student-selected');
    const options = [...root.querySelectorAll('.student-option')];

    function renderSelected() {
        const checked = options
            .map((li) => li.querySelector('.student-check'))
            .filter((input) => input && input.checked);

        selectedBox.innerHTML = '';
        if (!checked.length) {
            selectedBox.classList.add('d-none');
            return;
        }

        selectedBox.classList.remove('d-none');
        checked.forEach((input) => {
            const li = input.closest('.student-option');
            const chip = document.createElement('span');
            chip.className = 'badge text-bg-light text-dark border d-inline-flex align-items-center gap-1';
            chip.innerHTML = `<span>${li.dataset.label}</span><button type="button" class="btn-close" style="font-size:0.55rem" aria-label="Remove"></button>`;
            chip.querySelector('button').addEventListener('click', () => {
                input.checked = false;
                filterStudents();
                renderSelected();
            });
            selectedBox.appendChild(chip);
        });
    }

    function filterStudents() {
        const classId = classFilter.value;
        const q = (qFilter.value || '').trim().toLowerCase();
        let visible = 0;

        options.forEach((li) => {
            const matchClass = !classId || String(li.dataset.classId) === String(classId);
            const matchQ = !q
                || (li.dataset.name || '').includes(q)
                || (li.dataset.roll || '').includes(q);
            const show = matchClass && matchQ;
            li.classList.toggle('d-none', !show);
            if (show) visible += 1;
        });

        empty.classList.toggle('d-none', visible > 0);
    }

    classFilter.addEventListener('change', filterStudents);
    qFilter.addEventListener('input', filterStudents);
    options.forEach((li) => {
        li.querySelector('.student-check')?.addEventListener('change', renderSelected);
    });

    filterStudents();
    renderSelected();
})();
</script>
@endpush
@endonce
