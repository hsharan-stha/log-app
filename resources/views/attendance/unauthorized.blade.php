<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kiosk not authorized — {{ config('app.name') }}</title>
    <link href="{{ asset('vendor/bootstrap/5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 560px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h4">Face attendance blocked</h1>
            <p class="text-muted">This browser is not a registered school kiosk, or you are not signed in as the attendance operator.</p>
            <ol class="small text-muted mb-3">
                <li>Sign in with the <strong>attendance</strong> account.</li>
                <li>Open <strong>Setup kiosk device</strong> on this tablet once.</li>
                <li>Then open face attendance.</li>
            </ol>
            <hr>
            @auth
                @if(auth()->user()->isAttendance())
                    <a href="{{ route('attendance.setup') }}" class="btn btn-primary">Setup this kiosk</a>
                    <a href="{{ route('attendance.home') }}" class="btn btn-outline-secondary">Attendance home</a>
                @else
                    <p class="small text-muted mb-2">Signed in as {{ auth()->user()->name }} ({{ auth()->user()->role }}). Log out and use the attendance account.</p>
                    <a href="{{ route('login') }}" class="btn btn-primary">Portal login</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Attendance login</a>
            @endauth
        </div>
    </div>
</div>
</body>
</html>
