<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>School attendance — {{ config('app.name', 'ABIS Portal') }}</title>
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
        .kot-kiosk {
            height: 100dvh;
            max-height: 100dvh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: #eef3f8;
            color: #1c2834;
            font-family: "Segoe UI", "Hiragino Sans", "Noto Sans JP", sans-serif;
        }
        .kot-top {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1.25rem;
            padding-top: max(0.85rem, env(safe-area-inset-top, 0));
            background: linear-gradient(180deg, #0a4f86 0%, #083e6b 100%);
            color: #fff;
        }
        .kot-brand { display: flex; align-items: center; gap: 0.75rem; min-width: 0; }
        .kot-mark {
            width: 42px; height: 42px; border-radius: 10px;
            background: #fff; color: #0a4f86;
            display: grid; place-items: center;
            font-weight: 800; letter-spacing: -0.04em;
        }
        .kot-product { font-size: 1.05rem; font-weight: 700; line-height: 1.1; }
        .kot-place { font-size: 0.78rem; opacity: 0.85; margin-top: 0.15rem; }
        .kot-clock { text-align: right; }
        .kot-date { font-size: 0.85rem; opacity: 0.9; }
        .kot-time {
            font-size: clamp(2rem, 5vw, 3.1rem);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.04em;
            line-height: 1;
        }
        .kot-main {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 1rem 0.6rem;
        }
        .kot-stage {
            width: min(920px, 100%);
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
        }
        .attendance-kiosk__video-wrap {
            position: relative;
            flex: 1 1 auto;
            border-radius: 18px;
            overflow: hidden;
            background: #101820;
            box-shadow: 0 12px 32px rgba(8, 40, 70, 0.18);
            border: 6px solid #fff;
        }
        .attendance-kiosk__video-wrap video,
        .attendance-kiosk__video-wrap #face-overlay {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }
        .attendance-kiosk__video-wrap video { object-fit: cover; }
        .attendance-kiosk__video-wrap #face-overlay { pointer-events: none; z-index: 2; }
        .kot-guide {
            position: absolute;
            inset: 8% 22%;
            border: 3px solid rgba(255, 255, 255, 0.85);
            border-radius: 50%;
            box-shadow: 0 0 0 999px rgba(8, 30, 52, 0.28);
            z-index: 3;
            pointer-events: none;
        }
        .kot-status {
            font-size: 1.15rem;
            font-weight: 650;
            text-align: center;
            color: #0a4f86;
            min-height: 1.4rem;
        }
        .kot-result {
            width: min(920px, 100%);
            border-radius: 12px;
            text-align: center;
            font-size: 1.05rem;
            font-weight: 700;
            padding: 0.7rem 1rem;
            margin: 0;
        }
        .kot-result--success { background: #e5f6ea; color: #146c43; }
        .kot-result--danger { background: #fdecea; color: #b02a37; }
        .kot-foot {
            flex: 0 0 auto;
            text-align: center;
            font-size: 0.75rem;
            color: #6b7c8f;
            padding: 0.35rem 0.75rem max(0.5rem, env(safe-area-inset-bottom, 0));
        }
        .kot-auth { font-size: 0.75rem; opacity: 0.9; }
        .kot-auth a, .kot-auth button { color: #fff; }
        @media (max-width: 767.98px) {
            .kot-top { padding: 0.65rem 0.75rem; gap: 0.5rem; }
            .kot-mark { width: 34px; height: 34px; font-size: 0.8rem; }
            .kot-product { font-size: 0.92rem; }
            .kot-place { font-size: 0.68rem; }
            .kot-date { font-size: 0.68rem; }
            .kot-time { font-size: clamp(1.35rem, 7vw, 1.8rem); }
            .kot-main { padding: 0.55rem 0.55rem 0.35rem; gap: 0.45rem; }
            .attendance-kiosk__video-wrap { border-width: 3px; border-radius: 14px; }
            .kot-guide { display: none; }
            .kot-status { font-size: 0.95rem; }
            .kot-result { font-size: 0.9rem; padding: 0.5rem 0.7rem; }
            .kot-foot { font-size: 0.68rem; }
        }
        @media (min-width: 768px) and (max-width: 1199.98px) {
            .kot-stage { width: min(720px, 100%); }
            .kot-guide { inset: 8% 24%; }
            .kot-time { font-size: clamp(2.2rem, 4.5vw, 2.8rem); }
        }
        @media (min-width: 1200px) {
            .kot-top { padding: 1.1rem 1.75rem; }
            .kot-stage, .kot-result { width: min(1100px, 100%); }
            .kot-time { font-size: 3.4rem; }
            .kot-status { font-size: 1.35rem; }
            .kot-guide { inset: 7% 28%; }
        }
        @media (max-width: 767.98px) and (orientation: landscape) {
            .kot-main { flex-direction: row; flex-wrap: wrap; align-items: stretch; }
            .kot-stage { width: 58%; flex: 1 1 58%; }
            .kot-status, .kot-result, #btn-start { width: min(38%, 16rem); align-self: center; }
        }
    </style>
</head>
<body class="kot-kiosk">
<header class="kot-top">
    <div class="kot-brand">
        <div class="kot-mark" aria-hidden="true">AB</div>
        <div>
            <div class="kot-product">{{ ($kioskLocation ?? 'school') === 'bus' ? 'Bus attendance' : 'Face attendance' }}</div>
            <div class="kot-place">
                {{ $kioskName ?? 'School kiosk' }}
                @if(auth()->check())
                    <span class="kot-auth">· {{ auth()->user()->name }}
                        <a href="{{ route('attendance.home') }}">Home</a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button class="btn btn-link btn-sm p-0 align-baseline" type="submit">Sign out</button>
                        </form>
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div class="kot-clock">
        <div id="kot-date" class="kot-date"></div>
        <div id="kot-time" class="kot-time">--:--:--</div>
    </div>
</header>

<div class="kot-main">
    <div class="kot-stage">
        <div class="attendance-kiosk__video-wrap">
            <video id="video" autoplay muted playsinline webkit-playsinline></video>
            <canvas id="face-overlay" aria-hidden="true"></canvas>
            <div class="kot-guide" aria-hidden="true"></div>
        </div>
    </div>
    <canvas id="snapshot-canvas" class="d-none" aria-hidden="true"></canvas>

    <div id="status" class="kot-status">Loading face models…</div>
    <div id="result" class="kot-result d-none" role="alert"></div>
    <button id="btn-start" class="btn btn-outline-secondary btn-sm px-3 d-none" type="button">Try camera again</button>
</div>

<p class="kot-foot mb-0">Look at the camera. Clock-in and clock-out are recorded automatically.</p>
<script>
    (function () {
        const timeEl = document.getElementById('kot-time');
        const dateEl = document.getElementById('kot-date');
        const tick = () => {
            const now = new Date();
            timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            dateEl.textContent = now.toLocaleDateString([], { weekday: 'short', year: 'numeric', month: 'long', day: 'numeric' });
        };
        tick();
        setInterval(tick, 1000);
    })();
</script>

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
        @if(session('kiosk_plain_token'))
        try { localStorage.setItem('kiosk_device_token', @json(session('kiosk_plain_token'))); } catch (e) {}
        @endif

        function kioskDeviceToken() {
            try {
                const fromStore = localStorage.getItem('kiosk_device_token');
                if (fromStore) return fromStore;
            } catch (e) {}
            const match = document.cookie.match(/(?:^|; )kiosk_device_token=([^;]*)/);
            return match ? decodeURIComponent(match[1]) : '';
        }

        const STABILITY_MS = 2600;
        const DETECT_INTERVAL_MS = 420;
        const COOLDOWN_MS = 5500;
        const LOST_FACE_RESET_MS = 900;

        let modelsReady = false;
        let cameraLive = false;
        let verifying = false;
        let tickBusy = false;
        let cooldownUntil = 0;
        let faceStableSince = null;
        let lastFaceSeenAt = 0;
        let tickTimer = null;

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

        const thankYouAudioUrl = @json(asset('sounds/arigatou-gozaimasu.wav')).concat('?v=google1');
        let thankYouCtx = null;
        let thankYouBuffer = null;
        let thankYouFallback = null;

        function getThankYouContext() {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) {
                return null;
            }
            if (!thankYouCtx) {
                thankYouCtx = new Ctx();
            }
            return thankYouCtx;
        }

        async function unlockThankYouAudio() {
            const ctx = getThankYouContext();
            if (ctx && ctx.state === 'suspended') {
                try {
                    await ctx.resume();
                } catch (e) {}
            }
        }

        async function preloadThankYouAudio() {
            try {
                thankYouFallback = new Audio(thankYouAudioUrl);
                thankYouFallback.preload = 'auto';
                thankYouFallback.load();

                const ctx = getThankYouContext();
                if (!ctx) {
                    return;
                }
                const response = await fetch(thankYouAudioUrl, { cache: 'force-cache' });
                const arrayBuffer = await response.arrayBuffer();
                thankYouBuffer = await ctx.decodeAudioData(arrayBuffer.slice(0));
                await unlockThankYouAudio();
            } catch (e) {
                console.warn('Thank-you audio preload failed', e);
            }
        }

        function playThankYouVoice() {
            // Play immediately — no await, no UI work before this.
            try {
                const ctx = getThankYouContext();
                if (ctx && thankYouBuffer) {
                    if (ctx.state === 'suspended') {
                        ctx.resume().catch(() => {});
                    }
                    const source = ctx.createBufferSource();
                    source.buffer = thankYouBuffer;
                    source.connect(ctx.destination);
                    source.start(0);
                    return;
                }
            } catch (e) {
                console.warn('WebAudio thank-you failed', e);
            }

            try {
                if (!thankYouFallback) {
                    thankYouFallback = new Audio(thankYouAudioUrl);
                }
                thankYouFallback.pause();
                thankYouFallback.currentTime = 0;
                const playPromise = thankYouFallback.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch((err) => console.warn('Thank-you audio blocked', err));
                }
            } catch (e) {
                console.warn('Thank-you voice failed', e);
            }
        }

        function isAttendanceSuccessAction(action) {
            return action === 'checkin'
                || action === 'checkout'
                || action === 'school_checkin'
                || action === 'school_checkout'
                || action === 'bus_checkin'
                || action === 'bus_checkout';
        }

        async function ensureVideoPlaying() {
            if (!video.srcObject) {
                return false;
            }
            try {
                if (video.paused) {
                    await video.play();
                }
            } catch (e) {
                return false;
            }
            return video.readyState >= 2 && video.videoWidth > 0;
        }

        function resizeOverlay() {
            const w = video.clientWidth;
            const h = video.clientHeight;
            if (!w || !h) {
                return;
            }
            if (overlay.width !== w || overlay.height !== h) {
                overlay.width = w;
                overlay.height = h;
            }
        }

        function showResult(type, message) {
            resultEl.className = `kot-result kot-result--${type === 'success' ? 'success' : 'danger'}`;
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
                        'X-Kiosk-Device-Token': kioskDeviceToken(),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ descriptor, snapshot }),
                });

                const payload = await response.json();

                if (!response.ok || payload.ok === false) {
                    throw new Error(payload.message || 'Verification failed.');
                }

                // Sound first — right when check-in/out is confirmed, before UI updates.
                if (isAttendanceSuccessAction(payload.action)) {
                    playThankYouVoice();
                }

                const name = payload.staff_name ? `${payload.staff_name}: ` : '';
                statusEl.textContent = 'Success';
                showResult('success', `${name}${payload.message}`);

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

            const playing = await ensureVideoPlaying();
            if (!playing) {
                statusEl.textContent = 'Starting camera feed…';
                drawOverlay(null);
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
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 }))
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
            overlayCtx.clearRect(0, 0, overlay.width, overlay.height);
        }

        async function waitForVideoFrames(timeoutMs = 8000) {
            const started = Date.now();
            while (Date.now() - started < timeoutMs) {
                if (await ensureVideoPlaying()) {
                    return true;
                }
                await new Promise((r) => setTimeout(r, 150));
            }
            return video.videoWidth > 0;
        }

        async function startCamera() {
            statusEl.textContent = 'Requesting camera…';

            if (video.srcObject) {
                try {
                    video.srcObject.getTracks().forEach((t) => t.stop());
                } catch (e) {}
                video.srcObject = null;
            }

            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
                audio: false,
            });
            video.srcObject = stream;
            video.muted = true;
            video.playsInline = true;
            video.setAttribute('playsinline', 'true');
            video.setAttribute('webkit-playsinline', 'true');
            video.setAttribute('autoplay', 'true');

            try {
                await video.play();
            } catch (e) {
                // Some browsers pause until frames arrive; waitForVideoFrames will keep trying.
                console.warn('Initial video.play() deferred', e);
            }

            cameraLive = true;
            btnStart.classList.add('d-none');
            resizeOverlay();
            window.addEventListener('resize', resizeOverlay);
            video.addEventListener('loadedmetadata', resizeOverlay);
            video.addEventListener('loadeddata', () => {
                ensureVideoPlaying();
                resizeOverlay();
            });
            // If the browser pauses the stream (common on tablets), resume without a tap.
            video.addEventListener('pause', () => {
                if (cameraLive) {
                    ensureVideoPlaying();
                }
            });

            // Unlock audio as soon as camera is live so thank-you has no delay.
            unlockThankYouAudio();

            startAttendanceLoop();

            const ready = await waitForVideoFrames();
            if (ready) {
                statusEl.textContent = 'Position your face in the frame.';
                resizeOverlay();
            } else {
                statusEl.textContent = 'Camera started — waiting for video…';
            }
        }

        btnStart.addEventListener('click', () => {
            resultEl.classList.add('d-none');
            startCamera().catch((err) => {
                console.error(err);
                showResult('danger', 'Unable to access the webcam. Grant permission and try again.');
                btnStart.classList.remove('d-none');
                statusEl.textContent = 'Tap “Try camera again” after allowing access.';
            });
        });

        // Keep scanning when returning to the kiosk tab / unlocking the tablet.
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && cameraLive) {
                ensureVideoPlaying();
                if (!tickTimer) {
                    startAttendanceLoop();
                }
            }
        });

        async function beginKiosk() {
            // Decode thank-you WAV into memory so playback is instant on success.
            preloadThankYouAudio();

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
