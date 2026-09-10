@php
    $lesson = $lesson ?? null;
@endphp

<style>
    .lesson-form-section {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        background: #fff;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    .lesson-form-section__title {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .lesson-form-section__hint {
        color: #64748b;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    .lesson-tips {
        background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
        border: 1px solid rgba(37, 99, 235, 0.12);
        border-radius: 1rem;
        padding: 1rem 1.15rem;
        margin-bottom: 1.25rem;
    }
    .lesson-tips ul { margin: 0.5rem 0 0; padding-left: 1.1rem; }
    .lesson-tips li { margin-bottom: 0.25rem; color: #334155; font-size: 0.9rem; }
    .upload-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.85rem; }
    .upload-queue {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
    }
    .upload-card {
        position: relative;
        border: 1px solid rgba(15, 23, 42, 0.1);
        border-radius: 0.85rem;
        overflow: hidden;
        background: #f8fafc;
        min-height: 140px;
    }
    .upload-card img {
        width: 100%;
        height: 110px;
        object-fit: cover;
        display: block;
        background: #e2e8f0;
    }
    .upload-card__file {
        height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.35rem;
        padding: 0.75rem;
        text-align: center;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .upload-card__name {
        font-size: 0.72rem;
        color: #64748b;
        padding: 0.4rem 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .upload-card__remove {
        position: absolute;
        top: 0.35rem;
        right: 0.35rem;
        border: 0;
        border-radius: 999px;
        width: 1.6rem;
        height: 1.6rem;
        background: rgba(15, 23, 42, 0.75);
        color: #fff;
        font-size: 0.85rem;
        line-height: 1;
    }
    .existing-attach {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .existing-attach .upload-card form { margin: 0; }
    .wb-wrap {
        border: 1px solid rgba(15, 23, 42, 0.12);
        border-radius: 0.85rem;
        overflow: hidden;
        background: #f1f5f9;
    }
    .wb-toolbar {
        display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;
        padding: 0.65rem 0.75rem; background: #fff; border-bottom: 1px solid rgba(15,23,42,.08);
    }
    .wb-toolbar .btn-tool.active { background: #0f172a; color: #fff; border-color: #0f172a; }
    .wb-color {
        width: 1.55rem; height: 1.55rem; border-radius: 999px; border: 2px solid #fff;
        box-shadow: 0 0 0 1px rgba(15,23,42,.25); cursor: pointer; padding: 0;
    }
    .wb-color.active { box-shadow: 0 0 0 2px #2563eb; }
    .wb-canvas-box {
        position: relative; width: 100%; background: #fff;
        touch-action: none; cursor: crosshair;
    }
    #wb-canvas { display: block; width: 100%; height: auto; background: #fff; }
    .wb-tabs { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.75rem; }
    .wb-tab {
        border: 1px solid rgba(15,23,42,.12); background: #fff; border-radius: 999px;
        padding: 0.25rem 0.75rem; font-size: 0.85rem;
    }
    .wb-tab.active { background: #2563eb; color: #fff; border-color: #2563eb; }
    .wb-saved-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 0.75rem; margin-top: 0.75rem;
    }
</style>

<div class="lesson-tips">
    <div class="fw-semibold">Make today’s lesson fun</div>
    <ul>
        <li>Start with a short, clear title students will recognize.</li>
        <li>Add 1–3 classroom photos or drawings so the page feels lively.</li>
        <li>Keep practice questions short (3–5 is enough for Nursery–Grade 5).</li>
        <li>Use the whiteboard to draw letters, numbers, or diagrams — save several boards.</li>
        <li>Paste a Meet / YouTube link when you go live.</li>
    </ul>
</div>

<div class="lesson-form-section">
    <div class="lesson-form-section__title">1. Basics</div>
    <div class="lesson-form-section__hint">Date and title students see first.</div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="lesson_date">Lesson date</label>
            <input id="lesson_date" type="date" name="lesson_date" class="form-control" required
                   value="{{ old('lesson_date', optional($lesson)->lesson_date?->format('Y-m-d') ?? now()->toDateString()) }}">
        </div>
        <div class="col-md-8">
            <label class="form-label" for="title">Title</label>
            <input id="title" name="title" class="form-control" required
                   placeholder="e.g. Counting apples · Fun with colours"
                   value="{{ old('title', $lesson->title ?? '') }}">
        </div>
    </div>
</div>

<div class="lesson-form-section">
    <div class="lesson-form-section__title">2. Lesson story</div>
    <div class="lesson-form-section__hint">Write simply — what you did, what to practice at home.</div>
    <label class="form-label" for="body">Description / lesson content</label>
    <textarea id="body" name="body" class="form-control" rows="7"
              placeholder="Today we learned…&#10;&#10;1) …&#10;2) …&#10;&#10;At home: …">{{ old('body', $lesson->body ?? '') }}</textarea>
    <div class="mt-3">
        <label class="form-label" for="test_content">Practice / test (optional)</label>
        <textarea id="test_content" name="test_content" class="form-control" rows="4"
                  placeholder="1) Draw one thing from class&#10;2) Circle the correct answer&#10;3) Say 3 new words">{{ old('test_content', $lesson->test_content ?? '') }}</textarea>
    </div>
</div>

<div class="lesson-form-section">
    <div class="lesson-form-section__title">3. Photos &amp; files</div>
    <div class="lesson-form-section__hint">Add one photo or file at a time. Take a classroom picture, or pick from your gallery / computer.</div>

    @if($lesson && $lesson->relationLoaded('attachments') && $lesson->attachments->isNotEmpty())
        <div class="small text-muted mb-2">Already uploaded</div>
        <div class="existing-attach">
            @foreach($lesson->attachments as $file)
                <div class="upload-card">
                    @if($file->isImage())
                        <a href="{{ $file->url() }}" target="_blank" rel="noopener">
                            <img src="{{ $file->url() }}" alt="{{ $file->original_name }}">
                        </a>
                    @else
                        <div class="upload-card__file">
                            <span>{{ $file->isPdf() ? 'PDF' : 'FILE' }}</span>
                            <a href="{{ $file->url() }}" target="_blank" rel="noopener" class="small">Open</a>
                        </div>
                    @endif
                    <div class="upload-card__name" title="{{ $file->original_name }}">{{ $file->original_name }}</div>
                    <button class="upload-card__remove" type="submit" title="Remove"
                            form="delete-attachment-{{ $file->id }}"
                            onclick="return confirm('Remove this file?')">×</button>
                </div>
            @endforeach
        </div>
    @endif

    <div class="upload-actions">
        <button type="button" class="btn btn-primary" id="btn-take-photo">📷 Take photo</button>
        <button type="button" class="btn btn-outline-primary" id="btn-add-image">Add image</button>
        <button type="button" class="btn btn-outline-secondary" id="btn-add-file">Add PDF / Word</button>
    </div>

    <input type="file" id="capture-photo" class="d-none" accept="image/*" capture="environment">
    <input type="file" id="pick-image" class="d-none" accept="image/*">
    <input type="file" id="pick-file" class="d-none" accept=".pdf,.doc,.docx,image/*">
    <input type="file" id="attachments-input" class="d-none" multiple>

    <div id="upload-queue" class="upload-queue"></div>
    <div id="upload-empty" class="small text-muted">No new files selected yet. Use the buttons above to add them one by one.</div>
</div>

<div class="lesson-form-section">
    <div class="lesson-form-section__title">4. Whiteboard</div>
    <div class="lesson-form-section__hint">Draw like a classroom board. Save each board, then add another. Students will see all saved boards.</div>

    @if($lesson && $lesson->relationLoaded('whiteboards') && $lesson->whiteboards->isNotEmpty())
        <div class="small text-muted mb-2">Saved boards</div>
        <div class="wb-saved-grid mb-3">
            @foreach($lesson->whiteboards as $board)
                <div class="upload-card">
                    <a href="{{ $board->url() }}" target="_blank" rel="noopener">
                        <img src="{{ $board->url() }}" alt="{{ $board->title }}">
                    </a>
                    <div class="upload-card__name">{{ $board->title }}</div>
                    <button class="upload-card__remove" type="submit" title="Remove"
                            form="delete-whiteboard-{{ $board->id }}"
                            onclick="return confirm('Remove this board?')">×</button>
                </div>
            @endforeach
        </div>
    @endif

    <div class="wb-tabs" id="wb-tabs"></div>

    <div class="wb-wrap mb-2">
        <div class="wb-toolbar">
            <button type="button" class="btn btn-sm btn-outline-secondary btn-tool active" data-tool="pen">Pen</button>
            <button type="button" class="btn btn-sm btn-outline-secondary btn-tool" data-tool="eraser">Eraser</button>
            <span class="vr mx-1 d-none d-md-inline"></span>
            @foreach(['#0f172a', '#dc2626', '#2563eb', '#16a34a', '#ca8a04', '#7c3aed'] as $color)
                <button type="button" class="wb-color {{ $color === '#0f172a' ? 'active' : '' }}" data-color="{{ $color }}" style="background: {{ $color }}" title="{{ $color }}"></button>
            @endforeach
            <label class="small text-muted mb-0 ms-1" for="wb-size">Size</label>
            <input id="wb-size" type="range" min="2" max="28" value="5" style="width: 90px;">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="wb-undo">Undo</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="wb-clear">Clear</button>
        </div>
        <div class="wb-canvas-box">
            <canvas id="wb-canvas" width="960" height="540"></canvas>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
        <input type="text" id="wb-title" class="form-control" style="max-width: 220px;" placeholder="Board title" value="Board 1">
        <button type="button" class="btn btn-primary" id="wb-save-board">Add board to lesson</button>
        <button type="button" class="btn btn-outline-primary" id="wb-new-board">+ New blank board</button>
    </div>
    <div class="alert alert-info py-2 small mb-2">
        Draw → click <strong>Add board to lesson</strong> (you can add several) → click <strong>Update lesson</strong> at the bottom.
        You do not need extra database fields.
    </div>
    <div id="wb-queue" class="wb-saved-grid"></div>
    <div id="wb-titles-fields"></div>
    <input type="file" id="whiteboards-input" class="d-none" multiple accept="image/png,image/jpeg,image/webp">
</div>

<div class="lesson-form-section">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
        <div>
            <div class="lesson-form-section__title">5. Live class link</div>
            <div class="lesson-form-section__hint mb-0">Paste Google Meet or YouTube Live. Students get a Join button.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="https://meet.google.com/new" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">New Google Meet</a>
            <a href="https://studio.youtube.com/" class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener">YouTube Studio</a>
        </div>
    </div>
    <input id="recording_url" type="url" name="recording_url" class="form-control"
           placeholder="https://meet.google.com/… or https://youtube.com/…"
           value="{{ old('recording_url', $lesson->recording_url ?? '') }}">
    @php $liveUrl = old('recording_url', $lesson->recording_url ?? null); @endphp
    @if(filled($liveUrl))
        <div class="mt-3">
            <a href="{{ $liveUrl }}" class="btn btn-success" target="_blank" rel="noopener">Start / Join live class</a>
        </div>
    @endif
</div>

<div class="lesson-form-section mb-4">
    <div class="lesson-form-section__title">6. Publish</div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="publish" value="1" id="publish"
               @checked(old('publish', $lesson?->isPublished()))>
        <label class="form-check-label" for="publish">Publish for students</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="notify_students" value="1" id="notify_students"
               @checked(old('notify_students'))>
        <label class="form-check-label" for="notify_students">Email students in this class when publishing</label>
    </div>
</div>

@once
@push('scripts')
<script>
(() => {
    let submitting = false;
    const attachDt = new DataTransfer();

    // --- file uploads ---
    const queueEl = document.getElementById('upload-queue');
    const emptyEl = document.getElementById('upload-empty');
    const hiddenInput = document.getElementById('attachments-input');

    function syncAttachments() {
        if (!hiddenInput) return;
        if (attachDt.files.length) {
            hiddenInput.setAttribute('name', 'attachments[]');
            hiddenInput.files = attachDt.files;
            emptyEl?.classList.add('d-none');
        } else {
            hiddenInput.removeAttribute('name');
            hiddenInput.value = '';
            emptyEl?.classList.remove('d-none');
        }
    }

    if (queueEl && hiddenInput) {
        const maxFiles = 10;
        const maxBytes = 10 * 1024 * 1024;

        function render() {
            queueEl.innerHTML = '';
            [...attachDt.files].forEach((file, index) => {
                const card = document.createElement('div');
                card.className = 'upload-card';
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'upload-card__remove';
                remove.title = 'Remove';
                remove.textContent = '×';
                remove.addEventListener('click', () => {
                    const next = new DataTransfer();
                    [...attachDt.files].forEach((f, i) => { if (i !== index) next.items.add(f); });
                    while (attachDt.items.length) attachDt.items.remove(0);
                    [...next.files].forEach((f) => attachDt.items.add(f));
                    render();
                });
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.alt = file.name;
                    img.src = URL.createObjectURL(file);
                    card.appendChild(img);
                } else {
                    const box = document.createElement('div');
                    box.className = 'upload-card__file';
                    box.innerHTML = `<span>${file.name.toLowerCase().endsWith('.pdf') ? 'PDF' : 'FILE'}</span>`;
                    card.appendChild(box);
                }
                const name = document.createElement('div');
                name.className = 'upload-card__name';
                name.title = file.name;
                name.textContent = file.name;
                card.appendChild(name);
                card.appendChild(remove);
                queueEl.appendChild(card);
            });
            syncAttachments();
        }

        function addFiles(fileList) {
            for (const file of fileList) {
                if (attachDt.files.length >= maxFiles) {
                    alert('You can add up to ' + maxFiles + ' files at once.');
                    break;
                }
                if (file.size > maxBytes) {
                    alert(file.name + ' is larger than 10MB.');
                    continue;
                }
                attachDt.items.add(file);
            }
            render();
        }

        function bindPicker(buttonId, inputId) {
            const btn = document.getElementById(buttonId);
            const input = document.getElementById(inputId);
            if (!btn || !input) return;
            btn.addEventListener('click', () => input.click());
            input.addEventListener('change', () => {
                if (input.files?.length) addFiles(input.files);
                input.value = '';
            });
        }

        bindPicker('btn-take-photo', 'capture-photo');
        bindPicker('btn-add-image', 'pick-image');
        bindPicker('btn-add-file', 'pick-file');
        syncAttachments();
    }

    // --- whiteboard ---
    const canvas = document.getElementById('wb-canvas');
    const wbInput = document.getElementById('whiteboards-input');
    const wbQueue = document.getElementById('wb-queue');
    const wbTitleFields = document.getElementById('wb-titles-fields');
    const wbTabs = document.getElementById('wb-tabs');
    if (!canvas || !wbInput) return;

    const ctx = canvas.getContext('2d');
    const boards = [{ id: 1, title: 'Board 1', dataUrl: null, history: [], dirty: false }];
    let activeId = 1;
    let nextId = 2;
    let tool = 'pen';
    let color = '#0f172a';
    let size = 5;
    let drawing = false;
    let last = null;
    const savedFiles = new DataTransfer();
    const savedTitles = [];

    function fillWhite() {
        ctx.save();
        ctx.globalCompositeOperation = 'source-over';
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.restore();
    }

    function activeBoard() {
        return boards.find((b) => b.id === activeId);
    }

    function snapshot() {
        return canvas.toDataURL('image/png');
    }

    function pushHistory() {
        const board = activeBoard();
        if (!board) return;
        board.history.push(snapshot());
        board.dirty = true;
        if (board.history.length > 40) board.history.shift();
    }

    function loadDataUrl(dataUrl) {
        if (!dataUrl) {
            fillWhite();
            return Promise.resolve();
        }
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => {
                fillWhite();
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                resolve();
            };
            img.onerror = () => resolve();
            img.src = dataUrl;
        });
    }

    function persistActive() {
        const board = activeBoard();
        if (board) board.dataUrl = snapshot();
    }

    function renderTabs() {
        wbTabs.innerHTML = '';
        boards.forEach((board) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'wb-tab' + (board.id === activeId ? ' active' : '');
            btn.textContent = board.title;
            btn.addEventListener('click', () => switchBoard(board.id));
            wbTabs.appendChild(btn);
        });
    }

    async function switchBoard(id) {
        if (id === activeId) return;
        persistActive();
        activeId = id;
        const board = activeBoard();
        document.getElementById('wb-title').value = board.title;
        await loadDataUrl(board.dataUrl);
        renderTabs();
    }

    function pointerPos(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const point = e.touches ? e.touches[0] : e;
        return {
            x: (point.clientX - rect.left) * scaleX,
            y: (point.clientY - rect.top) * scaleY,
        };
    }

    function startDraw(e) {
        e.preventDefault();
        drawing = true;
        pushHistory();
        last = pointerPos(e);
    }

    function moveDraw(e) {
        if (!drawing) return;
        e.preventDefault();
        const pos = pointerPos(e);
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.lineWidth = size;
        if (tool === 'eraser') {
            ctx.globalCompositeOperation = 'destination-out';
            ctx.strokeStyle = 'rgba(0,0,0,1)';
        } else {
            ctx.globalCompositeOperation = 'source-over';
            ctx.strokeStyle = color;
        }
        ctx.beginPath();
        ctx.moveTo(last.x, last.y);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        last = pos;
    }

    function endDraw() {
        drawing = false;
        last = null;
        ctx.globalCompositeOperation = 'source-over';
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', moveDraw);
    window.addEventListener('mouseup', endDraw);
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', moveDraw, { passive: false });
    canvas.addEventListener('touchend', endDraw);

    document.querySelectorAll('.btn-tool').forEach((btn) => {
        btn.addEventListener('click', () => {
            tool = btn.dataset.tool;
            document.querySelectorAll('.btn-tool').forEach((b) => b.classList.toggle('active', b === btn));
        });
    });
    document.querySelectorAll('.wb-color').forEach((btn) => {
        btn.addEventListener('click', () => {
            color = btn.dataset.color;
            tool = 'pen';
            document.querySelectorAll('.wb-color').forEach((b) => b.classList.toggle('active', b === btn));
            document.querySelectorAll('.btn-tool').forEach((b) => b.classList.toggle('active', b.dataset.tool === 'pen'));
        });
    });
    document.getElementById('wb-size')?.addEventListener('input', (e) => {
        size = Number(e.target.value) || 5;
    });
    document.getElementById('wb-clear')?.addEventListener('click', () => {
        pushHistory();
        fillWhite();
    });
    document.getElementById('wb-undo')?.addEventListener('click', () => {
        const board = activeBoard();
        if (!board || !board.history.length) return;
        const prev = board.history.pop();
        loadDataUrl(prev);
        board.dataUrl = prev;
    });
    document.getElementById('wb-title')?.addEventListener('input', (e) => {
        const board = activeBoard();
        if (!board) return;
        board.title = e.target.value.trim() || ('Board ' + board.id);
        renderTabs();
    });

    function syncWhiteboardInput() {
        if (savedFiles.files.length) {
            wbInput.setAttribute('name', 'whiteboards[]');
            wbInput.files = savedFiles.files;
        } else {
            wbInput.removeAttribute('name');
            wbInput.value = '';
        }
    }

    function renderSavedQueue() {
        wbQueue.innerHTML = '';
        wbTitleFields.innerHTML = '';
        savedTitles.forEach((title, index) => {
            const file = savedFiles.files[index];
            if (!file) return;
            const card = document.createElement('div');
            card.className = 'upload-card';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = title;
            const name = document.createElement('div');
            name.className = 'upload-card__name';
            name.textContent = title;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'upload-card__remove';
            remove.textContent = '×';
            remove.addEventListener('click', () => {
                const next = new DataTransfer();
                const nextTitles = [];
                [...savedFiles.files].forEach((f, i) => {
                    if (i !== index) {
                        next.items.add(f);
                        nextTitles.push(savedTitles[i]);
                    }
                });
                while (savedFiles.items.length) savedFiles.items.remove(0);
                [...next.files].forEach((f) => savedFiles.items.add(f));
                savedTitles.length = 0;
                nextTitles.forEach((t) => savedTitles.push(t));
                syncWhiteboardInput();
                renderSavedQueue();
            });
            card.appendChild(img);
            card.appendChild(name);
            card.appendChild(remove);
            wbQueue.appendChild(card);

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'whiteboard_titles[]';
            hidden.value = title;
            wbTitleFields.appendChild(hidden);
        });
        syncWhiteboardInput();
    }

    async function queueBoardFromCanvas(board) {
        if (savedFiles.files.length >= 8) {
            alert('You can add up to 8 new boards at once.');
            return false;
        }
        const dataUrl = board.dataUrl || snapshot();
        const res = await fetch(dataUrl);
        const blob = await res.blob();
        const safeTitle = (board.title || 'Board').replace(/[^\w\- ]+/g, '').trim() || 'Board';
        const file = new File([blob], safeTitle + '.png', { type: 'image/png' });
        savedFiles.items.add(file);
        savedTitles.push(board.title || ('Board ' + (savedTitles.length + 1)));
        board.dirty = false;
        renderSavedQueue();
        return true;
    }

    document.getElementById('wb-save-board')?.addEventListener('click', async () => {
        persistActive();
        await queueBoardFromCanvas(activeBoard());
    });

    document.getElementById('wb-new-board')?.addEventListener('click', () => {
        persistActive();
        const id = nextId++;
        const title = 'Board ' + id;
        boards.push({ id, title, dataUrl: null, history: [], dirty: false });
        activeId = id;
        document.getElementById('wb-title').value = title;
        fillWhite();
        renderTabs();
    });

    const form = canvas.closest('form');
    form?.addEventListener('submit', async (e) => {
        if (submitting) return;
        e.preventDefault();
        submitting = true;

        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.dataset.oldText = btn.textContent;
            btn.textContent = 'Saving…';
        }

        try {
            persistActive();
            const board = activeBoard();
            if (board && board.dirty && board.history.length > 0) {
                await queueBoardFromCanvas(board);
            }
            for (const b of boards) {
                if (b.id === board?.id) continue;
                if (b.dirty && b.dataUrl) {
                    await queueBoardFromCanvas(b);
                }
            }
            syncAttachments();
            syncWhiteboardInput();
            form.submit();
        } catch (err) {
            submitting = false;
            if (btn) {
                btn.disabled = false;
                btn.textContent = btn.dataset.oldText || 'Save';
            }
            alert('Could not prepare whiteboard images. Please click Add board to lesson, then Update lesson.');
        }
    });

    fillWhite();
    renderTabs();
    syncWhiteboardInput();
})();
</script>
@endpush
@endonce
