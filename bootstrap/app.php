<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'staff' => \App\Http\Middleware\EnsureUserIsStaff::class,
            'teacher' => \App\Http\Middleware\EnsureUserIsTeacher::class,
            'student' => \App\Http\Middleware\EnsureUserIsStudent::class,
            'guardian' => \App\Http\Middleware\EnsureUserIsGuardian::class,
            'attendance' => \App\Http\Middleware\EnsureUserIsAttendance::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'kiosk' => \App\Http\Middleware\EnsureKioskDevice::class,
        ]);

        $middleware->encryptCookies(except: [
            \App\Http\Middleware\EnsureKioskDevice::COOKIE,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
