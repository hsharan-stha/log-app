<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Finance') — {{ config('app.name', 'School Portal') }}</title>
    <link href="{{ asset('vendor/bootstrap/5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .portal-nav { background: #0f766e; }
        .portal-nav .nav-link { color: rgba(255,255,255,.75); }
        .portal-nav .nav-link.active, .portal-nav .nav-link:hover { color: #fff; }
    </style>
    @stack('styles')
</head>
<body>
<nav class="portal-nav navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('finance.dashboard') }}">ABIS Finance</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#financeNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="financeNav">
            <ul class="navbar-nav me-auto gap-lg-2">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}" href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('finance.invoices.*') ? 'active' : '' }}" href="{{ route('finance.invoices.index') }}">Invoices</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('finance.fee-types.*') ? 'active' : '' }}" href="{{ route('finance.fee-types.index') }}">Fee types</a></li>
                @if(auth()->user()?->isAdmin())
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                @endif
            </ul>
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <span class="text-white-50 small d-none d-md-inline">{{ auth()->user()?->name }}</span>
                <a class="btn btn-sm btn-outline-light {{ request()->routeIs('password.*') ? 'active' : '' }}" href="{{ route('password.edit') }}">Password</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-light" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>
<main class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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
