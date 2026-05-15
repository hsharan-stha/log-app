<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff attendance — {{ config('app.name', 'Face Attendance') }}</title>
    <link href="{{ asset('vendor/bootstrap/5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        html {
            height: 100%;
            overflow: hidden;
        }
        body {
            height: 100%;
            margin: 0;
        }
        .attendance-kiosk {
            height: 100dvh;
            max-height: 100dvh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding-bottom: env(safe-area-inset-bottom, 0);
        }
        .attendance-kiosk__header {
            flex: 0 0 auto;
            padding: 0.5rem 0.75rem;
            padding-top: max(0.5rem, env(safe-area-inset-top, 0));
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }
        .attendance-kiosk__header h1 {
            font-size: clamp(1rem, 3.5vw, 1.25rem);
            line-height: 1.2;
            margin: 0;
        }
        .attendance-kiosk__header p {
            font-size: 0.7rem;
            line-height: 1.25;
            margin: 0.15rem 0 0;
            color: #6c757d;
        }
        .attendance-kiosk__main {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            padding: 0.5rem 0.75rem;
            gap: 0.5rem;
        }
        .attendance-kiosk__video-wrap {
            flex: 1 1 0;
            min-height: 0;
            position: relative;
            border-radius: 0.5rem;
            overflow: hidden;
            background: #212529;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.12);
        }
        .attendance-kiosk__video-wrap video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .attendance-kiosk__video-wrap #face-overlay {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
        }
        .attendance-kiosk__controls {
            flex: 0 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }
        .attendance-kiosk__footer {
            flex: 0 0 auto;
            font-size: 0.65rem;
            line-height: 1.2;
            color: #868e96;
            text-align: center;
            padding: 0 0.5rem 0.35rem;
        }
        .attendance-kiosk__status {
            flex: 0 0 auto;
            font-size: 0.75rem;
            line-height: 1.25;
            text-align: center;
            color: #6c757d;
            min-height: 1.25rem;
        }
        .attendance-kiosk__result {
            flex: 0 0 auto;
            font-size: 0.8rem;
            padding: 0.35rem 0.5rem;
            margin: 0 !important;
            max-height: 4.5rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }
    </style>
</head>
<body class="bg-light attendance-kiosk">
<header class="attendance-kiosk__header bg-white">
    <h1 class="fw-semibold">Staff attendance</h1>
    <p class="mb-0">Stand in view — after ~3 seconds with a steady face, check-in or check-out runs automatically.</p>
</header>

<div class="attendance-kiosk__main">
    <div class="attendance-kiosk__video-wrap">
        <video id="video" autoplay muted playsinline webkit-playsinline></video>
        <canvas id="face-overlay" aria-hidden="true"></canvas>
    </div>
    <canvas id="snapshot-canvas" class="d-none" aria-hidden="true"></canvas>

    <div class="attendance-kiosk__controls">
        <button id="btn-start" class="btn btn-outline-secondary btn-sm px-3 d-none" type="button">Try camera again</button>
    </div>

    <div id="status" class="attendance-kiosk__status">Loading face models…</div>
    <div id="result" class="alert attendance-kiosk__result d-none text-center py-2" role="alert"></div>
</div>

<p class="attendance-kiosk__footer mb-0">Need help? Ask an admin to register your face.</p>

