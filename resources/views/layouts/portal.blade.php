<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') — {{ config('app.name', 'School Portal') }}</title>
    <link href="{{ asset('vendor/bootstrap/5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .portal-nav { background: #1e293b; }
        .portal-nav .nav-link { color: rgba(255,255,255,.7); }
        .portal-nav .nav-link.active, .portal-nav .nav-link:hover { color: #fff; }
        .att-snap { text-align: center; max-width: 148px; margin: 0 auto; }
        .att-snap--lg .att-snap__frame { width: 118px; height: 118px; }
        .att-snap--sm .att-snap__frame { width: 72px; height: 72px; }
        .att-snap__frame {
            display: block; margin: 0 auto 0.5rem; border-radius: 14px; overflow: hidden;
            background: #e9ecef; box-shadow: 0 4px 14px rgba(0,0,0,.08); border: 1px solid rgba(0,0,0,.06);
        }
        .att-snap__img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .att-snap__placeholder {
            width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
            font-size: 0.7rem; font-weight: 600; color: #8b939e; text-transform: uppercase;
            background: #eef1f5;
        }
        .att-snap__time {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.25rem 0.65rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600;
        }
        .att-snap__time--in { background: rgba(25,135,84,.12); color: #146c43; }
        .att-snap__time--out { background: rgba(13,110,253,.12); color: #0a58ca; }
        .att-snap__time--empty { background: #f1f3f7; color: #8b939e; font-weight: 500; }
    </style>
    @stack('styles')
</head>
<body>
@php
    $role = auth()->user()?->role;
    $home = auth()->user()?->homeRoute() ?? 'login';
@endphp
<nav class="portal-nav navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route($home) }}">ABIS Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="portalNav">
            <ul class="navbar-nav me-auto gap-lg-2">
                @if($role === 'teacher')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.courses.*') || request()->routeIs('teacher.lessons.*') ? 'active' : '' }}" href="{{ route('teacher.courses.index') }}">Courses</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.students') ? 'active' : '' }}" href="{{ route('teacher.students') }}">My class</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.attendance') ? 'active' : '' }}" href="{{ route('teacher.attendance') }}">My attendance</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.notices*') ? 'active' : '' }}" href="{{ route('teacher.notices') }}">Notices</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.face') ? 'active' : '' }}" href="{{ route('teacher.face') }}">My face</a></li>
                @elseif($role === 'student')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.courses.*') || request()->routeIs('student.lessons.*') ? 'active' : '' }}" href="{{ route('student.courses.index') }}">Subjects</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.billing.*') ? 'active' : '' }}" href="{{ route('student.billing.index') }}">Billing</a></li>
                @elseif($role === 'guardian')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('guardian.dashboard') ? 'active' : '' }}" href="{{ route('guardian.dashboard') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('guardian.attendance*') ? 'active' : '' }}" href="{{ route('guardian.attendance') }}">Attendance</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('guardian.billing.*') ? 'active' : '' }}" href="{{ route('guardian.billing.index') }}">Billing</a></li>
                @elseif($role === 'attendance')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('attendance.home') ? 'active' : '' }}" href="{{ route('attendance.home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('attendance.setup*') ? 'active' : '' }}" href="{{ route('attendance.setup') }}">Setup kiosk</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('attendance.scan') }}">Face attendance</a></li>
                @elseif(in_array($role, ['staff', 'other'], true))
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('office.dashboard') ? 'active' : '' }}" href="{{ route('office.dashboard') }}">Home</a></li>
                @endif
                @if($role !== 'attendance')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">Messages</a></li>
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
