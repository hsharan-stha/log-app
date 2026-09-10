@extends($layout ?? 'layouts.admin')

@section('title', 'Register face')

@section('content')
    <div class="mb-4">
        @if(($faceRegistrationContext ?? 'admin') === 'self')
            <p class="text-muted small mb-2">Signed in as {{ $staff->name }}</p>
        @else
            <a href="{{ route('admin.staff.index') }}" class="text-decoration-none small">← Back to staff</a>
        @endif
        <h1 class="h3 mt-2 mb-0">Register face @if(($faceRegistrationContext ?? 'admin') !== 'self')for {{ $staff->name }} @endif</h1>
        <p class="text-muted mb-0">Center the face in the frame, ensure good lighting, then capture once models finish loading.</p>
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
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Tips</h2>
                    <ul class="small mb-0 ps-3">
                        <li>Use the same lighting as the attendance kiosk when possible.</li>
                        <li>Remove hats or masks covering the face.</li>
                        <li>Hold still for a second before capturing.</li>
                    </ul>
                </div>
            </div>
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
            const postUrl = @json($facePostUrl ?? (($faceRegistrationContext ?? 'admin') === 'self'
                ? route('teacher.face.store')
                : route('admin.staff.register-face.store', $staff)));
            const redirectFallback = @json($faceRedirectUrl ?? (($faceRegistrationContext ?? 'admin') === 'self'
                ? route('teacher.dashboard')
                : route('admin.staff.index')));

            let modelsReady = false;

            async function loadModels() {
                try {
                    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                    modelsReady = true;
                    statusEl.textContent = 'Models ready. Start the camera when you are set.';
                    btnStart.disabled = false;
                } catch (error) {
                    console.error(error);
                    statusEl.textContent = 'Could not load face models. Reload the page or contact support.';
                }
            }

            async function startCamera() {
                statusEl.textContent = 'Requesting camera…';
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                video.srcObject = stream;
                await video.play();
                btnCapture.disabled = !modelsReady;
                statusEl.textContent = 'Camera live. Align your face and capture.';
            }

            function showAlert(type, message) {
                alertEl.className = `alert alert-${type} mt-3`;
                alertEl.textContent = message;
                alertEl.classList.remove('d-none');
            }

            btnStart.addEventListener('click', () => {
                startCamera().catch((err) => {
                    console.error(err);
                    showAlert('danger', 'Unable to access the webcam. Grant permission and try again.');
                });
            });

            btnCapture.addEventListener('click', async () => {
                if (!modelsReady) return;
                statusEl.textContent = 'Scanning face…';
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    statusEl.textContent = 'No face detected. Adjust lighting or move closer.';
                    showAlert('warning', 'No face detected. Try again.');
                    return;
                }

                const descriptor = Array.from(detection.descriptor);

                try {
                    const response = await fetch(postUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ descriptor }),
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.ok) {
                        throw new Error(payload.message || 'Unable to save descriptor.');
                    }

                    showAlert('success', payload.message || 'Saved!');
                    statusEl.textContent = 'Face saved successfully. Redirecting…';
                    setTimeout(() => {
                        window.location.href = payload.redirect || redirectFallback;
                    }, 900);
                } catch (error) {
                    console.error(error);
                    statusEl.textContent = 'Save failed.';
                    showAlert('danger', error.message || 'Unexpected error while saving.');
                }
            });

            loadModels();
        });
    </script>
@endpush