<script src="{{ asset('vendor/bootstrap/5.3.3/js/bootstrap.bundle.min.js') }}"></script>
<script defer src="{{ asset('vendor/face-api.js/0.22.2/face-api.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const MODEL_URL = @json(asset('vendor/face-api.js/weights'));
        const video = document.getElementById('video');
        const overlay = document.getElementById('face-overlay');
        const overlayCtx = overlay.getContext('2d');
        const canvas = document.getElementById('snapshot-canvas');
        const btnStart = document.getElementById('btn-start');
        const statusEl = document.getElementById('status');
        const resultEl = document.getElementById('result');
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const verifyUrl = @json(route('attendance.verify'));

        const STABILITY_MS = 2600;
        /** iPhone/iPad WKWebView: faster cadence + smaller detector input = quicker first frame + box */
        const isLikelyIOS = /iPad|iPhone|iPod/.test(navigator.userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        /** Capacitor-injected bridge when app loads this URL inside the native wrapper */
        const isNativeWebViewShell = typeof window.Capacitor !== 'undefined';

        /** iOS / WKWebView: recycle camera on foreground; desktop tabs only pause/resume playback */
        const aggressiveCameraLifecycle = isLikelyIOS || isNativeWebViewShell;

        let DETECT_INTERVAL_MS = isLikelyIOS ? 260 : 420;
        const COOLDOWN_MS = 5500;
        const LOST_FACE_RESET_MS = 900;

        let modelsReady = false;
        let cameraLive = false;
        let verifying = false;
        let tickBusy = false;
        let resumeLock = false;
        let layoutSyncBound = false;
        /** @type {ResizeObserver|null} */
        let videoWrapResizeObserver = null;

        let cooldownUntil = 0;
        let faceStableSince = null;
        let lastFaceSeenAt = 0;
        let tickTimer = null;
        let audioCtx = null;

        let detectorOpts = null;

        function getDetectorOptions() {
            if (! detectorOpts) {
                const inputSize = isLikelyIOS ? 320 : 416;
                const scoreThreshold = isLikelyIOS ? 0.45 : 0.5;
                detectorOpts = new faceapi.TinyFaceDetectorOptions({ inputSize, scoreThreshold });
            }

            return detectorOpts;
        }

        async function loadModels() {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                modelsReady = true;
                statusEl.textContent = 'Models ready.';
            } catch (error) {
                console.error(error);
                statusEl.textContent = 'Could not load models. Reload or contact support.';
            }
        }

        async function ensureAudio() {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) {
                return null;
            }
            if (!audioCtx) {
                audioCtx = new Ctx();
            }
            if (audioCtx.state === 'suspended') {
                await audioCtx.resume();
            }
            return audioCtx;
        }

        function playCheckinChime() {
            ensureAudio().then((ctx) => {
                if (!ctx) {
                    return;
                }
                const t0 = ctx.currentTime;
                const blip = (freq, start, len) => {
                    const o = ctx.createOscillator();
                    const g = ctx.createGain();
                    o.type = 'sine';
                    o.frequency.setValueAtTime(freq, t0 + start);
                    g.gain.setValueAtTime(0.0001, t0 + start);
                    g.gain.exponentialRampToValueAtTime(0.14, t0 + start + 0.02);
                    g.gain.exponentialRampToValueAtTime(0.0001, t0 + start + len);
                    o.connect(g);
                    g.connect(ctx.destination);
                    o.start(t0 + start);
                    o.stop(t0 + start + len + 0.03);
                };
                blip(1047, 0, 0.1);
                blip(784, 0.12, 0.12);
            }).catch(() => {});
        }

        function resizeOverlay() {
            const w = video.clientWidth;
            const h = video.clientHeight;
            if (!w || !h) {
                return;
            }
            overlayCtx.setTransform(1, 0, 0, 1, 0, 0);
            overlay.width = w;
            overlay.height = h;
        }

        /**
         * iOS Safari sometimes leaves video.videoWidth === 0 until a frame pumps; bind all useful events.
         */
        function waitForVideoDimensions(videoEl, timeoutMs = 2500) {
            return new Promise((resolve) => {
                if (videoEl.videoWidth > 2 && videoEl.videoHeight > 2) {
                    resolve();
                    return;
                }

                let done = false;
                const finish = () => {
                    if (!done) {
                        done = true;
                        cleanup();
                        resolve();
                    }
                };

                /** @type {number|null} */
                let vfcHandle = null;
                const cleanup = () => {
                    videoEl.removeEventListener('loadeddata', tick);
                    videoEl.removeEventListener('loadedmetadata', tick);
                    videoEl.removeEventListener('playing', tick);
                    videoEl.removeEventListener('canplay', tick);
                    if (vfcHandle != null && typeof videoEl.cancelVideoFrameCallback === 'function') {
                        try {
                            videoEl.cancelVideoFrameCallback(vfcHandle);
                        } catch (ignoreErr) {
                            /* noop */
                        }
                    }
                    vfcHandle = null;
                    window.clearTimeout(timer);
                };

                const tick = () => {
                    if (videoEl.videoWidth > 2 && videoEl.videoHeight > 2) {
                        finish();
                    }
                };

                const timer = window.setTimeout(finish, timeoutMs);

                videoEl.addEventListener('loadeddata', tick);
                videoEl.addEventListener('loadedmetadata', tick);
                videoEl.addEventListener('playing', tick);
                videoEl.addEventListener('canplay', tick);

                if ('requestVideoFrameCallback' in HTMLVideoElement.prototype) {
                    const onFrame = () => {
                        tick();
                        if (! done && videoEl.srcObject) {
                            vfcHandle = videoEl.requestVideoFrameCallback(onFrame);
                        }
                    };
                    vfcHandle = videoEl.requestVideoFrameCallback(onFrame);
                }
            });
        }

        function bindLayoutSyncOnce() {
            if (layoutSyncBound) {
                return;
            }
            layoutSyncBound = true;

            window.addEventListener('resize', resizeOverlay);
            video.addEventListener('loadedmetadata', resizeOverlay);

            /** Some WebKit builds fire `resize` on the video element when intrinsic size changes */
            video.addEventListener('resize', resizeOverlay);

            const wrap = video.closest('.attendance-kiosk__video-wrap');
            if (wrap && typeof ResizeObserver !== 'undefined') {
                videoWrapResizeObserver = new ResizeObserver(() => resizeOverlay());
                videoWrapResizeObserver.observe(wrap);
            }
        }

        async function runBurstDetection(count = isLikelyIOS ? 8 : 4) {
            for (let i = 0; i < count && cameraLive && modelsReady && !verifying; i += 1) {
                await detectionTick(Date.now());
                await new Promise((r) => {
                    window.setTimeout(r, isLikelyIOS ? 80 : 110);
                });
            }
        }

        function showResult(type, message) {
            resultEl.className = `alert alert-${type} attendance-kiosk__result text-center`;
            resultEl.textContent = message;
            resultEl.classList.remove('d-none');
        }

        function captureSnapshotFromVideo() {
            const w = video.videoWidth;
            const h = video.videoHeight;
            if (!w || !h) {
                return null;
            }
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, w, h);
            return canvas.toDataURL('image/jpeg', 0.82);
        }

        async function verifyWithDetection(detection) {
            if (verifying || !modelsReady) {
                return;
            }
            verifying = true;
            resultEl.classList.add('d-none');
            statusEl.textContent = 'Verifying…';

            const descriptor = Array.from(detection.descriptor);
            const snapshot = captureSnapshotFromVideo();

            try {
                const response = await fetch(verifyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ descriptor, snapshot }),
                });

                const payload = await response.json();

                if (!response.ok || payload.ok === false) {
                    throw new Error(payload.message || 'Verification failed.');
                }

                const name = payload.staff_name ? `${payload.staff_name}: ` : '';
                statusEl.textContent = 'Success';
                showResult('success', `${name}${payload.message}`);

                if (payload.action === 'checkin') {
                    playCheckinChime();
                }

                cooldownUntil = Date.now() + COOLDOWN_MS;
                faceStableSince = null;
            } catch (error) {
                console.error(error);
                statusEl.textContent = 'Something went wrong.';
                showResult('danger', error.message || 'Unexpected error.');
                cooldownUntil = Date.now() + 2500;
                faceStableSince = null;
            } finally {
                verifying = false;
            }
        }

        function drawOverlay(detection) {
            resizeOverlay();
            overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
            if (!detection || !video.videoWidth) {
                return;
            }
            const box = detection.detection.box;
            const sx = overlay.width / video.videoWidth;
            const sy = overlay.height / video.videoHeight;
            const x = box.x * sx;
            const y = box.y * sy;
            const bw = box.width * sx;
            const bh = box.height * sy;
            overlayCtx.strokeStyle = 'rgba(72, 209, 204, 0.95)';
            overlayCtx.lineWidth = 3;
            overlayCtx.setLineDash([]);
            overlayCtx.strokeRect(x, y, bw, bh);
        }

        async function detectionTick(now) {
            if (!cameraLive || !modelsReady || verifying) {
                return;
            }

            if (now < cooldownUntil) {
                const left = Math.ceil((cooldownUntil - now) / 1000);
                statusEl.textContent = `Please wait ${left}s before the next scan.`;
                drawOverlay(null);
                return;
            }

            let detection = null;
            try {
                detection = await faceapi
                    .detectSingleFace(video, getDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();
            } catch (e) {
                console.warn(e);
            }

            if (detection) {
                lastFaceSeenAt = now;
                drawOverlay(detection);
                if (faceStableSince === null) {
                    faceStableSince = now;
                }
                const held = now - faceStableSince;
                if (held < STABILITY_MS) {
                    const sec = Math.max(1, Math.ceil((STABILITY_MS - held) / 1000));
                    statusEl.textContent = `Face detected — hold still (${sec}s)`;
                } else {
                    await verifyWithDetection(detection);
                }
            } else {
                drawOverlay(null);
                if (faceStableSince !== null && now - lastFaceSeenAt > LOST_FACE_RESET_MS) {
                    faceStableSince = null;
                }
                if (faceStableSince === null) {
                    statusEl.textContent = 'Position your face in the frame.';
                }
            }
        }

        function startAttendanceLoop() {
            stopAttendanceLoop();
            tickTimer = window.setInterval(async () => {
                if (!cameraLive || tickBusy) {
                    return;
                }
                tickBusy = true;
                try {
                    await detectionTick(Date.now());
                } finally {
                    tickBusy = false;
                }
            }, DETECT_INTERVAL_MS);
        }

        function stopAttendanceLoop() {
            if (tickTimer !== null) {
                clearInterval(tickTimer);
                tickTimer = null;
            }
            overlayCtx.setTransform(1, 0, 0, 1, 0, 0);
            overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
        }

        function stopCameraTracks() {
            const stream = video.srcObject;
            if (stream) {
                stream.getTracks().forEach((t) => {
                    try {
                        t.stop();
                    } catch (ignore) {
                        /* noop */
                    }
                });
                video.srcObject = null;
            }
            cameraLive = false;
            video.pause();
        }

        /**
         * WKWebView / iOS: backgrounding freezes or kills tracks; discard on hide & acquire fresh stream on show.
         */
        async function resumePipelineAfterForeground() {
            if (! modelsReady || resumeLock) {
                return;
            }
            if (verifying) {
                window.setTimeout(() => resumePipelineAfterForeground(), 320);

                return;
            }
            resumeLock = true;
            stopAttendanceLoop();
            cameraLive = false;
            btnStart.classList.add('d-none');

            /** Brief beat so iOS tears down GPU capture cleanly */
            await new Promise((r) => {
                window.setTimeout(r, isLikelyIOS ? 120 : 40);
            });

            stopCameraTracks();

            try {
                statusEl.textContent = 'Reconnecting camera…';
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        /** Hint for faster preview on mobile */
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                    },
                    audio: false,
                });
                video.srcObject = stream;
                bindLayoutSyncOnce();
                await video.play();
                await waitForVideoDimensions(video);
                await ensureAudio();
                resizeOverlay();
                cameraLive = true;
                statusEl.textContent = 'Position your face in the frame.';
                btnStart.classList.add('d-none');
                /** Fast first paint of face box instead of waiting for first interval tick */
                await runBurstDetection();
                startAttendanceLoop();
            } catch (e) {
                console.error(e);
                showResult('danger', 'Camera could not reconnect. Tap “Try camera again”.');
                btnStart.classList.remove('d-none');
                statusEl.textContent = 'Tap “Try camera again”.';
            } finally {
                resumeLock = false;
            }
        }

        async function restartDesktopResume() {
            if (resumeLock || verifying || ! modelsReady) {
                return;
            }
            resumeLock = true;
            stopAttendanceLoop();
            tickBusy = false;
            faceStableSince = null;
            try {
                if (! video.srcObject) {
                    await startCamera();
                    return;
                }
                await video.play();
                await waitForVideoDimensions(video);
                await ensureAudio();
                cameraLive = true;
                resizeOverlay();
                statusEl.textContent = 'Position your face in the frame.';
                await runBurstDetection(4);
                startAttendanceLoop();
            } catch (e) {
                console.error(e);
            } finally {
                resumeLock = false;
            }
        }

        async function startCamera() {
            statusEl.textContent = 'Requesting camera…';
            stopCameraTracks();
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                },
                audio: false,
            });
            video.srcObject = stream;
            bindLayoutSyncOnce();
            await video.play();
            await waitForVideoDimensions(video);
            await ensureAudio();
            cameraLive = true;
            resizeOverlay();
            statusEl.textContent = 'Position your face in the frame.';
            btnStart.classList.add('d-none');
            await runBurstDetection();
            startAttendanceLoop();
        }

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                stopAttendanceLoop();
                cameraLive = false;
                tickBusy = false;
                faceStableSince = null;
                if (aggressiveCameraLifecycle) {
                    if (! verifying) {
                        stopCameraTracks();
                    }
                } else {
                    try {
                        video.pause();
                    } catch (ignorePause) {
                        /* noop */
                    }
                }

                return;
            }

            if (! modelsReady) {
                return;
            }

            if (aggressiveCameraLifecycle) {
                resumePipelineAfterForeground();
            } else {
                restartDesktopResume();
            }
        });

        window.addEventListener('pageshow', (event) => {
            if (! event.persisted || ! modelsReady) {
                return;
            }

            if (aggressiveCameraLifecycle) {
                resumePipelineAfterForeground();
            } else {
                restartDesktopResume();
            }
        });

        window.addEventListener('focus', () => {
            if (! modelsReady || document.visibilityState !== 'visible' || verifying || resumeLock) {
                return;
            }

            if (aggressiveCameraLifecycle && ! video.srcObject) {
                resumePipelineAfterForeground();

                return;
            }

            if (! aggressiveCameraLifecycle && ! video.srcObject) {
                restartDesktopResume();
            }
        });

        btnStart.addEventListener('click', () => {
            resultEl.classList.add('d-none');
            startCamera().catch((err) => {
                console.error(err);
                showResult('danger', 'Unable to access the webcam. Grant permission and try again.');
                btnStart.classList.remove('d-none');
                statusEl.textContent = 'Tap “Try camera again” after allowing access.';
            });
        });

        async function beginKiosk() {
            await loadModels();
            if (!modelsReady) {
                return;
            }
            statusEl.textContent = 'Starting camera…';
            try {
                await startCamera();
            } catch (err) {
                console.error(err);
                showResult('danger', 'Unable to access the webcam. Grant permission and try again.');
                btnStart.classList.remove('d-none');
                statusEl.textContent = 'Tap “Try camera again” after allowing access.';
            }
        }

        beginKiosk();
    });
</script>
</body>
</html>
