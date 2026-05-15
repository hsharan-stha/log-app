@extends('layouts.admin')

@section('title', 'Add staff')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.staff.index') }}" class="text-decoration-none small">&larr; Back to staff</a>
        <h1 class="h3 mt-2 mb-0">Add staff</h1>
        <p class="text-muted mb-0">
            A password is set automatically to your configured default
            (<code>STAFF_DEFAULT_PASSWORD</code>, currently <strong class="text-body">{{ config('staff.default_password') }}</strong>).
            You can optionally upload one clear staff photo now to pre-register their face, or staff can sign in later at <strong>/my-face</strong>.
        </p>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.staff.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email (optional)</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="face-upload">Staff photo for face registration (optional)</label>
                    <input id="face-upload" type="file" accept="image/*" class="form-control">
                    <div class="form-text">Use a bright, front-facing photo with only one visible face.</div>
                    @error('descriptor')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <input id="descriptor" name="descriptor" type="hidden" value='@json(old('descriptor'))'>
                <div class="row g-3 align-items-start mb-4">
                    <div class="col-sm-6">
                        <div class="border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="aspect-ratio: 4 / 3;">
                            <img id="face-preview" alt="Selected staff preview" class="w-100 h-100 object-fit-cover d-none">
                            <span id="face-preview-placeholder" class="small text-muted px-3 text-center">No photo selected</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div id="face-status" class="small text-muted">Face verification is optional. If you upload a photo, the system will check that exactly one face can be registered.</div>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Save staff</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script defer src="{{ asset('vendor/face-api.js/0.22.2/face-api.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const MODEL_URL = @json(asset('vendor/face-api.js/weights'));
            const fileInput = document.getElementById('face-upload');
            const preview = document.getElementById('face-preview');
            const placeholder = document.getElementById('face-preview-placeholder');
            const statusEl = document.getElementById('face-status');
                const descriptorInput = document.getElementById('descriptor');
                const submitButton = document.querySelector('button[type="submit"]');
                let previewObjectUrl = null;

            let modelsReady = false;
            let isProcessing = false;

            function setStatus(message, tone = 'muted') {
                statusEl.className = `small text-${tone}`;
                statusEl.textContent = message;
            }

            function setSubmitState() {
                submitButton.disabled = isProcessing;
            }

            async function loadModels() {
                try {
                    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                    modelsReady = true;
                    setStatus('Photo verification ready. Upload a clear portrait if you want to register the face now.');
                } catch (error) {
                    console.error(error);
                    setStatus('Face models could not be loaded. Staff can still be created, but photo verification is unavailable right now.', 'warning');
                }
            }

            async function handleFileChange() {
                const [file] = fileInput.files || [];
                descriptorInput.value = '';

                if (!file) {
                    if (previewObjectUrl) {
                        URL.revokeObjectURL(previewObjectUrl);
                        previewObjectUrl = null;
                    }
                    preview.removeAttribute('src');
                    preview.classList.add('d-none');
                    placeholder.classList.remove('d-none');
                    setStatus('Face verification is optional. If you upload a photo, the system will check that exactly one face can be registered.');
                    return;
                }

                if (previewObjectUrl) {
                    URL.revokeObjectURL(previewObjectUrl);
                }

                previewObjectUrl = URL.createObjectURL(file);
                preview.src = previewObjectUrl;
                preview.classList.remove('d-none');
                placeholder.classList.add('d-none');

                if (!modelsReady) {
                    setStatus('Photo selected, but face models are still loading. Please wait a moment and choose the photo again if needed.', 'warning');
                    return;
                }

                isProcessing = true;
                setSubmitState();
                setStatus('Checking uploaded photo for a single clear face...');

                try {
                    await preview.decode();

                    const detections = await faceapi
                        .detectAllFaces(preview, new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 }))
                        .withFaceLandmarks()
                        .withFaceDescriptors();

                    if (detections.length === 0) {
                        throw new Error('No face detected in the uploaded photo.');
                    }

                    if (detections.length > 1) {
                        throw new Error('More than one face was detected. Please upload a photo with only the staff member.');
                    }

                    descriptorInput.value = JSON.stringify(Array.from(detections[0].descriptor));
                    setStatus('Face verified from the uploaded photo. Saving this staff member will also register their face.', 'success');
                } catch (error) {
                    console.error(error);
                    descriptorInput.value = '';
                    setStatus(error.message || 'Unable to verify the uploaded photo.', 'danger');
                } finally {
                    isProcessing = false;
                    setSubmitState();
                }
            }

            fileInput.addEventListener('change', () => {
                handleFileChange();
            });

            loadModels();
        });
    </script>
@endpush
