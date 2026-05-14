<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Face Attendance') }}</title>
    <link href="{{ asset('vendor/bootstrap/5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h1 class="h3 mb-3">Face attendance</h1>
                    <p class="text-muted">A lightweight Laravel kiosk for check-ins and an admin console for your team.</p>
                    <div class="d-grid gap-2 mt-4">
                        <a class="btn btn-primary btn-lg" href="{{ url('/attendance') }}">Open staff kiosk</a>
                        @if (Route::has('login'))
                            <a class="btn btn-outline-secondary" href="{{ route('login') }}">Sign in</a>
                            <span class="text-muted small d-block mt-2">Staff: sign in to register your face. Admins: full console.</span>
                        @endif
                    </div>
                    <p class="small text-muted mt-4 mb-0">Default admin: admin@example.com / password (change after first login).</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('vendor/bootstrap/5.3.3/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
