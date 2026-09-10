@extends('layouts.portal')

@section('title', 'Register my face')

@section('content')
    @php
        $staff = auth()->user();
        $faceRegistrationContext = 'self';
        $layout = 'layouts.portal';
    @endphp
    <div class="mb-4">
        <p class="text-muted small mb-2">Signed in as {{ $staff->name }}</p>
        <h1 class="h3 mt-2 mb-0">Register my face</h1>
        <p class="text-muted mb-0">Used only on the school kiosk for check-in/out. Portal login cannot face-scan.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="ratio ratio-4x3 bg-dark rounded shadow-sm overflow-hidden position-relative">
                <video id="video" class="w-100 h-100 object-fit-cover" autoplay muted playsinline></video>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button id="btn-start" class="btn btn-outline-secondary" type="button">Start camera</button>
                <button id="btn-capture" class="btn btn-primary" type="button" disabled>Capture &amp; save</button>
            </div>
            <div id="status" class="small text-muted mt-2">Loading face models…</div>
            <div id="alert" class="alert d-none mt-3" role="alert"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script defer src="{{ asset('vendor/face-api.js/0.22.2/face-api.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const MODEL_URL = @json(asset('vendor/face-api.js/weights'));
            const video = document.getElementById('video');
            const btnStart = document.getElementById('btn-start');
            const btnCapture = document.getElementById('btn-capture');
            const statusEl = document.getElementById('status');
            const alertEl = document.getElementById('alert');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const postUrl = @json(route('teacher.face.store'));
            const redirectFallback = @json(route('teacher.dashboard'));
            let modelsReady = false;

            async function loadModels() {
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                modelsReady = true;
                statusEl.textContent = 'Models ready.';
                btnStart.disabled = false;
            }

            btnStart.addEventListener('click', async () => {
                video.srcObject = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                await video.play();
                btnCapture.disabled = !modelsReady;
            });

            btnCapture.addEventListener('click', async () => {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 })).withFaceLandmarks().withFaceDescriptor();
                if (!detection) { alertEl.className = 'alert alert-warning mt-3'; alertEl.textContent = 'No face'; alertEl.classList.remove('d-none'); return; }
                const response = await fetch(postUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ descriptor: Array.from(detection.descriptor) }),
                });
                const payload = await response.json();
                alertEl.className = 'alert alert-' + (payload.ok ? 'success' : 'danger') + ' mt-3';
                alertEl.textContent = payload.message || 'Done';
                alertEl.classList.remove('d-none');
                if (payload.ok) setTimeout(() => location.href = payload.redirect || redirectFallback, 800);
            });

            loadModels().catch(() => { statusEl.textContent = 'Model load failed'; });
        });
    </script>
@endpush
