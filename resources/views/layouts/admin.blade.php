<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name', 'Face Attendance') }}</title>
    <link href="{{ asset('vendor/bootstrap/5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        .table-attendance { --att-photo-lg: 118px; --att-photo-sm: 72px; }
        .table-attendance thead th {
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 600;
            color: #5c6578;
            border-bottom-width: 1px;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        .table-attendance tbody tr { transition: background-color .15s ease; }
        .table-attendance tbody tr:hover { background-color: rgba(13, 110, 253, 0.04); }
        .table-attendance .staff-cell { min-width: 200px; }
        .table-attendance .staff-avatar {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #4f6df5 0%, #7b5cff 100%);
            box-shadow: 0 4px 12px rgba(79, 109, 245, 0.35);
        }
        .table-attendance .staff-avatar.is-compact {
            width: 36px; height: 36px; font-size: 0.9rem; border-radius: 10px;
            box-shadow: 0 2px 8px rgba(79, 109, 245, 0.28);
        }
        .table-attendance .staff-name { font-weight: 600; color: #1a1d24; }
        .table-attendance .date-pill {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: #f1f3f7;
            font-size: 0.875rem;
            font-weight: 500;
            color: #3d4450;
        }
        .att-snap { text-align: center; max-width: 148px; margin: 0 auto; }
        .att-snap--sm .att-snap__time { font-size: 0.72rem; padding: 0.18rem 0.5rem; }
        .att-snap__frame {
            display: block;
            margin: 0 auto 0.5rem;
            border-radius: 14px;
            overflow: hidden;
            background: #e9ecef;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }
        .att-snap--lg .att-snap__frame { width: var(--att-photo-lg); height: var(--att-photo-lg); }
        .att-snap--sm .att-snap__frame { width: var(--att-photo-sm); height: var(--att-photo-sm); }
        .att-snap__img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .2s ease;
        }
        .att-snap__link:hover .att-snap__img { transform: scale(1.04); }
        .att-snap__placeholder {
            width: 100%; height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
            color: #8b939e;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: repeating-linear-gradient(
                -45deg,
                #eef1f5,
                #eef1f5 6px,
                #f6f8fb 6px,
                #f6f8fb 12px
            );
        }
        .att-snap__time {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }
        .att-snap__time--in { background: rgba(25, 135, 84, 0.12); color: #146c43; }
        .att-snap__time--out { background: rgba(13, 110, 253, 0.12); color: #0a58ca; }
        .att-snap__time--empty { background: #f1f3f7; color: #8b939e; font-weight: 500; }

        /* Admin shell */
        .admin-shell {
            display: flex;
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            --sidebar-bg: #0f172a;
            --sidebar-border: rgba(148, 163, 184, 0.12);
            --sidebar-muted: #94a3b8;
            --sidebar-text: #e2e8f0;
            --sidebar-active: #38bdf8;
            --sidebar-active-bg: rgba(56, 189, 248, 0.12);
        }
        .admin-shell__sidebar {
            flex: 0 0 auto;
            width: clamp(240px, 18vw, 288px);
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
            border-right: 1px solid var(--sidebar-border);
            color: var(--sidebar-text);
        }
        .admin-shell__sidebar-inner {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            height: 100%;
        }
        .admin-shell__brand {
            flex-shrink: 0;
            padding: 1.25rem 1.15rem 1.1rem;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .admin-shell__brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            color: #0f172a;
            background: linear-gradient(145deg, #e2e8f0 0%, #38bdf8 100%);
            flex-shrink: 0;
        }
        .admin-shell__brand-title {
            font-size: 0.95rem;
            font-weight: 650;
            line-height: 1.2;
            color: #f8fafc;
        }
        .admin-shell__brand-sub {
            font-size: 0.72rem;
            color: var(--sidebar-muted);
            margin-top: 0.15rem;
        }
        .admin-shell__sidebar-nav {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 0.85rem 0.75rem 1rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(148,163,184,.35) transparent;
        }
        .admin-nav-group {
            margin: 1rem 0.55rem 0.4rem;
            font-size: 0.65rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 650;
        }
        .admin-nav-group:first-child { margin-top: 0.15rem; }
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.55rem 0.75rem;
            margin-bottom: 0.15rem;
            border-radius: 0.7rem;
            color: var(--sidebar-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color .15s ease, color .15s ease;
            border: 1px solid transparent;
        }
        .admin-nav-link:hover {
            color: #f1f5f9;
            background: rgba(148, 163, 184, 0.08);
        }
        .admin-nav-link.is-active {
            color: #f0f9ff;
            background: var(--sidebar-active-bg);
            border-color: rgba(56, 189, 248, 0.18);
            box-shadow: inset 3px 0 0 var(--sidebar-active);
        }
        .admin-nav-link svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            opacity: 0.85;
        }
        .admin-nav-link.is-active svg { opacity: 1; color: var(--sidebar-active); }
        .admin-shell__footer {
            flex-shrink: 0;
            padding: 0.9rem 0.85rem 1rem;
            border-top: 1px solid var(--sidebar-border);
            background: rgba(0,0,0,0.18);
        }
        .admin-shell__user {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.75rem;
            min-width: 0;
        }
        .admin-shell__user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #e2e8f0;
            background: #1e293b;
            border: 1px solid rgba(148,163,184,0.25);
            flex-shrink: 0;
        }
        .admin-shell__user-meta { min-width: 0; }
        .admin-shell__user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .admin-shell__user-role {
            font-size: 0.7rem;
            color: var(--sidebar-muted);
        }
        .admin-shell__logout {
            width: 100%;
            border: 1px solid rgba(148,163,184,0.22);
            background: transparent;
            color: #cbd5e1;
            border-radius: 0.65rem;
            padding: 0.45rem 0.75rem;
            font-size: 0.82rem;
            font-weight: 500;
        }
        .admin-shell__logout:hover {
            background: rgba(248, 113, 113, 0.12);
            border-color: rgba(248, 113, 113, 0.35);
            color: #fecaca;
        }
        .admin-shell__main {
            flex: 1 1 auto;
            min-width: 0;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            background: #f8fafc;
        }
        .academic-step-card { border-radius: 1rem; }
    </style>
    @stack('styles')
</head>
<body class="bg-light overflow-hidden">
@php
    $adminUser = auth()->user();
    $adminInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($adminUser?->name ?? 'A', 0, 1));
    $isHr = $adminUser?->isHr() ?? false;
    $isAdmin = $adminUser?->isAdmin() ?? false;
@endphp
<div class="admin-shell">
        <aside class="admin-shell__sidebar">
            <div class="admin-shell__sidebar-inner">
                <div class="admin-shell__brand">
                    <div class="admin-shell__brand-mark" aria-hidden="true">AB</div>
                    <div>
                        <div class="admin-shell__brand-title">ABIS Portal</div>
                        <div class="admin-shell__brand-sub">{{ $isHr ? 'HR office' : 'School administration' }}</div>
                    </div>
                </div>

                <nav class="admin-shell__sidebar-nav" aria-label="Admin">
                    @if($isAdmin)
                    <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                       href="{{ route('admin.dashboard') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5Z"/></svg>
                        Dashboard
                    </a>
                    @endif

                    <div class="admin-nav-group">Academics</div>
                    <a class="admin-nav-link {{ request()->routeIs('admin.academics.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.academics.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                        Setup hub
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.classes.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.classes.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5h16v14H4Z"/><path d="M8 9h8M8 13h5"/></svg>
                        Classes
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.staff.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.staff.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1"/><circle cx="9.5" cy="7.5" r="3"/><path d="M20 19v-1a3.5 3.5 0 0 0-2.5-3.35"/><path d="M16.5 4.7a3 3 0 0 1 0 5.6"/></svg>
                        Teachers
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.subjects.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.subjects.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h9a3 3 0 0 1 3 3v13H8a3 3 0 0 0-3 3V4Z"/><path d="M17 7h2a2 2 0 0 1 2 2v11"/></svg>
                        Subjects
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.courses.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.courses.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 4 4 8l8 4 8-4-8-4Z"/><path d="M4 12l8 4 8-4"/><path d="M4 16l8 4 8-4"/></svg>
                        Courses
                    </a>

                    <div class="admin-nav-group">People</div>
                    <a class="admin-nav-link {{ request()->routeIs('admin.people.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.people.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                        Setup hub
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.students.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.students.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="M5 11v5c0 1.5 3.1 3 7 3s7-1.5 7-3v-5"/></svg>
                        Students
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.guardians.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.guardians.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>
                        Guardians
                    </a>

                    @if($isAdmin)
                    <div class="admin-nav-group">Attendance</div>
                    <a class="admin-nav-link {{ request()->routeIs('admin.attendance.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.attendance.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M8 3.5V7M16 3.5V7M3.5 10h17"/></svg>
                        Calendar
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.reports.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 19V9M10 19V5M15 19v-7M20 19V8"/></svg>
                        Reports
                    </a>

                    <div class="admin-nav-group">School</div>
                    <a class="admin-nav-link {{ request()->routeIs('admin.notices.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.notices.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 6h11l3 3v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/><path d="M8 11h8M8 15h5"/></svg>
                        Notices
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.office-users.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.office-users.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1"/><circle cx="9.5" cy="7.5" r="3"/><path d="M20 19v-1a3.5 3.5 0 0 0-2.5-3.35"/><path d="M16.5 4.7a3 3 0 0 1 0 5.6"/></svg>
                        Office users
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('finance.*') ? 'is-active' : '' }}"
                       href="{{ route('finance.dashboard') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16v10H4z"/><path d="M8 11h8M8 15h5"/></svg>
                        Billing
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('messages.*') ? 'is-active' : '' }}"
                       href="{{ route('messages.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 6h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H9l-4 3v-3H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/></svg>
                        Messages
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.devices.*') ? 'is-active' : '' }}"
                       href="{{ route('admin.devices.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="3" width="12" height="18" rx="2"/><path d="M11 17h2"/></svg>
                        Kiosk devices
                    </a>
                    @else
                    <div class="admin-nav-group">School</div>
                    <a class="admin-nav-link {{ request()->routeIs('messages.*') ? 'is-active' : '' }}"
                       href="{{ route('messages.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 6h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H9l-4 3v-3H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/></svg>
                        Messages
                    </a>
                    @endif
                </nav>

                <div class="admin-shell__footer">
                    <div class="admin-shell__user">
                        <div class="admin-shell__user-avatar" aria-hidden="true">{{ $adminInitial }}</div>
                        <div class="admin-shell__user-meta">
                            <div class="admin-shell__user-name">{{ $adminUser?->name ?? 'User' }}</div>
                            <div class="admin-shell__user-role">{{ $adminUser?->roleLabel() ?? '' }}</div>
                        </div>
                    </div>
                    <a href="{{ route('password.edit') }}" class="admin-nav-link {{ request()->routeIs('password.*') ? 'is-active' : '' }}" style="margin-bottom:0.55rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                        Change password
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="admin-shell__logout" type="submit">Sign out</button>
                    </form>
                </div>
            </div>
        </aside>
        <main class="admin-shell__main px-4 py-4">
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
</div>
<script src="{{ asset('vendor/bootstrap/5.3.3/js/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body>
</html>
