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

        /* Admin shell: fixed sidebar, scrollable main only */
        .admin-shell {
            display: flex;
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
        }
        .admin-shell__sidebar {
            flex: 0 0 auto;
            width: clamp(220px, 18vw, 280px);
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .admin-shell__sidebar-inner {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            height: 100%;
        }
        .admin-shell__sidebar-nav {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
        }
        .admin-shell__main {
            flex: 1 1 auto;
            min-width: 0;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light overflow-hidden">
<div class="admin-shell">
        <aside class="admin-shell__sidebar px-0 bg-dark text-white shadow">
            <div class="admin-shell__sidebar-inner">
                <div class="p-4 border-bottom border-secondary flex-shrink-0">
                    <div class="fw-semibold">Face Attendance</div>
                    <small class="text-white-50">Admin panel</small>
                </div>
                <nav class="admin-shell__sidebar-nav nav flex-column px-2 py-3 gap-1">
                    <a class="nav-link rounded px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-secondary text-white' : 'text-white-50' }}"
                       href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="nav-link rounded px-3 py-2 {{ request()->routeIs('admin.staff.*') ? 'bg-secondary text-white' : 'text-white-50' }}"
                       href="{{ route('admin.staff.index') }}">Staff</a>
                    <a class="nav-link rounded px-3 py-2 {{ request()->routeIs('admin.attendance.*') ? 'bg-secondary text-white' : 'text-white-50' }}"
                       href="{{ route('admin.attendance.index') }}">Attendance</a>
                    <a class="nav-link rounded px-3 py-2 {{ request()->routeIs('admin.reports.*') ? 'bg-secondary text-white' : 'text-white-50' }}"
                       href="{{ route('admin.reports.attendance') }}">Reports</a>
                </nav>
                <div class="p-3 border-top border-secondary flex-shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-light w-100" type="submit">Logout</button>
                    </form>
                </div>
            </div>
        </aside>
        <main class="admin-shell__main px-4 py-4 bg-light">
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
