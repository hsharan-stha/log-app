<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My account') — {{ config('app.name', 'Face Attendance') }}</title>
    <link href="{{ asset('vendor/bootstrap/5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-light">
<header class="border-bottom bg-white shadow-sm">
    <div class="container py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <div class="fw-semibold">{{ config('app.name', 'Face Attendance') }}</div>
            <small class="text-muted">Staff — register your face for the kiosk</small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('attendance.scan') }}">Attendance kiosk</a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('password.edit') }}">Change password</a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-dark" type="submit">Logout</button>
            </form>
        </div>
    </div>
</header>
<main class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
<script src="{{ asset('vendor/bootstrap/5.3.3/js/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body>
</html>
